# Laravel Paytabs

[![Latest Version on Packagist](https://img.shields.io/packagist/v/devinweb/laravel-paytabs.svg?style=flat-square)](https://packagist.org/packages/devinweb/laravel-paytabs)
[![Total Downloads](https://img.shields.io/packagist/dt/devinweb/laravel-paytabs.svg?style=flat-square)](https://packagist.org/packages/devinweb/laravel-paytabs)
<a href="https://github.styleci.io/repos/534237521"><img src="https://github.styleci.io/repos/534237521/shield?branch=master" alt="StyleCI Shield"></a>
![GitHub Actions](https://github.com/devinweb/laravel-paytabs/actions/workflows/main.yml/badge.svg)


Laravel Paytabs makes integration with PayTabs payment gateway easier for Laravel developers, and that's by offering a wide range of functions to consume the paytabs transactions API.

## Installation

You can install the package via composer:

```bash
composer require devinweb/laravel-paytabs
```
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


## Requirements
This Package requires no external dependencies.
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
$isFollowUp = TransactionType::isFollowUpType($type);
$isInitiate = TransactionType::isInitiateType($type);

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
#### Set cart details

To initiate a payment, you need to set  the shopping cart details using the `setCart` function. A three-key array with the keys ***id**, **amount**, and **description** should form `$cart`. 
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
#### Set the customer details

To display the billing page prefilled on the payment page, you can set the customer details using this function. The `$user` should be an instance of `Illuminate\Database\Eloquent\Model` and can have some or all these fields: name, email, phone, address, city, state, country and, zip.
A value of type `LaravelPaytabsFacade` will be returned.
```php
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade;
$paytabs = LaravelPaytabsFacade::setCustomer($user);

```

#### Hide Shipping Details
You may occasionally include digital products among the services you offer to customers, in which case you won't require their shipping information. You can use this function to hide shipping details from the generated Hosted payment page. 
A value of type `LaravelPaytabsFacade` will be returned.
```php
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade;

$paytabs = LaravelPaytabsFacade::hideShipping($user);
```
### Framed Hosted Payment Page
To display the hosted payment page in an embed frame within the merchant website, you can call `framedPage`. 
A value of type `LaravelPaytabsFacade` will be returned.
```php
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade;

$paytabs = LaravelPaytabsFacade::framedPage($user);


```
#### Set redirect url
The redirect url must be included in your request. The user will be sent back to this URL after the payment has been processed. 
```php
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade;
$paytabs = LaravelPaytabsFacade::setRedirectUrl($url);

```
If you use one redirect url for all the payments inside your project, you can add it to the .env as explained [above](#paytabs-keys).
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
```json
{
  "tran_ref": "TST2110300142699",
  "tran_type": "Sale",
  "cart_id": "cart_11111",
  "cart_description": "Description of the items/services",
  "cart_currency": "EGP",
  "cart_amount": "100.00",
  "return": "https://devinweb.com/4b3af623-085f-4b82-ab22-cb6cedeba218",
  "redirect_url": "https://secure.paytabs.sa/payment/page/3F76B62E82E417E6AB2104212437A16EA53E657E75232A6C4C544962"
}
```

### Follow up a transaction
After initiating your payment, you may need to do some operations on it. 
#### Get a transaction by reference
You can use this function to retrieve the details of a transaction such as its status, type, etc.
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
    "name": "ALiaa Shafie",
    "email": "aliaa.ashafie@paytabs.com",
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
#### Refund, Capture or Void transaction
> Refund request is available for those Authenticated Sale transactions or Authenticated Capture transactions.

> Capture request is available for successfully Authenticated Authorize Transactions

> Void requests are available for Authenticated Authorize transactions that are not fully captured yet. 

To perform a refund, void or capture action on transaction using LaravaePaytabs, you need to set the transaction reference (`setTransactionRef`) and the cart details (`setCart`) as shown below, and pass the action type and class to the `followUpTransaction`.
```php
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade;
use Devinweb\LaravelPaytabs\Enums\TransactionClass;
use Devinweb\LaravelPaytabs\Enums\TransactionType;

LaravelPaytabsFacade::setCart($cart_data)
        ->setTransactionRef($payment->tran_ref)
        ->followUpTransaction(TransactionType::REFUND, TransactionClass::ECOM);
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

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
