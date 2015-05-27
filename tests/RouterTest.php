<?php

namespace Pop\Test;

use Pop\Router\Router;
use Pop\Router\Match;

class RouterTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructorRoutes()
    {
        $router = new Router(['/' => [
            'controller' => function() {
                echo 'index';
            }
        ]]);
        $this->assertInstanceOf('Pop\Router\Router', $router);
    }

    public function testAddRoute()
    {
        $router = new Router();
        $router->addRoute('command', [
            ' login' => [
                'controller' => function() {
                    echo 'cli login';
                }
            ]
        ]);
        $router->addRoute('', [
            '/login' => [
                'controller' => function() {
                    echo 'login';
                }
            ]
        ]);
        $router->addRoute('/system', [
            '/add' => [
                'controller' => function() {
                    echo 'add';
                }
            ],
            'edit' => [
                'controller' => function() {
                    echo 'edit';
                }
            ]
        ]);

        $this->assertTrue(array_key_exists('/login', $router->getRoutes()));
    }

    public function testAddRouteParams()
    {
        $router = new Router();
        $router->addRoute('/user', [
            'controller' => function($id) {
                echo $id;
            }
        ]);

        $router->addRouteParams('/user', 1000);
        $router->addRouteParams('/user', 2000);
        $router->addRouteParams('/user', [3000, 4000]);
        $this->assertContains(1000, $router->getRouterParams('/user'));
        $this->assertContains(2000, $router->getRouterParams('/user'));
        $this->assertContains(3000, $router->getRouterParams('/user'));
        $this->assertContains(4000, $router->getRouterParams('/user'));
    }

    public function testAddDispatchParams()
    {
        $router = new Router();
        $router->addRoute('/user', [
            'controller' => function ($id) {
                echo $id;
            }
        ]);

        $router->addDispatchParams('/user/edit', 1000);
        $router->addDispatchParams('/user/edit', 2000);
        $router->addDispatchParams('/user/edit', [3000, 4000]);
        $this->assertContains(1000, $router->getDispatchParams('/user/edit'));
        $this->assertContains(2000, $router->getDispatchParams('/user/edit'));
        $this->assertContains(3000, $router->getDispatchParams('/user/edit'));
        $this->assertContains(4000, $router->getDispatchParams('/user/edit'));
    }

    public function testIsCli()
    {
        $router = new Router();
        $this->assertTrue($router->isCli());
    }

    public function testGetRouteMatch()
    {
        $router = new Router();
        $router->addRoute('help', [
            'controller' => function () {
                echo 'help';
            }
        ]);
        $router->route();
        $this->assertInstanceOf('Pop\Router\Match\AbstractMatch', $router->getRouteMatch());
    }

    public function testHasRoute()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router();
        $router->addRoute('help', [
            'controller' => function () {
                echo 'help';
            }
        ]);

        $router->route();
        $this->assertTrue($router->hasRoute());
    }

    public function testGetControllerClass()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router();
        $router->addRoute('help', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help',
            'routeParams' => [123]
        ]);

        $router->route();
        $this->assertEquals('Pop\Test\TestAsset\TestController', $router->getControllerClass());
        $this->assertEquals(123, $router->getController()->foo);
    }

    public function testRouteMatch()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router();
        $router->addRoute('help', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);

        $router->route();
        $this->assertTrue(is_array($router->getRouteMatch()->getRoutes()));
        $this->assertEquals('help', $router->getRouteMatch()->getAction());
        $this->assertContains('help', $router->getRouteMatch()->getRoute());
        $this->assertTrue(is_array($router->getRouteMatch()->getRouteParams()));
        $this->assertTrue(is_array($router->getRouteMatch()->getDispatchParams()));
    }

    public function testCliMatch()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];
        $match = new Match\Cli();
        $this->assertEquals(1, count($match->getArguments()));
        $this->assertEquals('help', $match->getArgumentString());
    }

    public function testHttpMatch()
    {
        $_SERVER['REQUEST_URI'] = '/system/?id=123';
        $match = new Match\Http();
        $this->assertEquals('', $match->getBasePath());
        $this->assertContains('system', $match->getSegments());
        $this->assertEquals('/system/', $match->getSegmentString());
    }

    public function testHttpMatchIndex()
    {
        $_SERVER['REQUEST_URI'] = '';
        $match = new Match\Http();
        $this->assertEquals('', $match->getBasePath());
        $this->assertEquals('/', $match->getSegmentString());
    }

    public function testCliRoute()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help', 'bar'
        ];

        $router = new Router();
        $router->addRoute('help [foo|bar]', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);

        $match = new Match\Cli();
        $match->match($router->getRoutes());
        $this->assertTrue($match->hasRoute());
    }

    public function testCliWildcardRoute()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help', '-o1', '-o2'
        ];

        $router = new Router();
        $router->addRoute('help -o1 [-o2]', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);

        $match = new Match\Cli();
        $match->match($router->getRoutes());
        $this->assertTrue($match->hasRoute());
    }

    public function testCliOptionsRoute()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help', 'foo'
        ];

        $router = new Router();
        $router->addRoute('help *', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);

        $match = new Match\Cli();
        $match->match($router->getRoutes());
        $this->assertTrue($match->hasRoute());
    }

    public function testCliValueRoute()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help', 'test', 'test@test.com'
        ];

        $router = new Router();
        $router->addRoute('help <name> [<email>]', [
            'controller'  => 'Pop\Test\TestAsset\TestController',
            'action'      => 'help'
        ]);

        $match = new Match\Cli();
        $match->match($router->getRoutes());
        $this->assertTrue($match->hasRoute());
    }

    public function testHttpRoute()
    {
        $_SERVER['REQUEST_URI'] = '/help';

        $router = new Router();
        $router->addRoute('/help', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);

        $match = new Match\Http();
        $match->match($router->getRoutes());
        $this->assertEquals('/help', $match->getRoute());
    }

    public function testHttpParamsRoute()
    {
        $_SERVER['REQUEST_URI'] = '/help/foo/bar';

        $router = new Router();
        $router->addRoute('/help/:foo[/:bar]', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);

        $match = new Match\Http();
        $match->match($router->getRoutes());
        $this->assertTrue($match->hasRoute());
    }

    public function testHttpWildcardRoute()
    {
        $_SERVER['REQUEST_URI'] = '/help/foo/bar';

        $router = new Router();
        $router->addRoute('/help/:foo*', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);

        $match = new Match\Http();
        $match->match($router->getRoutes());
        $this->assertTrue($match->hasRoute());
    }

    public function testHttpOptionalWildcardRoute()
    {
        $_SERVER['REQUEST_URI'] = '/help/foo/bar/baz';

        $router = new Router();
        $router->addRoute('/help/:foo[/:bar*]', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);

        $match = new Match\Http();
        $match->match($router->getRoutes());
        $this->assertTrue($match->hasRoute());
    }

    public function testHttpTrailingSlashRoute()
    {
        $_SERVER['REQUEST_URI'] = '/help/foo/';

        $router = new Router();
        $router->addRoute('/help/foo/', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);

        $match = new Match\Http();
        $match->match($router->getRoutes());
        $this->assertTrue($match->hasRoute());
    }

    public function testHttpOptionalTrailingSlashRoute()
    {
        $_SERVER['REQUEST_URI'] = '/help/foo';

        $router = new Router();
        $router->addRoute('/help/foo[/]', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);

        $match = new Match\Http();
        $match->match($router->getRoutes());
        $this->assertTrue($match->hasRoute());
    }

}