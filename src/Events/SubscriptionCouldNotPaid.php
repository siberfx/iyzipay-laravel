<?php


namespace Actuallymab\IyzipayLaravel\Events;


use Actuallymab\IyzipayLaravel\PayableContract as Payable;

class SubscriptionCouldNotPaid
{

    /**
     * @var Payable
     */
    public $payable;

    public function __construct(Payable $payable)
    {
        $this->payable = $payable;
    }

}