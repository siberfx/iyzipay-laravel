<?php


use Actuallymab\IyzipayLaravel\Tests\Models\User;
use Actuallymab\IyzipayLaravel\Tests\TestCase;

class CreditCardsTest extends TestCase
{

    /** @test */
    public function must_set_bill_before_adding_credit_cards()
    {
        $user = $this->createUser();

        $this->expectException(\Actuallymab\IyzipayLaravel\Exceptions\BillFieldsException::class);
        $user->addCreditCard($this->prepareCreditCardFields());
    }

    /** @test */
    public function must_set_all_credit_card_fields()
    {
        $user = $this->prepareBilledUser();

        $this->expectException(\Actuallymab\IyzipayLaravel\Exceptions\CreditCardFieldsException::class);
        $user->addCreditCard([
            'alias' => $this->faker->word
        ]);
    }

    /** @test */
    public function add_credit_card_operations_returns_card_model()
    {
        $user = $this->prepareBilledUser();

        $this->assertInstanceOf(
            \Actuallymab\IyzipayLaravel\Models\CreditCard::class,
            $user->addCreditCard($this->prepareCreditCardFields())
        );

        $this->assertEquals(1, $user->creditCards->count());
        $this->assertNotEmpty($user->getBillFields()['iyzipay_key']);
    }

    /** @test */
    public function remove_credit_card_operations_return_true_if_succeed()
    {
        $user = $this->prepareBilledUser();
        $creditCard = $user->addCreditCard($this->prepareCreditCardFields());

        $this->assertTrue($user->removeCreditCard($creditCard));
        $this->assertEquals(0, $user->fresh()->creditCards->count());
    }

    protected function prepareCreditCardFields(): array
    {
        return [
            'alias' => $this->faker->word,
            'holder' => $this->faker->name,
            'number' => $this->faker->randomElement($this->correctCardNumbers()),
            'month' => '01',
            'year' => '2030'
        ];
    }

    protected function prepareBilledUser(): User
    {
        $user = $this->createUser();
        $user->setBillFields($this->prepareBillFields());

        return $user;
    }

    protected function correctCardNumbers(): array
    {
        return [
            '5890040000000016',
            '5526080000000006',
            '4766620000000001',
            '4603450000000000',
            '4987490000000002',
            '5400010000000004',
            '6221060000000004'
        ];
    }
}
