<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Bookmark;
use Illuminate\Contracts\View\View;

class BookController extends Controller
{
    public function show(Book $book): View
    {
        abort_unless($book->is_published, 404);

        $book->increment('views');
        $book->load(['author', 'publisher', 'categories', 'seriesList']);

        $related = Book::where('is_published', true)
            ->where('id', '!=', $book->id)
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $book->categories->pluck('id')))
            ->with('author')
            ->inRandomOrder()
            ->limit(6)
            ->get();

        $user = auth()->user();
        $progress = $book->progressFor($user);
        $isFavorite = $user ? $user->favoriteBooks()->whereKey($book->id)->exists() : false;
        $userReview = $user ? $book->reviews()->where('user_id', $user->id)->first() : null;
        $bookmark = $user
            ? Bookmark::where('user_id', $user->id)->where('book_id', $book->id)->first()
            : null;

        return view('pages.book-detail', compact(
            'book',
            'related',
            'progress',
            'isFavorite',
            'userReview',
            'bookmark'
        ));
    }
}
