<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Admin\PublisherController;
use App\Http\Controllers\Admin\SeriesController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReaderController;
use App\Http\Controllers\ReadingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// ── Halaman publik ────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/koleksi', [LibraryController::class, 'index'])->name('library');
Route::get('/buku/{book:slug}', [BookController::class, 'show'])->name('books.show');
Route::get('/peringkat', [RankingController::class, 'index'])->name('ranking');
Route::get('/peringkat-pembaca', [LeaderboardController::class, 'index'])->name('leaderboard');
Route::get('/kategori', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/kategori/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/penulis', [AuthorController::class, 'index'])->name('authors.index');
Route::get('/penulis/{author:slug}', [AuthorController::class, 'show'])->name('authors.show');
Route::get('/tentang', [AboutController::class, 'index'])->name('about');
Route::get('/kebijakan-privasi', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/syarat-ketentuan', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/kontak', [ContactController::class, 'show'])->name('contact');
Route::post('/kontak', [ContactController::class, 'send'])->name('contact.send');
Route::get('/cari', [SearchController::class, 'index'])->name('search');
Route::get('/cari/saran', [SearchController::class, 'suggest'])->name('search.suggest');

// ── Auth ──────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->middleware('throttle:5,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── Area user (perlu login) ───────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/baca/{book:slug}', [ReaderController::class, 'show'])->name('reader');
    Route::get('/baca/{book:slug}/halaman/{page}', [ReaderController::class, 'cbzPage'])->name('reader.cbz');
    Route::get('/baca/{book:slug}/file', [ReaderController::class, 'file'])->name('reader.file');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifikasi/jumlah', [NotificationController::class, 'count'])->name('notifications.count');

    Route::post('/favorit/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::post('/review', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/review/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/progres', [ReadingController::class, 'saveProgress'])->name('progress.save');
    Route::post('/bookmark', [ReadingController::class, 'toggleBookmark'])->name('bookmarks.toggle');
    Route::post('/goals', [GoalController::class, 'store'])->name('goals.store');
    Route::post('/newsletter/toggle', [NewsletterController::class, 'toggle'])->name('newsletter.toggle');
});

Route::post('/newsletter/berlangganan', [NewsletterController::class, 'subscribe'])
    ->name('newsletter.subscribe')
    ->middleware('throttle:5,10');

// ── Panel admin ───────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('buku', App\Http\Controllers\Admin\BookController::class)
        ->parameters(['buku' => 'book']);
    Route::resource('kategori', App\Http\Controllers\Admin\CategoryController::class)
        ->parameters(['kategori' => 'category'])->except(['show']);
    Route::resource('penulis', App\Http\Controllers\Admin\AuthorController::class)
        ->parameters(['penulis' => 'author'])->except(['show']);
    Route::resource('penerbit', PublisherController::class)
        ->parameters(['penerbit' => 'publisher'])->except(['show']);
    Route::resource('series', SeriesController::class)
        ->parameters(['series' => 'series'])->except(['show']);
    Route::resource('review', App\Http\Controllers\Admin\ReviewController::class)
        ->except(['create', 'store', 'edit', 'update']);
    Route::resource('user', UserController::class)
        ->except(['create', 'store']);
    Route::resource('newsletter', App\Http\Controllers\Admin\NewsletterController::class)
        ->parameters(['newsletter' => 'subscriber'])
        ->except(['create', 'store', 'edit']);
});
