<?php

namespace Pop\Test;

use Pop\Application;
use Pop\Config\Config;
use Pop\Router\Router;
use Pop\Service\Locator;
use Pop\Event;
use Pop\Middleware;
use Pop\Module;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{

    public function tearDown(): void
    {
        // Always reset - maintenance-mode tests below set this directly on
        // $_ENV (App::env() reads $_ENV directly), and it must never leak
        // into unrelated tests elsewhere in the suite that call run().
        unset($_ENV['MAINTENANCE_MODE']);
    }

    public function testConstructor()
    {
        $application = new Application(
            new Router(),
            new Locator(),
            new Event\Manager(),
            new Middleware\Manager(),
            new Module\Manager(),
            include __DIR__ . '/../vendor/autoload.php',
            ['foo' => 'bar']
        );

        $this->assertInstanceOf('Pop\Application', $application);
        $this->assertInstanceOf('Pop\Router\Router', $application->router());
        $this->assertInstanceOf('Pop\Service\Locator', $application->services());
        $this->assertInstanceOf('Pop\Event\Manager', $application->events());
        $this->assertInstanceOf('Pop\Middleware\Manager', $application->middleware());
        $this->assertInstanceOf('Pop\Module\Manager', $application->modules());
        $this->assertInstanceOf('Composer\Autoload\ClassLoader', $application->autoloader());
        $this->assertEquals($application->config()['foo'], 'bar');

        $this->assertInstanceOf('Pop\Router\Router', $application['router']);
        $this->assertInstanceOf('Pop\Service\Locator', $application['services']);
        $this->assertInstanceOf('Pop\Event\Manager', $application['events']);
        $this->assertInstanceOf('Pop\Middleware\Manager', $application['middleware']);
        $this->assertInstanceOf('Pop\Module\Manager', $application['modules']);
        $this->assertInstanceOf('Composer\Autoload\ClassLoader', $application['autoloader']);
        $this->assertEquals($application['config']['foo'], 'bar');

        $this->assertInstanceOf('Pop\Router\Router', $application->router);
        $this->assertInstanceOf('Pop\Service\Locator', $application->services);
        $this->assertInstanceOf('Pop\Event\Manager', $application->events);
        $this->assertInstanceOf('Pop\Middleware\Manager', $application->middleware);
        $this->assertInstanceOf('Pop\Module\Manager', $application->modules);
        $this->assertInstanceOf('Composer\Autoload\ClassLoader', $application->autoloader);
        $this->assertNull($application->foo);
        $this->assertEquals($application->config['foo'], 'bar');
    }

    public function testMagicMethods()
    {
        $application = new Application();

        $application->router     = new Router();
        $application->services   = new Locator();
        $application->events     = new Event\Manager();
        $application->middleware = new Middleware\Manager();
        $application->modules    = new Module\Manager();
        $application->autoloader = include __DIR__ . '/../vendor/autoload.php';
        $application->config     = ['foo' => 'bar'];

        $this->assertTrue(isset($application->router));
        $this->assertTrue(isset($application->services));
        $this->assertTrue(isset($application->events));
        $this->assertTrue(isset($application->middleware));
        $this->assertTrue(isset($application->modules));
        $this->assertTrue(isset($application->autoloader));
        $this->assertTrue(isset($application->config));

        unset($application->router);
        unset($application->services);
        unset($application->events);
        unset($application->middleware);
        unset($application->modules);
        unset($application->autoloader);
        unset($application->config);

        $this->assertFalse(isset($application->router));
        $this->assertFalse(isset($application->services));
        $this->assertFalse(isset($application->events));
        $this->assertFalse(isset($application->middleware));
        $this->assertFalse(isset($application->modules));
        $this->assertFalse(isset($application->autoloader));
        $this->assertFalse(isset($application->config));
        $this->assertFalse(isset($application->foo));
    }

    public function testOffsetMethods()
    {
        $application = new Application();

        $application['router']     = new Router();
        $application['services']   = new Locator();
        $application['events']     = new Event\Manager();
        $application['middleware'] = new Middleware\Manager();
        $application['modules']    = new Module\Manager();
        $application['autoloader'] = include __DIR__ . '/../vendor/autoload.php';
        $application['config']     = ['foo' => 'bar'];

        $this->assertTrue(isset($application['router']));
        $this->assertTrue(isset($application['services']));
        $this->assertTrue(isset($application['events']));
        $this->assertTrue(isset($application['middleware']));
        $this->assertTrue(isset($application['modules']));
        $this->assertTrue(isset($application['autoloader']));
        $this->assertTrue(isset($application['config']));

        unset($application['router']);
        unset($application['services']);
        unset($application['events']);
        unset($application['middleware']);
        unset($application['modules']);
        unset($application['autoloader']);
        unset($application['config']);

        $this->assertFalse(isset($application['router']));
        $this->assertFalse(isset($application['services']));
        $this->assertFalse(isset($application['events']));
        $this->assertFalse(isset($application['middleware']));
        $this->assertFalse(isset($application['modules']));
        $this->assertFalse(isset($application['autoloader']));
        $this->assertFalse(isset($application['config']));
        $this->assertFalse(isset($application['foo']));
    }

    public function testBootstrap()
    {
        $application = new Application(include __DIR__ . '/../vendor/autoload.php');
        $this->assertInstanceOf('Pop\Application', $application);
        $this->assertInstanceOf('Pop\Router\Router', $application->router());
        $this->assertInstanceOf('Pop\Service\Locator', $application->services());
        $this->assertInstanceOf('Pop\Event\Manager', $application->events());
        $this->assertInstanceOf('Pop\Middleware\Manager', $application->middleware());
        $this->assertInstanceOf('Pop\Module\Manager', $application->modules());
        $this->assertInstanceOf('Composer\Autoload\ClassLoader', $application->autoloader());
    }

    public function testAddConfigValue()
    {
        $application = new Application(['foo' => 'bar']);
        $application->addConfigValue('baz', 123);
        $this->assertEquals($application['config']['baz'], 123);
    }

    public function testUpdateConfigValue()
    {
        $application = new Application(['foo' => 'bar']);
        $application->updateConfigValue('foo', 123);
        $this->assertEquals($application['config']['foo'], 123);
    }

    public function testDeleteConfigValue()
    {
        $application = new Application(['foo' => 'bar']);
        $application->addConfigValue('baz', 123);
        $application->deleteConfigValue('baz');
        $this->assertFalse(isset($application['config']['baz']));
    }

    public function testMergeConfig1()
    {
        $application = new Application();
        $application->mergeConfig(['foo' => 'bar']);
        $application->mergeConfig(['baz' => 123]);
        $this->assertEquals($application->config()['baz'], 123);
        $application->mergeConfig(['foo' => 456]);
        $this->assertEquals($application->config()['foo'], 456);
        $application->mergeConfig(new Config(['test' => 789]));
        $this->assertEquals($application->config()['test'], 789);
    }

    public function testMergeConfig2()
    {
        $application = new Application(new Config(['test' => 123], true));
        $application->mergeConfig(new Config(['foo' => 'bar'], true));
        $this->assertEquals($application->config()['test'], 123);
        $this->assertEquals($application->config()['foo'], 'bar');
    }

    public function testRegisterConfig()
    {
        $config = [
            'name'     => 'Test App',
            'version'  => '1.0.0',
            'foo'      => 'bar',
            'routes'   => [
                '/login[/]' => [
                    'controller' => 'Foo\Controller\IndexController',
                    'action'     => 'login'
                ]
            ],
            'services' => [
                'session' => [
                    'call' => 'Pop\Session\Session::getInstance'
                ],
                'foo' => [
                    'call'   => 'Foo\Service::factory',
                    'params' => ['foo' => 'bar']
                ]
            ],
            'events'   => [
                [
                    'name'   => 'app.init',
                    'action' => function() {
                        return 123;
                    },
                    'priority' => 1000
                ]
            ],
            'middleware' => ['TestMiddleware'],
            'prefix'     => 'TestAsset\\',
            'src'        => __DIR__ . '/TestAsset'
        ];
        $application = new Application($config, include __DIR__ . '/../vendor/autoload.php');
        $application->addRoute('/logout', [
            'controller' => 'Foo\Controller\IndexController',
            'action'     => 'logout'
        ]);
        $application->addRoutes([
            '/save' => [
                'controller' => 'Foo\Controller\IndexController',
                'action'     => 'save'
            ]
        ]);
        $this->assertEquals($application->config()['foo'], 'bar');
        $this->assertTrue($application->hasName());
        $this->assertEquals($application->getName(), 'Test App');
        $this->assertTrue($application->hasVersion());
        $this->assertEquals($application->getVersion(), '1.0.0');
    }

    public function testRegisterConfig2()
    {
        $config = [
            'foo'      => 'bar',
            'routes'   => [
                '/login[/]' => [
                    'controller' => 'Foo\Controller\IndexController',
                    'action'     => 'login'
                ]
            ],
            'services' => [
                'session' => [
                    'call' => 'Pop\Session\Session::getInstance'
                ],
                'foo' => [
                    'call'   => 'Foo\Service::factory',
                    'params' => ['foo' => 'bar']
                ]
            ],
            'events'   => [
                [
                    'name'   => 'app.init',
                    'action' => function() {
                        return 123;
                    },
                    'priority' => 1000
                ]
            ],
            'prefix' => 'TestAsset',
            'src'    => __DIR__ . '/TestAsset',
            'psr-0'  => true
        ];
        $application = new Application($config, include __DIR__ . '/../vendor/autoload.php');
        $application->addRoute('/logout', [
            'controller' => 'Foo\Controller\IndexController',
            'action'     => 'logout'
        ]);
        $application->addRoutes([
            '/save' => [
                'controller' => 'Foo\Controller\IndexController',
                'action'     => 'save'
            ]
        ]);
        $this->assertEquals($application->config()['foo'], 'bar');
    }

    public function testInit()
    {
        $config = [
            'foo'      => 'bar',
            'events'   => [
                [
                    'name'   => 'app.init',
                    'action' => function() {
                        return 123;
                    },
                    'priority' => 1000
                ]
            ]
        ];
        $application = new Application($config);
        $application->init();
        $this->assertContains(123, $application->events()->getResults('app.init'));
    }

    public function testTrigger()
    {
        $config = [
            'foo'      => 'bar',
            'events'   => [
                [
                    'name'   => 'app.init',
                    'action' => function($var) {
                        return $var;
                    },
                    'priority' => 1000
                ]
            ]
        ];
        $application = new Application($config);
        $application->trigger('app.init', ['var' => 123]);
        $this->assertContains(123, $application->events()->getResults('app.init'));
    }

    public function testOff()
    {
        $config = [
            'foo'      => 'bar',
            'events'   => [
                [
                    'name'     => 'app.init',
                    'action'   => 'Foo\Bar::factory',
                    'priority' => 1000
                ]
            ]
        ];
        $application = new Application($config);
        $application->off('app.init', 'Foo\Bar::factory');
        $this->assertInstanceOf('Pop\Application', $application);
    }

    public function testRegisterConfigException()
    {
        $this->expectException('InvalidArgumentException');
        $application = new Application();
        $application->registerConfig('bad');
    }

    public function testRegisterAutoloaderTypeError()
    {
        $this->expectException(\TypeError::class);
        $application = new Application();
        $application->registerAutoloader(new \StdClass());
    }

    public function testConstructorIgnoresNonClassLoaderAutoloaderLookalike()
    {
        // FakeAutoloader has 'Autoload' in its class name and duck-types
        // add()/addPsr4(), which used to be enough to get mistaken for the
        // autoloader by the old class-name-substring heuristic. Confirms the
        // constructor now detects it by instanceof Composer\Autoload\ClassLoader
        // instead, so this decoy is correctly ignored rather than being (wrongly)
        // registered as the application's autoloader.
        $application = new Application(new TestAsset\FakeAutoloader(), ['foo' => 'bar']);

        $this->assertNull($application->autoloader());
        $this->assertEquals('bar', $application->config()['foo']);
    }

    public function testGetService()
    {
        $config = [
            'services' => [
                'foo' => [
                    'call' => function() {
                        return 123;
                    }
                ]
            ]
        ];
        $application = new Application($config);
        $this->assertEquals($application->getService('foo'), 123);
    }

    public function testRemoveService()
    {
        $this->expectException('Pop\Service\Exception');
        $config = [
            'services' => [
                'foo'  => [
                    'call' => function() {
                        return 123;
                    }
                ]
            ]
        ];
        $application = new Application($config);
        $application->removeService('foo');
        $this->assertEquals($application->getService('foo'), 123);
    }

    public function testRegisterModule1()
    {
        $application = new Application();
        $application->register(new TestAsset\TestModule(), 'test');
        $this->assertNotNull($application->module('test'));
        $this->assertTrue($application->isRegistered('test'));
    }

    public function testRegisterModule2()
    {
        $application = new Application();
        $application->register(['name' => 'test'], 'test');
        $this->assertNotNull($application->module('test'));
        $this->assertTrue($application->isRegistered('test'));
    }

    public function testUnregisterModule()
    {
        $application = new Application();
        $application->register(new TestAsset\TestModule(), 'test');
        $this->assertNotNull($application->module('test'));
        $this->assertTrue($application->isRegistered('test'));
        $application->unregister('test');
        $this->assertNull($application->module('test'));
        $this->assertFalse($application->isRegistered('test'));
    }

    public function testEventsOnRun()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];
        $config = [
            'foo'      => 'bar',
            'routes'   => [
                'help' => function() {
                    return 'help';
                }
            ],
            'events'   => [
                [
                    'name'   => 'app.init',
                    'action' => function() {
                        return 'app.init';
                    },
                    'priority' => 1000
                ],
                [
                    'name'   => 'app.route.pre',
                    'action' => function() {
                        return 'app.route.pre';
                    },
                    'priority' => 1000
                ],
                [
                    'name'   => 'app.dispatch.pre',
                    'action' => function() {
                        return 'app.dispatch.pre';
                    },
                    'priority' => 1000
                ],
                [
                    'name'   => 'app.dispatch.post',
                    'action' => function() {
                        return 'app.dispatch.post';
                    },
                    'priority' => 1000
                ]
            ]
        ];
        $application = new Application($config);
        $application->run();
        $this->assertContains('app.init', $application->events()->getResults('app.init'));
        $this->assertContains('app.route.pre', $application->events()->getResults('app.route.pre'));
        $this->assertContains('app.dispatch.pre', $application->events()->getResults('app.dispatch.pre'));
        $this->assertContains('app.dispatch.post', $application->events()->getResults('app.dispatch.post'));
    }

    public function testMiddlewareOnRun()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];
        $config = [
            'foo'      => 'bar',
            'routes'   => [
                'help' => function() {
                    return 'help';
                }
            ],
            'middleware' => 'Pop\Test\TestAsset\TestMiddleware'
        ];
        $application = new Application($config);
        ob_start();
        $application->run();
        $result = ob_get_clean();
        $this->assertStringContainsString('Entering Test Middleware.', $result);
        $this->assertStringContainsString('Exiting Test Middleware.', $result);
        $this->assertStringContainsString('Executing terminate method for test middleware.', $result);
    }

    public function testAddMiddleware()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];
        $config = [
            'foo'      => 'bar',
            'routes'   => [
                'help' => function() {
                    return 'help';
                }
            ]
        ];
        $application = new Application($config);
        $application->addMiddleware('Pop\Test\TestAsset\TestMiddleware', 'test');
        $this->assertEquals('Pop\Test\TestAsset\TestMiddleware', $application->getMiddleware('test'));
        $application->removeMiddleware('test');
        $this->assertNotEquals('Pop\Test\TestAsset\TestMiddleware', $application->getMiddleware('test'));
    }

    public function testMiddlewareManagerProcessIsReentrantSafe()
    {
        $log = new \ArrayObject();

        // Triggers a *nested* process() call on a separate Manager instance
        // from mid-chain, before continuing the outer chain below it - the
        // pre-fix implementation shared a single class-wide static handler
        // queue across every Manager instance, so this alone was enough to
        // wipe out the outer chain's remaining queue and silently skip
        // whatever came after this handler.
        $reentrant = new class($log) implements Middleware\MiddlewareInterface {
            public function __construct(protected \ArrayObject $log)
            {
            }

            public function handle(mixed $request, \Closure $next): mixed
            {
                $this->log[] = 'outer-a';

                $inner = new Middleware\Manager();
                $inner->addHandler(new class($this->log) implements Middleware\MiddlewareInterface {
                    public function __construct(protected \ArrayObject $log)
                    {
                    }

                    public function handle(mixed $request, \Closure $next): mixed
                    {
                        $this->log[] = 'inner';
                        return $next($request);
                    }
                });
                $inner->process('inner-request', function () {
                    return 'inner-response';
                });

                return $next($request);
            }
        };

        $second = new class($log) implements Middleware\MiddlewareInterface {
            public function __construct(protected \ArrayObject $log)
            {
            }

            public function handle(mixed $request, \Closure $next): mixed
            {
                $this->log[] = 'outer-b';
                return $next($request);
            }
        };

        $manager = new Middleware\Manager();
        $manager->addHandler($reentrant);
        $manager->addHandler($second);

        $manager->process('outer-request', function () {
            return 'outer-response';
        });

        $this->assertEquals(['outer-a', 'inner', 'outer-b'], $log->getArrayCopy());
    }

    public function testRunClosureController()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'edit'
        ];

        $config = [
            'routes' => [
                'edit' => [
                    'controller' => function() {
                        echo 'edit';
                    }
                ]
            ]
        ];
        $application = new Application($config);
        ob_start();
        $application->run();
        $result = ob_get_clean();
        $this->assertEquals('edit', $result);
    }

    public function testRunClosureControllerWithParam()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'edit', 1001
        ];

        $config = [
            'routes' => [
                'edit <id>' => [
                    'controller' => function($id) {
                        echo $id;
                    }
                ]
            ]
        ];
        $application = new Application($config);
        ob_start();
        $application->run();
        $result = ob_get_clean();
        $this->assertEquals(1001, $result);
    }

    public function testRunClassController()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'help'
        ];

        $config = [
            'routes' => [
                'help' => [
                    'controller' => 'Pop\Test\TestAsset\TestController',
                    'action'     => 'help',
                    'middleware' => 'Pop\Test\TestAsset\TestMiddleware'
                ]
            ]
        ];
        $application = new Application($config);
        ob_start();
        $application->run();
        $result = ob_get_clean();
        $this->assertStringContainsString('help', $result);
    }

    public function testRunClassControllerWithParam()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'edit', 1002
        ];

        $config = [
            'routes' => [
                'edit <id>' => [
                    'controller' => 'Pop\Test\TestAsset\TestController',
                    'action'     => 'edit'
                ]
            ]
        ];
        $application = new Application($config);
        $application->run();
        $this->assertEquals(1002, $application->router()->getController()->id);
    }

    public function testLoad()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'bad'
        ];

        $config = [
            'routes' => [
                'bad' => [
                    'controller' => 'Pop\Test\TestAsset\BadController',
                    'action'     => 'bad'
                ]
            ]
        ];
        $application = new Application($config);
        $this->assertInstanceOf('Pop\Application', $application->load());
    }

    public function testNoRouteFound()
    {
        $_SERVER['argv'] = [
            'myscript.php', 'unknown'
        ];

        $config = [
            'routes' => [
                'bad' => [
                    'controller' => 'Pop\Test\TestAsset\TestController',
                    'action'     => 'help'
                ]
            ]
        ];
        $application = new Application($config);

        ob_start();
        $application->run(false);
        $result = ob_get_clean();

        $this->assertStringContainsString('Command Not Found', $result);
    }

    public function testRunException()
    {
        $config = [
            'routes' => [
                'bad' => [
                    'controller' => function () {
                        throw new \Pop\Exception('Whoops!');
                    }
                ]
            ]
        ];
        $application = new Application($config);
        $application->on('app.error', function(\Pop\Exception $exception, Application $application) {
            file_put_contents(__DIR__ . '/tmp/error.log', $exception->getMessage());
        });

        $this->assertInstanceOf('Pop\Application', $application);

        try {
            $application->run(false, 'bad');
        } catch (\Pop\Exception $e) {

        }
        $this->assertTrue(str_contains(file_get_contents(__DIR__ . '/tmp/error.log'), 'Whoops!'));
        unlink(__DIR__ . '/tmp/error.log');
    }

    public function testApplicationVerbProxiesChain()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/b';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $app = new Application(new Router(null, new \Pop\Router\Match\Http()));
        $app->get('/a', function() { echo 'A'; })
            ->post('/b', function() { echo 'B'; });

        ob_start();
        $app->run(false);
        ob_get_clean();

        $this->assertTrue($app->router()->hasRoute());
    }

    public function testApplicationVerbProxyThrowsWhenNotHttp()
    {
        $this->expectException('Pop\Exception');

        $_SERVER['argv'] = ['myscript.php', 'help'];

        $app = new Application();
        $app->get('/a', function() {});
    }

    public function testApplicationRemainingVerbProxiesRegisterAndMatch()
    {
        foreach (['head', 'put', 'delete', 'trace', 'options', 'connect', 'patch'] as $verb) {
            $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
            $_SERVER['REQUEST_URI']    = '/resource';
            $_SERVER['REQUEST_METHOD'] = strtoupper($verb);

            $app = new Application(new Router(null, new \Pop\Router\Match\Http()));
            $app->$verb('/resource', function() {});

            ob_start();
            $app->run(false);
            ob_get_clean();

            $this->assertTrue($app->router()->hasRoute(), "Failed to match verb: $verb");
        }
    }

    public function testApplicationCustomMethodProxies()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/dav';
        $_SERVER['REQUEST_METHOD'] = 'PROPFIND';

        $app = new Application(new Router(null, new \Pop\Router\Match\Http()));
        $app->addCustomMethod('propfind');
        $app->addCustomMethods(['proppatch']);

        $this->assertTrue($app->hasCustomMethod('propfind'));
        $this->assertTrue($app->hasCustomMethod('proppatch'));

        $app->propfind('/dav', function() { echo 'Dav'; });

        ob_start();
        $app->run(false);
        ob_get_clean();

        $this->assertTrue($app->router()->hasRoute());
    }

    public function testApplicationMethodNotAllowedResponse()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/users';
        $_SERVER['REQUEST_METHOD'] = 'DELETE';

        $app = new Application(new Router(null, new \Pop\Router\Match\Http()));
        $app->get('/users', function() {});

        ob_start();
        $app->run(false);
        $result = ob_get_clean();

        $this->assertStringContainsString('Method Not Allowed', $result);
    }

    public function testApplicationNoRouteFoundStaysNotFound()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/does-not-exist';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $app = new Application(new Router(null, new \Pop\Router\Match\Http()));
        $app->get('/users', function() {});

        ob_start();
        $app->run(false);
        $result = ob_get_clean();

        $this->assertStringContainsString('Page Not Found', $result);
    }

    public function testPsr14DispatcherFiresAlongsideLegacyEventsOnRun()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $calls = [];
        $app   = new Application(new Router(null, new \Pop\Router\Match\Http()));
        $app->get('/', function() { echo 'Index'; });

        $app->dispatcher()->listeners()->listen(
            \Pop\Event\Psr14\RoutePreEvent::class,
            function($event) use (&$calls, $app) {
                $calls[] = ($event->application() === $app);
            }
        );

        ob_start();
        $app->run(false);
        ob_get_clean();

        $this->assertEquals([true], $calls);
    }

    public function testPsr14ErrorEventCarriesTheThrownException()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $app = new Application(new Router(null, new \Pop\Router\Match\Http()));
        $app->get('/', function() {
            throw new \Pop\Exception('boom');
        });

        $caught = null;
        $app->dispatcher()->listeners()->listen(
            \Pop\Event\Psr14\ErrorEvent::class,
            function($event) use (&$caught) { $caught = $event->exception(); }
        );

        ob_start();
        $app->run(false);
        ob_get_clean();

        $this->assertInstanceOf('Pop\Exception', $caught);
        $this->assertEquals('boom', $caught->getMessage());
    }

    public function testMaintenanceModeRunsControllersOwnMaintenanceAction()
    {
        $_ENV['MAINTENANCE_MODE'] = 'true';

        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $app = new Application(new Router(null, new \Pop\Router\Match\Http()));
        $app->router()->addRoute('/', [
            'controller' => 'Pop\Test\TestAsset\TestController',
            'action'     => 'help',
        ]);

        ob_start();
        $app->run(false);
        $result = ob_get_clean();

        // TestController::help() echoes 'help'; TestController::maintenance()
        // echoes nothing - proving the real action did NOT run.
        $this->assertStringNotContainsString('help', $result);
    }

    public function testMaintenanceModeBypassStillRunsTheRealAction()
    {
        $_ENV['MAINTENANCE_MODE'] = 'true';

        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $app = new Application(new Router(null, new \Pop\Router\Match\Http()));
        $app->router()->addRoute('/', [
            'controller' => 'Pop\Test\TestAsset\TestBypassMaintenanceController',
            'action'     => 'help',
        ]);

        ob_start();
        $app->run(false);
        $result = ob_get_clean();

        $this->assertStringContainsString('help', $result);
    }

    public function testMaintenanceModeRendersDefaultResponseForClosureRoutes()
    {
        $_ENV['MAINTENANCE_MODE'] = 'true';

        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $closureCalled = false;
        $app = new Application(new Router(null, new \Pop\Router\Match\Http()));
        $app->get('/', function() use (&$closureCalled) {
            $closureCalled = true;
            echo 'Index';
        });

        ob_start();
        $app->run(false);
        $result = ob_get_clean();

        $this->assertFalse($closureCalled);
        $this->assertStringContainsString('Service Unavailable', $result);
    }

    public function testMaintenanceModeExceptionSurfacesViaAppError()
    {
        $_ENV['MAINTENANCE_MODE'] = 'true';

        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $app = new Application(new Router(null, new \Pop\Router\Match\Http()));
        $app->router()->addRoute('/', [
            'controller' => 'Pop\Test\TestAsset\TestController2',
            'action'     => 'help',
        ]);

        $caught = null;
        $app->on('app.error', function($exception) use (&$caught) {
            $caught = $exception;
        });

        ob_start();
        $app->run(false);
        ob_get_clean();

        $this->assertInstanceOf('Pop\Dispatch\Exception', $caught);
    }

    public function testCallableObjectRouteWithMiddleware()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'routes' => [
                '/' => [
                    'controller' => 'Pop\Test\TestAsset\TestService::baz',
                    'middleware' => 'Pop\Test\TestAsset\TestMiddleware',
                ],
            ],
        ];
        $app = new Application(new Router(null, new \Pop\Router\Match\Http()), $config);

        ob_start();
        $app->run(false);
        $result = ob_get_clean();

        $this->assertStringContainsString('Entering Test Middleware', $result);
    }

    public function testCallableObjectRouteWithoutMiddleware()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $app = new Application(new Router(null, new \Pop\Router\Match\Http()));
        $app->router()->addRoute('/', [
            'controller' => 'Pop\Test\TestAsset\TestService::baz',
        ]);

        ob_start();
        $app->run(false);
        ob_get_clean();

        $this->assertTrue($app->router()->hasRoute());
    }

    public function testHttpControllerTraitRequestRetrievalWithMiddleware()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'routes' => [
                '/' => [
                    'controller' => 'Pop\Test\TestAsset\TestHttpController',
                    'action'     => 'help',
                    'middleware' => 'Pop\Test\TestAsset\TestMiddleware',
                ],
            ],
        ];
        $app = new Application(new Router(null, new \Pop\Router\Match\Http()), $config);

        ob_start();
        $app->run(false);
        $result = ob_get_clean();

        $this->assertStringContainsString('help', $result);
    }

    public function testConsoleControllerTraitRequestRetrievalWithMiddleware()
    {
        $_SERVER['argv'] = ['myscript.php', 'help'];

        $config = [
            'routes' => [
                'help' => [
                    'controller' => 'Pop\Test\TestAsset\TestConsoleController',
                    'action'     => 'help',
                    'middleware' => 'Pop\Test\TestAsset\TestMiddleware',
                ],
            ],
        ];
        $app = new Application($config);

        ob_start();
        $app->run();
        $result = ob_get_clean();

        $this->assertStringContainsString('help', $result);
    }

    public function testMaintenanceModeRendersDefaultResponseForClosureRoutesInCliMode()
    {
        $_ENV['MAINTENANCE_MODE'] = 'true';
        $_SERVER['argv']          = ['myscript.php', 'help'];

        $closureCalled = false;
        $app = new Application();
        $app->router()->addRoute('help', function() use (&$closureCalled) {
            $closureCalled = true;
        });

        ob_start();
        $app->run(false);
        $result = ob_get_clean();

        $this->assertFalse($closureCalled);
        $this->assertStringContainsString('Service Unavailable', $result);
    }

    public function testPopcornStyleMethodGroupConfig()
    {
        $_SERVER['DOCUMENT_ROOT']  = realpath(getcwd());
        $_SERVER['REQUEST_URI']    = '/users';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = [
            'routes' => [
                'options,get' => [
                    '/users' => ['controller' => function() { echo 'Users List'; }],
                    '/roles' => ['controller' => function() { echo 'Roles List'; }],
                ],
                'options,post' => [
                    '/users/create' => ['controller' => function() { echo 'Create User'; }],
                ],
            ],
        ];
        $app = new Application(new Router(null, new \Pop\Router\Match\Http()), $config);

        ob_start();
        $app->run(false);
        $result = ob_get_clean();

        $this->assertTrue($app->router()->hasRoute());
        $this->assertStringContainsString('Users List', $result);
    }

}
