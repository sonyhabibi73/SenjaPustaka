<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $query = Review::with(['user', 'book'])->latest();

        if ($q = $request->string('q')->trim()->toString()) {
            $query->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"))
                ->orWhereHas('book', fn ($b) => $b->where('title', 'like', "%{$q}%"));
        }

        $reviews = $query->paginate(15)->withQueryString();

        return view('admin.reviews', compact('reviews'));
    }

    public function destroy(Review $review): RedirectResponse
    {
        $bookId = $review->book_id;
        $review->delete();

        Book::find($bookId)?->recalcRating();

        return back()->with('success', 'Review dihapus.');
    }
}
