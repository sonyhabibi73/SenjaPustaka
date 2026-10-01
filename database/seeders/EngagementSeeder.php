<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\ReadingGoal;
use App\Models\ReadingProgress;
use App\Models\Review;
use App\Models\User;
use App\Services\Gamification;
use Illuminate\Database\Seeder;

/**
 * Data keterlibatan demo untuk instalasi baru.
 *
 * Semua barisnya mereferensikan 14 buku nyata dari BookCatalogSeeder,
 * sehingga tidak ada lagi review/progres/favorit yang menunjuk buku placeholder.
 *
 * Sengaja TIDAK dijalankan oleh `php artisan catalog:reseed`: perintah itu
 * justru bertujuan mereset total data demo.
 */
class EngagementSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedReviews();
        $this->seedDemoActivity();
    }

    private function seedReviews(): void
    {
        $books = Book::where('is_published', true)->orderBy('id')->get();

        // User demo sengaja dikeluarkan dari pool: test memakai
        // Book::where('is_published')->first() dan mengirim review sebagai user
        // demo, sehingga baris duplikat akan melanggar unique(user_id, book_id).
        $users = User::where('email', '!=', 'user@senjapustaka.test')->get();

        if ($books->isEmpty() || $users->isEmpty()) {
            return;
        }

        $comments = [
            'Buku ini benar-benar membuat saya betah berlama-lama membacanya.',
            'Alurnya rapi dan karakternya terasa hidup. Wajib baca!',
            'Awalnya biasa saja, tapi makin ke belakang makin seru.',
            'Salah satu buku terbaik yang saya baca tahun ini.',
            'Bahasa penulisnya mengalir dan mudah dinikmati.',
            'Saya berharap ada sekuelnya. Sangat memikat!',
            'Tema yang diangkat sangat relevan dengan kehidupan sekarang.',
            'Ceritanya menghangatkan hati. Recommended!',
        ];

        foreach ($books as $i => $book) {
            $count = random_int(2, 5);

            foreach ($users->random(min($count, $users->count())) as $user) {
                Review::firstOrCreate(
                    ['user_id' => $user->id, 'book_id' => $book->id],
                    [
                        'rating' => random_int(3, 5),
                        'comment' => $comments[$i % count($comments)],
                        'created_at' => now()->subDays(random_int(1, 120)),
                    ]
                );
            }

            $book->recalcRating();
        }

        // Satu review milik user demo, pada buku yang memang sudah selesai dibaca.
        $demo = User::where('email', 'user@senjapustaka.test')->first();
        $demoBook = Book::where('slug', 'cantik-itu-luka')->first();

        if ($demo && $demoBook) {
            Review::firstOrCreate(
                ['user_id' => $demo->id, 'book_id' => $demoBook->id],
                [
                    'rating' => 5,
                    'comment' => 'Penuturannya tenang tapi meninggalkan bekas. Saya membacanya pelan-pelan, dan bagian-bagiannya masih terngiang beberapa hari setelah selesai.',
                    'created_at' => now()->subDays(12),
                ]
            );
            $demoBook->recalcRating();
        }
    }

    private function seedDemoActivity(): void
    {
        $demo = User::where('email', 'user@senjapustaka.test')->first();

        if (! $demo) {
            return;
        }

        // Satu buku selesai dibaca. Sama seperti favorit/review, buku featured
        // dipilih agar tidak pernah tabrakan dengan `first()` pada test.
        $finishedBook = Book::where('slug', 'cantik-itu-luka')->first();
        if ($finishedBook) {
            ReadingProgress::firstOrCreate(
                ['user_id' => $demo->id, 'book_id' => $finishedBook->id],
                [
                    'current_page' => $finishedBook->pages,
                    'progress_percent' => 100,
                    'status' => 'finished',
                    'finished_at' => now()->subDays(3),
                ]
            );
        }

        // Tiga buku sedang dibaca.
        foreach (['laut-bercerita', 'parable', 'malioboro-at-midnight'] as $slug) {
            $book = Book::where('slug', $slug)->first();

            if (! $book) {
                continue;
            }

            $page = (int) round($book->pages * [0.3, 0.55, 0.75][random_int(0, 2)]);

            ReadingProgress::firstOrCreate(
                ['user_id' => $demo->id, 'book_id' => $book->id],
                [
                    'current_page' => $page,
                    'progress_percent' => (int) round($page / max($book->pages, 1) * 100),
                    'status' => 'reading',
                ]
            );
        }

        // Dua favorit. Hanya buku ber-`is_featured`: test memakai
        // Book::where('is_published')->first() yang lewat index
        // (is_published, is_featured) sehingga selalu menunjuk buku
        // non-featured — supaya tidak pernah bentrok dengan data demo.
        foreach (['3726-mdpl', 'cantik-itu-luka'] as $slug) {
            $book = Book::where('slug', $slug)->first();

            if ($book) {
                Favorite::firstOrCreate(['user_id' => $demo->id, 'book_id' => $book->id]);
            }
        }

        ReadingGoal::firstOrCreate(
            ['user_id' => $demo->id, 'year' => now()->year],
            ['target_books' => 24, 'target_pages' => 6000]
        );

        Gamification::checkBadges($demo);

        if (! $demo->notifications()->exists()) {
            Gamification::notify($demo, '🔥 Streak 7 hari!', 'Kamu membaca 7 hari berturut-turut. Pertahankan!');
            Gamification::notify($demo, '🎯 Target halaman hampir tercapai', 'Tinggal sedikit lagi menuju target tahunanmu.');
        }
    }
}
