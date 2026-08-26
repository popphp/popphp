<?php

namespace Pop\Test;

use Pop\Router;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{

    public function testConstructorRoutes()
    {
        $router = new Router\Router([
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
        $this->assertInstanceOf('Closure', $router->getRouteMatch()->getDefaultRoute()['*']['controller']);
        $this->assertInstanceOf('Closure', $router->getRouteMatch()->getDispatchable());
        $this->assertTrue($router->getRouteMatch()->hasDispatchable());
        $this->assertTrue($router->getRouteMatch()->hasDefaultRoute());
        $this->assertNull($router->getRouteMatch()->getDynamicRoute());
        $this->assertNull($router->getRouteMatch()->getDynamicRoutePrefix());
        $this->assertFalse($router->getRouteMatch()->hasDynamicRoute());
        $this->assertFalse($router->getRouteMatch()->isDynamicRoute());
    }

    public function testAddRoute()
    {
        $router = new Router\Router();
        $router->addRoute('/system/add', [
            'controller' => function() {
                echo 'add';
            },
            'name' => 'system'
        ]);

        $this->assertTrue(array_key_exists('/system/add', $router->getRoutes()));
    }

    public function testAddNestedRoute()
    {
        $router = new Router\Router();
        $router->addRoute('/system', [
            '/add' => [
                'controller' => function() {
                    echo 'add';
                }
            ]
        ]);

        $this->assertTrue(array_key_exists('/system/add', $router->getRoutes()));
    }

    public function testAddDynamicRoute()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'users', 'edit', '1'
        ];

        $router = new Router\Router();
        $router->addRoute('<controller> <action>', [
            'prefix' => 'Pop\Test\TestAsset\\'
        ]);

        $this->assertTrue($router->getRouteMatch()->hasDynamicRoute());
        $this->assertTrue($router->getRouteMatch()->hasAction());
    }

    public function testAddDispatchableParams()
    {
        $router = new Router\Router();
        $router->addRoute('/user', [
            'controller' => function($id) {
                echo $id;
            }
        ]);

        $router->addDispatchableParams('/user', [1000, 'append' => true]);
        $router->appendDispatchableParams('/user', [2000, 'append' => true]);
        $this->assertContains(1000, $router->getDispatchableParams('/user'));
        $this->assertContains(2000, $router->getDispatchableParams('/user'));
        $this->assertTrue($router->hasDispatchableParams('/user'));
        $router->removeDispatchableParams('/user');
        $this->assertFalse($router->hasDispatchableParams('/user'));
    }

    public function testAppendDispatchableParams()
    {
        $router = new Router\Router();
        $router->addRoute('/user', [
            'controller' => function($id) {
                echo $id;
            }
        ]);

        $router->appendDispatchableParams('/user', 1000);
        $this->assertContains(1000, $router->getDispatchableParams('/user'));
    }

    public function testAddDispatchableParamsNoAppend()
    {
        $router = new Router\Router();
        $router->addRoute('/user', [
            'controller' => function($id) {
                echo $id;
            }
        ]);

        $router->addDispatchableParams('/user', 1000);
        $this->assertContains(1000, $router->getDispatchableParams('/user'));
    }

    public function testAddDispatchableParamsNull()
    {
        $router = new Router\Router();
        $router->addRoute('/user', [
            'controller' => function($id) {
                echo $id;
            }
        ]);

        $router->addDispatchableParams('/user', 1000);
        $this->assertEquals(1, count($router->getDispatchableParams('/user')));
    }

    public function testIsCli()
    {
        $router = new Router\Router();
        $this->assertTrue($router->isCli());
        $this->assertFalse($router->isHttp());
    }

    public function testGetRouteMatch()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'edit'
        ];

        $router = new Router\Router();
        $router->addRoute('edit', [
            'controller' => function () {
                echo 'edit';
            },
            'middleware' => 'Pop\Test\TestAsset\TestMiddleware'
        ]);
        $router->route();
        $this->assertInstanceOf('Pop\Router\Match\AbstractMatch', $router->getRouteMatch());
        $this->assertEquals('edit', $router->getRouteMatch()->getSegment(0));
    }

    #[RunInSeparateProcess]
    public function testRouteWithMiddlewareDoesNotRequireBootstrappedApplication()
    {
        // Runs in a separate process so Pop\App's static instance is
        // guaranteed to still be null - reproduces route() being called
        // on a bare Router before any Application has ever been constructed
        // anywhere in the process.
        $this->assertFalse(\Pop\App::has());

        $_SERVER['argv'] = [
            'myscript.php', 'edit'
        ];

        $router = new Router\Router();
        $router->addRoute('edit', [
            'controller' => function () {
                echo 'edit';
            },
            'middleware' => 'Pop\Test\TestAsset\TestMiddleware'
        ]);
        $router->route();

        $this->assertTrue($router->hasRoute());
        $this->assertEquals('edit', $router->getRouteMatch()->getSegment(0));
    }

    public function testHasRoute1()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router\Router();
        $router->addRoute('help', [
            'controller' => function () {
                echo 'help';
            }
        ]);

        $router->route();
        $this->assertTrue($router->hasRoute());
    }

    public function testHasRoute2()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'users', '-o'
        ];

        $router = new Router\Router();
        $router->addRoute('users <name>', [
            'controller' => function ($name) {
                echo 'users';
            }
        ]);

        $router->route();
        $this->assertFalse($router->hasRoute());
    }

    public function testGetDispatchableClass()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router\Router();
        $router->addRoute('help', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help',
            'params'     => [123]
        ]);

        $router->prepare();
        $router->route();
        $this->assertEquals('Pop\Test\TestAsset\TestController', $router->getDispatchableClass());
        $this->assertEquals(123, $router->getDispatchable()->foo);
        $this->assertTrue($router->hasAction());
    }

    public function testDispatchableWithNoConstructorReceivesApplication()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $application = new \Pop\Application();

        $router = new Router\Router();
        $router->addRoute('help', [
            'controller' => 'Pop\Test\TestAsset\TestPlainDispatchable',
            'action'     => 'index'
        ]);

        $router->route();
        $this->assertTrue($router->getDispatchable()->hasApplication());
        $this->assertSame($application, $router->getDispatchable()->getApplication());
    }

    public function testDispatchableWithApplicationTypedCustomConstructorReceivesApplication()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $application = new \Pop\Application();

        $router = new Router\Router();
        $router->addRoute('help', [
            'controller' => 'Pop\Test\TestAsset\TestCustomConstructorDispatchable',
            'action'     => 'index'
        ]);

        $router->route();
        $this->assertTrue($router->getDispatchable()->hasApplication());
        $this->assertSame($application, $router->getDispatchable()->getApplication());
        $this->assertEquals('default', $router->getDispatchable()->label);
    }

    public function testDispatchableWithIncompatibleCustomConstructorDoesNotReceiveApplication()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        new \Pop\Application();

        $router = new Router\Router();
        $router->addRoute('help', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);

        $router->route();
        $this->assertFalse($router->getDispatchable()->hasApplication());
    }

    #[RunInSeparateProcess]
    public function testDispatchableWithNoConstructorAndNoBootstrappedApplicationFallsBackGracefully()
    {
        $this->assertFalse(\Pop\App::has());

        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router\Router();
        $router->addRoute('help', [
            'controller' => 'Pop\Test\TestAsset\TestPlainDispatchable',
            'action'     => 'index'
        ]);

        $router->route();
        $this->assertFalse($router->getDispatchable()->hasApplication());
    }

    public function testCliAddRouteAcceptsArrowNotationControllerString()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router\Router();
        $router->addRoute('help', 'Pop\Test\TestAsset\TestController->help');

        $router->route();
        $this->assertTrue($router->hasRoute());
        $this->assertEquals('Pop\Utils\CallableObject', $router->getDispatchableClass());
        $this->assertEquals('Pop\Test\TestAsset\TestController->help', $router->getDispatchable());
    }

    public function testRouteMatch()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router\Router();
        $router->addRoute('help', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);

        $router->route();
        $this->assertTrue(is_array($router->getRouteMatch()->getRoutes()));
        $this->assertTrue($router->getRouteMatch()->hasAction());
        $this->assertEquals('help', $router->getRouteMatch()->getAction());
        $this->assertStringContainsString('help', $router->getRouteMatch()->getRoute());
        $this->assertStringContainsString('help', $router->getRouteMatch()->getOriginalRoute());
    }

    public function testGetPreparedRoutes()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router\Router();
        $router->addRoute('help', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);

        $router->route();
        $preparedRoutes  = $router->getRouteMatch()->getPreparedRoutes();
        $flattenedRoutes = $router->getRouteMatch()->getFlattenedRoutes();

        $this->assertTrue(isset($preparedRoutes['/^help$(.*)$/']));
        $this->assertTrue(is_array($preparedRoutes['/^help$(.*)$/']));
        $this->assertEquals(3, count($preparedRoutes['/^help$(.*)$/']));
        $this->assertTrue(isset($preparedRoutes['/^help$(.*)$/']['controller']));
        $this->assertTrue(isset($preparedRoutes['/^help$(.*)$/']['action']));
        $this->assertTrue(isset($preparedRoutes['/^help$(.*)$/']['route']));
        $this->assertEquals('Pop\Test\TestAsset\TestController', $preparedRoutes['/^help$(.*)$/']['controller']);
        $this->assertEquals('help', $preparedRoutes['/^help$(.*)$/']['action']);
        $this->assertEquals('help', $preparedRoutes['/^help$(.*)$/']['route']);
    }

    public function testGetFlattenedRoutes()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router\Router();
        $router->addRoute('help', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);

        $router->route();
        $flattenedRoutes = $router->getRouteMatch()->getFlattenedRoutes();

        $this->assertTrue(isset($flattenedRoutes['help']));
        $this->assertTrue(isset($flattenedRoutes['help']['controller']));
        $this->assertTrue(isset($flattenedRoutes['help']['action']));
        $this->assertEquals('Pop\Test\TestAsset\TestController', $flattenedRoutes['help']['controller']);
        $this->assertEquals('help', $flattenedRoutes['help']['action']);
    }

    public function testHasRouteConfig()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router\Router();
        $router->addRoute('help', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);


        $this->assertFalse($router->getRouteMatch()->hasRouteConfig());
        $router->route();
        $this->assertTrue($router->getRouteMatch()->hasRouteConfig());
    }

    public function testGetRouteConfig()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router\Router();
        $router->addRoute('help', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);


        $this->assertNull($router->getRouteMatch()->getRouteConfig());
        $router->route();
        $this->assertTrue(is_array($router->getRouteMatch()->getRouteConfig()));
        $this->assertEquals('help', $router->getRouteMatch()->getRouteConfig('action'));
    }

    public function testDispatchableParams()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router\Router();
        $router->addRoute('help', [
            'controller'       => 'Pop\Test\TestAsset\TestController',
            'action'           => 'help',
            'params' => [123]
        ]);

        $router->addDispatchableParams('*', ['foo' => 123]);
        $router->route();
        $this->assertEquals(123, $router->getDispatchable()->foo);
    }

    public function testWildcardDispatchableParams()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router\Router();
        $router->addRoute('help', [
            'controller'  => 'Pop\Test\TestAsset\TestController',
            'action'      => 'help'
        ]);

        $router->addDispatchableParams('*', ['foo' => 123]);
        $router->route();
        $this->assertEquals(123, $router->getDispatchable()->foo);
    }

    public function testCliMatch()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];
        $match = new Router\Match\Cli();
        $this->assertEquals('help', $match->getRouteString());
        $this->assertTrue(is_array($match->getCommandParameters()));
        $this->assertTrue(is_array($match->getCommandOptions()));
    }

    public function testCliNoMatch()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];
        $match = new Router\Match\Cli();
        $this->assertFalse($match->match('foo'));
        ob_start();
        $match->noRouteFound(false);
        $result = ob_get_clean();
        $this->assertFalse(ctype_print($result));
    }

    public function testHttpMatch()
    {
        $_SERVER['REQUEST_URI'] = '/system/?id=123';
        $match = new Router\Match\Http();
        $this->assertEquals('', $match->getBasePath());
        $this->assertContains('system', $match->getSegments());
        $this->assertEquals('/system/', $match->getRouteString());
    }

    public function testHttpDynamicMatch()
    {
        $_SERVER['REQUEST_URI'] = '/test/edit/1001';

        $router = new Router\Router(null, new Router\Match\Http());
        $router->addRoute('/:controller/:action/:param', [
            'prefix' => 'Pop\Test\TestAsset\\'
        ]);

        $router->route();
        $this->assertTrue($router->hasRoute());
        $this->assertEquals('Pop\Test\TestAsset\TestController', $router->getDispatchableClass());
        $this->assertInstanceOf('Pop\Test\TestAsset\TestController', $router->getDispatchable());
    }

    public function testCliDynamicMatch()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'foo', 'test', 'edit', '1001'
        ];
        $router = new Router\Router(null, new Router\Match\Cli());
        $router->addRoute('<controller> <action> <param>', [
            'prefix' => 'Pop\Test\TestAsset\\'
        ]);

        $this->assertTrue($router->hasRoute());
    }

    public function testHttpMatchIndex()
    {
        $_SERVER['REQUEST_URI'] = '';
        $match = new Router\Match\Http();
        $this->assertEquals('', $match->getBasePath());
        $this->assertEquals('/', $match->getRouteString());
    }

    public function testCliRoute()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router\Router();
        $router->addRoute('help', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);
        $router->route();
        $this->assertTrue($router->hasRoute());
    }

    public function testCliRouteWithSlashDoesNotThrowRegexWarning()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router\Router();
        $router->addRoute('/nope', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help'
        ]);

        $router->route();
        $this->assertFalse($router->hasRoute());
    }

    public function testCliRouteWithLiteralSlashMatches()
    {
        $_SERVER['argv'] = [
            'myscript.php', '/nope'
        ];

        $router = new Router\Router();
        $router->addRoute('/nope', [
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

        $router = new Router\Router();
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

        $router = new Router\Router();
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

        $router = new Router\Router();
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

        $router = new Router\Router();
        $router->addRoute('help [--o1|--options1] [--o2|--options2] [--o3|--options3]', [
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

    public function testCliOptionValues()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help', '-i1'
        ];

        $router = new Router\Router();
        $router->addRoute('help [-i|--id=]', [
            'controller'  => function() {}
        ]);

        $router->route();
        $this->assertTrue($router->hasRoute());
        $this->assertEquals(1, count($router->getRouteMatch()->getOptions()));
        $this->assertEquals("1", $router->getRouteMatch()->getOption('id'));
    }

    public function testCliArrays()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help', '--id=1', '--id=2'
        ];

        $router = new Router\Router();
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

        $router = new Router\Router();
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

    public function testCliSpecificityOrderIndependentOfDeclarationOrderLiteralFirst()
    {
        $_SERVER['argv'] = ['myscript.php', 'users', 'new'];

        $router = new Router\Router();
        $router->addRoute('users new', [
            'controller' => function() { echo 'New'; },
        ]);
        $router->addRoute('users <id>', [
            'controller' => function($id) { echo 'Show'; },
        ]);

        $router->route();
        $this->assertTrue($router->hasRoute());
        $this->assertStringContainsString('users new', $router->getRouteMatch()->getOriginalRoute());
    }

    public function testCliSpecificityOrderIndependentOfDeclarationOrderParamFirst()
    {
        $_SERVER['argv'] = ['myscript.php', 'users', 'new'];

        $router = new Router\Router();
        $router->addRoute('users <id>', [
            'controller' => function($id) { echo 'Show'; },
        ]);
        $router->addRoute('users new', [
            'controller' => function() { echo 'New'; },
        ]);

        $router->route();
        $this->assertTrue($router->hasRoute());
        $this->assertStringContainsString('users new', $router->getRouteMatch()->getOriginalRoute());
    }

    public function testCliSingleCharShortOption()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help', '-o'
        ];

        $router = new Router\Router();
        $router->addRoute('help [-o]', [
            'controller' => function() {}
        ]);

        $router->route();
        $this->assertTrue($router->hasRoute());
        $this->assertEquals(1, count($router->getRouteMatch()->getOptions()));
    }

    public function testCliArrayOptionLongFormOnly()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help', '--id=1', '--id=2'
        ];

        $router = new Router\Router();
        $router->addRoute('help [--id=*]', [
            'controller' => function() {}
        ]);

        $router->route();
        $this->assertTrue($router->hasRoute());
        $this->assertEquals(2, count($router->getRouteMatch()->getOption('id')));
    }

    public function testCliArrayOptionDashPrefixWithoutEquals()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help', '-i1', '-i2'
        ];

        $router = new Router\Router();
        $router->addRoute('help [-i|--id=*]', [
            'controller' => function() {}
        ]);

        $router->route();
        $this->assertTrue($router->hasRoute());
        $this->assertEquals(2, count($router->getRouteMatch()->getOption('id')));
    }

    public function testCliRouteWithOnlyOptionalParameter()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help', 'world'
        ];

        $router = new Router\Router();
        $router->addRoute('help [<name>]', [
            'controller' => function() {}
        ]);

        $router->route();
        $this->assertTrue($router->hasRoute());
        $this->assertEquals(1, count($router->getRouteMatch()->getParameters()));
    }

    public function testCliDynamicRouteWithParamCollection()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'users', 'edit', 'a', 'b', 'c'
        ];

        $router = new Router\Router();
        $router->addRoute('<controller> <action> <param*>', [
            'prefix' => 'Pop\Test\TestAsset\\'
        ]);

        $router->route();
        $this->assertTrue($router->hasRoute());
        $this->assertTrue($router->hasRouteParams());
        $this->assertEquals(['a', 'b', 'c'], $router->getRouteParams()[0]);
    }

    public function testCliDynamicRouteWithCommandUsesDeclaredTokenPositions()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'admin', 'users', 'edit', '1001'
        ];

        $router = new Router\Router(null, new Router\Match\Cli());
        $router->addRoute('admin <controller> <action> [<param>]', [
            'prefix' => 'Pop\Test\TestAsset\\'
        ]);
        $router->route();

        $this->assertTrue($router->hasRoute());
        $this->assertEquals('Pop\Test\TestAsset\UsersController', $router->getDispatchableClass());
        $this->assertEquals('edit', $router->getRouteMatch()->getAction());
        $this->assertTrue($router->getRouteMatch()->hasAction());
        $this->assertEquals(['1001'], array_values($router->getRouteParams()));
    }

    public function testCliDynamicRouteWithCommandDoesNotMatchWithoutTheCommand()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'users', 'edit', '1001'
        ];

        $match = new Router\Match\Cli();
        $match->addRoute('admin <controller> <action> [<param>]', [
            'prefix' => 'Pop\Test\TestAsset\\'
        ]);
        $match->match();

        $this->assertTrue($match->hasDynamicRoute());
        $this->assertFalse($match->hasRoute());
        $this->assertFalse($match->hasDispatchable());
        $this->assertNull($match->getDispatchable());
        $this->assertNull($match->getAction());
        $this->assertFalse($match->hasRouteParams());
    }

    public function testCliDynamicRouteWithNoCommandIsUnchanged()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'users', 'edit', '1001'
        ];

        $match = new Router\Match\Cli();
        $match->addRoute('<controller> <action> <param>', [
            'prefix' => 'Pop\Test\TestAsset\\'
        ]);
        $match->match();

        $this->assertTrue($match->hasRoute());
        $this->assertEquals('Pop\Test\TestAsset\UsersController', $match->getDispatchable());
        $this->assertEquals('edit', $match->getAction());
        $this->assertEquals(['1001'], array_values($match->getRouteParams()));
    }

    public function testCliDefaultRouteConfigKey()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'anything'
        ];

        $router = new Router\Router();
        $router->addRoute('help', [
            'controller' => function() { echo 'Help'; },
            'default'    => true,
        ]);
        $router->route();

        $this->assertTrue($router->getRouteMatch()->hasDefaultRoute());
    }

    public function testCliForceRouteCarriesRequiredParam()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'unrelated'
        ];

        $router = new Router\Router();
        $router->addRoute('greet <name>', [
            'controller' => function($name) { echo 'Greet'; },
        ]);

        $router->route('greet Nick');
        $this->assertTrue($router->hasRoute());
        $this->assertEquals('Nick', $router->getRouteMatch()->getParameter('name'));
    }

    public function testCliForceRouteCarriesShortOptionFlag()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'unrelated'
        ];

        $router = new Router\Router();
        $router->addRoute('tag <id> [--force]', [
            'controller' => function($id) { echo 'Tag'; },
        ]);

        $router->route('tag 42 --force');
        $this->assertTrue($router->hasRoute());
        $this->assertEquals('42', $router->getRouteMatch()->getParameter('id'));
        $this->assertTrue($router->getRouteMatch()->getOption('force'));
    }

    public function testCliForceRouteCarriesLongOptionFlag()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'unrelated'
        ];

        $router = new Router\Router();
        $router->addRoute('send:email [-q|--quiet] <email>', [
            'controller' => function($email) { echo 'Send'; },
        ]);

        $router->route('send:email --quiet test@test.com');
        $this->assertTrue($router->hasRoute());
        $this->assertTrue($router->getRouteMatch()->getOption('quiet'));
        $this->assertEquals('test@test.com', $router->getRouteMatch()->getParameter('email'));
    }

    public function testCliForceRouteArrayFormPreservesValueWithSpaces()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'unrelated'
        ];

        $router = new Router\Router();
        $router->addRoute('send:email [-q|--quiet] <name>', [
            'controller' => function($name) { echo 'Send'; },
        ]);

        $router->route(['send:email', '-q', 'John Smith']);
        $this->assertTrue($router->hasRoute());
        $this->assertTrue($router->getRouteMatch()->getOption('quiet'));
        $this->assertEquals('John Smith', $router->getRouteMatch()->getParameter('name'));
    }

    public function testCliMatchResetsStaleRouteAfterFailedForceRoute()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $match = new Router\Match\Cli();
        $match->addRoute('help', ['controller' => function() {}]);

        $this->assertTrue($match->match());
        $this->assertNotNull($match->getOriginalRoute());

        $this->assertFalse($match->match('nonexistent command'));
        $this->assertNull($match->getOriginalRoute());
    }

    public function testCliForceRouteCarriesOptionValue()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'unrelated'
        ];

        $router = new Router\Router();
        $router->addRoute('help [--name=] [--email=]', [
            'controller' => function() { echo 'Help'; },
        ]);

        $router->route('help --name=test --email=test@test.com');
        $this->assertTrue($router->hasRoute());
        $this->assertEquals('test', $router->getRouteMatch()->getOption('name'));
        $this->assertEquals('test@test.com', $router->getRouteMatch()->getOption('email'));
    }

    public function testCliForceRouteCarriesArrayOption()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'unrelated'
        ];

        $router = new Router\Router();
        $router->addRoute('help [-i|--id=*]', [
            'controller' => function() { echo 'Help'; },
        ]);

        $router->route('help --id=1 --id=2');
        $this->assertTrue($router->hasRoute());
        $this->assertEquals(2, count($router->getRouteMatch()->getOption('id')));
    }

    public function testCliRepeatedForceRouteDoesNotLeakParamsIntoNextMatch()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'unrelated'
        ];

        $router = new Router\Router();
        $router->addRoute('send:email <email>', [
            'controller' => function($email) { echo 'Send'; },
        ]);
        $router->addRoute('greet <name>', [
            'controller' => function($name) { echo 'Greet'; },
        ]);

        $router->route('send:email a@b.com');
        $this->assertEquals('a@b.com', $router->getRouteMatch()->getParameter('email'));

        $router->route('greet Nick');
        $this->assertTrue($router->hasRoute());
        $this->assertEquals(['name' => 'Nick'], $router->getRouteMatch()->getParameters());
        $this->assertEquals(['Nick'], array_values($router->getRouteParams()));
    }

    public function testCliRepeatedForceRouteDoesNotLeakOptionsIntoNextMatch()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'unrelated'
        ];

        $router = new Router\Router();
        $router->addRoute('tag <id> [--force]', [
            'controller' => function($id) { echo 'Tag'; },
        ]);
        $router->addRoute('greet <name>', [
            'controller' => function($name) { echo 'Greet'; },
        ]);

        $router->route('tag 42 --force');
        $this->assertTrue($router->getRouteMatch()->getOption('force'));

        $router->route('greet Nick');
        $this->assertTrue($router->hasRoute());
        $this->assertNull($router->getRouteMatch()->getOption('force'));
    }

    public function testCliMatchResetsHasAllRequiredAfterMissingParam()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'unrelated'
        ];

        $router = new Router\Router();
        $router->addRoute('greet <first> <last>', [
            'controller' => function($first, $last) { echo 'Greet'; },
        ]);

        $router->route('greet Nick');
        $this->assertFalse($router->hasRoute());

        $router->route('greet Nick Sagona');
        $this->assertTrue($router->hasRoute());
        $this->assertEquals('Sagona', $router->getRouteMatch()->getParameter('last'));
    }

    public function testRouterRouteResetsStaleDispatchableAfterFailedMatch()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $router = new Router\Router();
        $router->addRoute('help', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help',
        ]);

        $router->route();
        $this->assertTrue($router->hasDispatchable());

        $router->route('nonexistent command');
        $this->assertFalse($router->hasDispatchable());
        $this->assertFalse($router->hasAction());
    }

    public function testUndefinedMethodCallInCliModeReportsUndefinedMethodNotHttpMismatch()
    {
        $router = new Router\Router();

        $this->expectException('Pop\Router\Exception');
        $this->expectExceptionMessage('Call to undefined method Pop\Router\Router::hasController()');

        $router->hasController();
    }

}
