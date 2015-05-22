<?php

namespace PopTest;

use Pop\Service\Locator;

class TestService {
    public $foo = null;
    public function __construct($var = null)
    {
        $this->foo = $var;
    }
    public function bar($var = null)
    {
        return (null !== $var) ? $var : 456;
    }
    public static function baz()
    {
        return 789;
    }
    public static function foo($var)
    {
        return $var;
    }
}

class ServiceTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $services = new Locator([
            'foo' => [
                'call' => 'TestService'
            ]
        ]);
        $this->assertInstanceOf('Pop\Service\Locator', $services);
    }

    /**
     * @expectedException \Pop\Service\Exception
     */
    public function testSetServicesException()
    {
        $services = new Locator();
        $services->setServices(['foo' => ['bar' => 123]]);
    }

    public function testSetAndGetCall()
    {
        $services = new Locator([
            'foo' => [
                'call' => 'Foo'
            ]
        ]);
        $services->setCall('foo', 'Bar');
        $this->assertEquals('Bar', $services->getCall('foo'));
    }

    public function testSetAndGetParam()
    {
        $services = new Locator([
            'foo' => [
                'call'   => 'Foo',
                'params' => 123
            ]
        ]);
        $services->setParams('foo', 456);
        $this->assertEquals(456, $services->getParams('foo'));
    }

    public function testIsAvailable()
    {
        $services = new Locator([
            'foo' => [
                'call'   => 'Foo',
                'params' => 123
            ]
        ]);
        $this->assertTrue($services->isAvailable('foo'));
    }

    public function testIsLoaded()
    {
        $services = new Locator([
            'foo' => [
                'call'   => function(){
                    return 123;
                }
            ]
        ]);
        $result = $services['foo'];
        $this->assertTrue($services->isLoaded('foo'));
        $this->assertEquals(123, $result);
        unset($services['foo']);
        $this->assertFalse($services->isAvailable('foo'));
        $this->assertFalse($services->isLoaded('foo'));
    }

    public function testOffsets()
    {
        $services = new Locator();
        $services['foo'] = [
            'call'   => function(){
                return 123;
            }
        ];
        $this->assertTrue(isset($services['foo']));
    }

    public function testLoad()
    {
        $services = new Locator([
            'foo' => [
                'call'   => function($var){
                    return $var;
                },
                'params'  => 123
            ],
            'bar' => [
                'call' => 'PopTest\TestService::foo',
                'params'  => 456
            ],
            'baz' => [
                'call' => 'PopTest\TestService::foo',
                'params'  => function(){
                    return 789;
                }
            ],
            'test1' => [
                'call' => 'PopTest\TestService->bar'
            ],
            'test2' => [
                'call'    => 'PopTest\TestService->bar',
                'params'  => 123
            ],
            'test3' => [
                'call'    => 'PopTest\TestService'
            ],
            'test4' => [
                'call'    => 'PopTest\TestService::baz'
            ],
            'test5' => [
                'call' => 'PopTest\TestService',
                'params'  => 123
            ],
            'test6' => [
                'call' => new TestService,
            ]
        ]);
        $test3 = $services['test3'];
        $test5 = $services['test5'];
        $test6 = $services['test6'];
        $this->assertEquals(123, $services['foo']);
        $this->assertEquals(456, $services['bar']);
        $this->assertEquals(789, $services['baz']);
        $this->assertEquals(456, $services['test1']);
        $this->assertEquals(123, $services['test2']);
        $this->assertEquals(456, $test3->bar());
        $this->assertEquals(789, $services['test4']);
        $this->assertEquals(123, $test5->foo);
        $this->assertNull($test6->foo);
    }

    /**
     * @expectedException \Pop\Service\Exception
     */
    public function testRecursionLoop()
    {
        $services = new Locator();
        $services->setServices([
            'foo' => function($service){
                $result = $service;
            },
            'params' => $services['foo']
        ]);
        $result = $services['foo'];
    }

}