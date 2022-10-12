<?php

namespace Devinweb\LaravelPaytabs\Actions;

class PrepareRedirectRequest
{
    public function __invoke(array $parameters): string
    {
        $request = '';
        $isFirstParameter = true;
        foreach ($parameters as $key => $value) {
            $request = $isFirstParameter ? "$request?{$key}={$value}" : "$request&{$key}={$value}";
            $isFirstParameter = false;
        }

        return $request;
    }
}
