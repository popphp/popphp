<?php

namespace Pop\Test;

use Pop\Application;
use Pop\Middleware\Psr15\RequestHandler;
use Pop\Router\Router;
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

    public function testPsr15MiddlewareInFrontOfControllerRouteThrowsBeforeDispatching()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $psr15Middleware = new class implements \Psr\Http\Server\MiddlewareInterface {
            public function process($request, $handler): ResponseInterface
            {
                return $handler->handle($request);
            }
        };

        $app = new Application(new Router(null, new \Pop\Router\Match\Http()));
        $app->router()->addRoute('/', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help',
        ]);
        $app->addMiddleware(new \Pop\Middleware\Psr15\MiddlewareAdapter($psr15Middleware));

        $this->expectException(\Pop\Middleware\Exception::class);

        ob_start();
        try {
            $app->run(false);
        } finally {
            $result = ob_get_clean();
            // TestController::help() echoes 'help' - confirms it never ran.
            $this->assertStringNotContainsString('help', $result);
        }
    }

    public function testCallableObjectRouteResponseFlowsThroughPsr15MiddlewareChain()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $capturedResponse = null;
        $capture = function($response) use (&$capturedResponse) {
            $capturedResponse = $response;
        };

        $psr15Middleware = new class($capture) implements \Psr\Http\Server\MiddlewareInterface {
            protected \Closure $capture;
            public function __construct(\Closure $capture)
            {
                $this->capture = $capture;
            }
            public function process($request, $handler): ResponseInterface
            {
                $response = $handler->handle($request);
                ($this->capture)($response);
                return $response;
            }
        };

        $app = new Application(new Router(null, new \Pop\Router\Match\Http()));
        $app->router()->addRoute('/', [
            'controller' => 'Pop\Test\TestAsset\TestPsr7Callable::respond',
        ]);
        $app->addMiddleware(new \Pop\Middleware\Psr15\MiddlewareAdapter($psr15Middleware));

        $app->run(false);

        $this->assertInstanceOf(ResponseInterface::class, $capturedResponse);
    }

    public function testPopsOwnHttpRequestAndResponseAreBothPsr7()
    {
        // Pins the README's PSR-15 compatibility claim: an HTTP application needs
        // no PSR-7 shim, because Pop's own server request/response pair already
        // satisfies the interfaces PSR-15 is declared against.
        $this->assertInstanceOf(
            \Psr\Http\Message\ServerRequestInterface::class, new \Pop\Http\Server\Request(new \Pop\Http\Uri())
        );
        $this->assertInstanceOf(ResponseInterface::class, new \Pop\Http\Server\Response());
    }
}
