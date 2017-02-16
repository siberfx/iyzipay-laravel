<?php


namespace Actuallymab\IyzipayLaravel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CreditCard extends Model
{

    protected $fillable = [
        'alias', 'number', 'token', 'bank'
    ];

    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
