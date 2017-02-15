<?php

namespace Actuallymab\IyzipayLaravel;

use Actuallymab\IyzipayLaravel\Exceptions\AddressFieldsException;
use Actuallymab\IyzipayLaravel\Exceptions\BillFieldsException;
use Actuallymab\IyzipayLaravel\Models\Billable;
use Actuallymab\IyzipayLaravel\Models\CreditCard;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Validator;
use Actuallymab\IyzipayLaravel\IyzipayLaravelFacade as IyzipayLaravel;

trait Payable
{

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

    public function addCreditCard(array $attributes = []): CreditCard
    {
        return IyzipayLaravel::addCreditCard($this, $attributes);
    }

    public function removeCreditCard(CreditCard $creditCard): bool
    {
        return IyzipayLaravel::removeCreditCard($this, $creditCard);
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
}
