<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'postal_code',
        'address',
        'phone',
        'email',
        'memo',
        'is_orderer',
        'is_recipient',
    ];

    protected function casts(): array
    {
        return [
            'is_orderer' => 'boolean',
            'is_recipient' => 'boolean',
        ];
    }
}
