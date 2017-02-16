<?php


namespace Actuallymab\IyzipayLaravel\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{

    protected $fillable = [
        'amount',
        'products'
    ];

    protected $casts = [
        'products' => 'array'
    ];

    public function billable()
    {
        return $this->morphTo();
    }

    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(CreditCard::class);
    }

}