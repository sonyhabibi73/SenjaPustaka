<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\Series;
use App\Services\ImageOptimizer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $query = Book::with('author');

        if ($q = $request->string('q')->trim()->toString()) {
            $query->where('title', 'like', "%{$q}%");
        }

        $books = $query->latest()->paginate(15)->withQueryString();

        return view('admin.books', compact('books'));
    }

    public function create(): View
    {
        return $this->form(null);
    }

    public function edit(Book $book): View
    {
        return $this->form($book);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->save($request, new Book);
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        return $this->save($request, $book);
    }

    public function destroy(Book $book): RedirectResponse
    {
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }

        $book->delete();

        return back()->with('success', 'Buku dihapus.');
    }

    private function form(?Book $book): View
    {
        $authors = Author::orderBy('name')->get();
        $publishers = Publisher::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $series = Series::orderBy('name')->get();

        return view('admin.book-form', compact('book', 'authors', 'publishers', 'categories', 'series'));
    }

    private function save(Request $request, Book $book): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author_id' => ['required', 'exists:authors,id'],
            'publisher_id' => ['nullable', 'exists:publishers,id'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'cover_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'file' => ['nullable', 'file', 'mimes:pdf,zip,cbz', 'max:204800'],
            'pages' => ['required', 'integer', 'min:1'],
            'year' => ['nullable', 'integer', 'between:1900,2100'],
            'language' => ['nullable', 'string', 'max:10'],
            'is_featured' => ['nullable'],
            'is_published' => ['nullable'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
            'series' => ['nullable', 'array'],
            'series.*' => ['exists:series,id'],
        ], [
            'file.max' => 'Berkas PDF/CBZ maksimal 200MB.',
            'file.mimes' => 'Format berkas harus PDF atau CBZ.',
            'cover_image.max' => 'Gambar cover maksimal 2MB.',
            'cover_image.image' => 'File cover harus berupa gambar.',
        ]);

        $book->title = $data['title'];
        $book->slug = $book->slug ?: Str::slug($data['title']).'-'.Str::lower(Str::random(4));
        $book->author_id = $data['author_id'];
        $book->publisher_id = $data['publisher_id'] ?? null;
        $book->description = $data['description'] ?? null;
        $book->content = $data['content'] ?? null;
        $book->cover_color = $data['cover_color'] ?? '#2F5D52';
        $book->pages = $data['pages'];
        $book->year = $data['year'] ?? null;
        $book->language = $data['language'] ?? 'id';
        $book->is_featured = $request->boolean('is_featured');
        $book->is_published = $request->boolean('is_published');

        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $ext = strtolower((string) $file->guessExtension()) ?: 'jpg';
            $storedCover = $file->storeAs('covers', Str::random(40).'.'.$ext, 'public');

            if ($storedCover !== false) {
                $webp = ImageOptimizer::toWebp($storedCover, 600, 80);

                if ($webp !== null) {
                    Storage::disk('public')->delete($storedCover);
                    $storedCover = $webp;
                }

                if ($book->cover_image) {
                    Storage::disk('public')->delete($book->cover_image);
                }

                $book->cover_image = $storedCover;
            }
        } elseif ($request->boolean('remove_cover') && $book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
            $book->cover_image = null;
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $ext = match (strtolower((string) $file->guessExtension())) {
                // CBZ terdeteksi sebagai zip oleh finfo — simpan sebagai .cbz agar reader mengenalinya.
                'zip', 'zipx', 'cbz' => 'cbz',
                default => 'pdf',
            };
            $stored = $file->storeAs('books', Str::random(40).'.'.$ext, 'public');

            if ($stored !== false) {
                if ($book->file_path) {
                    Storage::disk('public')->delete($book->file_path);
                }

                $book->file_path = $stored;
            }
        }

        $book->save();

        $book->categories()->sync($data['categories'] ?? []);
        $chapterMap = collect((array) ($data['series'] ?? []))
            ->mapWithKeys(fn ($id) => [$id => ['chapter_number' => 1]]);
        $book->seriesList()->sync($chapterMap);

        return redirect()->route('admin.buku.index')->with('success', 'Buku berhasil disimpan.');
    }
}
