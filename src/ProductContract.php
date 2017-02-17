<?php


namespace Actuallymab\IyzipayLaravel;


interface ProductContract
{

    public function getKey();

    public function getName();

    public function getPrice();

    public function getCategory();

    public function getType();

}