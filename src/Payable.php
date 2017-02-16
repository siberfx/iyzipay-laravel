<?php

namespace Actuallymab\IyzipayLaravel;

use Actuallymab\IyzipayLaravel\Exceptions\Fields\AddressFieldsException;
use Actuallymab\IyzipayLaravel\Exceptions\Fields\BillFieldsException;
use Actuallymab\IyzipayLaravel\Models\Billable;
use Actuallymab\IyzipayLaravel\Models\CreditCard;
use Actuallymab\IyzipayLaravel\Models\Transaction;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Validator;
use Actuallymab\IyzipayLaravel\IyzipayLaravelFacade as IyzipayLaravel;

trait Payable
{

    public function getId()
    {
        return $this->getKey();
    }

    /**
     * @param array $attributes
     * @throws BillFieldsException
     * @throws AddressFieldsException
     */
    public function setBillFields(array $attributes = []): void
    {
        if (!empty($this->fresh()->billable)) {
            $this->updateBillAttributes($attributes);

            return;
        }

        $this->validateBillAttributes($attributes);

        $this->billable()->save(new Billable($attributes));
    }

    public function getBillFields(): array
    {
        return (!empty($this->billable)) ? $this->billable->toArray() : [];
    }

    public function creditCards(): MorphMany
    {
        return $this->morphMany(CreditCard::class, 'billable');
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'billable');
    }

    public function addCreditCard(array $attributes = []): CreditCard
    {
        return IyzipayLaravel::addCreditCard($this, $attributes);
    }

    public function removeCreditCard(CreditCard $creditCard): bool
    {
        return IyzipayLaravel::removeCreditCard($creditCard);
    }

    public function pay($products, $currency = 'TRY', $installment = 1)
    {
        return IyzipayLaravel::singlePayment($this, $products, $currency, $installment);
    }

    protected function billable(): MorphOne
    {
        return $this->morphOne(Billable::class, 'billable');
    }

    /**
     * @param $attributes
     */
    private function updateBillAttributes($attributes): void
    {
        $this->billable()->save($this->billable->fill($attributes));
    }

    /**
     * @param $attributes
     * @throws BillFieldsException
     * @throws AddressFieldsException
     */
    private function validateBillAttributes($attributes): void
    {
        $v = Validator::make($attributes, [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'shipping_address' => 'required|array',
            'billing_address' => 'required|array',
            'identity_number' => 'required',
            'mobile_number' => 'required'
        ]);

        if ($v->fails()) {
            throw new BillFieldsException();
        }

        $this->validateAddressAttributes($attributes['shipping_address']);
        $this->validateAddressAttributes($attributes['billing_address']);
    }

    /**
     * @param $attributes
     * @throws AddressFieldsException
     */
    private function validateAddressAttributes($attributes): void
    {
        $v = Validator::make($attributes, [
            'city' => 'required',
            'country' => 'required',
            'address' => 'required'
        ]);

        if ($v->fails()) {
            throw new AddressFieldsException();
        }
    }

    abstract public function getKey();
}
