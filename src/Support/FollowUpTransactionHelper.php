<?php

namespace Devinweb\LaravelPaytabs\Support;

use Devinweb\LaravelPaytabs\Enums\TransactionClass;
use Devinweb\LaravelPaytabs\Enums\TransactionType;
use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade as LaravelPaytabs;
use Devinweb\LaravelPaytabs\Models\Transaction;
use InvalidArgumentException;

final class FollowUpTransactionHelper extends TransactionHelper
{
    /**
     * @var array
     */
    protected $transactionRef;

    public function __construct(string $transactionRef, $transactionType = null, $transactionClass = null)
    {
        parent::__construct($transactionType, $transactionClass);
        $this->transactionRef = $transactionRef;
    }

    /**
     * Perform an actions on an initaited or done payment.
     *
     * @param  array  $cart
     * @return array
     */
    public function followUpTransaction($user, $cart)
    {
        $this->validateTransaction();
        $config = LaravelPaytabs::config();
        $attributes = $this->prepareRequest($config, $cart);
        $response = $this->httpRequestHandler->post("{$config->get('paytabs_api')}payment/request", $attributes)->getData(true);
        if (isset($response['payment_result']) && $response['payment_result']['response_status'] == 'A') {
            $this->save($response, 'paid', $user, $this->transactionRef);
            event(new TransactionSucceed($response));
        } else {
            event(new TransactionFail($response));
        }

        return $response;
    }

    /**
     * Get the details of a transaction.
     *
     * @return array
     */
    public function getTransaction()
    {
        $config = LaravelPaytabs::config();
        $attributes = [
            'profile_id' => $config->get('profile_id'),
            'tran_ref' => $this->transactionRef,
        ];
        $response = $this->httpRequestHandler->post("{$config->get('paytabs_api')}payment/query", $attributes);

        return json_decode($response->content(), true);
    }

    /**
     * Prepare follow up paytabs transaction request.
     *
     * @param  \Illuminate\Support\Collection  $config
     * @param  array  $cart
     * @param  \Illuminate\Database\Eloquent\Model  $user
     * @param  string  $redirectUrl
     * @return array
     */
    protected function prepareRequest($config, $cart, $user = null, $redirectUrl = null): array
    {
        return array_merge(
            [
                'profile_id' => $config->get('profile_id'),
                'tran_type' => $this->transactionType,
                'tran_class' => $this->transactionClass,
                'tran_ref' => $this->transactionRef,
            ],
            $this->getCartDetails($config, $cart)
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
        if (!TransactionType::isFollowUpType($this->transactionType)) {
            throw new InvalidArgumentException("Transaction type {$this->transactionType} not supported.");
        }

        if (!in_array($this->transactionClass, TransactionClass::values())) {
            throw new InvalidArgumentException("Transaction class {$this->transactionClass} not supported.");
        }
    }
}
