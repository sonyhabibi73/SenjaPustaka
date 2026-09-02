<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Series;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SeriesController extends Controller
{
    public function index(Request $request): View
    {
        $series = Series::withCount('books')->orderBy('name')->get();
        $edit = $request->has('edit') ? Series::find($request->integer('edit')) : null;

        return view('admin.series', compact('series', 'edit'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['slug'] = Str::slug($data['name']);
        Series::create($data);

        return back()->with('success', 'Series ditambahkan.');
    }

    public function update(Request $request, Series $series): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['slug'] = Str::slug($data['name']);
        $series->update($data);

        return redirect()->route('admin.series.index')->with('success', 'Series diperbarui.');
    }

    public function destroy(Series $series): RedirectResponse
    {
        $series->delete();

        return back()->with('success', 'Series dihapus.');
    }
}
