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
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use OpenApi\Annotations as OA;


class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/auth/register",
     *     tags={"Auth"},
     *     summary="Register new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","username","phone_number","password","password_confirmation"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="phone_number", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="password_confirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Register success"),
     *     @OA\Response(response=400, description="Validation error")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"Auth"},
     *     summary="Login user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="user@mail.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login success",
     *         @OA\JsonContent(
     *             @OA\Property(property="meta", type="object"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/auth/get-auth",
     *     tags={"Auth"},
     *     summary="Get authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User retrieved"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/auth/update-account",
     *     tags={"Auth"},
     *     summary="Update account data",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="phone_number", type="string"),
     *             @OA\Property(property="address", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Account updated"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function updateAccount(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return $this->error('Unauthenticated', 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$user->id}",
            'username' => "required|string|unique:users,username,{$user->id}",
            'phone_number' => 'nullable|string',
            'address' => 'required|string',
        ]);

        $user->update($validated);

        return $this->success(new UserResource($user), 'Account updated successfully');
    }

    /**
     * @OA\Put(
     *     path="/auth/change-password",
     *     tags={"Auth"},
     *     summary="Change user password",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","new_password","new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string"),
     *             @OA\Property(property="new_password", type="string"),
     *             @OA\Property(property="new_password_confirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password changed"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return $this->error('Unauthenticated', 401);
        }

        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return $this->error('Current password is incorrect', 400);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return $this->success(null, 'Password updated successfully');
    }

    /**
     * @OA\Post(
     *     path="/auth/change-profile-picture",
     *     tags={"Auth"},
     *     summary="Change profile picture",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"profile_picture"},
     *                 @OA\Property(
     *                     property="profile_picture",
     *                     type="string",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profile picture updated"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function changeProfilePicture(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return $this->error('Unauthenticated', 401);
        }

        $request->validate([
            'profile_picture' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($user->profile_picture && Storage::exists($user->profile_picture)) {
            Storage::delete($user->profile_picture);
        }

        $file = $request->file('profile_picture');
        $path = $file->store('profile-pictures', 'public');

        $user->update([
            'profile_picture' => $path,
        ]);

        return $this->success([
            'profile_picture' => $path,
        ], 'Profile picture updated successfully');
    }
}
