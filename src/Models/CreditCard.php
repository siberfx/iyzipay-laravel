<?php


namespace Actuallymab\IyzipayLaravel\Models;

use Illuminate\Database\Eloquent\Model;

class CreditCard extends Model
{

    protected $fillable = [
        'alias', 'number', 'token', 'bank'
    ];

    public function billable()
    {
        return $this->morphTo();
    }
}
