<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $emoji Nama ikon Lucide (sebelumnya emoji), lihat iconName()
 * @property string|null $description
 */
#[Fillable(['name', 'slug', 'emoji', 'description'])]
class Category extends Model
{
    /** @return BelongsToMany<Book, $this> */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'book_category');
    }

    /**
     * Nama ikon Lucide untuk kategori. Kolom emoji kini menyimpan nama ikon
     * (contoh: "book-open", "sparkles"); kalau masih berisi emoji asli,
     * dipetakan dari slug agar tetap konsisten.
     */
    public function iconName(): string
    {
        $stored = trim((string) $this->emoji);

        if ($stored !== '' && preg_match('/^[a-z][a-z0-9-]*$/', $stored)) {
            return $stored;
        }

        return match ($this->slug) {
            'fantasi' => 'sparkles',
            'romantis' => 'heart',
            'misteri' => 'search',
            'sejarah' => 'landmark',
            'teknologi' => 'cpu',
            'sains' => 'atom',
            'bisnis' => 'trending-up',
            'komik' => 'palette',
            'self-help' => 'sprout',
            'puisi' => 'feather',
            'biografi' => 'user',
            default => 'book-open',
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'int',
        ];
    }
}
