<?php

namespace Devinweb\LaravelPaytabs\Tests\Feature;

use Devinweb\LaravelPaytabs\Enums\TransactionClass;
use Devinweb\LaravelPaytabs\Enums\TransactionType;
use Devinweb\LaravelPaytabs\Events\TransactionFail;
use Devinweb\LaravelPaytabs\Events\TransactionSucceed;
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade as LaravelPaytabs;
use Devinweb\LaravelPaytabs\Support\HttpRequest;
use Devinweb\LaravelPaytabs\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;

class FollowUpTransactionTest extends TestCase
{
    protected $cart;

    public function setUp(): void
    {
        parent::setUp();
        $this->cart = $this->createCart();
        $this->user = $this->createCustomer();
    }

    /** @test */
    public function cannot_follow_up_a_transaction_with_invalid_transaction_type()
    {
        $this->expectException(\InvalidArgumentException::class);

        $transactionType = $this->faker->randomElement([TransactionType::AUTH, TransactionType::SALE]);
        $transaction = LaravelPaytabs::setCart($this->cart)->setTransactionRef($this->faker->text(9))->followUpTransaction($transactionType, TransactionClass::ECOM);
    }

    /** @test */
    public function cannot_follow_up_a_transaction_with_invalid_transaction_class()
    {
        $this->expectException(\InvalidArgumentException::class);

        $transactionClass = $this->faker->word;
        $transaction = LaravelPaytabs::setCart($this->cart)->setTransactionRef($this->faker->text(9))->followUpTransaction(TransactionType::REFUND, $transactionClass);
    }

    /** @test */
    public function a_follow_up_transaction_action_can_be_done_successfully()
    {
        $initialDispatcher = Event::getFacadeRoot();
        Event::fake();
        Model::setEventDispatcher($initialDispatcher);
        $config = LaravelPaytabs::config();
        $transactionType = $this->faker->randomElement([TransactionType::REFUND, TransactionType::CAPTURE, TransactionType::VOID]);

        $mock = $this->getMockBuilder(HttpRequest::class)
            ->setMethods(['post'])
            ->getMock();
        $tranRef = $this->faker->text(9);
        $mock->expects($this->once())->method('post')
            ->with($this->equalTo($config->get('paytabs_api') . 'payment/request'),
                $this->equalTo([
                    'profile_id' => $config->get('profile_id'),
                    'tran_type' => $transactionType,
                    'tran_class' => TransactionClass::ECOM,
                    'tran_ref' => $tranRef,
                    'cart_amount' => $this->cart['amount'],
                    'cart_currency' => $config->get('currency'),
                    'cart_id' => $this->cart['id'],
                    'cart_description' => $this->cart['description'],
                ])
            )->willReturn(response()->json([
            'tran_ref' => 'TST2227200594762',
            'tran_type' => $transactionType,
            'cart_currency' => 'SAR',
            'cart_amount' => '80.00',
            "payment_result" => [
                "response_status" => "A",
                "response_code" => "G97111",
                "response_message" => "Authorised",
                "transaction_time" => "2021-04-21T09:25:11Z",
            ],
            "payment_info" => [
                "card_type" => "Credit",
                "card_scheme" => "Visa",
                "payment_description" => "4111 11## #### 1111",
            ],
        ], 200));

        $transaction = LaravelPaytabs::injectHttpRequestHandler($mock)
            ->setCart($this->cart)
            ->setCustomer($this->user)
            ->setTransactionRef($tranRef)
            ->followUpTransaction($transactionType, TransactionClass::ECOM);
        Event::assertDispatched(TransactionSucceed::class);
    }

    /** @test */
    public function transaction_fail_event_dispatched_when_a_follow_up_transaction_fail()
    {
        $initialDispatcher = Event::getFacadeRoot();
        Event::fake();
        Model::setEventDispatcher($initialDispatcher);
        $config = LaravelPaytabs::config();
        $transactionType = $this->faker->randomElement([TransactionType::REFUND, TransactionType::CAPTURE, TransactionType::VOID]);

        $mock = $this->getMockBuilder(HttpRequest::class)
            ->setMethods(['post'])
            ->getMock();
        $tranRef = $this->faker->text(9);
        $mock->expects($this->once())->method('post')
            ->willReturn(response()->json([
                'tran_ref' => 'TST2227200594762',
                'tran_type' => $transactionType,
                'cart_currency' => 'SAR',
                'cart_amount' => '80.00',
                "payment_result" => [
                    "response_status" => "D",
                    "response_code" => "320",
                    "response_message" => "Unable to refund",
                    "transaction_time" => "2021-04-21T09:33:54Z",
                ],
            ], 200));

        $transaction = LaravelPaytabs::injectHttpRequestHandler($mock)
            ->setCart($this->cart)
            ->setCustomer($this->user)
            ->setTransactionRef($tranRef)
            ->followUpTransaction($transactionType, TransactionClass::ECOM);
        Event::assertDispatched(TransactionFail::class);
    }

    /** @test */
    public function get_transation_by_reference_can_be_done_successfully()
    {
        $config = LaravelPaytabs::config();
        $mock = $this->getMockBuilder(HttpRequest::class)
            ->setMethods(['post'])
            ->getMock();
        $tranRef = $this->faker->text(9);
        $mock->expects($this->once())->method('post')
            ->with($this->equalTo($config->get('paytabs_api') . 'payment/query'),
                $this->equalTo([
                    'profile_id' => $config->get('profile_id'),
                    'tran_ref' => $tranRef,
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
            ->setTransactionRef($tranRef)
            ->getTransaction();
    }
}
