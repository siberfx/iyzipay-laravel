<?php


namespace Actuallymab\IyzipayLaravel;

use Actuallymab\IyzipayLaravel\Models\CreditCard;
use Actuallymab\IyzipayLaravel\Models\Transaction;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

interface PayableContract
{

    public function getKey();

    public function creditCards(): HasMany;

    public function transactions(): HasMany;

    public function addCreditCard(array $attributes = []): CreditCard;

    public function removeCreditCard(CreditCard $creditCard): bool;

    public function pay(Collection $products, $currency = 'TRY', $installment = 1): Transaction;

    public function isBillable(): bool;
}
