<?php

namespace Actuallymab\IyzipayLaravel;

use Actuallymab\IyzipayLaravel\Exceptions\Card\CardRemoveException;
use Actuallymab\IyzipayLaravel\Models\CreditCard;
use Actuallymab\IyzipayLaravel\Models\Subscription;
use Actuallymab\IyzipayLaravel\Models\Transaction;
use Actuallymab\IyzipayLaravel\StorableClasses\BillFields;
use Actuallymab\IyzipayLaravel\StorableClasses\Plan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
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

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'billable_id');
    }

    public function addCreditCard(array $attributes = []): CreditCard
    {
        return IyzipayLaravel::addCreditCard($this, $attributes);
    }

    public function removeCreditCard(CreditCard $creditCard): bool
    {
        if (! $this->creditCards->contains($creditCard)) {
            throw new CardRemoveException('This card does not belong to member!');
        }

        return IyzipayLaravel::removeCreditCard($creditCard);
    }

    public function pay(Collection $products, $currency = 'TRY', $installment = 1, $subscription = false): Transaction
    {
        return IyzipayLaravel::singlePayment($this, $products, $currency, $installment, $subscription);
    }

    public function subscribe(Plan $plan): void
    {
        Model::unguard();

        $this->subscriptions()->save(
            new Subscription([
                'next_charge_amount' => $plan->price,
                'currency'           => $plan->currency,
                'next_charge_at'     => Carbon::now()->addDays($plan->trialDays)->startOfDay(),
                'plan'               => $plan
            ])
        );

        $this->paySubscription();

        Model::reguard();
    }

    public function isSubscribeTo(Plan $plan): bool
    {
        foreach ($this->subscriptions as $subscription) {
            if (! $subscription->canceled() &&
                 $subscription->plan == $plan &&
                 $subscription->next_charge_at > Carbon::today()->startOfDay()
            ) {
                return true;
            }
        }

        return false;
    }

    public function paySubscription()
    {
        foreach ($this->subscriptions as $subscription) {
            if ($subscription->canceled() || $subscription->next_charge_at > Carbon::today()->startOfDay()) {
                continue;
            }

            $transaction = $this->pay(collect([$subscription->plan]), $subscription->plan->currency, 1, true);
            $transaction->subscription()->associate($subscription);
            $transaction->save();

            $subscription->next_charge_at = $subscription->next_charge_at->addMonths(($subscription->plan->interval == 'yearly') ? 12 : 1);
            $subscription->save();
        }
    }

    public function isBillable(): bool
    {
        return ! empty($this->bill_fields);
    }
}
