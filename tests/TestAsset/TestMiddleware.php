<?php

namespace Pop\Test\TestAsset;

use Pop\Middleware;

class TestMiddleware implements Middleware\MiddlewareInterface, Middleware\TerminableInterface
{
    public function handle(mixed $request, \Closure $next): mixed
    {
        echo 'Entering Test Middleware.<br />';
        $response = $next($request);
        echo 'Exiting Test Middleware.<br />';
        return $response;
    }

    public function terminate(mixed $request = null, mixed $response = null): void
    {
        echo 'Executing terminate method for test middleware.<br />';
    }
}
