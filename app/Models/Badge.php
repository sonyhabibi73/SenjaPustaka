<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $emoji
 * @property string $criteria_key
 * @property int $criteria_value
 * @property string|null $description
 */
#[Fillable(['name', 'slug', 'emoji', 'criteria_key', 'criteria_value', 'description'])]
class Badge extends Model
{
    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badge')->withPivot('earned_at');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'int',
            'criteria_value' => 'int',
        ];
    }
}
