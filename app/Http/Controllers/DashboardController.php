<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Book;
use App\Models\NewsletterSubscriber;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $level = $user->levelInfo();
        $year = now()->year;

        $reading = $user->progress()->where('status', 'reading')
            ->with('book.author')
            ->latest('updated_at')
            ->limit(10)
            ->get()
            ->filter(fn ($p) => $p->book !== null);

        $finished = $user->progress()->where('status', 'finished')
            ->with('book.author')
            ->latest('finished_at')
            ->limit(10)
            ->get()
            ->filter(fn ($p) => $p->book !== null);

        $favorites = $user->favoriteBooks()->with('author')->latest()->limit(10)->get();

        $booksFinished = $user->booksFinished();
        $pagesRead = $user->pagesRead();

        $goal = $user->goals()->where('year', $year)->first();
        $goalData = null;
        if ($goal && ($goal->target_books > 0 || $goal->target_pages > 0)) {
            $goalData = [
                'books' => [
                    'current' => $booksFinished,
                    'target' => $goal->target_books,
                ],
                'pages' => [
                    'current' => $pagesRead,
                    'target' => $goal->target_pages,
                ],
            ];
        }

        $badges = Badge::orderBy('criteria_value')->get();
        $ownedBadgeIds = $user->badges()->pluck('badges.id');

        // Rekomendasi berdasarkan kategori yang paling sering dibaca.
        $topCategoryIds = $user->progress()
            ->with('book.categories')
            ->get()
            ->flatMap(function ($p) {
                return $p->book ? $p->book->categories : collect();
            })
            ->groupBy('id')
            ->map->count()
            ->sortDesc()
            ->keys()
            ->take(3);

        $recommendations = Book::where('is_published', true)
            ->with('author')
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $topCategoryIds))
            ->whereNotIn('id', $user->progress()->pluck('book_id'))
            ->inRandomOrder()
            ->limit(6)
            ->get();

        if ($recommendations->isEmpty()) {
            $recommendations = Book::where('is_published', true)
                ->with('author')
                ->latest()
                ->limit(6)
                ->get();
        }

        $stats = [
            'reading' => $user->progress()->where('status', 'reading')->count(),
            'finished' => $booksFinished,
            'pages' => $pagesRead,
            'favorites' => $user->favorites()->count(),
        ];

        $newsletter = NewsletterSubscriber::where('email', $user->email)->first();

        $hour = (int) now()->format('G');
        $greeting = match (true) {
            $hour < 11 => ['teks' => 'Selamat pagi', 'emoji' => '🌅'],
            $hour < 15 => ['teks' => 'Selamat siang', 'emoji' => '☀️'],
            $hour < 19 => ['teks' => 'Selamat sore', 'emoji' => '🌤️'],
            default => ['teks' => 'Selamat malam', 'emoji' => '🌙'],
        };

        return view('user.dashboard', compact(
            'user',
            'level',
            'year',
            'reading',
            'finished',
            'favorites',
            'goal',
            'goalData',
            'booksFinished',
            'pagesRead',
            'badges',
            'ownedBadgeIds',
            'recommendations',
            'stats',
            'newsletter',
            'greeting'
        ));
    }
}
