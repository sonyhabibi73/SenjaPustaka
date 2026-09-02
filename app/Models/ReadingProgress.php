<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $book_id
 * @property int $current_page
 * @property int $progress_percent
 * @property string $status
 */
#[Fillable(['user_id', 'book_id', 'current_page', 'progress_percent', 'status', 'finished_at'])]
class ReadingProgress extends Model
{
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Book, $this> */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'int',
            'current_page' => 'int',
            'progress_percent' => 'int',
            'finished_at' => 'datetime',
        ];
    }
}
