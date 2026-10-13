<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Bookmark;
use App\Services\FileStorage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
                $cbzPages = $this->countCbzPages($book);
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
     *
     * Dua jalur, tergantung disk buku aktif:
     * - lokal : buka ZipArchive, kirim isi satu gambar (paling hemat, tanpa
     *   menulis file apa pun ke disk tiap request);
     * - cloud (R2): arahkan browser ke URL bertanda tangan. Cloud tidak
     *   bisa membuka ZIP dari jauh, jadi halamannya sudah diekstrak lebih
     *   dulu oleh `catalog:extract-cbz` dan redirect ke berkas hasil.
     */
    public function cbzPage(Request $request, Book $book, int $page): Response
    {
        abort_unless($book->is_published, 404);
        abort_unless($this->isCbz($book), 404);

        if (FileStorage::isRemote()) {
            return $this->redirectToExtractedPage($book, $page);
        }

        $full = FileStorage::localPath($book->file_path);

        abort_unless($full !== null, 404);

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
     * Sajikan berkas PDF asli.
     *
     * Mengapa lewat controller, bukan file statis di public/storage?
     * Karena pdf.js memakai HTTP Range request untuk mengambil potongan
     * file saja — BinaryFileResponse Symfony mendukung Range (206 Partial
     * Content), sehingga browser cukup mengunduh halaman yang dibutuhkan
     * alih-alih seluruh PDF yang bisa puluhan MB.
     *
     * Di disk cloud, PDF tidak bisa dilayani lewat response()->file()
     * karena berkasnya tidak ada di filesystem server. R2 menghormati
     * Range request seperti S3, jadi cukup redirect ke URL bertanda tangan
     * dan pdf.js tetap menarik hanya bagian yang dibutuhkan — lewat R2,
     * bukan lewat server aplikasi.
     */
    public function file(Book $book): Response
    {
        abort_unless($book->is_published, 404);
        abort_unless($this->isPdf($book), 404);

        if (FileStorage::isRemote()) {
            return $this->redirectToTemporaryUrl($book->file_path);
        }

        $full = FileStorage::localPath($book->file_path);

        abort_unless($full !== null, 404);

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

    private function countCbzPages(Book $book): int
    {
        // Di cloud, sumber kebenaran jumlah halaman adalah manifest hasil
        // ekstraksi — bukan ZipArchive (tak bisa jarak jauh) dan bukan kolom
        // books.pages (bisa tertinggal kalau berkas CBZ-nya diganti).
        if (FileStorage::isRemote()) {
            return count($this->extractedPages($book));
        }

        $full = FileStorage::localPath((string) $book->file_path);

        if ($full === null) {
            return 0;
        }

        return count($this->cbzImageIndex($full, (int) filemtime($full)));
    }

    private function isCbz(Book $book): bool
    {
        return strtolower(pathinfo((string) $book->file_path, PATHINFO_EXTENSION)) === 'cbz';
    }

    private function isPdf(Book $book): bool
    {
        return strtolower(pathinfo((string) $book->file_path, PATHINFO_EXTENSION)) === 'pdf';
    }

    /**
     * Manifest halaman hasil ekstraksi CBZ, mis. books/tougei/pages.json.
     *
     * Cloud tidak bisa membuka ZIP dari jauh, jadi `catalog:extract-cbz`
     * menyimpan tiap halaman sebagai berkas sendiri lalu menulis urutan
     * namanya ke manifest. Manifest yang jadi sumber kebenaran, bukan
     * books.pages, supaya ekstensi tiap halaman (png/jpg/webp) tidak perlu
     * ditebak di sini.
     *
     * @return array<int, string>
     */
    private function extractedPages(Book $book): array
    {
        $manifest = 'books/'.$book->slug.'/pages.json';
        $disk = FileStorage::books();

        return Cache::remember('cbz-manifest:'.$book->id.':'.$book->file_path, now()->addDay(), function () use ($disk, $manifest) {
            if (! $disk->exists($manifest)) {
                return [];
            }

            $decoded = json_decode($disk->get($manifest), true);

            if (! is_array($decoded)) {
                return [];
            }

            return array_values(array_filter($decoded, 'is_string'));
        });
    }

    /**
     * Redirect ke URL bertanda tangan di disk cloud (R2).
     */
    private function redirectToTemporaryUrl(string $path): RedirectResponse
    {
        $url = FileStorage::books()->temporaryUrl($path, now()->addMinutes(30));

        abort_if(blank($url), 404);

        // 302 supaya tiap pembukaan dokumen mendapat URL baru dan tidak
        // menggantung di cache browser setelah URL lama kedaluwarsa.
        return redirect()->away($url);
    }

    /**
     * Redirect satu halaman CBZ ke berkas hasil ekstraksi di disk cloud.
     */
    private function redirectToExtractedPage(Book $book, int $page): RedirectResponse
    {
        $pages = $this->extractedPages($book);
        $name = $pages[$page - 1] ?? null;

        abort_unless($name !== null, 404);

        return $this->redirectToTemporaryUrl('books/'.$book->slug.'/pages/'.$name);
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
