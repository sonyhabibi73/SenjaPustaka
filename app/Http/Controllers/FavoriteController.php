<?php

namespace App\Http\Controllers;

use App\Services\Gamification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
        ]);

        $user = auth()->user();
        $favorite = $user->favorites()->where('book_id', $data['book_id'])->first();

        if ($favorite) {
            $favorite->delete();

            return response()->json(['favorited' => false]);
        }

        $user->favorites()->create(['book_id' => $data['book_id']]);
        Gamification::checkBadges($user);

        return response()->json(['favorited' => true]);
    }
}
