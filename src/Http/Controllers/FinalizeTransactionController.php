<?php

namespace Devinweb\LaravelPaytabs\Http\Controllers;

use Devinweb\LaravelPaytabs\Actions\FinalizeTransaction;
use Devinweb\LaravelPaytabs\Http\Requests\FinalizeTransactionRequest;
use Illuminate\Routing\Controller;

class FinalizeTransactionController extends Controller
{
    public function __invoke(FinalizeTransactionRequest $request)
    {
        return app(FinalizeTransaction::class)($request);

    }
}
