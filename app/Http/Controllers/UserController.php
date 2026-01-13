<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/users",
     *     tags={"Users"},
     *     summary="Get all users (name, email, username, profile_picture only)",
     *     @OA\Response(response=200, description="Users retrieved successfully"),
     *     @OA\Response(response=404, description="No users found")
     * )
     */
    public function index(): JsonResponse
    {
        $users = User::query()
            ->select(['id', 'name', 'email', 'username', 'profile_picture', 'created_at'])
            ->get();

        if ($users->isEmpty()) {
            return $this->error('No users found', 404);
        }

        return $this->success($users, 'Users retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/users/{id}",
     *     tags={"Users"},
     *     summary="Get user detail (all fields except password)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="User retrieved successfully"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function show($id): JsonResponse
    {
        $user = User::query()
            ->select([
                'id',
                'name',
                'email',
                'username',
                'address',
                'phone_number',
                'profile_picture',
                'role',
                'created_at',
                'updated_at',
            ])
            ->find($id);

        if (!$user) {
            return $this->error('User not found', 404);
        }

        return $this->success($user, 'User retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/users/{id}/reset-password",
     *     tags={"Users"},
     *     summary="Reset user's password to default (12345)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Password reset successfully"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function resetPassword($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error('User not found', 404);
        }

        // Reset password to default 'password12345'
        $user->password = Hash::make('password12345');
        $user->save();

        return $this->success(null, 'Password reset successfully');
    }

    /**
     * @OA\Delete(
     *     path="/users/{id}",
     *     tags={"Users"},
     *     summary="Delete user by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="User deleted successfully"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function destroy($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error('User not found', 404);
        }

        $user->delete();

        return $this->success(null, 'User deleted successfully');
    }
}
