<?php

namespace Devinweb\LaravelPaytabs\Support;

use Devinweb\LaravelPaytabs\Enums\TransactionClass;
use Devinweb\LaravelPaytabs\Enums\TransactionType;
use Devinweb\LaravelPaytabs\Events\TransactionInitiated;
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade as LaravelPaytabs;
use Devinweb\LaravelPaytabs\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

final class InitiateTransactionHelper extends TransactionHelper
{
    /**
     * @var array
     */
    protected $pageSettings;
    protected $billing;
    protected $shipping;

    public function __construct($transactionType, $transactionClass, $pageSettings)
    {
        parent::__construct($transactionType, $transactionClass);
        $this->pageSettings = $pageSettings;
    }

    /**
     * Initiate the paytabs transaction.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $user
     * @param  array  $cart
     * @param  string  $redirectUrl
     * @return array
     */
    public function initiate($user, $cart, $redirectUrl = null)
    {
        $this->validateTransaction();
        $config = LaravelPaytabs::config();
        $attributes = $this->prepareRequest($config, $cart, $user);

        $response = $this->httpRequestHandler->post("{$config->get('paytabs_api')}payment/request", $attributes)->getData(true);
        $this->save($response, 'pending', $user);
        $this->cacheRedirectUrl($response['tran_ref'], $redirectUrl ?: $config->get('redirect_url'));
        event(new TransactionInitiated($response));
        return $response;
    }

    private function cacheRedirectUrl($tranRef, $redirectUrl)
    {
        Cache::put($tranRef, $redirectUrl, 60 * 60);
    }

    /**
     * Prepare initiate paytabs transaction request.
     *
     * @param  \Illuminate\Support\Collection  $config
     * @param  array  $cart
     * @param  \Illuminate\Database\Eloquent\Model  $user
     * @param  string  $redirectUrl
     * @return array
     */
    protected function prepareRequest($config, $cart, $user = null): array
    {
        return array_merge(
            [
                'profile_id' => $config->get('profile_id'),
                'tran_type' => $this->transactionType,
                'tran_class' => $this->transactionClass,
                'paypage_lang' => $config->get('lang'),
                'return' => config('app.url') . '/api/paytabs/finalize',
            ],

            $this->getCartDetails($config, $cart),
            $this->getCustomerDetails($user),
            $this->getShippingDetails($user),
            Arr::except($this->pageSettings, 'hide_billing')
        );
    }

    /**
     * Validate the transaction parameters.
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function validateTransaction()
    {
        if (!TransactionType::isInitiateType($this->transactionType)) {
            throw new InvalidArgumentException("Transaction type {$this->transactionType} not supported.");
        }

        if (!in_array($this->transactionClass, TransactionClass::values())) {
            throw new InvalidArgumentException("Transaction class {$this->transactionClass} not supported.");
        }
    }
    public function setTransactionDetails($billing, $shipping)
    {
        $this->billing = $billing;
        $this->shipping = $shipping;
    }

    /**
     * Prepare customer details.
     *
     * @param  Model  $customer
     * @return array
     */
    protected function getCustomerDetails(Model $customer = null): array
    {

        return [
            'customer_details' => [
                'name' => $customer->name ?: '',
                'email' => $customer->email ?: '',
                'phone' => $customer->phone ?: '',
                'street1' => Arr::has($this->billing, 'street1') ? $this->billing['street1'] : '',
                'city' => Arr::has($this->billing, 'city') ? $this->billing['city'] : '',
                'state' => Arr::has($this->billing, 'state') ? $this->billing['state'] : '',
                'country' => Arr::has($this->billing, 'country') ? $this->billing['country'] : '',
                'zip' => Arr::has($this->billing, 'zip') ? $this->billing['zip'] : '',
                'ip' => \Request::ip(),
            ],
        ];

    }
    /**
     * Prepare customer details.
     *
     * @param  Model  $customer
     * @return array
     */
    protected function getShippingDetails(Model $customer = null): array
    {
        return $this->shipping ? [
            'shipping_details' => [
                'name' => $customer->name ?: '',
                'email' => $customer->email ?: '',
                'phone' => $customer->phone ?: '',
                'street1' => Arr::has($this->shipping, 'street1') ? $this->shipping['street1'] : '',
                'city' => Arr::has($this->shipping, 'city') ? $this->shipping['city'] : '',
                'state' => Arr::has($this->shipping, 'state') ? $this->shipping['state'] : '',
                'country' => Arr::has($this->shipping, 'country') ? $this->shipping['country'] : '',
                'zip' => Arr::has($this->shipping, 'zip') ? $this->shipping['zip'] : '',
                'ip' => \Request::ip(),
            ],
        ] : [];

    }
}
