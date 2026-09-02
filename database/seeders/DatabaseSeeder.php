<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Badge;
use App\Models\Book;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\NewsletterSubscriber;
use App\Models\Publisher;
use App\Models\ReadingGoal;
use App\Models\ReadingProgress;
use App\Models\Review;
use App\Models\Series;
use App\Models\User;
use App\Services\Gamification;
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
        $this->seedCatalog();
        $this->seedBooks();
        $this->seedEngagement();
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
            Badge::create([
                'name' => $name,
                'slug' => $slug,
                'emoji' => $emoji,
                'criteria_key' => $key,
                'criteria_value' => $value,
                'description' => $desc,
            ]);
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

    private function seedCatalog(): void
    {
        $categories = [
            ['Fiksi', 'fiksi', 'book-open', 'Cerita rekaan yang menghanyutkan.'],
            ['Fantasi', 'fantasi', 'sparkles', 'Dunia magis penuh petualangan.'],
            ['Romantis', 'romantis', 'heart', 'Kisah cinta yang menghangatkan hati.'],
            ['Misteri', 'misteri', 'search', 'Teka-teki yang menantang nalar.'],
            ['Sejarah', 'sejarah', 'landmark', 'Jejak masa lalu untuk masa depan.'],
            ['Teknologi', 'teknologi', 'cpu', 'Dunia digital dan inovasi.'],
            ['Sains', 'sains', 'atom', 'Penemuan yang mengubah cara pandang.'],
            ['Bisnis', 'bisnis', 'trending-up', 'Strategi dan semangat wirausaha.'],
            ['Komik', 'komik', 'palette', 'Visual yang bercerita.'],
            ['Self-Help', 'self-help', 'sprout', 'Tumbuh jadi versi terbaik dirimu.'],
            ['Puisi', 'puisi', 'feather', 'Kata-kata yang menyentuh jiwa.'],
            ['Biografi', 'biografi', 'user', 'Kisah nyata para inspirator.'],
        ];

        foreach ($categories as [$name, $slug, $emoji, $desc]) {
            Category::create(['name' => $name, 'slug' => $slug, 'emoji' => $emoji, 'description' => $desc]);
        }

        $authors = [
            ['Alya Rahmawati', 'Penulis novel yang hobi menonton senja dari balkon rumahnya di Yogyakarta.'],
            ['Bagus Wiratama', 'Penulis fantasi yang percaya setiap sudut kota menyimpan pintu ke dunia lain.'],
            ['Citra Lestari', 'Mantan jurnalis yang kini menulis cerita inspiratif dari sudut pandang manusia biasa.'],
            ['Dimas Anggara', 'Insinyur perangkat lunak yang menulis tentang persilangan kode dan kehidupan.'],
            ['Eka Purnama', 'Sejarawan muda yang gemar menjelajahi arsip dan situs bersejarah Nusantara.'],
            ['Farhan Hidayat', 'Penulis misteri dengan kegemaran mengamati detail-detail kecil yang luput dari perhatian.'],
            ['Gita Savitri', 'Penulis roman yang percaya cinta sejati selalu punya alur yang tak terduga.'],
            ['Hadi Susanto', 'Pengusaha yang berbagi kisah naik turun membangun bisnis dari nol.'],
            ['Intan Permata', 'Penulis fantasi remaja yang terinspirasi dari dongeng Nusantara.'],
            ['Kirana Ayu', 'Ilustrator dan komikus independen asal Bandung.'],
        ];

        foreach ($authors as [$name, $bio]) {
            Author::create(['name' => $name, 'slug' => Str::slug($name), 'bio' => $bio]);
        }

        $publishers = ['Aksara Senja', 'Gramedia Pustaka', 'Penerbit Lintang', 'Bumi Cerita', 'Pustaka Pagi', 'Rumah Kata'];
        foreach ($publishers as $name) {
            Publisher::create(['name' => $name, 'slug' => Str::slug($name)]);
        }

        $seriesData = [
            ['Seri Senja', 'Tiga purnama, tiga takdir — petualangan di kota yang tak pernah benar-benar tidur.'],
            ['Legenda Nusantara', 'Kisah-kisah legenda Nusantara yang dihidupkan kembali.'],
            ['Detektif Lorong', 'Misteri-misteri kecil di lorong-lorong kota tua.'],
        ];
        foreach ($seriesData as [$name, $desc]) {
            Series::create(['name' => $name, 'slug' => Str::slug($name), 'description' => $desc]);
        }
    }

    private function seedBooks(): void
    {
        $categories = Category::pluck('id', 'slug');
        $authors = Author::pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [mb_strtolower($name) => $id]);
        $publishers = Publisher::pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [mb_strtolower($name) => $id]);
        $series = Series::pluck('id', 'name');

        $books = [
            ['Senja di Ujung Lorong', 'alya rahmawati', 'aksara senja', ['fiksi'], 240, 2023, '#4E7291', false, null, 4200],
            ['Naga Penjaga Bulan', 'bagus wiratama', 'bumi cerita', ['fantasi'], 320, 2022, '#566B80', true, 'legenda nusantara', 5100],
            ['Jejak Cahaya', 'citra lestari', 'pustaka pagi', ['fiksi', 'biografi'], 210, 2024, '#4F7386', false, null, 2900],
            ['Kode dan Takdir', 'dimas anggara', 'aksara senja', ['teknologi', 'fiksi'], 280, 2023, '#5E7A94', false, null, 3400],
            ['Kota di Bawah Langit', 'eka purnama', 'gramedia pustaka', ['sejarah'], 360, 2021, '#8A6D3B', true, null, 4700],
            ['Misteri Rumah Panggung', 'farhan hidayat', 'bumi cerita', ['misteri'], 260, 2024, '#43596E', false, 'detektif lorong', 3900],
            ['Hujan di Bulan April', 'gita savitri', 'penerbit lintang', ['romantis'], 190, 2022, '#B05A5A', false, null, 5200],
            ['Bisnis dari Nol', 'hadi susanto', 'rumah kata', ['bisnis', 'self-help'], 230, 2023, '#5C7689', false, null, 2600],
            ['Pulau Impian', 'intan permata', 'bumi cerita', ['fantasi'], 300, 2024, '#A8792F', true, null, 4400],
            ['Ilmu yang Membebaskan', 'joko saputra', 'pustaka pagi', ['self-help', 'sains'], 250, 2021, '#58748A', false, null, 2100],
            ['Komedi Statis', 'kirana ayu', 'penerbit lintang', ['komik'], 120, 2023, '#C4692E', false, null, 3100],
            ['Sejarah Kopi Nusantara', 'laksana wijaya', 'gramedia pustaka', ['sejarah'], 340, 2020, '#7F5539', false, null, 2800],
            ['Algoritma Cinta', 'maya kusuma', 'aksara senja', ['romantis', 'teknologi'], 270, 2024, '#4E5E72', false, null, 3600],
            ['Surat untuk Senja', 'nabila zahra', 'pustaka pagi', ['fiksi', 'puisi'], 160, 2022, '#C97B2D', false, null, 4800],
            ['Perpustakaan Malam', 'oki setiawan', 'bumi cerita', ['misteri', 'fantasi'], 290, 2023, '#2E4559', true, 'detektif lorong', 5500],
            ['Ruang Tenang', 'putri melati', 'penerbit lintang', ['self-help'], 200, 2024, '#4C7A86', false, null, 1900],
            ['Robot yang Bermimpi', 'qori ahmad', 'aksara senja', ['teknologi', 'sains'], 310, 2023, '#3E5670', false, null, 3300],
            ['Bintang Jatuh di Selatan', 'raka ardian', 'rumah kata', ['fiksi', 'romantis'], 220, 2022, '#A44A3F', false, null, 4100],
            ['Tanah Leluhur', 'sari wulandari', 'gramedia pustaka', ['sejarah', 'fiksi'], 380, 2021, '#3A5672', true, 'legenda nusantara', 3000],
            ['Koki Jalanan', 'taufik rahman', 'rumah kata', ['bisnis'], 180, 2024, '#C25A33', false, null, 2400],
            ['Dunia Paralel', 'umar fauzi', 'bumi cerita', ['fantasi', 'sains'], 330, 2023, '#4F6D88', false, null, 3500],
            ['Catatan Perjalanan', 'vina amalia', 'pustaka pagi', ['fiksi', 'biografi'], 240, 2022, '#A8765E', false, null, 2700],
            ['Merangkai Kata', 'wahyu pratama', 'penerbit lintang', ['self-help', 'puisi'], 150, 2024, '#D4A373', false, null, 2200],
            ['Purnama Pertama', 'alya rahmawati', 'aksara senja', ['fiksi'], 280, 2023, '#2E5E82', true, 'seri senja', 4600],
            ['Purnama Kedua', 'alya rahmawati', 'aksara senja', ['fiksi'], 300, 2024, '#4A7BA8', true, 'seri senja', 3800],
            ['Purnama Ketiga', 'alya rahmawati', 'aksara senja', ['fiksi'], 320, 2025, '#5E8FB8', true, 'seri senja', 2100],
        ];

        foreach ($books as [$title, $author, $publisher, $cats, $pages, $year, $color, $featured, $seriesName, $views]) {
            $authorId = $authors[$author] ?? null;
            if (! $authorId) {
                $authorId = Author::create(['name' => ucwords($author), 'slug' => Str::slug($author)])->id;
                $authors[$author] = $authorId;
            }

            $book = Book::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'author_id' => $authorId,
                'publisher_id' => $publishers[$publisher] ?? null,
                'description' => $this->descriptionFor($title),
                'content' => $this->contentFor($title),
                'cover_color' => $color,
                'pages' => $pages,
                'year' => $year,
                'language' => 'id',
                'views' => $views,
                'is_featured' => $featured,
                'is_published' => true,
            ]);

            $book->categories()->attach(collect($cats)->map(fn ($c) => $categories[$c] ?? null)->filter());

            if ($seriesName && isset($series[$seriesName])) {
                $book->seriesList()->attach($series[$seriesName], ['chapter_number' => 1]);
            }
        }
    }

    private function seedEngagement(): void
    {
        $books = Book::all();
        $users = User::all();

        // Review untuk buku populer.
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

        foreach ($books->take(16) as $i => $book) {
            $count = random_int(2, 5);
            $pickedUsers = $users->random(min($count, $users->count()));

            foreach ($pickedUsers as $user) {
                Review::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'rating' => random_int(3, 5),
                    'comment' => $comments[$i % count($comments)],
                    'created_at' => now()->subDays(random_int(1, 120)),
                ]);
            }

            $book->recalcRating();
        }

        // Progres & aktivitas user demo.
        $demo = User::where('email', 'user@senjapustaka.test')->first();
        if ($demo) {
            $seriSenja = Series::where('slug', 'seri-senja')->first();

            // Selesai 1 buku (Purnama Pertama).
            $finishedBook = $books->firstWhere('slug', 'purnama-pertama');
            if ($finishedBook) {
                ReadingProgress::create([
                    'user_id' => $demo->id,
                    'book_id' => $finishedBook->id,
                    'current_page' => $finishedBook->pages,
                    'progress_percent' => 100,
                    'status' => 'finished',
                    'finished_at' => now()->subDays(3),
                ]);
                Favorite::create(['user_id' => $demo->id, 'book_id' => $finishedBook->id]);
            }

            // 3 buku sedang dibaca.
            $readingSlugs = ['naga-penjaga-bulan', 'misteri-rumah-panggung', 'senja-di-ujung-lorong'];
            foreach ($readingSlugs as $slug) {
                $book = $books->firstWhere('slug', $slug);
                if (! $book) {
                    continue;
                }

                $page = (int) round($book->pages * [0.3, 0.55, 0.75][random_int(0, 2)]);
                ReadingProgress::create([
                    'user_id' => $demo->id,
                    'book_id' => $book->id,
                    'current_page' => $page,
                    'progress_percent' => (int) round($page / $book->pages * 100),
                    'status' => 'reading',
                ]);
            }

            // 2 favorit lain.
            foreach (['kota-di-bawah-langit', 'perpustakaan-malam'] as $slug) {
                $book = $books->firstWhere('slug', $slug);
                if ($book) {
                    Favorite::firstOrCreate(['user_id' => $demo->id, 'book_id' => $book->id]);
                }
            }

            // Goals tahun ini.
            ReadingGoal::create([
                'user_id' => $demo->id,
                'year' => now()->year,
                'target_books' => 24,
                'target_pages' => 6000,
            ]);

            // Review demo user.
            if ($seriSenja) {
                foreach ($seriSenja->books as $book) {
                    if (! Review::where('user_id', $demo->id)->where('book_id', $book->id)->exists()) {
                        Review::create([
                            'user_id' => $demo->id,
                            'book_id' => $book->id,
                            'rating' => 5,
                            'comment' => 'Seri favorit saya tahun ini!',
                        ]);
                        $book->recalcRating();
                    }
                }
            }

            // Beri badge yang sudah memenuhi syarat + notifikasi.
            Gamification::checkBadges($demo);
            Gamification::notify($demo, '🔥 Streak 7 hari!', 'Kamu membaca 7 hari berturut-turut. Pertahankan!');
            Gamification::notify($demo, '🎯 Target halaman hampir tercapai', 'Tinggal sedikit lagi menuju target tahunanmu.');
        }

        // Subscriber newsletter.
        $subs = ['salsa@example.com' => 'Salsa Putri', 'budi@example.com' => 'Budi Santoso', 'maya@example.com' => 'Maya Anggraini'];
        foreach ($subs as $email => $name) {
            NewsletterSubscriber::create([
                'email' => $email,
                'name' => $name,
                'token' => Str::random(40),
                'subscribed' => true,
            ]);
        }
    }

    private function descriptionFor(string $title): string
    {
        return "Sebuah kisah yang lahir di kala senja. \"{$title}\" mengajak pembaca menyelami dunia yang hangat, penuh warna, dan meninggalkan kesan lama setelah halaman terakhir ditutup. Cocok untuk menemani malam-malammu yang tenang.";
    }

    private function contentFor(string $title): string
    {
        $paragraphs = [
            "Bab 1 — Awal yang Tak Terduga\n\n".$title.' dimulai seperti hari-hari biasa. Langit jingga menutupi ufuk barat, dan angin sore membawa aroma tanah basah. Di sudut kota, seseorang tengah menatap jauh ke depan, seolah sedang menunggu sesuatu yang akan mengubah segalanya.',
            'Pintu kayu tua itu berderit pelan saat dibuka. Di dalamnya, debu menari-nari di sorot cahaya senja yang masuk lewat jendela. Udara terasa dingin, tetapi ada kehangatan aneh yang menyelimuti setiap sudut ruangan — seperti memori yang menunggu untuk diingat kembali.',
            'Ia menarik napas dalam-dalam. Di tangannya, sebuah catatan usang dengan tulisan tangan yang rapi. Setiap kata terasa seperti petunjuk, setiap baris seperti janji. "Kadang, untuk menemukan jawaban, kita harus berani tersesat lebih dulu," gumamnya lirih.',
            'Malam tiba lebih cepat dari perkiraan. Bintang-bintang bermunculan satu per satu, menaburi langit seperti permata yang jatuh dari genggaman malam. Di kejauhan, lampu-lampu kota berkelip pelan, berkedip seperti mata-mata kecil yang mengawasi perjalanan para pencerita.',
            'Pagi berikutnya datang dengan semangat baru. Matahari perlahan merangkak naik, membasuh kota dengan cahaya keemasan. Rencana demi rencana disusun ulang, keberanian dipungut kembali. Tidak ada jalan mundur — ia sudah memutuskan untuk melangkah sejauh cerita ini membawanya.',
            'Di tengah perjalanan, ia bertemu dengan mereka yang tak pernah disangka: seorang tua dengan seribu kisah di matanya, seorang anak kecil yang percaya penuh pada keajaiban, dan teman lama yang ternyata menyimpan rahasia yang sama besarnya dengan rahasia yang ia cari.',
            'Mereka berbagi cerita di bawah pohon besar yang rindang. Terdengar tawa, terdengar juga air mata yang tertahan. Dari situlah ia belajar bahwa setiap orang adalah buku dengan cerita masing-masing — dan membaca adalah cara terbaik untuk saling memahami.',
            'Hujan turun di sore yang kelima. Tetes-tetesnya mengetuk jendela seperti jari yang memainkan melodi pelan. Di dalam ruang kecil itu, sebuah keputusan besar akhirnya diambil. Kadang, jawaban tidak datang dengan keras — ia datang dengan tenang, seperti senja yang tiba tanpa suara.',
            'Hari terakhir tiba lebih cepat dari yang diinginkan. Namun, bukannya kesedihan, yang dirasakan adalah rasa syukur. Setiap jejak, setiap pelajaran, setiap tawa yang tertinggal — semua itu kini menjadi bagian dari dirinya. Seperti yang selalu dikatakan ibunya: "Yang baik akan selalu menemukan jalannya pulang."',
            "Bab Terakhir — Senja yang Baru\n\nKini, di ujung cerita, ia tersenyum. Langit sore kembali menyala jingga, sama seperti hari pertama. Tapi kali ini, ia tidak lagi menunggu. Ia berjalan — menuju babak baru yang menantinya, dengan hati yang penuh, dan cerita yang siap dibagikan kepada siapa pun yang mau mendengar.",
        ];

        // 8 paragraf, mulai dari indeks deterministik.
        $start = crc32($title) % 3;
        $picked = [];
        for ($i = 0; $i < 8; $i++) {
            $picked[] = $paragraphs[($start + $i) % count($paragraphs)];
        }

        return implode("\n\n", $picked);
    }
}
