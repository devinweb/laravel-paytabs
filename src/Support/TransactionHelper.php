<?php

namespace Devinweb\LaravelPaytabs\Support;

use Illuminate\Database\Eloquent\Model;

abstract class TransactionHelper
{
    /**
     * @var string
     */
    protected $transactionType;

    /**
     * @var string
     */
    protected $transactionClass;

    protected $httpRequestHandler;

    public function __construct($transactionType = null, $transactionClass = null)
    {
        $this->transactionType = $transactionType;
        $this->transactionClass = $transactionClass;
    }

    /**
     * Prepare server to server request parameters value.
     *
     * @return array
     */
    abstract protected function prepareRequest($config, $cart, $user = null, $redirectUrl = null): array;

    /**
     * Validate the transaction parameters.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    abstract protected function validateTransaction();

    /**
     * Prepare customer details.
     *
     * @param  Model  $customer
     * @return array
     */
    protected function getCustomerDetails(Model $customer = null): array
    {
        return $customer ? [
            'customer_details' => [
                'name' => $customer->name ?: '',
                'email' => $customer->email ?: '',
                'phone' => $customer->phone ?: '',
                'street1' => $customer->address ?: '',
                'city' => $customer->city ?: '',
                'state' => $customer->state ?: '',
                'country' => $customer->country ?: '',
                'zip' => $customer->zip ?: '',
                'ip' => '',
            ],
        ] : [];
    }

    /**
     * Prepare cart details.
     *
     * @param  \Illuminate\Support\Collection  $config
     * @param  array  $cart
     * @return array
     */
    protected function getCartDetails($config, $cart): array
    {
        return [
            'cart_amount' => $cart['amount'],
            'cart_currency' => $config->get('currency'),
            'cart_id' => $cart['id'],
            'cart_description' => $cart['description'],
        ];
    }

    public function setHttpRequestHandler($handler)
    {
        $this->httpRequestHandler = $handler;
    }
}
