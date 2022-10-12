<p align="center"><img src="/art/socialcard.png" alt="Laravel Paytabs"></p>

# Laravel Paytabs

[![Latest Version on Packagist](https://img.shields.io/packagist/v/devinweb/laravel-paytabs.svg?style=flat-square)](https://packagist.org/packages/devinweb/laravel-paytabs)
[![Total Downloads](https://img.shields.io/packagist/dt/devinweb/laravel-paytabs.svg?style=flat-square)](https://packagist.org/packages/devinweb/laravel-paytabs)
<a href="https://github.styleci.io/repos/534237521"><img src="https://github.styleci.io/repos/534237521/shield?branch=master" alt="StyleCI Shield"></a>
[![codecov](https://codecov.io/gh/devinweb/laravel-paytabs/branch/master/graph/badge.svg?token=11LZHKWQL4)](https://codecov.io/gh/devinweb/laravel-paytabs)
![GitHub Actions](https://github.com/devinweb/laravel-paytabs/actions/workflows/main.yml/badge.svg)


Laravel Paytabs makes integration with PayTabs payment gateway easier for Laravel developers, and that's by offering a wide range of functions to consume the paytabs transactions API.

## Requirements
This package requires php 7.4 or higher.

## Installation

You can install the package via composer:

```bash
composer require devinweb/laravel-paytabs
```
## Database migration
This package provides a migration to handle the transactions. You need to publish the migration file after the installation.
```bash
php artisan vendor:publish --tag="paytabs-migrations"
```
Then, you need to migrate the transactions table.
```bash
php artisan migrate
```
# Setup and configuration
You can also publish the config file using
```bash
php artisan vendor:publish --tag="paytabs-config"
```
After that, you can see the file in app/paytabs.php and update it. You might need to change the model variable to use your custom User model.

## Paytabs Keys

Before being able to use this package, you should configure your paytabs environment in your application's .env file.
```
PAYTABS_SERVER_KEY=your-server-key
PAYTABS_PROFILE_ID=your-profile-id
# default SAR
CURRENCY=
# default SAU
PAYTABS_REGION=
# default https://secure.paytabs.sa/
PAYTABS_API=
PAYTABS_REDIRECT_URL=your-redirect-url
```

## Usage

### Transaction type enum
The transaction types that Paytabs supports are described by this abstract class. There are five different sorts of transactions: `sale`, `auth`, `refund`, `void`, and `capture`. 
This class makes it simple to find and use these values:
```php
use Devinweb\LaravelPaytabs\Enums\TransactionType;

$saleType = TransactionType::SALE;

```
It can be used also to validate if the given type is a follow-up type or an initiate one.
```php
use Devinweb\LaravelPaytabs\Enums\TransactionType;

$type = TransactionType::SALE;
$isFollowUp = TransactionType::isFollowUpType($type); // will return false
$isInitiate = TransactionType::isInitiateType($type); // will return true

```
### Transaction class enum
*TansactionClass* describes the possible values that the transaction class could take, just like TransactionType does. The value may be `recurring`, `ecom`, or `moto`. This package currently only supports the `ecom` type.

```php
use Devinweb\LaravelPaytabs\Enums\TransactionClass;

$type = TransactionClass::ECOM;

```
You can use this static function to obtain the supported values.

```php
use Devinweb\LaravelPaytabs\Enums\TransactionClass;

$types = TransactionClass::values;

```

### Initiate a payment (Genarate a hosted payment page)

To create a paytabs transaction using this package, you need to initiate the payment first to generate the form link. Then you can visit the generated page or embed it in your app to finalize the payment. The process to do that and the options available are described in the following sections.

#### Set the customer details
To initiate a payment, you need to specify the user model using setCustomer method. The `$user` should be an instance of `Illuminate\Database\Eloquent\Model` and the used fields are: name, email and phone. The phone is optional.
A value of type `LaravelPaytabsFacade` will be returned.
```php
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade;
$paytabs = LaravelPaytabsFacade::setCustomer($user);

```
#### Set cart details
You need also to set  the shopping cart details using the `setCart` function. A three-key array with the keys **id**, **amount**, and **description** should form `$cart`. 
A value of type `LaravelPaytabsFacade` will be returned.
```php
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade;

$cart = [
    'id' => "123",
    'amount' => 10,
    'description' => 'cart description'
];

$paytabs = LaravelPaytabsFacade::setCart($cart);
```
#### Set redirect url
The redirect url must be included in your request. The user will be sent back to this URL after the payment has been processed. 

```php
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade;
$paytabs = LaravelPaytabsFacade::setRedirectUrl($url);

```
If you use one redirect url for all the payments inside your project, you can add it to the .env as explained [above](#paytabs-keys).

#### Framed Hosted Payment Page
To display the hosted payment page in an embed frame within the merchant website, you can call `framedPage`. 
A value of type `LaravelPaytabsFacade` will be returned.
```php
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade;

$paytabs = LaravelPaytabsFacade::framedPage($user);

```

#### Billing

If you want to display the billing section prefilled on the payment page, you can set the billing details by creating the billing class using this command. You can find all the billing files in app/Billing the folder.
```bash
php artisan make:billing PaytabsBilling
```
then use
```php
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade;
use App\Billing\PaytabsBilling;

LaravelPaytabsFacade::addBilling(new PaytabsBilling);
```
The getData method of the created class should return an array with keys: `street1`, `city`, `state`, `country`, `zip` and `ip`.
If you added all this values, you can hide the billing section from the hosted payment page using hideBilling function.
```php
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade;
use App\Billing\PaytabsBilling;

LaravelPaytabsFacade::addBilling(new PaytabsBilling)->hideBilling();
```
#### Shipping
The same way as billing, you can use display the shipping section prefilled on the payment page.
Create the shipping class and then add the shipping details.
```bash
php artisan make:billing PaytabsShipping
```
```php
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade;
use App\Billing\PaytabsShipping;

LaravelPaytabsFacade::addShipping(new PaytabsShipping);
```
You may occasionally include digital products among the services you offer to customers, in which case you won't require their shipping information. You can use `hideShipping` option to hide shipping details section from the generated Hosted payment page. 
A value of type `LaravelPaytabsFacade` will be returned.
```php
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade;

$paytabs = LaravelPaytabsFacade::hideShipping();
```
If the billing details are already added, this function will hide the billing section as well.


#### Generate the hosted payment page
After setting all the payment page details and parameters, you need to use the `initiate` function to call the Paytabs Transactions Api and initiate your payment. The transaction type can be sale or auth.
```php
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade;
use Devinweb\LaravelPaytabs\Enums\TransactionClass;
use Devinweb\LaravelPaytabs\Enums\TransactionType;

LaravelPaytabsFacade::setCustomer($user)
            ->setCart($cart_data)
            ->setRedirectUrl($url)
            ->hideShipping()
            ->initiate(TransactionType::SALE, TransactionClass::ECOM);
```
If the payment page is created successfully, you will receive a response such as shown below including the payment page URL (**redirect_url**). 
A row with `status=pending` will be added to the transactions table and a `TransactionInitiated` event will be fired.
```json
{
  "tran_ref": "TST2110300142699",
  "tran_type": "Sale",
  "cart_id": "cart_11111",
  "cart_description": "Description of the items/services",
  "cart_currency": "EGP",
  "cart_amount": "100.00",
  "return": "https://devinweb.com/4b3af623-085f-4b82-ab22-cb6cedeba218",
  "redirect_url": "https://secure.paytabs.sa/payment/page/3F76B62E82E417E6AB2104212437A16EA53E657E75232A6C4C544962",
   "customer_details": {
    "name": "first last",
    "email": "email@domain.com",
    "phone": "0522222222",
    "street1": "address street",
    "city": "dubai",
    "state": "du",
    "country": "AE",
    "zip": "12345",
    "ip": "1.1.1.1"
  },

}
```
After visiting the generated URL and finalizing your payment, the transaction status will be changed to `paid` and you will be redirected to your return url. A `TransactionSucceed` or a `TransactionFail` event will be fired.
### Follow up a transaction
After initiating or finalizing your payment, you may need to do some operations on it. 
#### Get a transaction by reference
You can use this function to retrieve the details of a transaction such as its status, type, etc.
The `transactionRef` represents the reference of the transaction returned in the responses as `tran_ref` and stored in transactions table as `transaction_ref`.
```php
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade;
$transactionRef = "TST2105900091468";
$transaction = LaravelPaytabsFacade::setTransactionRef($transactionRef)
            ->getTransaction();
```
* Response
```json
{
  "tran_ref": "TST2105900091468",
  "tran_type": "Sale",
  "cart_id": "Sample Payment",
  "cart_description": "Sample Payment",
  "cart_currency": "EGP",
  "cart_amount": "1",
  "customer_details": {
    "name": "Imane Acherrat",
    "email": "imane@devinweb.com",
    "phone": "01005417901",
    "street1": "Alexandria",
    "city": "Alexandria",
    "state": "ALX",
    "country": "EG",
    "ip": "40.123.210.168"
  },
  "payment_result": {
    "response_status": "A",
    "response_code": "G15046",
    "response_message": "Authorised",
    "transaction_time": "2021-02-28T12:24:06Z"
  },
  "payment_info": {
    "card_type": "Credit",
    "card_scheme": "Visa",
    "payment_description": "4111 11## #### 1111"
  }
}
```
You can visit the paytabs [official documentation](https://support.paytabs.com/en/support/solutions/articles/60000711358-what-is-response-code-vs-the-response-status-) to learn more about payment_result section.
#### Refund, Capture or Void transaction
> Refund request is available for those Authenticated Sale transactions or Authenticated Capture transactions.

> Capture request is available for successfully Authenticated Authorize Transactions

> Void requests are available for Authenticated Authorize transactions that are not fully captured yet. 

To perform a refund, void or capture action on transaction using LaravelPaytabs, you need to set the user model (`setCustomer`), the transaction reference (`setTransactionRef`) and the cart details (`setCart`) as shown below, and pass the action type and class to the `followUpTransaction`.

```php
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade;
use Devinweb\LaravelPaytabs\Enums\TransactionClass;
use Devinweb\LaravelPaytabs\Enums\TransactionType;

LaravelPaytabsFacade::setCart($cart_data)
        ->setTransactionRef($payment->tran_ref)
        ->setCustomer(Auth::user())
        ->followUpTransaction(TransactionType::REFUND, TransactionClass::ECOM);
```
A `TransactionSucceed` or `TransactionFail` event will be fired.
### Events handlers
This package fires three events during the process of transaction: `TransactionInitiated`, `TransactionSucceed` and `TransactionFail`.
| Event                                               | Description            |
| --------------------------------------------------- | ---------------------- |
| Devinweb\LaravelPaytabs\Events\TransactionInitiated | Transaction initiated  |
| Devinweb\LaravelPaytabs\Events\TransactionSucceed   | Successful transaction |
| Devinweb\LaravelPaytabs\Events\TransactionFail      |  Transaction fail       |

The content of the events is the response returned by the transaction api.

#### Listener exemple

Use this laravel command to create a new listener
```bash
php artisan make:listener TransactionInitiatedListener
```
Then register the event with your listener in app/Providers/EventServiceProvider.
```php
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
         'Devinweb\LaravelPaytabs\Events\TransactionInitiated' => [
            'App\Listeners\TransactionInitiatedListener',
        ],
    ];

}
```


### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email imane@devinweb.com instead of using the issue tracker.

## Credits

-   [Imane Acherrat](https://github.com/devinweb)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Paytabs Boilerplate

You can use this repository to check the integration of the package [laravel-paytabs-boilerplate](https://github.com/devinweb/laravel-paytabs-boilerplate).
