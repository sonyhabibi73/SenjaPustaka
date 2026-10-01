<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->seedBadges();
        $this->seedUsers();
        $this->call(BookCatalogSeeder::class);
        $this->call(EngagementSeeder::class);
        $this->seedNewsletter();
    }

    private function seedBadges(): void
    {
        $badges = [
            ['Langkah Pertama', 'langkah-pertama', '📖', 'books_finished', 1, 'Selesaikan buku pertamamu.'],
            ['Kutu Buku', 'kutu-buku', '📚', 'books_finished', 10, 'Selesaikan 10 buku.'],
            ['Maestro Buku', 'maestro-buku', '🏆', 'books_finished', 25, 'Selesaikan 25 buku.'],
            ['Seratus Halaman', 'seratus-halaman', '🧭', 'pages_read', 100, 'Baca 100 halaman.'],
            ['Maraton Membaca', 'maraton-membaca', '🏃', 'pages_read', 1000, 'Baca 1.000 halaman.'],
            ['Pelancong Cerita', 'pelancong-cerita', '✈️', 'pages_read', 10000, 'Baca 10.000 halaman.'],
            ['Kritikus', 'kritikus', '🗣️', 'reviews', 1, 'Tulis review pertamamu.'],
            ['Sang Pengritik', 'sang-pengritik', '⭐', 'reviews', 5, 'Tulis 5 review.'],
            ['Pecinta Sejati', 'pecinta-sejati', '❤️', 'favorites', 10, 'Kumpulkan 10 buku favorit.'],
            ['Api Semangat', 'api-semangat', '🔥', 'streak', 7, 'Baca 7 hari berturut-turut.'],
            ['Bintang Malam', 'bintang-malam', '🌟', 'streak', 30, 'Baca 30 hari berturut-turut.'],
            ['Penjelajah', 'penjelajah', '🗺️', 'categories', 3, 'Baca buku dari 3 kategori berbeda.'],
        ];

        foreach ($badges as [$name, $slug, $emoji, $key, $value, $desc]) {
            Badge::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'emoji' => $emoji,
                    'criteria_key' => $key,
                    'criteria_value' => $value,
                    'description' => $desc,
                ]
            );
        }
    }

    private function seedUsers(): void
    {
        User::create([
            'name' => 'Rizky Admin',
            'email' => 'admin@senjapustaka.test',
            'password' => 'password',
            'is_admin' => true,
            'bio' => 'Penjaga perpustakaan SenjaPustaka.',
        ]);

        User::create([
            'name' => 'Andi Pratama',
            'email' => 'user@senjapustaka.test',
            'password' => 'password',
            'bio' => 'Suka baca fantasi dan misteri di malam hari 🌙',
            'points' => 620,
            'streak_days' => 9,
            'longest_streak' => 14,
            'last_read_at' => now(),
        ]);

        $extraNames = ['Salsa Putri', 'Budi Santoso', 'Maya Anggraini', 'Doni Kurniawan'];
        foreach ($extraNames as $name) {
            User::create([
                'name' => $name,
                'email' => Str::slug($name).'@example.com',
                'password' => 'password',
                'points' => random_int(40, 900),
                'streak_days' => random_int(1, 12),
                'longest_streak' => random_int(1, 15),
                'last_read_at' => now()->subHours(random_int(1, 48)),
            ]);
        }
    }

    private function seedNewsletter(): void
    {
        $subs = ['salsa@example.com' => 'Salsa Putri', 'budi@example.com' => 'Budi Santoso', 'maya@example.com' => 'Maya Anggraini'];

        foreach ($subs as $email => $name) {
            NewsletterSubscriber::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'token' => Str::random(40), 'subscribed' => true]
            );
        }
    }
}
