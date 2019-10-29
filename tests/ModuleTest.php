<?php

namespace Pop\Test;

use Pop\Application;
use Pop\Module\Module;
use Pop\Module\Manager;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{

    public function testConstructor()
    {
        $config = [
            'prefix'   => 'Test\\',
            'name'     => 'foo',
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
            ]
        ];

        $application = new Application(include __DIR__ . '/../vendor/autoload.php');
        $module = new Module($config, 'foo');
        $application->register($module);
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
        $module = new Module($config, $application, 'foo');
        $this->assertEquals('bar', $module->config()['foo']);
    }

    public function testAddConfigValue()
    {
        $module = new Module(['foo' => 'bar']);
        $module->addConfigValue('baz', 123);
        $this->assertEquals($module['config']['baz'], 123);
    }

    public function testUpdateConfigValue()
    {
        $module = new Module(['foo' => 'bar']);
        $module->updateConfigValue('foo', 123);
        $this->assertEquals($module['config']['foo'], 123);
    }

    public function testDeleteConfigValue()
    {
        $module = new Module(['foo' => 'bar']);
        $module->addConfigValue('baz', 123);
        $module->deleteConfigValue('baz');
        $this->assertFalse(isset($module['config']['baz']));
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
        $manager = new Manager([new Module(['foo' => 'bar'], 'foo')]);
        $manager['bar'] = new Module(['baz' => 123]);

        $this->assertInstanceOf('Pop\Module\Manager', $manager);

        foreach ($manager as $name => $module) {
            $this->assertTrue($manager->isRegistered($name));
        }

        unset($manager['bar']);
        $this->assertFalse($manager->isRegistered('bar'));
    }

    public function testVersion()
    {
        $module = new Module('1.0.0');
        $this->assertEquals('1.0.0', $module->getVersion());
        $this->assertTrue($module->hasVersion());
    }

}