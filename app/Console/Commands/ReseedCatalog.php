<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\Book;
use App\Models\Bookmark;
use App\Models\Favorite;
use App\Models\Publisher;
use App\Models\ReadingProgress;
use App\Models\Review;
use App\Models\Series;
use Database\Seeders\BookCatalogSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReseedCatalog extends Command
{
    /**
     * Enam penulis nyata yang wajib dipertahankan (lihat BookCatalogSeeder::AUTHORS).
     */
    private const KEEP_AUTHORS = [
        'nurwina-sari',
        'tere-liye',
        'leila-s-chudori',
        'brian-khrisna',
        'eka-kurniawan',
        'shima-nanigashi',
    ];

    /**
     * Tiga seri placeholder buatan seeder lama — kini tidak lagi punya buku.
     */
    private const PLACEHOLDER_SERIES = ['seri-senja', 'legenda-nusantara', 'detektif-lorong'];

    protected $signature = 'catalog:reseed
                            {--force : Jalankan tanpa konfirmasi}';

    protected $description = 'Hapus katalog placeholder dan data demo, lalu seed ulang 14 buku nyata';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Ini akan menghapus SEMUA buku, review, progres, favorit, bookmark, penulis, seri, dan penerbit placeholder. Lanjutkan?')) {
            $this->comment('Dibatalkan.');

            return self::SUCCESS;
        }

        $this->info('Membersihkan katalog lama...');

        // 1. Data yang menempel pada buku (harus duluan karena ada foreign key).
        $deleted = [
            'reviews' => Review::query()->delete(),
            'reading_progress' => ReadingProgress::query()->delete(),
            'favorites' => Favorite::query()->delete(),
            'bookmarks' => Bookmark::query()->delete(),
        ];

        // 2. Pivot kategori & seri.
        $deleted['book_category'] = DB::table('book_category')->delete();
        $deleted['book_series'] = DB::table('book_series')->delete();

        // 3. Buku.
        $deleted['books'] = Book::query()->delete();

        // 4. Penulis placeholder (enam penulis nyata dipertahankan).
        $deleted['authors'] = Author::whereNotIn('slug', self::KEEP_AUTHORS)->delete();

        // 5. Seri placeholder yang kini menggantung.
        $deleted['series'] = Series::whereIn('slug', self::PLACEHOLDER_SERIES)->delete();

        // 6. Penerbit placeholder yang tidak lagi dipakai. Aman dihapus di sini:
        //    semua buku sudah dibersihkan dan FK books.publisher_id memakai
        //    nullOnDelete. Empat penerbit nyata dibuat ulang oleh seeder.
        $keepPublishers = array_column(BookCatalogSeeder::PUBLISHERS, 1);
        $deleted['publishers'] = Publisher::whereNotIn('slug', $keepPublishers)->delete();

        foreach ($deleted as $table => $count) {
            $this->line(sprintf('  %-16s %d baris', $table, $count));
        }

        $this->newLine();
        $this->info('Menanam 14 buku nyata...');

        $this->call(BookCatalogSeeder::class);

        $this->newLine();
        $this->info('Selesai. Data review/progres/favorit sengaja tidak dibuat ulang (reset total).');
        $this->line('Jalankan <info>php artisan db:seed --class=EngagementSeeder</info> bila data demo dibutuhkan lagi.');

        return self::SUCCESS;
    }
}
