<?php

namespace Actuallymab\IyzipayLaravel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Billable extends Model
{

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'identity_number',
        'shipping_address',
        'billing_address',
        'mobile_number',
        'iyzipay_key'
    ];

    protected $hidden = [
        'billable_id',
        'billable_type'
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'billing_address' => 'array'
    ];

    public function billable(): MorphTo
    {
        return $this->morphTo();
    }
}
