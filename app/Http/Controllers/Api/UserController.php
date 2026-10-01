<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Get the authenticated user's profile.
     */
    public function show(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'bio' => $user->bio,
                'points' => $user->points,
                'streak_days' => $user->streak_days,
                'longest_streak' => $user->longest_streak,
                'level' => $user->levelInfo(),
                'email_verified' => $user->hasVerifiedEmail(),
            ],
        ]);
    }
}
