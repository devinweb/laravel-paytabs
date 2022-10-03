<?php

namespace Devinweb\LaravelPaytabs\Support;

use Devinweb\LaravelPaytabs\Facades\LaravelPaytabsFacade as LaravelPaytabs;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class HttpRequest
{
    /**
     * Create a post sever-to-server request.
     *
     * @param  string  $url
     * @param  array  $parameters
     * @return Response
     *
     * @throws InvalidArgumentException
     * @throws RequestException
     */
    public function post(string $url, array $parameters)
    {
        $config = LaravelPaytabs::config();
        try {
            $response = Http::withHeaders([
                'Authorization' => $config->get('server_key'),
                'Content-Type' => 'application/json',
            ])->post($url, $parameters);

            if (!$response->successful()) {
                throw new InvalidArgumentException($response->body());
            }
            return response()->json($response->body(), $response->status());
        } catch (RequestException $e) {
            return $e->getResponse();
        }
    }
}
