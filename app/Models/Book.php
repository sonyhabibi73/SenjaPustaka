<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property int $author_id
 * @property int|null $publisher_id
 * @property string|null $description
 * @property string $cover_color
 * @property string|null $cover_image
 * @property string|null $file_path
 * @property string|null $content
 * @property int $pages
 * @property int|null $year
 * @property string $language
 * @property int $views
 * @property float $rating_avg
 * @property int $rating_count
 * @property bool $is_featured
 * @property bool $is_published
 */
#[Fillable([
    'title',
    'slug',
    'author_id',
    'publisher_id',
    'description',
    'cover_color',
    'cover_image',
    'file_path',
    'content',
    'pages',
    'year',
    'language',
    'views',
    'rating_avg',
    'rating_count',
    'is_featured',
    'is_published',
])]
class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    /** @return BelongsTo<Author, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    /** @return BelongsTo<Publisher, $this> */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    /** @return BelongsToMany<Category, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'book_category');
    }

    /** @return BelongsToMany<Series, $this> */
    public function seriesList(): BelongsToMany
    {
        return $this->belongsToMany(Series::class, 'book_series')->withPivot('chapter_number');
    }

    /** @return HasMany<Review, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->with('user')->latest();
    }

    /** @return HasMany<ReadingProgress, $this> */
    public function progress(): HasMany
    {
        return $this->hasMany(ReadingProgress::class);
    }

    /**
     * Hitung ulang rating agregat buku.
     */
    public function recalcRating(): void
    {
        $this->rating_avg = round((float) $this->reviews()->avg('rating'), 1);
        $this->rating_count = $this->reviews()->count();
        $this->save();
    }

    /**
     * Progres user tertentu untuk buku ini.
     */
    public function progressFor(?User $user): ?ReadingProgress
    {
        if (! $user) {
            return null;
        }

        return ReadingProgress::where('user_id', $user->id)->where('book_id', $this->id)->first();
    }

    /**
     * Path publik gambar cover (disk public), null jika tidak ada.
     *
     * Sengaja path relatif (bukan URL absolut): kalau pakai URL absolut,
     * APP_URL=http://localhost:8000 ikut tertanam di src, jadi di HP/device
     * lain gambar mengarah ke localhost device itu sendiri dan tidak tampil.
     * Path relatif selalu same-origin, jadi aman di host mana pun — pola yang
     * sama dengan path relatif dari route reader.file di ReaderController untuk PDF.
     */
    public function coverUrl(): ?string
    {
        return $this->cover_image
            ? '/storage/'.ltrim($this->cover_image, '/')
            : null;
    }

    /**
     * Gradien cover dari warna dasar.
     */
    public function coverGradient(): string
    {
        $darker = self::shadeHex($this->cover_color, -32);

        return sprintf('linear-gradient(165deg, %s 0%%, %s 100%%)', $this->cover_color, $darker);
    }

    /**
     * Gelapkan/terangkan hex color.
     */
    public static function shadeHex(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (strlen($hex) !== 6) {
            return '#2F5D52';
        }

        $rgb = array_map(fn ($h) => hexdec($h), str_split($hex, 2));
        $rgb = array_map(function ($c) use ($percent) {
            $c = (int) max(0, min(255, $c + (int) round($percent * 2.55)));

            return str_pad(dechex($c), 2, '0', STR_PAD_LEFT);
        }, $rgb);

        return '#'.implode('', $rgb);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'int',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'rating_avg' => 'float',
        ];
    }
}
