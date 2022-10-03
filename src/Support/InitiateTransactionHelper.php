<?php

namespace Devinweb\LaravelPaytabs\Support;

use Devinweb\LaravelPaytabs\Enums\TransactionClass;
use Devinweb\LaravelPaytabs\Enums\TransactionType;
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade as LaravelPaytabs;
use Devinweb\LaravelPaytabs\Models\Transaction;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

final class InitiateTransactionHelper extends TransactionHelper
{
    /**
     * @var array
     */
    protected $pageSettings;

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
                "profile_id" => $config->get('profile_id'),
                "tran_type" => $this->transactionType,
                "tran_class" => $this->transactionClass,
                "paypage_lang" => $config->get('lang'),
                "return" => config('app.url') . "/api/paytabs/finalize",
            ],

            $this->getCartDetails($config, $cart),
            $this->getCustomerDetails($user),
            $this->pageSettings
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
}
