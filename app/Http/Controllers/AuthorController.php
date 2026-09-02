<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Contracts\View\View;

class AuthorController extends Controller
{
    public function index(): View
    {
        $authors = Author::withCount(['books' => fn ($q) => $q->where('is_published', true)])
            ->orderBy('name')
            ->get();

        return view('pages.authors', compact('authors'));
    }

    public function show(Author $author): View
    {
        $books = $author->books()
            ->where('is_published', true)
            ->with('author')
            ->paginate(12);

        return view('pages.author', compact('author', 'books'));
    }
}
