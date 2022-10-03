<?php

namespace Devinweb\LaravelPaytabs\Http\Controllers;

use Devinweb\LaravelPaytabs\Actions\FinalizeTransaction;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FinalizeTransactionController extends Controller
{
    public function __invoke(Request $request)
    {
        return app(FinalizeTransaction::class)($request);

    }
}
