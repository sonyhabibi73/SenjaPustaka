<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Bookmark;
use App\Services\Gamification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReadingController extends Controller
{
    public function saveProgress(Request $request): JsonResponse
    {
        $data = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'page' => ['required', 'integer', 'min:1'],
        ]);

        /** @var Book $book */
        $book = Book::findOrFail($data['book_id']);
        $user = auth()->user();

        $result = Gamification::recordPage($user, $book, (int) $data['page']);

        $progress = $book->progressFor($user);

        return response()->json([
            'ok' => true,
            'page' => $progress->current_page,
            'percent' => $progress->progress_percent,
            'finished' => $progress->status === 'finished',
            'new_badges' => $result['new_badges']->map(fn ($b) => $b->name)->values(),
        ]);
    }

    public function toggleBookmark(Request $request): JsonResponse
    {
        $data = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'page' => ['nullable', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = auth()->user();
        $existing = Bookmark::where('user_id', $user->id)
            ->where('book_id', $data['book_id'])
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json(['bookmarked' => false]);
        }

        Bookmark::create([
            'user_id' => $user->id,
            'book_id' => $data['book_id'],
            'page' => $data['page'] ?? 1,
            'note' => $data['note'] ?? null,
        ]);

        return response()->json(['bookmarked' => true]);
    }
}
