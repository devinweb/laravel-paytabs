<?php

namespace Devinweb\LaravelPaytabs\Support;

use Devinweb\LaravelPaytabs\Models\Transaction;

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
    abstract protected function prepareRequest($config, $cart, $user = null): array;

    /**
     * Validate the transaction parameters.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    abstract protected function validateTransaction();

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

    protected function save($transaction, $status, $user, $parent = null): Transaction
    {
        $attributes = [
            'user_id' => $user->id,
            'transaction_ref' => $transaction['tran_ref'],
            'type' => $transaction['tran_type'],
            'class' => $this->transactionClass,
            'status' => $status,
            'amount' => $transaction['cart_amount'],
            'currency' => $transaction['cart_currency'],
            'data' => $transaction,
        ];
        if ($parent) {
            $attributes = array_merge($attributes, ['parent' => $parent]);
        }

        return Transaction::create($attributes);
    }
}
