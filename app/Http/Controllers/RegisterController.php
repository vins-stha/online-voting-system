<?php

namespace App\Http\Controllers;

use Exception;

use App\Exceptions\CustomException;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class RegisterController extends Controller
{
    //
    public function register(Request $request)
    {
        // validate inputs
        $validate = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'password' => 'required|confirmed', // confirmed from input attribute with name password_confirmation
            'email' => 'required|email|unique:users'
        ]);
        if ($validate->fails()) {
            return response()->json([
                'Validation error' => $validate->errors()
            ]);
        }

        try {
            $user = User::create([
                'name' => $request->get('name'),
                'password' => Hash::make($request->get('password')),
                'email' => $request->get('email')
            ]);

            $token = $user->createToken(CustomConstants::TOKEN_SECRET);
            return response()->json([
                'access_token' => $token->plainTextToken,
                'type' => 'Bearer',
                'message' => 'Registration successful'
            ]);
        } catch (Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    public function login(Request $request)
    {
        $data = [
            'email' => $request->get('email'),
            'password' => $request->get('password')
        ];
        var_dump($data);
        if (!auth()->attempt($data)) {
            return response()->json([
                'message' => 'User could not be authorized'
            ], 401);
        }
        $user = Auth::user();
        return response()->json([
            // 'access_token' => auth()->user()->createToken(CustomConstants::TOKEN_SECRET)->plainTextToken
            'access_token' => $user->createToken(CustomConstants::TOKEN_SECRET)->plainTextToken

        ], 200);
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            // delete all the tokens
            $user->tokens()->delete();

            return response()->json([
                'message' => 'Logout successful'
            ]);
        } catch (Exception $e) {
            throw new CustomException($e->getMessage());
        }

    }
}
