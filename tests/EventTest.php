<?php

namespace Pop\Test;

use Pop\Event\Manager;

class EventTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $events = new Manager('foo', function(){
            return 'bar';
        }, 1000);
        $this->assertInstanceOf('Pop\Event\Manager', $events);
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
        $events->on('foo', 'Pop\Test\TestAsset\TestEvent->bar', 1000);
        $events->on('bar', 'new Pop\Test\TestAsset\TestEvent', 1000);
        $events->trigger('foo');
        $this->assertContains(123, $events->getResults('foo'));
        $this->assertContains(456, $events->getResults('foo'));
    }

    /**
     * @expectedException \Pop\Event\Exception
     */
    public function testCallableException()
    {
        $events = new Manager();
        $events->on('foo', TestAsset\TestEvent::baz(), 1000);
        $events->trigger('foo');
    }

}