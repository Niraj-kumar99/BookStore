<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Admin;
use App\Models\PasswordReset;
use App\Http\Requests\SendEmailRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use Validator;

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

    public function forgotPasswordAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin)
        {
            return response()->json([
                'message' => 'can not find the email address'
            ],404);
        }
        
        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $admin->email],

            [
                'email' => $admin->email,
                'token' => JWTAuth::fromUser($admin)
            ]
        );
        
        if ($admin && $passwordReset) 
        {
            $sendEmail = new SendEmailRequest();
            $sendEmail->sendMail($admin->email,$passwordReset->token);
        }

        return response()->json(['message' => 'password reset link genereted in mail'],205);

    }
}
