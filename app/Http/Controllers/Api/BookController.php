<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    /**
     * Get recommended books for the authenticated user.
     */
    public function recommended(): JsonResponse
    {
        $user = auth()->user();

        $recommendations = Book::where('is_published', true)
            ->with('author')
            ->when($user, function ($query) use ($user) {
                $topCategoryIds = $user->progress()
                    ->with('book.categories')
                    ->get()
                    ->flatMap(fn ($p) => $p->book ? $p->book->categories->pluck('id') : collect())
                    ->groupBy('id')
                    ->map->count()
                    ->sortDesc()
                    ->keys()
                    ->take(3);

                if ($topCategoryIds->isNotEmpty()) {
                    $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $topCategoryIds));
                }

                return $query->whereNotIn('id', $user->progress()->pluck('book_id'));
            })
            ->inRandomOrder()
            ->limit(6)
            ->get()
            ->map(fn (Book $b) => [
                'id' => $b->id,
                'title' => $b->title,
                'slug' => $b->slug,
                'author' => $b->author?->name,
                'cover_color' => $b->cover_color,
                'rating_avg' => $b->rating_avg,
            ]);

        return response()->json([
            'ok' => true,
            'data' => $recommendations,
        ]);
    }

    /**
     * Get reading progress for the authenticated user.
     */
    public function progress(): JsonResponse
    {
        $user = auth()->user();

        $progress = $user->progress()
            ->with('book')
            ->get()
            ->map(fn ($p) => [
                'book_id' => $p->book_id,
                'book_title' => $p->book?->title,
                'current_page' => $p->current_page,
                'progress_percent' => $p->progress_percent,
                'status' => $p->status,
            ]);

        return response()->json([
            'ok' => true,
            'data' => $progress,
        ]);
    }
}
