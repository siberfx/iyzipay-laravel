<?php


namespace Actuallymab\IyzipayLaravel\Traits;

use Actuallymab\IyzipayLaravel\StorableClasses\Plan;
use Illuminate\Support\Collection;

trait ManagesPlans
{

    /**
     * All of plans defined for the application.
     *
     * @var array
     */
    public static $plans = [];

    /**
     * Create a new plan instance.
     *
     * @param  string $name
     *
     * @return Plan
     */
    public static function plan($name): Plan
    {
        static::$plans[] = $plan = (new Plan())->name($name);

        return $plan;
    }

    /**
     * Get the plans defined for the application.
     *
     * @return Collection
     */
    public static function plans(): Collection
    {
        return collect(static::$plans);
    }

    /**
     * Get all of the yearly plans.
     *
     * @return Collection
     */
    public static function yearlyPlans(): Collection
    {
        return static::plans()->where('interval', 'yearly');
    }

    /**
     * Get all of the yearly plans.
     *
     * @return Collection
     */
    public static function monthlyPlans(): Collection
    {
        return static::plans()->where('interval', 'monthly');
    }

    /**
     * Rollbacks plans
     *
     * @return void
     */
    public function rollbackPlans(): void
    {
        static::$plans = [];
    }
}
