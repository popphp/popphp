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
        $router->addRoute('/system/add', [
            'controller' => function() {
                echo 'add';
            }
        ]);

        $this->assertTrue(array_key_exists('/system/add', $router->getRoutes()));
    }

    public function testAddDynamicRoute()
    {
        $router = new Router();
        $router->addRoute('<controller> <action>', [
            'prefix' => 'MyApp\Controller\\'
        ]);

        $this->assertTrue($router->getRouteMatch()->hasDynamicRoute());
    }

    public function testAddControllerParams()
    {
        $router = new Router();
        $router->addRoute('/user', [
            'controller' => function($id) {
                echo $id;
            }
        ]);

        $router->addControllerParams('/user', [1000, 'append' => true]);
        $router->addControllerParams('/user', [2000, 'append' => true]);
        $router->addControllerParams('/user', [3000, 4000, 'append' => true]);
        $this->assertContains(1000, $router->getControllerParams('/user'));
        $this->assertContains(2000, $router->getControllerParams('/user'));
        $this->assertContains(3000, $router->getControllerParams('/user'));
        $this->assertContains(4000, $router->getControllerParams('/user'));
    }

    public function testAddControllerParamsNoAppend()
    {
        $router = new Router();
        $router->addRoute('/user', [
            'controller' => function($id) {
                echo $id;
            }
        ]);

        $router->addControllerParams('/user', 1000);
        $this->assertContains(1000, $router->getControllerParams('/user'));
    }

    public function testAddControllerParamsNull()
    {
        $router = new Router();
        $router->addRoute('/user', [
            'controller' => function($id) {
                echo $id;
            }
        ]);

        $router->addControllerParams('/user', 1000);
        $this->assertEquals(1, count($router->getControllerParams('/user')));
    }

    public function testIsCli()
    {
        $router = new Router();
        $this->assertTrue($router->isCli());
        $this->assertFalse($router->isHttp());
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
            'params'     => [123]
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
    }

    public function testControllerParams()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router();
        $router->addRoute('help', [
            'controller'       => 'Pop\Test\TestAsset\TestController',
            'action'           => 'help',
            'params' => [123]
        ]);

        $router->addControllerParams('*', ['foo' => 123]);
        $router->route();
        $this->assertEquals(123, $router->getController()->foo);
    }

    public function testWildcardControllerParams()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router();
        $router->addRoute('help', [
            'controller'  => 'Pop\Test\TestAsset\TestController',
            'action'      => 'help'
        ]);

        $router->addControllerParams('*', ['foo' => 123]);
        $router->route();
        $this->assertEquals(123, $router->getController()->foo);
    }

    public function testCliMatch()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];
        $match = new Match\Cli();
        $this->assertEquals('help', $match->getRouteString());
    }

    /**
     * @runInSeparateProcess
     */
    public function testCliNoMatch()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];
        $match = new Match\Cli();
        $this->assertFalse($match->match(['foo' => ['controller' => function() {}]]));
        ob_start();
        $match->noRouteFound(false);
        $result = ob_get_clean();
        $this->assertFalse(ctype_print($result));
    }

    public function testHttpMatch()
    {
        $_SERVER['REQUEST_URI'] = '/system/?id=123';
        $match = new Match\Http();
        $this->assertEquals('', $match->getBasePath());
        $this->assertContains('system', $match->getSegments());
        $this->assertEquals('/system/', $match->getRouteString());
    }

    public function testHttpDynamicMatch()
    {
        $_SERVER['REQUEST_URI'] = '/test/edit/1001';

        $router = new Router(null, new Match\Http());
        $router->addRoute('/:controller/:action/:param', [
            'prefix' => 'Pop\Test\TestAsset\\'
        ]);

        $this->assertTrue($router->hasRoute());
    }

    public function testCliDynamicMatch()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'foo', 'test', 'edit', '1001'
        ];
        $router = new Router(null, new Match\Cli());
        $router->addRoute('<controller> <action> <param>', [
            'prefix' => 'Pop\Test\TestAsset\\'
        ]);

        $this->assertTrue($router->hasRoute());
    }

//    /**
//     * @runInSeparateProcess
//     */
//    public function testHttpNoMatch()
//    {
//        $_SERVER['REQUEST_URI'] = '/system';
//        $match = new Match\Http();
//        $match->addRoutes(['/' => ['controller' => function() {}]]);
//        ob_start();
//        $this->assertFalse($match->match());
//        $match->noRouteFound();
//        $result = ob_get_clean();
//        $this->assertContains('Page Not Found', $result);
//    }

    public function testHttpMatchIndex()
    {
        $_SERVER['REQUEST_URI'] = '';
        $match = new Match\Http();
        $this->assertEquals('', $match->getBasePath());
        $this->assertEquals('/', $match->getRouteString());
    }

    public function testCliRoute()
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
        $this->assertTrue($router->hasRoute());
    }

    public function testCliOptionsRoute()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help', '-o1', '-o2'
        ];

        $router = new Router();
        $router->addRoute('help -o1 [-o2]', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);

        $router->route();
        $this->assertTrue($router->hasRoute());
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

        $router->route();
        $this->assertTrue($router->hasRoute());
    }

    public function testCliLongValueRoute()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help', '--name=test', '--email=test@test.com'
        ];

        $router = new Router();
        $router->addRoute('help --name= [--email=]', [
            'controller'  => 'Pop\Test\TestAsset\TestController',
            'action'      => 'help'
        ]);

        $router->route();
        $this->assertTrue($router->hasRoute());
    }

    public function testCliMultipleOptionsRoute()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help', '--options1', '-o3'
        ];

        $router = new Router();
        $router->addRoute('help --options1|-o1 [--options2|-o2] [--options3|-o3]', [
            'controller'  => 'Pop\Test\TestAsset\TestController',
            'action'      => 'help'
        ]);

        $router->route();
        $this->assertTrue($router->hasRoute());
    }

}