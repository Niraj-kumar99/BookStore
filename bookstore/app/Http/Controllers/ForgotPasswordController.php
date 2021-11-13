<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordReset;
use App\Http\Requests\SendEmailRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use Validator;
use Illuminate\Support\Facades\Log;


class ForgotPasswordController extends Controller
{
    /*
     *This API Takes the email id request and validates it and check whether given email id
     *is in DB or not.
     *if it is not,it returns failure message with the appropriate response code and 
     *checks for password reset model once the email is valid and by creating an object of the 
     *sendEmail function which is there in App\Http\Requests\SendEmailRequest and calling the function
     * by passing args and successfully sending the password reset link to the specified email id.
    */

    public function forgotPasswordUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user)
        {
            return response()->json([
                'message' => 'can not find the email address'
            ],404);
        }
        
        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],

            [
                'email' => $user->email,
                'token' => JWTAuth::fromUser($user)
            ]
        );
        
        if ($user && $passwordReset) 
        {
            $sendEmail = new SendEmailRequest();
            $sendEmail->sendMail($user->email,$passwordReset->token);
        }

        return response()->json(['message' => 'password reset link genereted in mail'],205);

    }

    public function resetPassword(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'new_password' => 'min:6|required|',
            'confirm_password' => 'required|same:new_password'
        ]);

        if ($validate->fails())
        {
            return response()->json([
                 'message' => "Password doesn't match"
                ],400);
        }
        
        $passwordReset = PasswordReset::where('token', $request->token)->first();


        if (!$passwordReset) 
        {
            return response()->json(['message' => 'This token is invalid'],401);
        }

        $user = User::where('email', $passwordReset->email)->first();

        if (!$user)
        {
            Log::error('Email not found.', ['id' => $request->email]);
            return response()->json([
                'message' => "we can't find the user with that e-mail address"
            ], 400);
        }
        else
        {
            $user->password = bcrypt($request->new_password);
            $user->save();
            $passwordReset->delete();
            Log::info('Reset Successful : '.'Email Id :'.$request->email );
            
            return response()->json([
                'status' => 201, 
                'message' => 'Password reset successfull!'
            ],201);
        }
    }
}
