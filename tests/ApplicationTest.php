<?php

namespace Pop\Test;

use Pop\Application;
use Pop\Router\Router;
use Pop\Service\Locator;
use Pop\Event;
use Pop\Module;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $application = new Application(
            new Router(),
            new Locator(),
            new Event\Manager(),
            new Module\Manager(),
            include __DIR__ . '/../vendor/autoload.php',
            ['foo' => 'bar']
        );

        $this->assertInstanceOf('Pop\Application', $application);
        $this->assertInstanceOf('Pop\Router\Router', $application->router());
        $this->assertInstanceOf('Pop\Service\Locator', $application->services());
        $this->assertInstanceOf('Pop\Event\Manager', $application->events());
        $this->assertInstanceOf('Pop\Module\Manager', $application->modules());
        $this->assertInstanceOf('Composer\Autoload\ClassLoader', $application->autoloader());
        $this->assertEquals($application->config()['foo'], 'bar');

        $this->assertInstanceOf('Pop\Router\Router', $application['router']);
        $this->assertInstanceOf('Pop\Service\Locator', $application['services']);
        $this->assertInstanceOf('Pop\Event\Manager', $application['events']);
        $this->assertInstanceOf('Pop\Module\Manager', $application['modules']);
        $this->assertInstanceOf('Composer\Autoload\ClassLoader', $application['autoloader']);
        $this->assertEquals($application['config']['foo'], 'bar');

        $this->assertInstanceOf('Pop\Router\Router', $application->router);
        $this->assertInstanceOf('Pop\Service\Locator', $application->services);
        $this->assertInstanceOf('Pop\Event\Manager', $application->events);
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
        $application->modules    = new Module\Manager();
        $application->autoloader = include __DIR__ . '/../vendor/autoload.php';
        $application->config     = ['foo' => 'bar'];

        $this->assertTrue(isset($application->router));
        $this->assertTrue(isset($application->services));
        $this->assertTrue(isset($application->events));
        $this->assertTrue(isset($application->modules));
        $this->assertTrue(isset($application->autoloader));
        $this->assertTrue(isset($application->config));

        unset($application->router);
        unset($application->services);
        unset($application->events);
        unset($application->modules);
        unset($application->autoloader);
        unset($application->config);

        $this->assertFalse(isset($application->router));
        $this->assertFalse(isset($application->services));
        $this->assertFalse(isset($application->events));
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
        $application['modules']    = new Module\Manager();
        $application['autoloader'] = include __DIR__ . '/../vendor/autoload.php';
        $application['config']     = ['foo' => 'bar'];

        $this->assertTrue(isset($application['router']));
        $this->assertTrue(isset($application['services']));
        $this->assertTrue(isset($application['events']));
        $this->assertTrue(isset($application['modules']));
        $this->assertTrue(isset($application['autoloader']));
        $this->assertTrue(isset($application['config']));

        unset($application['router']);
        unset($application['services']);
        unset($application['events']);
        unset($application['modules']);
        unset($application['autoloader']);
        unset($application['config']);

        $this->assertFalse(isset($application['router']));
        $this->assertFalse(isset($application['services']));
        $this->assertFalse(isset($application['events']));
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
        $this->assertInstanceOf('Pop\Module\Manager', $application->modules());
        $this->assertInstanceOf('Composer\Autoload\ClassLoader', $application->autoloader());
    }

    public function testMergeConfig()
    {
        $application = new Application();
        $application->mergeConfig(['foo' => 'bar']);
        $application->mergeConfig(['baz' => 123]);
        $this->assertEquals($application->config()['baz'], 123);
        $application->mergeConfig(['foo' => 456], true);
        $this->assertEquals($application->config()['foo'], 456);
    }

    public function testLoadConfig()
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
                    'call' => 'Pop\Web\Session::getInstance'
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
            'prefix' => 'TestAsset\\',
            'src'    => __DIR__ . '/TestAsset'
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

    public function testLoadConfig2()
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
                    'call' => 'Pop\Web\Session::getInstance'
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
    }

    public function testLoadConfigException()
    {
        $this->setExpectedException('InvalidArgumentException');
        $application = new Application();
        $application->loadConfig('bad');
    }

    public function testRegisterAutoloaderException()
    {
        $this->setExpectedException('Pop\Exception');
        $application = new Application();
        $application->registerAutoloader(new \StdClass());
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
        $this->setExpectedException('Pop\Service\Exception');
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

    public function testRegisterModule()
    {
        $application = new Application();
        $application->register('foo', ['bar' => 'baz']);
        $this->assertNotNull($application->module('foo'));
        $this->assertTrue($application->isRegistered('foo'));
    }

    public function testRegisterCustomModule()
    {
        $application = new Application();
        $application->register('test', new TestAsset\TestModule());
        $this->assertNotNull($application->module('test'));
        $this->assertTrue($application->isRegistered('test'));
    }

    public function testUnregisterModule()
    {
        $application = new Application();
        $application->register('foo', ['bar' => 'baz']);
        $this->assertNotNull($application->module('foo'));
        $this->assertTrue($application->isRegistered('foo'));
        $application->unregister('foo');
        $this->assertNull($application->module('foo'));
        $this->assertFalse($application->isRegistered('foo'));
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
                    'name'   => 'app.route.post',
                    'action' => function() {
                        return 'app.route.post';
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
        $this->assertContains('app.route.post', $application->events()->getResults('app.route.post'));
        $this->assertContains('app.dispatch.pre', $application->events()->getResults('app.dispatch.pre'));
        $this->assertContains('app.dispatch.post', $application->events()->getResults('app.dispatch.post'));
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
                    'action'     => 'help'
                ]
            ]
        ];
        $application = new Application($config);
        ob_start();
        $application->run();
        $result = ob_get_clean();
        $this->assertEquals('help', $result);
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

}