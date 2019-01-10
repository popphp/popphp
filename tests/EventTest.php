<?php

namespace Pop\Test;

use Pop\Event\Manager;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{

    public function testConstructor()
    {
        $events = new Manager('foo', function(){
            return 'bar';
        }, 1000);
        $this->assertInstanceOf('Pop\Event\Manager', $events);
    }

    public function testMagicMethods()
    {
        $events = new Manager();
        $events->foo = function(){
            return 'bar';
        };
        $this->assertTrue(isset($events->foo));
        $this->assertInstanceOf('SplPriorityQueue', $events->foo);
        unset($events->foo);
        $this->assertFalse(isset($events->foo));
    }

    public function testOffsetMethods()
    {
        $events = new Manager();
        $events['foo'] = function(){
            return 'bar';
        };
        $this->assertTrue(isset($events['foo']));
        $this->assertInstanceOf('SplPriorityQueue', $events['foo']);
        unset($events['foo']);
        $this->assertFalse(isset($events['foo']));
    }

    public function testOff()
    {
        $events = new Manager();
        $events->on('foo', function(){
            return 'bar';
        }, 1000);
        $events->on('baz', function(){
            return 123;
        }, 1001);
        $events->on('hello', 'Foo::bar', 1002);

        $this->assertTrue($events->has('hello'));
        $this->assertNotNull($events->get('hello'));
        $events->off('hello', 'Foo::baz');
        $this->assertNotNull($events->get('hello'));
    }

    public function testAlive()
    {
        $events = new Manager('foo', function(){
            return Manager::KILL;
        });
        $events->trigger('foo');
        $this->assertFalse($events->alive());
    }

    public function testCallable()
    {
        $events = new Manager();
        $events->on('foo', 'Pop\Test\TestAsset\TestEvent::foo', 1000);
        $events->on('foo', 'Pop\Test\TestAsset\TestEvent::foo', 1000);
        $events->on('foo', 'Pop\Test\TestAsset\TestEvent->bar', 1000);
        $events->on('bar', 'new Pop\Test\TestAsset\TestEvent', 1000);
        $events->on('test', 'Pop\Test\TestAsset\TestEvent::test', 1000);
        $events->on('test', [new TestAsset\TestEvent(), 'bar'], 1000);
        $events->trigger('foo');
        $events->trigger('bar');
        $events->trigger('test', ['param' => 789]);
        $this->assertContains(123, $events->getResults('foo'));
        $this->assertContains(456, $events->getResults('foo'));
        $this->assertContains(456, $events->getResults('test'));
        $this->assertContains(789, $events->getResults('test'));
    }

    public function testCallableException()
    {
        $this->expectException('Pop\Event\Exception');
        $events = new Manager();
        $events->on('foo', TestAsset\TestEvent::baz(), 1000);
        $events->trigger('foo');
    }

    public function testStop()
    {
        $events = new Manager();
        $events->on('foo', function(){
            return 123;
        }, 3);
        $events->on('foo', function(){
            return Manager::STOP;
        }, 2);
        $events->on('foo', function(){
            return 456;
        }, 1);
        $events->trigger('foo');
        $results = $events->getResults('foo');
        $this->assertEquals(2, count($results));
        $this->assertFalse(in_array(456, $results));
    }

}