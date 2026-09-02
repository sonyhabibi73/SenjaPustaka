<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $email
 * @property string|null $name
 * @property string|null $token
 * @property bool $subscribed
 */
#[Fillable(['email', 'name', 'token', 'subscribed'])]
class NewsletterSubscriber extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'int',
            'subscribed' => 'boolean',
        ];
    }
}
