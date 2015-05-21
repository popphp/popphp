<?php

namespace PopTest;

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
            ]
        ];
        $application = new Application($config);
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

    /**
     * @expectedException \Pop\Exception
     */
    public function testLoadConfigException()
    {
        $application = new Application();
        $application->loadConfig('bad');
    }

    /**
     * @expectedException \Pop\Exception
     */
    public function testRegisterAutoloaderException()
    {
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

    /**
     * @expectedException \Pop\Service\Exception
     */
    public function testRemoveService()
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

    public function testRun()
    {
        $config = [
            'foo'      => 'bar',
            'routes'   => [
                'help' => [
                    'controller' => 'Foo\Controller\IndexController',
                    'action'     => 'help'
                ]
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

}