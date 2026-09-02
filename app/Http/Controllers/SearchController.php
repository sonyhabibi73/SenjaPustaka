<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->toString();

        $books = Book::where('is_published', true)
            ->with('author')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($builder) use ($q) {
                    $builder->where('title', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhereHas('author', fn ($a) => $a->where('name', 'like', "%{$q}%"));
                });
            })
            ->paginate(12)
            ->withQueryString();

        return view('pages.search', compact('books', 'q'));
    }

    /**
     * Auto-suggest untuk kotak pencarian.
     */
    public function suggest(Request $request): JsonResponse
    {
        $q = $request->string('q')->trim()->toString();

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $books = Book::where('is_published', true)
            ->with('author')
            ->where('title', 'like', "%{$q}%")
            ->limit(6)
            ->get()
            ->map(fn (Book $b) => [
                'title' => $b->title,
                'slug' => $b->slug,
                'cover_color' => $b->cover_color,
                'author' => $b->author?->name,
                'url' => route('books.show', $b),
            ]);

        return response()->json($books);
    }
}
