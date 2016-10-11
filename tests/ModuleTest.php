<?php

namespace Pop\Test;

use Pop\Application;
use Pop\Module\Module;
use Pop\Module\Manager;

class ModuleTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $config = [
            'prefix'   => 'Test\\',
            'src'      => __DIR__,
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

        $application = new Application(include __DIR__ . '/../vendor/autoload.php');
        $module = new Module($config);
        $application->register('foo', $module);
        $this->assertTrue($module->isRegistered());
        $this->assertTrue($application->isRegistered('foo'));
        $this->assertEquals('bar', $module->config()['foo']);
        $this->assertInstanceOf('Pop\Module\Module', $module);
        $this->assertInstanceOf('Pop\Application', $module->application());
    }

    public function testPsr0()
    {
        $config = [
            'psr-0'  => true,
            'prefix' => 'Test',
            'src'    => __DIR__,
            'foo'    => 'bar'
        ];

        $application = new Application(include __DIR__ . '/../vendor/autoload.php');
        $module = new Module($config, $application);
        $this->assertEquals('bar', $module->config()['foo']);
    }

    public function testMergeConfig()
    {
        $module = new Module();
        $module->mergeConfig(['foo' => 'bar']);
        $module->mergeConfig(['baz' => 123]);
        $this->assertEquals($module->config()['baz'], 123);
        $module->mergeConfig(['foo' => 456]);
        $this->assertEquals($module->config()['foo'], 456);
    }

    public function testLoadConfigException()
    {
        $this->expectException('InvalidArgumentException');
        $module = new Module();
        $module->registerConfig('bad');
    }

    public function testOffsets()
    {
        $module = new Module(['foo' => 'bar']);
        $this->assertEquals('bar', $module['config']['foo']);
        $this->assertTrue(isset($module['config']));
    }

    public function testManagerConstructor()
    {
        $manager = new Manager(['foo' => new Module(['foo' => 'bar'])]);
        $manager['bar'] = new Module(['baz' => 123]);

        $this->assertInstanceOf('Pop\Module\Manager', $manager);

        foreach ($manager as $name => $module) {
            $this->assertTrue($manager->isRegistered($name));
        }

        unset($manager['bar']);
        $this->assertFalse($manager->isRegistered('bar'));
    }

}