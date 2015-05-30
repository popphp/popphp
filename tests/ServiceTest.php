<?php

namespace Pop\Test;

use Pop\Service\Locator;

class ServiceTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $services = new Locator([
            'foo' => [
                'call' => 'Pop\Test\TestAsset\TestService'
            ]
        ]);
        $this->assertInstanceOf('Pop\Service\Locator', $services);
    }

    public function testRecursionLoop()
    {
        $this->setExpectedException('Pop\Service\Exception');
        $services = new Locator();
        $services->set('service1', function($locator) {
            return $locator->get('service2');
        });
        $services->set('service2', function($locator) {
            return $locator->get('service1');
        });

        $result = $services->get('service1');
    }

    public function testNotCallableException()
    {
        $this->setExpectedException('Pop\Service\Exception');
        $services = new Locator();
        $services->set('badservice', ['bad call']);
        $result = $services->get('badservice');
    }

    public function testSetServicesException()
    {
        $this->setExpectedException('Pop\Service\Exception');
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
                'call' => 'Pop\Test\TestAsset\TestService::foo',
                'params'  => 456
            ],
            'baz' => [
                'call' => 'Pop\Test\TestAsset\TestService::foo',
                'params'  => function(){
                    return 789;
                }
            ],
            'test1' => [
                'call' => 'Pop\Test\TestAsset\TestService->bar'
            ],
            'test2' => [
                'call'    => 'Pop\Test\TestAsset\TestService->bar',
                'params'  => 123
            ],
            'test3' => [
                'call'    => 'Pop\Test\TestAsset\TestService'
            ],
            'test4' => [
                'call'    => 'Pop\Test\TestAsset\TestService::baz'
            ],
            'test5' => [
                'call' => 'Pop\Test\TestAsset\TestService',
                'params'  => 123
            ],
            'test6' => [
                'call' => new TestAsset\TestService,
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

}