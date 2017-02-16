<?php


namespace Actuallymab\IyzipayLaravel\Tests;


use Actuallymab\IyzipayLaravel\Exceptions\Card\PayableMustHaveCreditCardException;
use Actuallymab\IyzipayLaravel\Exceptions\Fields\BillFieldsException;
use Actuallymab\IyzipayLaravel\Exceptions\Fields\TransactionFieldsException;
use Actuallymab\IyzipayLaravel\Exceptions\Transaction\TransactionSaveException;
use Actuallymab\IyzipayLaravel\Models\Transaction;
use Actuallymab\IyzipayLaravel\Tests\Models\User;
use Models\Product;
use Iyzipay\Model\Currency;

class TransactionTest extends TestCase
{

    /** @test */
    public function must_set_bill_information_before_transaction()
    {
        $user = $this->createUser();

        $this->expectException(BillFieldsException::class);
        $user->pay($this->prepareProducts());
    }

    /** @test */
    public function payable_must_have_credit_card_before_transaction()
    {
        $user = $this->prepareBilledUser();

        $this->expectException(PayableMustHaveCreditCardException::class);
        $user->pay($this->prepareProducts());
    }

    /** @test */
    public function currency_must_be_in_allowed_form()
    {
        $user = $this->prepareUserHasCard();

        $this->expectException(TransactionFieldsException::class);
        $user->pay($this->prepareProducts(), 'ASD');
    }

    /** @test */
    public function installment_must_be_greater_that_zero()
    {
        $user = $this->prepareUserHasCard();

        $this->expectException(TransactionFieldsException::class);
        $user->pay($this->prepareProducts(), Currency::TL, 0);
    }

    /** @test */
    public function success_transaction_operation_returns_transaction_model()
    {
        $user = $this->prepareUserHasCard();
        $products = $this->prepareProducts();

        try {
            $this->assertInstanceOf(Transaction::class, $user->pay($products));
            $this->assertEquals(1, $user->transactions->count());
        } catch (TransactionSaveException $e) {
            if ($e->getMessage() != 'System error') {
                throw $e;
            }
        }
    }

    protected function prepareUserHasCard(): User
    {
        $user = $this->prepareBilledUser();
        $user->addCreditCard($this->prepareCreditCardFields());

        return $user->fresh();
    }

    protected function prepareProducts($count = 5): array
    {
        $products = [];
        for ($i = 0; $i < $count; $i++) {
            $products[] = Product::create([
                'name' => $this->faker->word,
                'price' => $this->faker->numberBetween(1,100),
                'category' => $this->faker->word
            ]);
        }

        return $products;
    }

}