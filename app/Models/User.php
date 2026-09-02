<?php

namespace App\Models;

use App\Services\Level;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $avatar
 * @property string|null $bio
 * @property bool $is_admin
 * @property int $points
 * @property int $streak_days
 * @property int $longest_streak
 * @property Carbon|null $last_read_at
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name',
    'email',
    'password',
    'avatar',
    'bio',
    'is_admin',
    'points',
    'streak_days',
    'longest_streak',
    'last_read_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'last_read_at' => 'datetime',
        ];
    }

    /** @return HasMany<Review, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** @return HasMany<Favorite, $this> */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /** @return BelongsToMany<Book, $this> */
    public function favoriteBooks(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'favorites');
    }

    /** @return HasMany<ReadingProgress, $this> */
    public function progress(): HasMany
    {
        return $this->hasMany(ReadingProgress::class);
    }

    /** @return HasMany<Bookmark, $this> */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /** @return HasMany<ReadingGoal, $this> */
    public function goals(): HasMany
    {
        return $this->hasMany(ReadingGoal::class);
    }

    /** @return BelongsToMany<Badge, $this> */
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badge')->withPivot('earned_at');
    }

    /** @return HasMany<Notification, $this> */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class)->latest();
    }

    /** @return HasMany<Notification, $this> */
    public function unreadNotifications(): HasMany
    {
        return $this->hasMany(Notification::class)->whereNull('read_at')->latest();
    }

    /**
     * Jumlah buku yang sudah selesai dibaca.
     */
    public function booksFinished(): int
    {
        return $this->progress()->where('status', 'finished')->count();
    }

    /**
     * Total halaman yang sudah dibaca (dari semua progres).
     */
    public function pagesRead(): int
    {
        return (int) $this->progress()->sum('current_page');
    }

    /**
     * Jumlah kategori berbeda yang pernah dibaca.
     */
    public function readingCategoriesCount(): int
    {
        return $this->progress()
            ->with('book.categories')
            ->get()
            ->flatMap(fn (ReadingProgress $p) => $p->book?->categories->pluck('id') ?? collect())
            ->unique()
            ->count();
    }

    /**
     * Info level & poin saat ini.
     *
     * @return array<string, mixed>
     */
    public function levelInfo(): array
    {
        return Level::for($this->points);
    }

    /**
     * Inisial nama untuk avatar.
     */
    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->name)) ?: [];

        return mb_strtoupper(mb_substr($parts[0] ?? '?', 0, 1).(isset($parts[1]) ? mb_substr($parts[1], 0, 1) : ''));
    }
}
