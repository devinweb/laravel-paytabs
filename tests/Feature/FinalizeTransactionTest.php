<?php

namespace Devinweb\LaravelPaytabs\Tests\Feature;

use Devinweb\LaravelPaytabs\Enums\TransactionClass;
use Devinweb\LaravelPaytabs\Enums\TransactionType;
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade as LaravelPaytabs;
use Devinweb\LaravelPaytabs\Http\Requests\FinalizeTransactionRequest;
use Devinweb\LaravelPaytabs\Models\Transaction;
use Devinweb\LaravelPaytabs\Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Validator;

class FinalizeTransactionTest extends TestCase
{
    protected $user;

    protected $cart;

    protected $transaction;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createCustomer();
        $this->cart = $this->createCart();
        $this->transaction = Transaction::create([
            'user_id' => $this->user->id,
            'transaction_ref' => Str::random(9),
            'type' => TransactionType::SALE,
            'class' => TransactionClass::ECOM,
            'status' => 'pending',
            'amount' => $this->cart['amount'],
            'currency' => 'SAR',
            'data' => [],
        ]);
    }

    /** @test */
    public function finalize_transaction_request_should_fail_without_transaction_reference()
    {
        $attributes = [];
        $request = new FinalizeTransactionRequest();
        $rules = $request->rules();
        $validator = Validator::make($attributes, $rules);
        $fails = $validator->fails();
        $this->assertEquals(true, $fails);
    }

    /** @test */
    public function finalize_transaction_request_should_fail_with_invalid_transaction_reference()
    {
        $attributes = [
            'tranRef' => Str::random(9),
        ];
        $request = new FinalizeTransactionRequest();
        $rules = $request->rules();
        $validator = Validator::make($attributes, $rules);
        $fails = $validator->fails();
        $this->assertEquals(true, $fails);
    }

    /** @test */
    public function transaction_can_be_finalized_successfully()
    {
        $config = LaravelPaytabs::config();
        $cacheDriver = app('cache')->driver();
        Cache::shouldReceive('driver')->andReturn($cacheDriver);
        Cache::shouldReceive('get')
            ->once()
            ->with($this->transaction->transaction_ref)
            ->andReturn($this->faker->url);
        Cache::shouldReceive('forget')
            ->once()
            ->with($this->transaction->transaction_ref);
        Http::fake([
            "{$config->get('paytabs_api')}payment/query" => Http::response([
                "tran_ref" => $this->transaction->transaction_ref,
                "tran_type" => $this->transaction->type,
                "cart_id" => $this->cart['id'],
                "cart_description" => $this->cart['description'],
                "cart_currency" => "SAR",
                "cart_amount" => $this->cart['amount'],
                "payment_result" => [
                    "response_status" => "A",
                    "response_code" => "G15046",
                    "response_message" => "Authorised",
                    "transaction_time" => "2021-02-28T12:24:06Z",
                ],
                "payment_info" => [
                    "card_type" => "Credit",
                    "card_scheme" => "Visa",
                    "payment_description" => "4111 11## #### 1111",
                ],
            ]),
            200]);

        $response = $this->post('/api/paytabs/finalize', [
            'tranRef' => $this->transaction->transaction_ref,
        ]);
        $response->assertStatus(302);
    }

}
