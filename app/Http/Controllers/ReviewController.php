<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use App\Services\Gamification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        /** @var Book $book */
        $book = Book::findOrFail($data['book_id']);

        $review = Review::updateOrCreate(
            ['user_id' => auth()->id(), 'book_id' => $book->id],
            ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null]
        );

        $book->recalcRating();
        Gamification::checkBadges(auth()->user());

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'rating_avg' => $book->rating_avg,
                'rating_count' => $book->rating_count,
                'review' => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                ],
            ]);
        }

        return back()->with('success', 'Review tersimpan. Terima kasih! ⭐');
    }

    public function destroy(Request $request, Review $review): JsonResponse|RedirectResponse
    {
        if ($review->user_id !== auth()->id() && ! auth()->user()->is_admin) {
            abort(403);
        }

        $bookId = $review->book_id;
        $review->delete();

        Book::findOrFail($bookId)->recalcRating();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Review dihapus.');
    }
}
