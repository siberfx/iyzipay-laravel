# Iyzipay (by Iyzico) Integration for Laravel

This package is under development and all contributions are welcomed!

This package covers popular payment solution [iyzipay](https://github.com/iyzico/iyzipay-php) with eloquent support, so you can store credit cards and also create charges for your application's users! This package also implements a basic **Subscription** model for your users / organizations (whatever your Payable model is). So you can create plans for your users, and get paid.

Think this package like iyzipay version of popular [laravel cashier](https://github.com/laravel/cashier) package.

## Install

Add package to your composer.json file `"actuallymab/iyzipay-laravel": "dev-master"`. And run `composer install`.

Add service provider to your `app.php` file

``` php
\Actuallymab\IyzipayLaravel\IyzipayLaravelServiceProvider::class
```

Add IyzipayLaravel facade to your `app.php`file. This action helps to create plans for your payable models.

``` php
'IyzipayLaravel' => \Actuallymab\IyzipayLaravel\IyzipayLaravelFacade::class
```

Publish configuration file:

``` bash
$ php artisan vendor:publish
```

You must modify `config/iyzipay.php` file and map your payable model before running migration files, it is `App/User` by default. Change this line to your model that you want to get paid:

``` php
'billableModel' => 'App\User'
```

You can see other configurations on this configuration file, you must be sure that you have these environment variables and be sure about they are correct:

**.env**

```
IYZIPAY_BASE_URL=BASE_URL_HERE
IYZIPAY_API_KEY=YOUR_KEY_HERE
IYZIPAY_SECRET_KEY=YOUR_SECRET_KEY_HERE
```

After this action run migrate command.

``` bash
$ php artisan migrate
```

## Usage

Now you are ready to use this awesome package, just follow the instructions:

### 1- Payable implementation

First, `PayableContract` must be implemented by your payable model, and it must use `Payable` trait.

``` php
...
use Actuallymab\IyzipayLaravel\Payable;
use Actuallymab\IyzipayLaravel\PayableContract;

class User extends Authenticatable implements PayableContract
{
    use Notifiable, Payable;
    
    .....
}
```

### 2- Filling bill information for payable.

Let's continue with your `App/User` model. 

``` php
    use Actuallymab\IyzipayLaravel\StorableClasses\Address;
    use Actuallymab\IyzipayLaravel\StorableClasses\BillFields;

    $user = factory(\App\User::class)->create();
    $user->bill_fields = new BillFields([
            'first_name'       => 'Mehmet Aydın',
            'last_name'        => 'Bahadır',
            'email'            => 'mehmet.aydin.bahadir@gmail.com',
            'shipping_address' => new Address([
                'city'    => 'Beşiktaş, İstanbul',
                'country' => 'Türkiye',
                'address' => 'Mecidiye Mh.'
            ]),
            'billing_address'  => new Address([
                'city'    => 'Beşiktaş, İstanbul',
                'country' => 'Türkiye',
                'address' => 'Mecidiye Mh.'
            ]),
            'identity_number'  => '12345678901',
            'mobile_number'    => '05555555555'
        ]);
```

### 3- Storing credit card on iyzipay for your payable model.

``` php
    $user->addCreditCard([
        'alias'  => 'asd', 
        'number' => '5526080000000006',
        'month'  => '01'
        'year'   => '2030',
        'holder' => 'Mehmet Aydın Bahadır'
    ]);
```

### 4- Selling products through stored cards to your payables.

OK, now we want to sell products right? Then, your product model must be implement `ProductContract` of this package.

``` php
use Actuallymab\IyzipayLaravel\ProductContract;

class Product extends Model implements ProductContract
{
    public function getName()
    {
        // return product name here.
        return $this->name;
    }

    public function getPrice()
    {
	    // return price here
        return $this->price;
    }

    public function getCategory()
    {
        // return category here
        return $this->category;
    }

    public function getType()
    {
	    // return product type here, for ex;
        return BasketItemType::VIRTUAL;
    }
}
```

Now we can get paid, through `pay` function of our payable model.

``` php
	factory(App\Product::class, 2)->create();
	$user->pay(App\Product::all());
```

### 5- Creating plans that your users can subscribe.

You can create your plans at `AppServiceProvider.php` file's `register()` method. 

```php
	\IyzipayLaravel::plan('aylik-ucretsiz', 'Aylık Ücretsiz');
    \IyzipayLaravel::plan('aylik-standart', 'Aylık Standart')->trialDays(15)->price(20);
    \IyzipayLaravel::plan('aylik-platinum', 'Aylık Platinum')->trialDays(15)->price(40);
    \IyzipayLaravel::plan('yillik-kucuk', 'Yıllık Küçük')->yearly()->price(150);
    \IyzipayLaravel::plan('yillik-standart', 'Yıllık Standart')->yearly()->trialDays(15)->price(200);
    \IyzipayLaravel::plan('yillik-platinum', 'Yıllık Platinum')->yearly()->trialDays(15)->price(400);
```

As you can see, you can create monthly or yearly plans very easily, also you can define trial days for your plans.

Also you can filter your plans with `IyzipayLaravel::plans()` collection or `IyzipayLaravel::monthlyPlans()` || `IyzipayLaravel::yearlyPlans()` || `IyzipayLaravel::findPlan($id)`

### 6- Subscription to plans

You can subscribe your `PayableContract`'s to plans with just like this;

```php
	$user->subscribe(\IyzipayLaravel::findPlan($id));
```

**To get paid in time be sure that, you already mapped schedule runner to your cron file [like this](https://laravel.com/docs/5.4/scheduling#introduction).**

## Testing

For testing purposes after `composer install`, you must `cp .env.example .env` and modify `.env` file with your credentials. 

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

