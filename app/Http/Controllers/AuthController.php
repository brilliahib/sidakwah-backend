<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        if (User::where('email', $data['email'])->exists()) {
            return $this->error('Email sudah digunakan', 400);
        }

        if (User::where('username', $data['username'])->exists()) {
            return $this->error('Username sudah digunakan', 400);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'phone_number' => $data['phone_number'],
            'password' => Hash::make($data['password']),
            'address' => $data['address'],
        ]);

        $token = JWTAuth::fromUser($user);

        return $this->success([
            'id' => $user->id,
            'token' => $token,
        ], 'Registration successful');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return $this->error('Invalid credentials', 401);
        }

        $token = JWTAuth::fromUser($user);

        return $this->success([
            'id' => $user->id,
            'token' => $token,
        ], 'Login successful');
    }

    public function getAuth(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(
                [
                    'error' => 'User not authenticated',
                ],
                401,
            );
        }

        return $this->success(new UserResource($user), 'User retrieved successfully');
    }
}
