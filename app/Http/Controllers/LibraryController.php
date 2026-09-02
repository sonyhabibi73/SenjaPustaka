<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Series;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Book::query()
            ->where('is_published', true)
            ->with(['author', 'categories']);

        $q = $request->string('q')->trim()->toString();
        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhereHas('author', fn ($a) => $a->where('name', 'like', "%{$q}%"));
            });
        }

        $categorySlug = $request->string('kategori')->trim()->toString();
        if ($categorySlug !== '') {
            $query->whereHas('categories', fn ($c) => $c->where('categories.slug', $categorySlug));
        }

        $seriesSlug = $request->string('series')->trim()->toString();
        if ($seriesSlug !== '') {
            $query->whereHas('seriesList', fn ($s) => $s->where('series.slug', $seriesSlug));
        }

        $sort = $request->string('sort')->trim()->toString() ?: 'terbaru';
        match ($sort) {
            'populer' => $query->orderByDesc('views'),
            'rating' => $query->orderByDesc('rating_avg')->orderByDesc('rating_count'),
            'judul' => $query->orderBy('title'),
            default => $query->latest(),
        };

        $books = $query->paginate(12)->withQueryString();

        $categories = Category::withCount(['books' => fn ($q) => $q->where('is_published', true)])
            ->orderBy('name')
            ->get();
        $series = Series::withCount(['books' => fn ($q) => $q->where('is_published', true)])
            ->orderBy('name')
            ->get();

        $filters = [
            'q' => $q,
            'kategori' => $categorySlug,
            'series' => $seriesSlug,
            'sort' => $sort,
        ];

        return view('pages.library', compact('books', 'categories', 'series', 'filters'));
    }
}
