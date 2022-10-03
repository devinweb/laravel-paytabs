<?php

namespace Devinweb\LaravelPaytabs\Actions;

use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade;
use Devinweb\LaravelPaytabs\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;

class FinalizeTransaction
{
    public function __invoke(Request $request)
    {
        $transaction = Transaction::where('transaction_ref', $request->tranRef)->first();
        $response = LaravelPaytabsFacade::setTransactionRef($request->tranRef)->getTransaction();

        if (isset($response['payment_result']) && $response['payment_result']['response_status'] == 'A') {
            $transaction->update([
                'status' => 'paid',
                'data' => array_merge($transaction->data, $response['payment_info']),
            ]);
        }

        if ($redirectUrl = Cache::get($request->tranRef)) {

            Cache::forget($request->tranRef);

            return Redirect::to($redirectUrl . app(PrepareRedirectRequest::class)($request->all()));
        }

        return;
    }
}
