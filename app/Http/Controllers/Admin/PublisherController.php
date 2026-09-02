<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Publisher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublisherController extends Controller
{
    public function index(Request $request): View
    {
        $publishers = Publisher::withCount('books')->orderBy('name')->get();
        $edit = $request->has('edit') ? Publisher::find($request->integer('edit')) : null;

        return view('admin.publishers', compact('publishers', 'edit'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Publisher::create(['name' => $data['name'], 'slug' => Str::slug($data['name'])]);

        return back()->with('success', 'Penerbit ditambahkan.');
    }

    public function update(Request $request, Publisher $publisher): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $publisher->update(['name' => $data['name'], 'slug' => Str::slug($data['name'])]);

        return redirect()->route('admin.penerbit.index')->with('success', 'Penerbit diperbarui.');
    }

    public function destroy(Publisher $publisher): RedirectResponse
    {
        $publisher->delete();

        return back()->with('success', 'Penerbit dihapus.');
    }
}
