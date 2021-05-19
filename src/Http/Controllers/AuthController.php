<?php

namespace ppe\dvtinh\Http\Controllers;
use ppe\dvtinh\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends \App\Http\Controllers\Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'string|email|required|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        if($validator->errors()->first()){
            return response()->json(['code' => 422, 'message' => $validator->errors()->first()], 422);
        }
        $data = $request->all();
        $user = User::where('email', $data['email'])
            ->first();
        if($user){
            $data = [
                'password' => $data['password'],
                'name' => $user->name ? $user->name : $data['name'],
            ];
            User::where('id', $user->id)->update($data);
        }
        else{
            $user = User::create($data);
        }
        $access_token = $user->createToken('authToken')->accessToken;

        return response(['user' => $user, 'access_token' => $access_token]);
    }
}
