<?php


namespace Actuallymab\IyzipayLaravel\Tests;

use Actuallymab\IyzipayLaravel\IyzipayLaravel;

class SubscriptionTest extends TestCase
{

    /** @test */
    public function users_can_subscribe_plans()
    {
        $this->createPlans();
        $user = $this->createUser();

        $plan = IyzipayLaravel::monthlyPlans()->first();
        $anotherPlan = IyzipayLaravel::yearlyPlans()->first();
        $user->subscribe($plan);

        $this->assertEquals(1, $user->subscriptions->count());
        $this->assertTrue($user->isSubscribeTo($plan));
        $this->assertFalse($user->isSubscribeTo($anotherPlan));

        $user->subscribe($anotherPlan);
        $user = $user->fresh();
        $this->assertEquals(2, $user->subscriptions->count());
        $this->assertTrue($user->isSubscribeTo($plan));
        $this->assertTrue($user->isSubscribeTo($anotherPlan));
    }
}
