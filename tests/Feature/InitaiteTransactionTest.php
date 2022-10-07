<?php

namespace Devinweb\LaravelPaytabs\Tests\Feature;

use Devinweb\LaravelPaytabs\Enums\TransactionClass;
use Devinweb\LaravelPaytabs\Enums\TransactionType;
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade as LaravelPaytabs;
use Devinweb\LaravelPaytabs\Support\HttpRequest;
use Devinweb\LaravelPaytabs\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

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
        $initialDispatcher = Event::getFacadeRoot();
        Event::fake();
        Model::setEventDispatcher($initialDispatcher);
        $config = LaravelPaytabs::config();
        $transactionType = $this->faker->randomElement([TransactionType::SALE, TransactionType::AUTH]);

        $mock = $this->getMockBuilder(HttpRequest::class)
            ->setMethods(['post'])
            ->getMock();
        $mock->expects($this->once())->method('post')
            ->with($this->equalTo($config->get('paytabs_api') . 'payment/request'),
                $this->equalTo([
                    'profile_id' => $config->get('profile_id'),
                    'tran_type' => $transactionType,
                    'tran_class' => TransactionClass::ECOM,
                    'paypage_lang' => $config->get('lang'),
                    'return' => 'http://localhost/api/paytabs/finalize',
                    'cart_amount' => $this->cart['amount'],
                    'cart_currency' => $config->get('currency'),
                    'cart_id' => $this->cart['id'],
                    'cart_description' => $this->cart['description'],
                    'customer_details' => [
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                        'phone' => $this->user->phone,
                        'street1' => null,
                        'city' => null,
                        'state' => null,
                        'country' => null,
                        'zip' => null,
                        'ip' => \Request::ip(),
                    ],
                ])
            )->willReturn(response()->json([
            'tran_ref' => 'TST2227200594762',
            'tran_type' => 'Sale',
            'cart_currency' => 'SAR',
            'cart_amount' => '80.00',
            'return' => "https:\/\/paytabs.me\/api\/paytabs\/finalize",
            'redirect_url' => "https:\/\/secure.paytabs.sa\/payment\/page\/59C69E8A82E43A53A77C3A89F56223DB9917730B9BF1870B10493B25",
        ], 200));

        $transaction = LaravelPaytabs::injectHttpRequestHandler($mock)
            ->setCustomer($this->user)
            ->setCart($this->cart)
            ->initiate($transactionType, TransactionClass::ECOM);
        $this->assertDatabaseHas('transactions', [
            'transaction_ref' => 'TST2227200594762',
        ]);
    }

    /** @test */
    public function redirect_url_is_cached_successfully()
    {
        $initialDispatcher = Event::getFacadeRoot();
        Event::fake();
        Model::setEventDispatcher($initialDispatcher);
        $config = LaravelPaytabs::config();
        $url = $this->faker->url;
        $transactionType = $this->faker->randomElement([TransactionType::SALE, TransactionType::AUTH]);
        $reference = $this->faker->text(9);
        $mock = $this->getMockBuilder(HttpRequest::class)
            ->setMethods(['post'])
            ->getMock();

        $mock->expects($this->once())->method('post')
            ->with(
                $this->equalTo($config->get('paytabs_api') . 'payment/request'),
                $this->callback(function ($attributes) {
                    return $attributes['return'] == 'http://localhost/api/paytabs/finalize';
                })
            )->willReturn(response()->json([
            'tran_ref' => $reference,
            'tran_type' => 'Sale',
            'cart_currency' => 'SAR',
            'cart_amount' => '80.00',
            'return' => "https:\/\/paytabs.me\/api\/paytabs\/finalize",
            'redirect_url' => "https:\/\/secure.paytabs.sa\/payment\/page\/59C69E8A82E43A53A77C3A89F56223DB9917730B9BF1870B10493B25",
        ], 200));
        Cache::shouldReceive('put')
            ->once()
            ->with($reference, $url, 60 * 60);
        $transaction = LaravelPaytabs::injectHttpRequestHandler($mock)
            ->setCustomer($this->user)
            ->setRedirectUrl($url)
            ->setCart($this->cart)
            ->initiate($transactionType, TransactionClass::ECOM);
    }

    /** @test */
    public function page_settings_are_added_to_initiate_transaction_request()
    {
        $initialDispatcher = Event::getFacadeRoot();
        Event::fake();
        Model::setEventDispatcher($initialDispatcher);
        $config = LaravelPaytabs::config();
        $transactionType = $this->faker->randomElement([TransactionType::SALE, TransactionType::AUTH]);

        $mock = $this->getMockBuilder(HttpRequest::class)
            ->setMethods(['post'])
            ->getMock();

        $mock->expects($this->once())->method('post')
            ->with(
                $this->equalTo($config->get('paytabs_api') . 'payment/request'),
                $this->callback(function ($attributes) {
                    return isset($attributes['framed']) && $attributes['framed'] === true && isset($attributes['hide_shipping']) && $attributes['hide_shipping'] === true;
                })
            )->willReturn(response()->json([
            'tran_ref' => 'TST2227200594762',
            'tran_type' => 'Sale',
            'cart_currency' => 'SAR',
            'cart_amount' => '80.00',
            'return' => "https:\/\/paytabs.me\/api\/paytabs\/finalize",
            'redirect_url' => "https:\/\/secure.paytabs.sa\/payment\/page\/59C69E8A82E43A53A77C3A89F56223DB9917730B9BF1870B10493B25",
        ], 200));
        $transaction = LaravelPaytabs::injectHttpRequestHandler($mock)
            ->hideShipping()
            ->framedPage()
            ->setCustomer($this->user)
            ->setCart($this->cart)
            ->initiate($transactionType, TransactionClass::ECOM);
    }
}
