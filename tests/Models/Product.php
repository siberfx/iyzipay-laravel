<?php


namespace Models;


use Actuallymab\IyzipayLaravel\ProductContract;
use Illuminate\Database\Eloquent\Model;

class Product extends Model implements ProductContract
{

    protected $fillable = [
        'name',
        'price',
        'category'
    ];

    public function getName()
    {
        return $this->name;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getCategory()
    {
        return $this->category;
    }

}