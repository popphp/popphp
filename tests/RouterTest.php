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
            'action'     => 'help'
        ]);

        $router->route();
        $this->assertEquals('Pop\Test\TestAsset\TestController', $router->getControllerClass());
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
        $this->assertNull($router->getRouteMatch()->getRoute());
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

}