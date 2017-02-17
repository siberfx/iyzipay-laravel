<?php


namespace Actuallymab\IyzipayLaravel;

use Actuallymab\IyzipayLaravel\Models\CreditCard;
use Actuallymab\IyzipayLaravel\Models\Transaction;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

interface PayableContract
{

    public function getId();

    public function setBillFields(array $attributes = []): void;

    public function getBillFields(): array;

    public function creditCards(): MorphMany;

    public function transactions(): MorphMany;

    public function addCreditCard(array $attributes = []): CreditCard;

    public function removeCreditCard(CreditCard $creditCard): bool;

    public function pay(Collection $products, $currency = 'TRY', $installment = 1): Transaction;
}
