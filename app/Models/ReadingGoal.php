<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $year
 * @property int $target_books
 * @property int $target_pages
 */
#[Fillable(['user_id', 'year', 'target_books', 'target_pages'])]
class ReadingGoal extends Model
{
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'int',
            'year' => 'int',
            'target_books' => 'int',
            'target_pages' => 'int',
        ];
    }
}
