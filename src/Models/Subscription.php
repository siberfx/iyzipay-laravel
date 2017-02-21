<?php


namespace Actuallymab\IyzipayLaravel\Models;

use Actuallymab\IyzipayLaravel\StorableClasses\Plan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{

    public function owner(): BelongsTo
    {
        return $this->belongsTo(config('iyzipay.billableModel'), 'billable_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function canceled(): bool
    {
        return ! empty($this->canceled_at);
    }

    public function setPlanAttribute($value)
    {
        $this->attributes['plan'] = (string)$value;
    }

    public function getPlanAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return (new \JsonMapper())->map(json_decode($value), new Plan());
    }
}
