<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthorController extends Controller
{
    public function index(Request $request): View
    {
        $authors = Author::withCount('books')->orderBy('name')->get();
        $edit = $request->has('edit') ? Author::find($request->integer('edit')) : null;

        return view('admin.authors', compact('authors', 'edit'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['slug'] = Str::slug($data['name']);
        Author::create($data);

        return back()->with('success', 'Penulis ditambahkan.');
    }

    public function update(Request $request, Author $author): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['slug'] = Str::slug($data['name']);
        $author->update($data);

        return redirect()->route('admin.penulis.index')->with('success', 'Penulis diperbarui.');
    }

    public function destroy(Author $author): RedirectResponse
    {
        $author->delete();

        return back()->with('success', 'Penulis dihapus.');
    }
}
