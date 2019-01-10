<?php

namespace Pop\Test;

use Pop\Router\Router;
use Pop\Router\Match;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{

    public function testConstructorRoutes()
    {
        $router = new Router([
            '/' => [
                'controller' => function() {
                    echo 'index';
                }
            ],
            '*' => function() {
                echo 'default';
            }
        ]);
        $this->assertInstanceOf('Pop\Router\Router', $router);
        $this->assertInstanceOf('Closure', $router->getRouteMatch()->getDefaultRoute()['controller']);
        $this->assertInstanceOf('Closure', $router->getRouteMatch()->getController());
        $this->assertTrue($router->getRouteMatch()->hasController());
        $this->assertTrue($router->getRouteMatch()->hasDefaultRoute());
        $this->assertNull($router->getRouteMatch()->getDynamicRoute());
        $this->assertNull($router->getRouteMatch()->getDynamicRoutePrefix());
        $this->assertFalse($router->getRouteMatch()->hasDynamicRoute());
        $this->assertFalse($router->getRouteMatch()->isDynamicRoute());
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
        $router->appendControllerParams('/user', [2000, 'append' => true]);
        $this->assertContains(1000, $router->getControllerParams('/user'));
        $this->assertContains(2000, $router->getControllerParams('/user'));
        $this->assertTrue($router->hasControllerParams('/user'));
        $router->removeControllerParams('/user');
        $this->assertFalse($router->hasControllerParams('/user'));
    }

    public function testAppendControllerParams()
    {
        $router = new Router();
        $router->addRoute('/user', [
            'controller' => function($id) {
                echo $id;
            }
        ]);

        $router->appendControllerParams('/user', 1000);
        $this->assertContains(1000, $router->getControllerParams('/user'));
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
        $this->assertNull($router->getRouteMatch()->getSegment(0));
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

        $router->prepare();
        $router->route();
        $this->assertEquals('Pop\Test\TestAsset\TestController', $router->getControllerClass());
        $this->assertEquals(123, $router->getController()->foo);
        $this->assertTrue($router->hasAction());
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
        $this->assertTrue($router->getRouteMatch()->hasAction());
        $this->assertEquals('help', $router->getRouteMatch()->getAction());
        $this->assertContains('help', $router->getRouteMatch()->getRoute());
        $this->assertContains('help', $router->getRouteMatch()->getOriginalRoute());
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

        $router->route();
        $this->assertTrue($router->hasRoute());
        $this->assertEquals('Pop\Test\TestAsset\TestController', $router->getControllerClass());
        $this->assertInstanceOf('Pop\Test\TestAsset\TestController', $router->getController());
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
        $router->addRoute('help [--o1|options1] [--o2|options2] [--o3|options3]', [
            'controller'  => 'Pop\Test\TestAsset\TestController',
            'action'      => 'help'
        ]);

        $router->route();
        $this->assertTrue($router->hasRoute());
        $this->assertEquals(1, count($router->getRouteMatch()->getCommands()));
        $this->assertEquals(1, count($router->getRouteMatch()->getOptions()));
        $this->assertEquals(0, count($router->getRouteMatch()->getParameters()));
        $this->assertNull($router->getRouteMatch()->getOption('foo'));
        $this->assertNull($router->getRouteMatch()->getParameter('foo'));
    }

    public function testCliArrays()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help', '--id=1', '--id=2'
        ];

        $router = new Router();
        $router->addRoute('help [-i|--id=*]', [
            'controller'  => function() {}
        ]);

        $router->route();
        $this->assertTrue($router->hasRoute());
        $this->assertEquals(1, count($router->getRouteMatch()->getOptions()));
        $this->assertEquals(2, count($router->getRouteMatch()->getOption('id')));
    }

    public function testNoRoute()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'foo'
        ];

        $router = new Router();
        $router->addRoute('help', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);
        $router->route();
        $this->assertFalse($router->hasRoute());

        ob_start();
        $router->noRouteFound(false);
        $result = ob_get_clean();
        $this->assertFalse(ctype_print($result));
    }

    public function testNotController()
    {
        $this->expectException('Pop\Router\Exception');
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router();
        $router->addRoute('help', [
            'controller' => 'Pop\Test\TestAsset\TestNotController',
            'action'     => 'help'
        ]);
        $router->route();
    }


}