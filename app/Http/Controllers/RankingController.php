<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Contracts\View\View;

class RankingController extends Controller
{
    public function index(): View
    {
        $books = Book::where('is_published', true)
            ->with('author')
            ->where('rating_count', '>', 0)
            ->orderByDesc('rating_avg')
            ->orderByDesc('rating_count')
            ->limit(20)
            ->get();

        return view('pages.ranking', compact('books'));
    }
}
