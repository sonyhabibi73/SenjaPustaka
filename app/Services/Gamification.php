<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Book;
use App\Models\Notification;
use App\Models\ReadingProgress;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Gamification
{
    /**
     * Catat halaman yang dibaca: update progres, streak, poin, dan cek badge.
     *
     * @return array{level_up: bool, new_badges: Collection<int, Badge>}
     */
    public static function recordPage(User $user, Book $book, int $page): array
    {
        $totalPages = max(1, $book->pages);
        $page = max(1, min($page, $totalPages));

        $progress = ReadingProgress::firstOrNew([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
        $previousPage = $progress->current_page ?? 0;

        $progress->current_page = $page;
        $progress->progress_percent = (int) round($page / $totalPages * 100);
        $justFinished = false;
        if ($page >= $totalPages && $progress->status !== 'finished') {
            $progress->status = 'finished';
            $progress->finished_at = now();
            $justFinished = true;
        }
        $progress->save();

        self::updateStreak($user);

        // Poin: +2 per halaman baru (delta dibatasi 30 halaman/simpan untuk
        // mencegah loncatan halaman besar sekaligus), +50 bonus saat selesai.
        $newPages = min(30, max(0, $page - $previousPage));
        $points = $newPages * 2;
        if ($justFinished) {
            $points += 50;
        }
        if ($points > 0) {
            $user->increment('points', $points);
        }

        if ($justFinished) {
            self::notify(
                $user,
                '🎉 Buku selesai!',
                "Kamu baru saja menamatkan \"{$book->title}\" dan mendapat bonus 50 poin.",
                route('books.show', $book)
            );
        }

        $newBadges = self::checkBadges($user);

        return [
            'level_up' => false,
            'new_badges' => $newBadges,
        ];
    }

    /**
     * Perbarui streak membaca harian.
     */
    public static function updateStreak(User $user): void
    {
        $today = Carbon::today();
        $last = $user->last_read_at ? Carbon::parse($user->last_read_at)->startOfDay() : null;

        if ($last && $last->lt($today->copy()->subDay())) {
            $user->streak_days = 1;
        } elseif (! $last || $last->lt($today)) {
            $user->streak_days = $user->streak_days + 1;
        }

        $user->last_read_at = Carbon::now();
        $user->longest_streak = max($user->longest_streak, $user->streak_days);
        $user->save();
    }

    /**
     * Cek dan berikan semua badge yang baru memenuhi syarat.
     *
     * @return Collection<int, Badge>
     */
    public static function checkBadges(User $user): Collection
    {
        $stats = [
            'books_finished' => $user->progress()->where('status', 'finished')->count(),
            'pages_read' => (int) $user->progress()->sum('current_page'),
            'reviews' => $user->reviews()->count(),
            'favorites' => $user->favorites()->count(),
            'streak' => $user->streak_days,
            'categories' => $user->readingCategoriesCount(),
        ];

        $earned = collect();
        $owned = $user->badges()->pluck('badges.id');

        foreach (Badge::all() as $badge) {
            $meets = ($stats[$badge->criteria_key] ?? 0) >= $badge->criteria_value;

            if ($meets && ! $owned->contains($badge->id)) {
                $user->badges()->attach($badge->id, ['earned_at' => now()]);
                $earned->push($badge);

                self::notify(
                    $user,
                    '🏅 Badge baru: '.$badge->name,
                    $badge->description,
                    route('dashboard')
                );
            }
        }

        return $earned;
    }

    /**
     * Buat notifikasi in-app.
     */
    public static function notify(User $user, string $title, ?string $message = null, ?string $url = null): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'url' => $url,
        ]);
    }
}
