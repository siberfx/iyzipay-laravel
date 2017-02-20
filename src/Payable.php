<?php

namespace Actuallymab\IyzipayLaravel;

use Actuallymab\IyzipayLaravel\Exceptions\Card\CardRemoveException;
use Actuallymab\IyzipayLaravel\Models\CreditCard;
use Actuallymab\IyzipayLaravel\Models\Transaction;
use Actuallymab\IyzipayLaravel\StorableClasses\BillFields;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Actuallymab\IyzipayLaravel\IyzipayLaravelFacade as IyzipayLaravel;

trait Payable
{

    public function setBillFieldsAttribute($value)
    {
        $this->attributes['bill_fields'] = (string)$value;
    }

    /**
     * @param $value
     *
     * @return object
     */
    public function getBillFieldsAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return (new \JsonMapper())->map(json_decode($value), new BillFields());
    }

    public function creditCards(): HasMany
    {
        return $this->hasMany(CreditCard::class, 'billable_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'billable_id');
    }

    public function addCreditCard(array $attributes = []): CreditCard
    {
        return IyzipayLaravel::addCreditCard($this, $attributes);
    }

    public function removeCreditCard(CreditCard $creditCard): bool
    {
        if ( ! $this->creditCards->contains($creditCard)) {
            throw new CardRemoveException('This card does not belong to member!');
        }

        return IyzipayLaravel::removeCreditCard($creditCard);
    }

    public function pay(Collection $products, $currency = 'TRY', $installment = 1): Transaction
    {
        return IyzipayLaravel::singlePayment($this, $products, $currency, $installment);
    }

    public function isBillable(): bool
    {
        return ! empty($this->bill_fields);
    }
}
