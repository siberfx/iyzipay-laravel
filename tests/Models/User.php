<?php

namespace Iyzico\IyzipayLaravel\Tests\Models;

use Iyzico\IyzipayLaravel\Payable;
use Iyzico\IyzipayLaravel\PayableContract;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements PayableContract
{
    use Payable;

    protected $fillable = [
        'name'
    ];

    public $timestamps = false;
}
