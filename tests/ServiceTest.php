<?php

namespace Pop\Test;

use Pop\Service\Container;
use Pop\Service\Exception;
use Pop\Service\Locator;
use Pop\Service\NotFoundException;
use PHPUnit\Framework\TestCase;
use Pop\Utils\CallableObject;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ServiceTest extends TestCase
{

    public function testImplementsPsrContainerInterface()
    {
        $services = new Locator();
        $this->assertInstanceOf(ContainerInterface::class, $services);
    }

    public function testHasMirrorsIsAvailable()
    {
        $services = new Locator([
            'foo' => 'Pop\Test\TestAsset\TestService'
        ]);
        $this->assertTrue($services->has('foo'));
        $this->assertFalse($services->has('bar'));
    }

    public function testGetMissingServiceThrowsPsrNotFoundException()
    {
        $services = new Locator();

        try {
            $services->get('missing');
            $this->fail('Expected NotFoundException was not thrown');
        } catch (NotFoundException $exception) {
            $this->assertInstanceOf(NotFoundExceptionInterface::class, $exception);
            // NotFoundExceptionInterface extends ContainerExceptionInterface, per PSR-11
            $this->assertInstanceOf(ContainerExceptionInterface::class, $exception);
            // Still catchable as the pre-existing, non-PSR exception type - not a BC break
            $this->assertInstanceOf(Exception::class, $exception);
        }
    }

    public function testServiceExceptionImplementsPsrContainerExceptionInterface()
    {
        $this->assertInstanceOf(ContainerExceptionInterface::class, new Exception('test'));
    }

    public function testConstructor()
    {
        $services = new Locator([
            'foo' => 'Pop\Test\TestAsset\TestService'
        ]);
        $this->assertInstanceOf('Pop\Service\Locator', $services);
        $this->assertEquals(1, count($services));
        $i = 0;

        foreach ($services as $service) {
            $i++;
        }
        $this->assertEquals(1, $i);
    }

    public function testContainer()
    {
        $services = new Locator([
            'foo' => [
                'call'   => function(){
                    return 123;
                }
            ]
        ]);

        Container::set('foo', new Locator());

        $this->assertTrue(Container::has('default'));
        $this->assertTrue(Container::has('foo'));
        $this->assertFalse(Container::has('bad'));
        $this->assertInstanceOf('Pop\Service\Locator', Container::get('default'));
        $this->assertInstanceOf('Pop\Service\Locator', Container::get('foo'));

        Container::remove('foo');

        $this->assertFalse(Container::has('foo'));
    }

    public function testContainerException()
    {
        $this->expectException('Pop\Service\Exception');
        Container::get('bar');
    }

    public function testSetWithCallableObject()
    {
        $services = new Locator();
        $services->set('foo', new CallableObject('trim'));
        $this->assertTrue($services->isAvailable('foo'));
    }

    public function testNotCallableException()
    {
        $this->expectException('Pop\Utils\Exception');
        $services = new Locator();
        $services->set('badservice', ['call' => 'bad call']);
        $result = $services->get('badservice');
    }

    public function testSetServicesException()
    {
        $this->expectException('Pop\Service\Exception');
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
        $services->setCallable('foo', 'Bar');
        $this->assertEquals('Bar', $services->getCallable('foo'));
    }

    public function testSetParams1()
    {
        $services = new Locator([
            'foo' => [
                'call'   => 'Foo'
            ]
        ]);
        $services->setParameters('foo', 456);
        $this->assertEquals(456, $services->getParameters('foo')[0]);
    }

    public function testSetParams2()
    {
        $services = new Locator([
            'foo' => [
                'call'   => 'Foo'
            ]
        ]);
        $services->setParameters('foo', [123, 456]);
        $this->assertEquals(123, $services->getParameters('foo')[0]);
        $this->assertEquals(456, $services->getParameters('foo')[1]);
    }

    public function testSetAndGetParam()
    {
        $services = new Locator([
            'foo' => [
                'call'   => 'Foo',
                'params' => 123
            ]
        ]);
        $services->addParameter('foo', 456);
        $this->assertEquals(456, $services->getParameters('foo')[1]);
    }

    public function testAddParam1()
    {
        $services = new Locator([
            'foo' => [
                'call'   => 'Foo',
                'params' => 123
            ]
        ]);
        $services->addParameter('foo', 456);
        $this->assertTrue(is_array($services->getParameters('foo')));
    }

    public function testAddParam2()
    {
        $services = new Locator([
            'foo' => [
                'call'   => 'Foo',
                'params' => [123]
            ]
        ]);
        $services->addParameter('foo', 456, 'test');
        $this->assertTrue(is_array($services->getParameters('foo')));
        $this->assertEquals(456, $services->getParameters('foo')['test']);
    }

    public function testAddParam3()
    {
        $services = new Locator([
            'foo' => [
                'call' => 'Foo'
            ]
        ]);
        $services->addParameter('foo', 456);
        $this->assertEquals(456, $services->getParameters('foo')[0]);
    }

    public function testAddParam4()
    {
        $services = new Locator([
            'foo' => [
                'call' => 'Foo'
            ]
        ]);
        $services->addParameter('foo', 456, 'test');
        $this->assertTrue(is_array($services->getParameters('foo')));
        $this->assertEquals(456, $services->getParameters('foo')['test']);
    }

    public function testRemoveParam1()
    {
        $services = new Locator([
            'foo' => [
                'call'   => 'Foo',
                'params' => 123
            ]
        ]);
        $services->removeParameters('foo', 123);
        $this->assertFalse($services->hasParameter('foo'));
    }

    public function testRemoveParam2()
    {
        $services = new Locator([
            'foo' => [
                'call'   => 'Foo',
                'params' => [123]
            ]
        ]);
        $services->removeParameters('foo', 123, 0);
        $this->assertFalse($services->hasParameter('foo'));
    }

    public function testRemoveParam3()
    {
        $services = new Locator([
            'foo' => [
                'call'   => 'Foo',
                'params' => [123]
            ]
        ]);
        $services->removeParameters('foo', 123);
        $this->assertFalse($services->hasParameter('foo'));
    }

    public function testRemoveParam4()
    {
        $services = new Locator([
            'foo' => [
                'call'   => 'Foo',
                'params' => [
                    'test' => 123
                ]
            ]
        ]);
        $services->removeParameters('foo', 123, 'test');
        $this->assertFalse($services->hasParameter('foo'));
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

    public function testReload()
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
        $services->reload('foo');
        $this->assertTrue($services->isLoaded('foo'));
        $this->assertEquals(123, $result);
    }

    public function testMagicMethods()
    {
        $services = new Locator();
        $services->foo = [
            'call'   => function(){
                return 123;
            }
        ];

        $this->assertTrue(isset($services->foo));
        $this->assertEquals(123, $services->foo);
        unset($services->foo);
        $this->assertFalse(isset($services->foo));
    }

    public function testOffsetMethods()
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

    public function testMultipleClosureParams()
    {
        $services = new Locator();
        $services->setServices([
            'service1' => [
                'call' => function($param1, $param2) {
                    return $param1 + $param2;
                },
                'params' => [1, 2]
            ],
            'service2' => [
                'call' => function($param1, $param2, $param3) {
                    return $param1 + $param2 + $param3;
                },
                'params' => [1, 2, 3]
            ],
            'service3' => [
                'call' => function($param1, $param2, $param3, $param4) {
                    return $param1 + $param2 + $param3 + $param4;
                },
                'params' => [1, 2, 3, 4]
            ],
            'service4' => [
                'call' => function($param1, $param2, $param3, $param4, $param5) {
                    return $param1 + $param2 + $param3 + $param4 + $param5;
                },
                'params' => [1, 2, 3, 4, 5]
            ]
        ]);

        $this->assertEquals(3, $services->get('service1'));
        $this->assertEquals(6, $services->get('service2'));
        $this->assertEquals(10, $services->get('service3'));
        $this->assertEquals(15, $services->get('service4'));
    }

    public function testRecursionLoop()
    {
        $this->expectException('Pop\Service\Exception');
        $services = new Locator();
        $services->set('service1', [
            'call' => function($locator) {
                return $locator->get('service2');
            },
            'params' => [$services]
        ]);
        $services->set('service2', [
            'call' => function($locator) {
                return $locator->get('service1');
            },
            'params' => [$services]
        ]);

        $result = $services->get('service1');
    }

    protected function getRecursionState(): array
    {
        $reflection   = new \ReflectionClass(Locator::class);
        $depthProp    = $reflection->getProperty('depth');
        $calledProp   = $reflection->getProperty('called');
        $depthProp->setAccessible(true);
        $calledProp->setAccessible(true);

        return [$depthProp->getValue(), $calledProp->getValue()];
    }

    public function testCalledStackDoesNotLeakAfterSuccess()
    {
        $services = new Locator();
        $services->set('service1', function() {
            return 1;
        });

        $services->get('service1');

        [$depth, $called] = $this->getRecursionState();
        $this->assertEquals(0, $depth);
        $this->assertEquals([], $called);
    }

    public function testDepthAndCalledResetAfterFactoryThrows()
    {
        $services = new Locator();
        $services->set('willThrow', function() {
            throw new \RuntimeException('Boom');
        });

        try {
            $services->get('willThrow');
            $this->fail('Expected exception was not thrown');
        } catch (\RuntimeException $exception) {
            $this->assertEquals('Boom', $exception->getMessage());
        }

        [$depth, $called] = $this->getRecursionState();
        $this->assertEquals(0, $depth, 'depth must be restored even when the factory throws');
        $this->assertEquals([], $called, 'called stack must be restored even when the factory throws');

        // A prior failure must not corrupt the recursion guard for later, unrelated calls
        $services->set('service1', function() {
            return 1;
        });
        $this->assertEquals(1, $services->get('service1'));
    }

}
