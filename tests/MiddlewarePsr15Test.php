<?php

namespace Pop\Test;

use Pop\Middleware\Psr15\RequestHandler;
use Pop\Test\TestAsset\FakeResponse;
use Pop\Test\TestAsset\FakeServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class MiddlewarePsr15Test extends TestCase
{
    public function testRequestHandlerCallsTheWrappedClosureWithTheRequest()
    {
        $received = null;
        $response = new FakeResponse(200);

        $handler = new RequestHandler(function($request) use (&$received, $response) {
            $received = $request;
            return $response;
        });

        $request = new FakeServerRequest('GET', '/');
        $result  = $handler->handle($request);

        $this->assertSame($request, $received);
        $this->assertSame($response, $result);
    }

    public function testMiddlewareAdapterBridgesAPsr15MiddlewareIntoPopsChain()
    {
        $response = new FakeResponse(200);

        $psr15Middleware = new class($response) implements \Psr\Http\Server\MiddlewareInterface {
            protected ResponseInterface $response;
            public function __construct(ResponseInterface $response)
            {
                $this->response = $response;
            }
            public function process($request, $handler): ResponseInterface
            {
                // Confirms the wrapped Pop $next closure is reachable through
                // the PSR-15 $handler argument, then returns its own response.
                $handler->handle($request);
                return $this->response;
            }
        };

        $nextCalled = false;
        $adapter    = new \Pop\Middleware\Psr15\MiddlewareAdapter($psr15Middleware);
        $request    = new FakeServerRequest('GET', '/');

        // The wrapped $next closure flows through RequestHandler::handle(),
        // which is strictly typed to return ResponseInterface per PSR-15 -
        // so it must return a real response, not an arbitrary value.
        $result = $adapter->handle($request, function($req) use (&$nextCalled, $request) {
            $nextCalled = true;
            $this->assertSame($request, $req);
            return new FakeResponse(200);
        });

        $this->assertTrue($nextCalled);
        $this->assertSame($response, $result);
    }

    public function testMiddlewareAdapterRunsInsideAPopMiddlewareManagerChain()
    {
        $order = [];

        $psr15Middleware = new class($order) implements \Psr\Http\Server\MiddlewareInterface {
            protected array $order;
            public function __construct(array &$order)
            {
                $this->order = &$order;
            }
            public function process($request, $handler): ResponseInterface
            {
                $this->order[] = 'psr15-before';
                $handler->handle($request);
                $this->order[] = 'psr15-after';
                return new FakeResponse(200);
            }
        };

        $nativeMiddleware = new class($order) implements \Pop\Middleware\MiddlewareInterface {
            protected array $order;
            public function __construct(array &$order)
            {
                $this->order = &$order;
            }
            public function handle(mixed $request, \Closure $next): mixed
            {
                $this->order[] = 'native-before';
                $result = $next($request);
                $this->order[] = 'native-after';
                return $result;
            }
        };

        $manager = new \Pop\Middleware\Manager();
        $manager->addHandler(new \Pop\Middleware\Psr15\MiddlewareAdapter($psr15Middleware));
        $manager->addHandler($nativeMiddleware);

        $manager->process(new FakeServerRequest('GET', '/'), function() use (&$order) {
            $order[] = 'dispatch';
            return new FakeResponse(200);
        });

        $this->assertEquals(
            ['psr15-before', 'native-before', 'dispatch', 'native-after', 'psr15-after'],
            $order
        );
    }
}
