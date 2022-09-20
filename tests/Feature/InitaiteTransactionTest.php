<?php

namespace Devinweb\LaravelPaytabs\Tests\Feature;

use Devinweb\LaravelPaytabs\Enums\TransactionClass;
use Devinweb\LaravelPaytabs\Enums\TransactionType;
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade as LaravelPaytabs;
use Devinweb\LaravelPaytabs\Support\HttpRequest;
use Devinweb\LaravelPaytabs\Tests\TestCase;

class InitiateTransactionTest extends TestCase
{

    protected $user;

    protected $cart;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createCustomer();
        $this->cart = $this->createCart();
    }

    /** @test */
    public function cannot_initiate_a_transaction_with_invalid_transaction_type()
    {
        $this->expectException(\InvalidArgumentException::class);

        $transactionType = $this->faker->randomElement([TransactionType::REFUND, TransactionType::CAPTURE, TransactionType::VOID]);
        $transaction = LaravelPaytabs::setCustomer($this->user)->setCart($this->cart)->initiate($transactionType, TransactionClass::ECOM);

    }

    /** @test */
    public function cannot_initiate_a_transaction_with_invalid_transaction_class()
    {
        $this->expectException(\InvalidArgumentException::class);

        $transactionClass = $this->faker->word;
        $transaction = LaravelPaytabs::setCustomer($this->user)->setCart($this->cart)->initiate(TransactionType::SALE, $transactionClass);

    }
    /** @test */
    public function a_transaction_can_be_initiated_successfully()
    {
        $config = LaravelPaytabs::config();
        $transactionType = $this->faker->randomElement([TransactionType::SALE, TransactionType::AUTH]);

        $mock = $this->getMockBuilder(HttpRequest::class)
            ->setMethods(['post'])
            ->getMock();

        $mock->expects($this->once())->method('post')
            ->with($this->equalTo($config->get('paytabs_api') . "payment/request"),
                $this->equalTo([
                    "profile_id" => $config->get('profile_id'),
                    "tran_type" => $transactionType,
                    "tran_class" => TransactionClass::ECOM,
                    "paypage_lang" => $config->get('lang') ?: app()->getLocale(),
                    "return" => $config->get('redirect_url'),
                    "cart_amount" => $this->cart['amount'],
                    "cart_currency" => $config->get('currency'),
                    "cart_id" => $this->cart['id'],
                    "cart_description" => $this->cart['description'],
                ])
            );
        $transaction = LaravelPaytabs::injectHttpRequestHandler($mock)
            ->setCart($this->cart)
            ->initiate($transactionType, TransactionClass::ECOM);
    }

    /** @test */
    public function redirect_url_is_added_to_initiate_transaction_request()
    {
        $config = LaravelPaytabs::config();
        $url = $this->faker->url;
        $transactionType = $this->faker->randomElement([TransactionType::SALE, TransactionType::AUTH]);

        $mock = $this->getMockBuilder(HttpRequest::class)
            ->setMethods(['post'])
            ->getMock();

        $mock->expects($this->once())->method('post')
            ->with(
                $this->equalTo($config->get('paytabs_api') . "payment/request"),
                $this->callback(function ($attributes) use ($url) {
                    return $attributes['return'] == $url;
                })
            );
        $transaction = LaravelPaytabs::injectHttpRequestHandler($mock)
            ->setRedirectUrl($url)
            ->setCart($this->cart)
            ->initiate($transactionType, TransactionClass::ECOM);
    }

    /** @test */
    public function customer_details_are_added_to_initiate_transaction_request()
    {
        $config = LaravelPaytabs::config();
        $transactionType = $this->faker->randomElement([TransactionType::SALE, TransactionType::AUTH]);

        $mock = $this->getMockBuilder(HttpRequest::class)
            ->setMethods(['post'])
            ->getMock();

        $mock->expects($this->once())->method('post')
            ->with(
                $this->equalTo($config->get('paytabs_api') . "payment/request"),
                $this->callback(function ($attributes) {
                    return $attributes["customer_details"] == [
                        "name" => $this->user->name,
                        "email" => $this->user->email,
                        "phone" => $this->user->phone,
                        "street1" => $this->user->address,
                        "city" => $this->user->city,
                        "state" => $this->user->state,
                        "country" => $this->user->country,
                        "zip" => $this->user->zip,
                        "ip" => "",
                    ];
                })
            );
        $transaction = LaravelPaytabs::injectHttpRequestHandler($mock)
            ->setCustomer($this->user)
            ->setCart($this->cart)
            ->initiate($transactionType, TransactionClass::ECOM);
    }

    /** @test */
    public function page_settings_are_added_to_initiate_transaction_request()
    {
        $config = LaravelPaytabs::config();
        $transactionType = $this->faker->randomElement([TransactionType::SALE, TransactionType::AUTH]);

        $mock = $this->getMockBuilder(HttpRequest::class)
            ->setMethods(['post'])
            ->getMock();

        $mock->expects($this->once())->method('post')
            ->with(
                $this->equalTo($config->get('paytabs_api') . "payment/request"),
                $this->callback(function ($attributes) {
                    return isset($attributes["framed"]) && $attributes["framed"] === true && isset($attributes["hide_shipping"]) && $attributes["hide_shipping"] === true;
                })
            );
        $transaction = LaravelPaytabs::injectHttpRequestHandler($mock)
            ->hideShipping()
            ->framedPage()
            ->setCart($this->cart)
            ->initiate($transactionType, TransactionClass::ECOM);
    }

}
