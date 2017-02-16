<?php

namespace Actuallymab\IyzipayLaravel\Tests;

class BillTest extends TestCase
{

    /** @test */
    public function must_set_all_required_fields_for_bill()
    {
        $user = $this->createUser();

        $this->expectException(\Actuallymab\IyzipayLaravel\Exceptions\Fields\BillFieldsException::class);
        $user->setBillFields([
            'first_name' => $this->faker->firstName
        ]);
    }

    /** @test */
    public function check_bill_fields_has_been_set_correct_and_can_be_updated_after_creation()
    {
        $user = $this->createUser();

        $billFields = $this->prepareBillFields();

        $user->setBillFields($billFields);

        $this->assertEquals($billFields['first_name'], $user->getBillFields()['first_name']);
        $this->assertEquals($billFields['last_name'], $user->getBillFields()['last_name']);
        $this->assertEquals($billFields['email'], $user->getBillFields()['email']);
        $this->assertEquals(
            $billFields['shipping_address']['city'],
            $user->getBillFields()['shipping_address']['city']
        );
        $this->assertEquals(
            $billFields['shipping_address']['country'],
            $user->getBillFields()['shipping_address']['country']
        );
        $this->assertEquals(
            $billFields['shipping_address']['address'],
            $user->getBillFields()['shipping_address']['address']
        );
        $this->assertEquals($billFields['billing_address']['city'], $user->getBillFields()['billing_address']['city']);
        $this->assertEquals(
            $billFields['billing_address']['country'],
            $user->getBillFields()['billing_address']['country']
        );
        $this->assertEquals(
            $billFields['billing_address']['address'],
            $user->getBillFields()['billing_address']['address']
        );
        $this->assertEquals($billFields['identity_number'], $user->getBillFields()['identity_number']);
        $this->assertEquals($billFields['mobile_number'], $user->getBillFields()['mobile_number']);

        $user->setBillFields([
            'first_name' => $firstName = $this->faker->firstName
        ]);

        $this->assertEquals($firstName, $user->getBillFields()['first_name']);

        $user->setBillFields([
            'last_name' => $lastName = $this->faker->lastName
        ]);

        $this->assertEquals($lastName, $user->getBillFields()['last_name']);
    }
}
