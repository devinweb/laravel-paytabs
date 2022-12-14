<?php

namespace Devinweb\LaravelPaytabs;

use Devinweb\LaravelPaytabs\Contracts\BillingInterface;
use Devinweb\LaravelPaytabs\Support\FollowUpTransactionHelper;
use Devinweb\LaravelPaytabs\Support\HttpRequest;
use Devinweb\LaravelPaytabs\Support\InitiateTransactionHelper;
use Illuminate\Database\Eloquent\Model;

class LaravelPaytabs
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $customer;

    /**
     * @var array
     */
    protected $cart;

    /**
     * @var string
     */
    protected $transactionRef;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $config;

    /**
     * @var string
     */
    protected $redirectUrl;

    /**
     * @var mixed
     */
    public $httpRequestHandler;

    /**
     * @var bool
     */
    protected $hideShipping = false;

    /**
     * @var bool
     */
    protected $hideBilling = false;

    /**
     * @var bool
     */
    protected $framed = false;
    protected $billingDetails;
    protected $shippingDetails;

    public function __construct()
    {
        $this->config = collect(config('paytabs'));
        $this->httpRequestHandler = new HttpRequest();
    }

    /**
     * Set the http server request handler for tests.
     *
     * @return $this
     */
    public function injectHttpRequestHandler($handler)
    {
        $this->httpRequestHandler = $handler;

        return $this;
    }

    /**
     * Return paytabs config.
     *
     * @return \Illuminate\Support\Collection $config
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * Set redirectUrl value.
     *
     * @param  string  $url
     * @return $this
     */
    public function setRedirectUrl(string $url)
    {
        $this->redirectUrl = $url;

        return $this;
    }

    /**
     * Set customer value.
     *
     * @param  Model  $customer
     * @return $this
     */
    public function setCustomer(Model $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Set cart details value.
     *
     * @param  array  $cart
     * @return $this
     */
    public function setCart(array $cart)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * Hide Shipping Details.
     *
     * @return $this
     */
    public function hideShipping()
    {
        $this->hideShipping = true;

        return $this;
    }

    /**
     * Hide Shipping Details.
     *
     * @return $this
     */
    public function hideBilling()
    {
        $this->hideBilling = true;

        return $this;
    }

    /**
     * Hide Shipping Details.
     *
     * @return $this
     */
    public function addBilling(BillingInterface $billing)
    {
        $this->billingDetails = $billing->getData();

        return $this;
    }

    /**
     * Hide Shipping Details.
     *
     * @return $this
     */
    public function addShipping(BillingInterface $shipping)
    {
        $this->shippingDetails = $shipping->getData();

        return $this;
    }

    /**
     * Display the hosted payment page in an embed frame.
     *
     * @return $this
     */
    public function framedPage()
    {
        $this->framed = true;

        return $this;
    }

    /**
     * Set transaction reference value.
     *
     * @param  string  $transactionRef
     * @return $this
     */
    public function setTransactionRef($transactionRef)
    {
        $this->transactionRef = $transactionRef;

        return $this;
    }

    /**
     * Initiate a transaction.
     *
     * @param  string  $transactionType
     * @param  string  $transactionClass
     * @return mixed
     */
    public function initiate($transactionType, $transactionClass)
    {
        $initiateHelper = new InitiateTransactionHelper($transactionType, $transactionClass, $this->paymentPageSettings());
        $initiateHelper->setHttpRequestHandler($this->httpRequestHandler);
        $initiateHelper->setTransactionDetails($this->billingDetails, $this->shippingDetails);

        return $initiateHelper->initiate(
            $this->customer,
            $this->cart,
            $this->redirectUrl
        );
    }

    /**
     * Perform an actions on an initaited or done payment.
     *
     * @param  string  $transactionType
     * @param  string  $transactionClass
     * @return mixed
     */
    public function followUpTransaction($transactionType, $transactionClass)
    {
        $followUpHelper = new FollowUpTransactionHelper($this->transactionRef, $transactionType, $transactionClass);
        $followUpHelper->setHttpRequestHandler($this->httpRequestHandler);

        return $followUpHelper->followUpTransaction(
            $this->customer,
            $this->cart
        );
    }

    /**
     * Get the details of a transaction.
     *
     * @return mixed
     */
    public function getTransaction()
    {
        $followUpHelper = new FollowUpTransactionHelper($this->transactionRef);
        $followUpHelper->setHttpRequestHandler($this->httpRequestHandler);

        return $followUpHelper->getTransaction();
    }

    /**
     * Get payment page settings.
     *
     * @return array
     */
    private function paymentPageSettings()
    {
        $settings = [];
        if ($this->hideShipping || $this->hideBilling) {
            $settings['hide_shipping'] = true;
        }

        if ($this->framed) {
            $settings['framed'] = true;
        }

        $settings['hide_billing'] = $this->hideBilling;

        return $settings;
    }
}
