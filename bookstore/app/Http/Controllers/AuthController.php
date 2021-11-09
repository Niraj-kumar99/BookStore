<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Validator;

class AuthController extends Controller
{
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function registerUser(Request $request) {
        $validator = Validator::make($request->all(), [
            'usertype' => 'required|string|between:2,20',
            'firstname' => 'required|string|between:2,100',
            'lastname' => 'required|string|between:2,100',
            'phone_no' => 'required|string|min:9',
            'email' => 'required|string|email|max:100',
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|same:password',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::where('email', $request->email)->first();
        if ($user)
        {
            return response()->json(['message' => 'The email has already been taken'],401);
        }

        $user = User::create([
            'usertype' => $request->usertype,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'phone_no' => $request->phone_no,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            ]);

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }


    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
    */
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            /*'user' => auth()->user(), */
            'message' => 'User successfully login'
        ]);
    }


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginUser(Request $request){
    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Mail or password incorrect'], 401);
        }

        return $this->createNewToken($token);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function logoutuser() {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }


    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function userProfile() {
        return response()->json(auth()->user());
    }
}
