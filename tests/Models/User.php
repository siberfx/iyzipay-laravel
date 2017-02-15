<?php

namespace Actuallymab\IyzipayLaravel\Tests\Models;

use Actuallymab\IyzipayLaravel\Payable;
use Actuallymab\IyzipayLaravel\PayableContract;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements PayableContract
{

    use Payable;

    protected $fillable = [
        'name'
    ];

    public $timestamps = false;
}
