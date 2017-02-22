[![Stories in Ready](https://badge.waffle.io/actuallymab/iyzipay-laravel.png?label=ready&title=Ready)](https://waffle.io/actuallymab/iyzipay-laravel)
# Iyzipay (by Iyzico) Integration for Laravel

This package is under development!

---

For testing purposes after `composer install --prefer-dist`, you must `cp .env.example .env` and modify `.env` file with your credentials. 

## Roadmap:
* Bill Storage ✓
* Card Storage ✓
    * Add Credit Card ✓
    * Remove Credit Card ✓
* Single Charges ✓
    * Collect Charges ✓
    * Void Charges ✓
    * Refund Charges
* Subscriptions
    * Creating Plans ✓
    * Creating Subscriptions ✓ 
    * Canceling Subscriptions ✓
* Documentation


1- Service Provider include
2- User implements & trait
3- Product model creation & implements

$user = factory(\App\User::class)->create();
$user->bill_fields = new Actuallymab\IyzipayLaravel\StorableClasses\BillFields(['first_name' => 'asd', 'last_name' => 'asd', 'email' => 'asd@asd.com', 'shipping_address' => new Actuallymab\IyzipayLaravel\StorableClasses\Address(['city' => 'Hatay', 'country' => 'Türkiye', 'address' => 'deneme']), 'billing_address' => new Actuallymab\IyzipayLaravel\StorableClasses\Address(['city' => 'Hatay', 'country' => 'Türkiye', 'address' => 'Deneme']), 'identity_number' => '12312312312', 'mobile_number' => '05555555555']);
$user->addCreditCard(['alias' => 'asd', 'holder' => 'Deneme', 'number' => '5526080000000006', 'month' => '01', 'year' => '2030']);
factory(App\Product::class, 2)->create();
$user->pay(App\Product::all());
$user->subscribe(\IyzipayLaravel::plans()->first());