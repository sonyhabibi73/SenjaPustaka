<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $bookshelf = Book::where('is_published', true)->inRandomOrder()->limit(9)->get();
        $trending = Book::where('is_published', true)
            ->with('author')
            ->orderByDesc('views')
            ->limit(6)
            ->get();
        $latest = Book::where('is_published', true)
            ->with('author')
            ->latest()
            ->limit(8)
            ->get();
        $categories = Category::withCount(['books' => fn ($q) => $q->where('is_published', true)])
            ->orderByDesc('books_count')
            ->limit(6)
            ->get();

        $stats = [
            'buku' => number_format(Book::where('is_published', true)->count()),
            'pembaca' => number_format(User::count()),
            'halaman' => number_format((int) Book::where('is_published', true)->sum('pages')),
            'badge' => number_format(Badge::count()),
        ];

        $continue = collect();
        if (auth()->check()) {
            $continue = auth()->user()->progress()
                ->where('status', 'reading')
                ->with('book.author')
                ->latest('updated_at')
                ->limit(4)
                ->get()
                ->filter(fn ($p) => $p->book !== null);
        }

        return view('pages.home', compact('bookshelf', 'trending', 'latest', 'categories', 'stats', 'continue'));
    }
}
