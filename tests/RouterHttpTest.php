<?php

namespace Pop\Test;

use Pop\Router\Match\Http;
use PHPUnit\Framework\TestCase;
use Pop\Router\Router;
use Pop\Router\Route;

class RouterHttpTest extends TestCase
{

    public function testHttpRoute()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/foo';
        $routes = [
            '/foo' => [
                'controller' => function() {
                    echo 'Foo';
                }
            ]
        ];
        $http = new Http();
        $http->addRoutes($routes);
        $http->match();
        $this->assertTrue($http->hasRoute());
    }

    public function testHttpRouteWithSlash()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/foo';
        $routes = [
            '/foo/' => [
                'controller' => function() {
                    echo 'Foo';
                }
            ]
        ];
        $http = new Http();
        $http->addRoutes($routes);
        $http->match();
        $this->assertTrue($http->hasRoute());
    }

    public function testHttpRouteWithOptionalSlash()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/foo';
        $routes = [
            '/foo[/]' => [
                'controller' => function() {
                    echo 'Foo';
                }
            ]
        ];
        $http = new Http();
        $http->addRoutes($routes);
        $http->match();
        $this->assertTrue($http->hasRoute());
    }

    public function testHttpPreparedRoute()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/foo/1';
        $routes = [
            '/foo/:id' => [
                'controller' => function() {
                    echo 'Foo';
                }
            ]
        ];
        $http = new Http();
        $http->addRoutes($routes);
        $http->match();
        $this->assertTrue($http->hasRoute());
    }

    public function testHttpPreparedRouteWithOptions()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/foo/1/2';
        $routes = [
            '/foo/:id[/:uid]' => [
                'controller' => function($id, $uid = null) {
                    echo 'Foo';
                }
            ]
        ];
        $http = new Http();
        $http->addRoutes($routes);
        $http->match();
        $this->assertTrue($http->hasRoute());
    }

    public function testHttpPreparedRouteWithArray()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/foo/1/2/3';
        $routes = [
            '/foo/:id*' => [
                'controller' => function(array $id) {
                    echo 'Foo';
                }
            ]
        ];
        $http = new Http();
        $http->addRoutes($routes);
        $http->match();
        $this->assertTrue($http->hasRoute());
    }

    public function testHttpNoRouteFound()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/foo';
        $routes = [
            '/bar' => [
                'controller' => function() {
                    echo 'Foo';
                }
            ]
        ];
        $http = new Http();
        $http->addRoutes($routes);
        $http->match();
        $this->assertFalse($http->hasRoute());

        ob_start();
        $http->noRouteFound(false);
        $result = ob_get_clean();

        $this->assertStringContainsString('Page Not Found', $result);
    }

    public function testHttpNoRouteFoundDoesNotEmitNullOffsetDeprecation()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/foo';
        $routes = [
            '/bar' => [
                'controller' => function() {
                    echo 'Foo';
                }
            ]
        ];

        $deprecations = [];
        set_error_handler(function ($errno, $errstr) use (&$deprecations) {
            $deprecations[] = $errstr;
            return true;
        }, E_DEPRECATED);

        $http = new Http();
        $http->addRoutes($routes);
        $http->match();

        restore_error_handler();

        $this->assertFalse($http->hasRoute());
        $this->assertSame([], $deprecations);
    }

    public function testHttpDefaultRoute()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/foo';
        $routes = [
            '*' => [
                'controller' => function() {
                    echo 'Foo';
                }
            ]
        ];
        $http = new Http();
        $http->addRoutes($routes);
        $http->match();
        $this->assertTrue($http->hasRoute());
    }

    public function testNamedRoute()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/user';

        $router = new Router(null, new Http());
        $router->addRoute('/user/:id', function($id) {
            echo 'User: ' . $id;
        })->name('user');

        $router->addRoute('/user/show/:id*', function($id) {
            echo 'User: ' . $id;
        })->name('user.show');

        Route::setRouter($router);

        $userObject      = new \stdClass();
        $userObject->id  = 1;
        $usersObject     = new \stdClass();
        $usersObject->id = [1, 2, 3];

        $this->assertTrue(Route::hasRouter());
        $this->assertInstanceOf('Pop\Router\Router', Route::getRouter());
        $this->assertEquals('/user/1', Route::url('user', ['id' => 1]));
        $this->assertEquals('/user/show/1/2/3', Route::url('user.show', ['id' => [1, 2, 3]]));
        $this->assertEquals('/user/1', Route::url('user', $userObject));
        $this->assertEquals('/user/show/1/2/3', Route::url('user.show', $usersObject));
    }

    public function testNamedRouteFqdn()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/user';
        $_SERVER['HTTP_HOST']     = 'www.domain.com';

        $router = new Router(null, new Http());
        $router->addRoute('/user/:id', function($id) {
            echo 'User: ' . $id;
        })->name('user');

        Route::setRouter($router);

        $this->assertTrue(Route::hasRouter());
        $this->assertInstanceOf('Pop\Router\Router', Route::getRouter());
        $this->assertEquals('http://www.domain.com/user/1', Route::url('user', ['id' => 1], true));
    }

    public function testNamedRouteException1()
    {
        $this->expectException('Pop\Router\Exception');

        $_SERVER['REQUEST_URI']   = '/user';

        $router = new Router(null, new Http());

        Route::setRouter($router);

        $this->assertTrue(Route::hasRouter());
        $this->assertInstanceOf('Pop\Router\Router', Route::getRouter());
        $this->assertEquals('/user/1', Route::url('user', ['id' => 1]));
    }

    public function testNamedRouteException2()
    {
        $this->expectException('Pop\Router\Match\Exception');
        $router = new Router(null, new Http());
        $router->name('user');
    }

    public function testNamedRouteNotHttpException()
    {
        $this->expectException('Pop\Router\Exception');

        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/user';

        $router = new Router(null, new \Pop\Router\Match\Cli());

        Route::setRouter($router);
        $this->assertEquals('/user', $router->getUrl('user'));
    }

    public function testMethodRouteMatchesCorrectController()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/users';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $http = new Http();
        $http->addRoutes([
            '/users' => [
                'controller' => function() { echo 'List'; },
                'method'     => 'get',
            ],
        ]);
        $http->addRoute('/users', [
            'controller' => function() { echo 'Create'; },
            'method'     => 'post',
        ]);

        $http->match();
        $this->assertTrue($http->hasRoute());
        $this->assertEquals('post', $http->getRouteConfig('method')[0]);
    }

    public function testMethodRouteRejectsWrongMethodButFindsOtherMatch()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/users';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $http = new Http();
        $http->addRoutes([
            '/users' => [
                'controller' => function() { echo 'List'; },
                'method'     => 'get',
            ],
        ]);
        $http->addRoute('/users', [
            'controller' => function() { echo 'Create'; },
            'method'     => 'post',
        ]);

        $http->match();
        $this->assertTrue($http->hasRoute());
        $this->assertEquals('get', $http->getRouteConfig('method')[0]);
        $this->assertFalse($http->hasMethodMismatch());
    }

    public function testMethodMismatchWithNoFallbackReportsAllowedMethods()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/users';
        $_SERVER['REQUEST_METHOD'] = 'DELETE';

        $http = new Http();
        $http->addRoutes([
            '/users' => [
                'controller' => function() { echo 'List'; },
                'method'     => 'get',
            ],
        ]);
        $http->addRoute('/users', [
            'controller' => function() { echo 'Create'; },
            'method'     => 'post',
        ]);

        $http->match();
        $this->assertFalse($http->hasController());
        $this->assertTrue($http->hasMethodMismatch());
        $this->assertEquals(['get', 'post'], $http->getAllowedMethods());
    }

    public function testMethodMismatchFallsBackToWildcard()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/users';
        $_SERVER['REQUEST_METHOD'] = 'DELETE';

        $http = new Http();
        $http->addRoutes([
            '/users' => [
                'controller' => function() { echo 'List'; },
                'method'     => 'get',
            ],
            '*' => [
                'controller' => function() { echo 'Fallback'; },
            ],
        ]);

        $http->match();
        $this->assertTrue($http->hasController());
    }

    public function testMethodNotAllowedSendsAllowHeader()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/users';
        $_SERVER['REQUEST_METHOD'] = 'DELETE';

        $http = new Http();
        $http->addRoutes([
            '/users' => [
                'controller' => function() {},
                'method'     => 'get,post',
            ],
        ]);

        $http->match();

        ob_start();
        $http->methodNotAllowed($http->getAllowedMethods(), false);
        $result = ob_get_clean();

        $this->assertStringContainsString('Method Not Allowed', $result);
    }

    public function testNoMethodKeyMatchesAnyMethod()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/foo';
        $_SERVER['REQUEST_METHOD'] = 'DELETE';

        $http = new Http();
        $http->addRoutes([
            '/foo' => [
                'controller' => function() {},
            ],
        ]);

        $http->match();
        $this->assertTrue($http->hasRoute());
        $this->assertFalse($http->hasMethodMismatch());
    }

    public function testSpecificityOrderIndependentOfDeclarationOrderLiteralFirst()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/users/new';

        $http = new Http();
        $http->addRoutes([
            '/users/new' => [
                'controller' => function() { echo 'New'; },
            ],
            '/users/:id' => [
                'controller' => function($id) { echo 'Show'; },
            ],
        ]);

        $http->match();
        $this->assertTrue($http->hasRoute());
        $this->assertEquals('/users/new', $http->getRouteConfig('route'));
    }

    public function testSpecificityOrderIndependentOfDeclarationOrderParamFirst()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/users/new';

        $http = new Http();
        $http->addRoutes([
            '/users/:id' => [
                'controller' => function($id) { echo 'Show'; },
            ],
            '/users/new' => [
                'controller' => function() { echo 'New'; },
            ],
        ]);

        $http->match();
        $this->assertTrue($http->hasRoute());
        $this->assertEquals('/users/new', $http->getRouteConfig('route'));
    }

    public function testFluentVerbMethodsRegisterAndChain()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/b';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $http = new Http();
        $http->get('/a', function() { echo 'A'; })
             ->post('/b', function() { echo 'B'; });

        $http->match();
        $this->assertTrue($http->hasRoute());
        $this->assertEquals('post', $http->getRouteConfig('method')[0]);
    }

    public function testCustomMethodRequiresWhitelisting()
    {
        $this->expectException('Pop\Router\Match\Exception');

        $http = new Http();
        $http->propfind('/dav', function() {});
    }

    public function testCustomMethodWorksOnceWhitelisted()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/dav';
        $_SERVER['REQUEST_METHOD'] = 'PROPFIND';

        $http = new Http();
        $http->addCustomMethod('propfind');
        $http->propfind('/dav', function() { echo 'Dav'; })
             ->propfind('/dav2', function() { echo 'Dav2'; });

        $http->match();
        $this->assertTrue($http->hasRoute());
        $this->assertTrue($http->hasCustomMethod('propfind'));
    }

    public function testRouterVerbProxiesChain()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/b';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $router = new Router(null, new Http());
        $router->get('/a', function() { echo 'A'; })
               ->post('/b', function() { echo 'B'; });

        $router->route();
        $this->assertTrue($router->hasRoute());
    }

    public function testRouterCustomMethodProxy()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/dav';
        $_SERVER['REQUEST_METHOD'] = 'PROPFIND';

        $router = new Router(null, new Http());
        $router->addCustomMethod('propfind');
        $router->propfind('/dav', function() { echo 'Dav'; });

        $router->route();
        $this->assertTrue($router->hasRoute());
        $this->assertTrue($router->hasCustomMethod('propfind'));
    }

    public function testRouterVerbProxyThrowsWhenNotHttp()
    {
        $this->expectException('Pop\Router\Exception');

        $_SERVER['argv'] = ['myscript.php', 'help'];

        $router = new Router(null, new \Pop\Router\Match\Cli());
        $router->get('/a', function() {});
    }

    public function testRouterMethodMismatchProxies()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/users';
        $_SERVER['REQUEST_METHOD'] = 'DELETE';

        $router = new Router(null, new Http());
        $router->get('/users', function() {});

        $router->route();
        $this->assertTrue($router->hasMethodMismatch());
        $this->assertEquals(['get'], $router->getAllowedMethods());
    }

    public function testRemainingFluentVerbMethodsRegisterAndMatchOnHttp()
    {
        foreach (['head', 'put', 'delete', 'trace', 'options', 'connect', 'patch'] as $verb) {
            $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
            $_SERVER['REQUEST_URI']    = '/resource';
            $_SERVER['REQUEST_METHOD'] = strtoupper($verb);

            $http = new Http();
            $http->$verb('/resource', function() {});

            $http->match();
            $this->assertTrue($http->hasRoute(), "Failed to match verb: $verb");
            $this->assertEquals($verb, $http->getRouteConfig('method')[0], "Wrong method for verb: $verb");
        }
    }

    public function testAddCustomMethodsRegistersEachMethod()
    {
        $http = new Http();
        $http->addCustomMethods(['propfind', 'proppatch']);

        $this->assertTrue($http->hasCustomMethod('propfind'));
        $this->assertTrue($http->hasCustomMethod('proppatch'));
    }

    public function testCustomMethodCallRequiresExactlyRouteAndController()
    {
        $this->expectException('Pop\Router\Match\Exception');

        $http = new Http();
        $http->addCustomMethod('propfind');
        $http->propfind('/dav');
    }

    public function testRemainingFluentVerbMethodsRegisterAndMatchOnRouter()
    {
        foreach (['head', 'put', 'delete', 'trace', 'options', 'connect', 'patch'] as $verb) {
            $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
            $_SERVER['REQUEST_URI']    = '/resource';
            $_SERVER['REQUEST_METHOD'] = strtoupper($verb);

            $router = new Router(null, new Http());
            $router->$verb('/resource', function() {});

            $router->route();
            $this->assertTrue($router->hasRoute(), "Failed to match verb: $verb");
        }
    }

    public function testRouterAddCustomMethodsProxy()
    {
        $router = new Router(null, new Http());
        $router->addCustomMethods(['propfind', 'proppatch']);

        $this->assertTrue($router->hasCustomMethod('propfind'));
        $this->assertTrue($router->hasCustomMethod('proppatch'));
    }

    public function testDynamicRouteControllerClassDoesNotExist()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/nonexistent/index';

        $http = new Http();
        $http->addRoute('/:controller/:action', [
            'prefix' => 'Pop\Test\TestAsset\\'
        ]);
        $http->match();

        $this->assertTrue($http->hasDynamicRoute());
        $this->assertNull($http->getController());
        $this->assertFalse($http->isDynamicRoute());
    }

    public function testNonWildcardDefaultRouteMatchesByPrefix()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/admin/foo';

        $http = new Http();
        $http->addRoute('/admin/*', [
            'controller' => function() { echo 'Admin'; }
        ]);
        $http->match();

        $this->assertTrue($http->hasRoute());
        $this->assertTrue($http->hasController());
        $this->assertInstanceOf('Closure', $http->getController());
    }

    public function testDirectMatchSkipsUnrelatedLiteralRoutes()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/users';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $http = new Http();
        $http->addRoute('/other', ['controller' => function() {}]);
        $http->addRoute('/users', ['controller' => function() {}]);
        $http->match();

        $this->assertTrue($http->hasRoute());
    }

    public function testDirectMatchAccumulatesAllowedMethodsBeforeUnconstrainedFallback()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/users';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $http = new Http();
        $http->addRoute('/users', ['controller' => function() { echo 'Create'; }, 'method' => 'post']);
        $http->addRoute('/users', ['controller' => function() { echo 'List'; }]);
        $http->match();

        $this->assertTrue($http->hasRoute());
        $this->assertFalse($http->hasMethodMismatch());
    }

    public function testDefaultRouteWithActionKeyIsUnset()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/anything';

        $http = new Http();
        $http->addRoute('help', [
            'controller' => function() {},
            'default'    => true,
            'action'     => 'foo',
        ]);
        $http->match();

        $this->assertTrue($http->hasDefaultRoute());
        $this->assertArrayNotHasKey('action', $http->getDefaultRoute()['*']);
    }

    public function testOptionalArrayParamWithNoSegments()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(getcwd());
        $_SERVER['REQUEST_URI']   = '/foo';

        $http = new Http();
        $http->addRoute('/foo[/:id*]', ['controller' => function() {}]);
        $http->match();

        $this->assertTrue($http->hasRoute());
    }

    public function testAddRouteWithEmptyMethodValueDoesNotCrash()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/users';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $http = new Http();
        $http->addRoute('/users', ['controller' => function() { echo 'List'; }, 'method' => '']);
        $http->match();

        $this->assertTrue($http->hasRoute());
    }

    public function testNormalizeMethodsReturnsNullForEmptyValue()
    {
        $http = new Http();
        $ref  = new \ReflectionMethod($http, 'normalizeMethods');
        $ref->setAccessible(true);

        $this->assertNull($ref->invoke($http, ''));
        $this->assertNull($ref->invoke($http, []));
        $this->assertNull($ref->invoke($http, null));
    }

    public function testPopcornStyleMethodGroupRegistersNestedRoutes()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/users';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $http = new Http();
        $http->addRoutes([
            'options,get' => [
                '/users' => ['controller' => function() { echo 'Users List'; }],
                '/roles' => ['controller' => function() { echo 'Roles List'; }],
            ],
        ]);
        $http->match();

        $this->assertTrue($http->hasRoute());
        $this->assertEquals(['get', 'options'], $http->getRouteConfig('method'));
    }

    public function testPopcornStyleMethodGroupRejectsWrongMethod()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/users';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $http = new Http();
        $http->addRoutes([
            'options,get' => [
                '/users' => ['controller' => function() { echo 'Users List'; }],
            ],
        ]);
        $http->match();

        $this->assertFalse($http->hasRoute());
        $this->assertTrue($http->hasMethodMismatch());
    }

    public function testPopcornStyleMethodGroupOverridesNestedMethodKey()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/users';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $http = new Http();
        $http->addRoutes([
            'options,get' => [
                '/users' => ['controller' => function() {}, 'method' => 'delete'],
            ],
        ]);
        $http->match();

        // The group's method list wins over the nested route's own 'method' key.
        $this->assertTrue($http->hasRoute());
        $this->assertEquals(['get', 'options'], $http->getRouteConfig('method'));
    }

}
