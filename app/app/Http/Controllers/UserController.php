<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function signup(SignupRequest $request): JsonResponse
    {
        User::create($request->all());
        return response()->json(['message' => 'success']);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('mobile', $request->mobile)->where('status', 1)->first();
        if (Hash::check($request->password, $user->password)) {
            $token = $user->createToken('access token')->plainTextToken;
            return response()->json(['message' => 'success', 'token' => $token]);
        } else {
            return response()->json(['message' => 'password is not matched.'], 400);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'success']);
    }
}
