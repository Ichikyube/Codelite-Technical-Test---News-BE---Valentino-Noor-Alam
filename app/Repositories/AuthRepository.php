<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AuthRepository
{
    public function register(array $data): User
    {
        $data = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ];

        return User::create($data);
    }
    public function login(array $data)
    {
        // fetch user data from database based on request(email)
        $user = User::query()
            ->where("email", $data['email'])
            ->firstOr(fn() => response()->json([
                "success" => false,
                "message" => "email is not registered",
                "data" => null
            ]));
        // check user login data
        if (!Auth::attempt($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Login Failed, Invalid Email and Password!'
            ], Response::HTTP_UNAUTHORIZED);
        } 
        // create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        // response success
        return response()->json([
            'success' => true,
            'message' => 'Logged In Successfully !',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 200);
    }
}
