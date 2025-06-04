<?php

namespace App\Http\Controllers\Api\User;

use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\User\Profile\UpdateProfileRequest;
use App\Http\Requests\User\Profile\ChangePasswordRequest;

class ProfileController extends Controller
{
    use ApiResponse;

    public function me(): JsonResponse
    {
        return $this->success(auth()->user(), 'User profile data');
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = auth()->user();

        $user->name  = $request->name;
        $user->email = $request->email;

        // Jika upload avatar
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::delete($user->avatar); // hapus avatar lama kalau ada
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return $this->success($user, 'Profile updated successfully');
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->error('Current password is incorrect', 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->success(null, 'Password changed successfully');
    }
}
