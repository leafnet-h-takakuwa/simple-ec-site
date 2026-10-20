<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'body',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'date',
        ];
    }

    // お知らせの新着判定
    public function isNew(): bool
    {
        return $this->is_published
            && $this->published_at !== null
            && $this->published_at->betweenIncluded(
                today()->subDays(30),
                today()
            );
    }
}
