<?php

namespace Devinweb\LaravelPaytabs\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionInitiated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $transaction)
    {
        $this->transaction = $transaction;
    }
}
