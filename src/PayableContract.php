<?php


namespace Actuallymab\IyzipayLaravel;

use Actuallymab\IyzipayLaravel\Models\CreditCard;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface PayableContract
{

    public function setBillFields(array $attributes = []): void;

    public function getBillFields(): array;

    public function creditCards(): MorphMany;

    public function addCreditCard(array $attributes = []): CreditCard;

//    public function removeCreditCard(CreditCard $creditCard): bool;
}
