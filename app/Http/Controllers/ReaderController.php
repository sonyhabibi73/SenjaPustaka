<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Bookmark;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

class ReaderController extends Controller
{
    public function show(Book $book): View
    {
        abort_unless($book->is_published, 404);

        $user = auth()->user();
        $progress = $book->progressFor($user);
        $bookmark = Bookmark::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->first();

        $mode = 'text';
        $pdfUrl = null;
        $cbzPages = 0;
        $pages = [];

        if ($book->file_path) {
            $ext = strtolower(pathinfo($book->file_path, PATHINFO_EXTENSION));

            if ($ext === 'pdf') {
                $mode = 'pdf';
                $pdfUrl = parse_url(route('reader.file', $book), PHP_URL_PATH);
            } elseif ($ext === 'cbz') {
                $mode = 'cbz';
                $cbzPages = $this->countCbzPages($book->file_path);
                $book->pages = $cbzPages ?: $book->pages;
            }
        }

        if ($mode === 'text') {
            $pages = $this->splitContent($book->content ?? $this->defaultContent($book));
            $book->pages = count($pages) ?: 1;
        }

        $startPage = $progress?->current_page ?: 1;

        if ($mode === 'cbz' && $cbzPages > 0) {
            $startPage = min($startPage, $cbzPages);
        } elseif ($mode === 'text') {
            $startPage = min($startPage, count($pages));
        }
        $startPage = max(1, $startPage);

        return view('reader.reader', compact(
            'book',
            'progress',
            'bookmark',
            'mode',
            'pdfUrl',
            'cbzPages',
            'pages',
            'startPage'
        ));
    }

    /**
     * Sajikan satu halaman gambar dari file CBZ.
     */
    public function cbzPage(Request $request, Book $book, int $page): Response
    {
        abort_unless($book->is_published, 404);

        $full = $book->file_path ? Storage::disk('public')->path($book->file_path) : null;
        $isCbz = $full !== null && file_exists($full)
            && strtolower(pathinfo($full, PATHINFO_EXTENSION)) === 'cbz';

        abort_unless($isCbz, 404);

        $mtime = (int) filemtime($full);
        $etag = '"'.md5($full.'|'.$page.'|'.$mtime).'"';

        // Halaman CBZ tidak berubah setelah di-upload → cache lama di browser.
        if ($request->header('If-None-Match') === $etag) {
            return response('', 304)->header('ETag', $etag);
        }

        $images = $this->cbzImageIndex($full, $mtime);

        if (! isset($images[$page - 1])) {
            abort(404);
        }

        $target = $images[$page - 1];

        $zip = new ZipArchive;
        if ($zip->open($full) !== true) {
            abort(404);
        }

        $content = $zip->getFromIndex($target['index']);
        $zip->close();

        if ($content === false) {
            abort(404);
        }

        return response($content)
            ->header('Content-Type', $this->mime($target['name']))
            ->header('Cache-Control', 'public, max-age=86400, immutable')
            ->header('ETag', $etag);
    }

    /**
     * Sajikan berkas PDF asli lewat BinaryFileResponse.
     *
     * Mengapa lewat controller, bukan file statis di public/storage?
     * Karena pdf.js memakai HTTP Range request untuk mengambil potongan
     * file saja — BinaryFileResponse Symfony mendukung Range (206 Partial
     * Content), sehingga browser cukup mengunduh halaman yang dibutuhkan
     * alih-alih seluruh PDF yang bisa puluhan MB.
     */
    public function file(Book $book): BinaryFileResponse
    {
        abort_unless($book->is_published, 404);

        $full = $book->file_path ? Storage::disk('public')->path($book->file_path) : null;
        $isPdf = $full !== null && file_exists($full)
            && strtolower(pathinfo($full, PATHINFO_EXTENSION)) === 'pdf';

        abort_unless($isPdf, 404);

        return response()->file($full, [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /**
     * Pecah konten teks menjadi halaman-halaman kecil.
     *
     * @return array<int, string>
     */
    private function splitContent(string $content): array
    {
        $paragraphs = preg_split('/\n\s*\n/', trim($content)) ?: [trim($content)];
        $paragraphs = array_values(array_filter($paragraphs, fn ($p) => trim($p) !== ''));

        $chunks = [];
        $buffer = '';

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);

            if (mb_strlen($buffer) + mb_strlen($paragraph) > 750 && $buffer !== '') {
                $chunks[] = trim($buffer);
                $buffer = '';
            }

            $buffer .= $paragraph."\n\n";
        }

        if ($buffer !== '') {
            $chunks[] = trim($buffer);
        }

        return $chunks ?: [trim($content)];
    }

    private function countCbzPages(string $path): int
    {
        $full = Storage::disk('public')->path($path);

        if (! file_exists($full)) {
            return 0;
        }

        return count($this->cbzImageIndex($full, (int) filemtime($full)));
    }

    /**
     * Daftar gambar dalam CBZ, di-cache per file + mtime supaya tidak
     * memindai ulang isi zip pada setiap request halaman.
     *
     * @return array<int, array{index: int, name: string}>
     */
    private function cbzImageIndex(string $full, int $mtime): array
    {
        $key = 'cbz-index:'.md5($full).':'.$mtime;

        return Cache::remember($key, now()->addDay(), function () use ($full) {
            $zip = new ZipArchive;
            if ($zip->open($full) !== true) {
                return [];
            }

            $images = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);

                if ($name !== false && preg_match('/\.(png|jpe?g|webp|gif)$/i', $name)) {
                    $images[] = ['index' => $i, 'name' => $name];
                }
            }
            $zip->close();

            return $images;
        });
    }

    private function mime(string $name): string
    {
        return match (strtolower(pathinfo($name, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'application/octet-stream',
        };
    }

    private function defaultContent(Book $book): string
    {
        return "{$book->title}\n\noleh {$book->author?->name}\n\n".
            "Buku ini belum memiliki berkas digital. Kamu sedang membaca versi pratinjau.\n\n".
            ($book->description ?? 'Semoga cerita di dalamnya menginspirasi kamu hari ini.').
            "\n\n— SenjaPustaka";
    }
}
