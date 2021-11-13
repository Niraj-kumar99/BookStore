<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use JWTAuth;
use Validator;

class AddressController extends Controller
{
    public function addAddress(Request $request) {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string|between:2,600',
            'city' => 'required|string|between:2,100',
            'state' => 'required|string|between:2,100',
            'landmark' => 'required|string|between:2,100',
            'addresstype' => 'required|string|between:2,100',
        ]);

        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $currentUser = JWTAuth::parseToken()->authenticate();
        if ($currentUser)
        {
            $address_exist = Address::select('address')->where([
                ['user_id','=',$currentUser->id]
            ])->get();

            if(count($address_exist)!= 0)
            {
                return response()->json([
                    'message' => 'Address alredy present ...'
                ],401);
            }

            $address = new Address;
            $address->user_id = $currentUser->id;
            $address->address = $request->input('address');
            $address->city = $request->input('city');
            $address->state = $request->input('state');
            $address->landmark = $request->input('landmark');
            $address->addresstype = $request->input('addresstype');
            $address->save();
        }
        return response()->json([
            'message' => ' Address Added......'
        ],201);
    }

    public function deleteAddress(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user_id = $request->input('user_id');
        $currentUser = JWTAuth::parseToken()->authenticate();
        $user = $currentUser->addresses()->find($user_id);

        if(!$user)
        {
            return response()->json(['message' => 'User not Found'], 404);
        }
            
        if($user->delete())
        {
            return response()->json(['message' => 'Address deleted Sucessfully'], 201);
        }
        return response()->json(['message' => 'Invalid authorization token' ], 404);
    }

    public function changeAddress(Request $request) {
        $validator = Validator::make($request->all(), [
            'address' => 'string|between:2,600',
            'city' => 'string|between:2,100',
            'state' => 'string|between:2,100',
            'landmark' => 'string|between:2,100',
            'addresstype' => 'string|between:2,100',
        ]);

        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $currentUser = JWTAuth::parseToken()->authenticate();
        
        if($currentUser)
        {
            $address_exist = Address::select('id')->where([
                ['user_id','=',$currentUser->id]
            ])->get();


            if(count($address_exist) == 0)
            {
                return response()->json([
                    'message' => 'Address not present add address first ...'
                ],401);
            }

            $address = Address::where('user_id', $currentUser->id)->first();
            $address->fill($request->all());
            if($address->save())
            {
                return response()->json([
                    'message' => 'Updation done'
                ],201);
            }
        }
        return response()->json([
            'message' => 'User token provided is invalid'
        ],404);
    }

    public function getAddress()
    {
        $currentUser = JWTAuth::parseToken()->authenticate();
        try
        {
            if ($currentUser)
            {
                $user = Address::select('addresses.id', 'addresses.user_id', 'addresses.address', 'addresses.city', 'addresses.state', 'addresses.landmark', 'addresses.addresstype')
                ->where([['addresses.user_id','=',$currentUser->id]])
                ->get();
            }
        }
        catch(InvalidAuthenticationException $e)
        {
            if ($user=='[]')
            {
                return response()->json([
                    'message' => 'Address not found'
                ], 404);
            }
        }
            return response()->json([
                'address' => $user,
                'message' => 'Fetched Address Successfully'
            ], 201);
    }
}