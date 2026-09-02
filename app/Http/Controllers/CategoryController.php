<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount(['books' => fn ($q) => $q->where('is_published', true)])
            ->orderBy('name')
            ->get();

        return view('pages.categories', compact('categories'));
    }

    public function show(Category $category): View
    {
        $books = $category->books()
            ->where('is_published', true)
            ->with('author')
            ->paginate(12);

        return view('pages.category', compact('category', 'books'));
    }
}
