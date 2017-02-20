<?php


namespace Actuallymab\IyzipayLaravel\Models;

use Actuallymab\IyzipayLaravel\Exceptions\Transaction\TransactionVoidException;
use Actuallymab\IyzipayLaravel\IyzipayLaravelFacade as IyzipayLaravel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{

    protected $fillable = [
        'amount',
        'products',
        'refunds',
        'iyzipay_key',
        'voided_at'
    ];

    protected $casts = [
        'products' => 'array',
        'refunds'  => 'array'
    ];

    protected $dates = [
        'voided_at'
    ];

    protected $appends = [
        'refunded_amount'
    ];

    public function billable(): BelongsTo
    {
        return $this->belongsTo(config('iyzipay.billableModel'), 'billable_id');
    }

    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(CreditCard::class);
    }

    public function void(): Transaction
    {
        if ($this->created_at < Carbon::today()->startOfDay()) {
            throw new TransactionVoidException('This transaction cannot be voided.');
        }

        return IyzipayLaravel::void($this);
    }

    public function refund($price = null, $productId = null): Transaction
    {
        return IyzipayLaravel::refund($this, $price, $productId);
    }

    public function getRefundedAmountAttribute()
    {
        $amount = 0;
        foreach ($this->refunds as $refund) {
            $amount += $refund['amount'];
        }

        return $amount;
    }
}
