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
        $this->assertEquals(1, count($events));
        $i = 0;
        foreach ($events as $event) {
            $i++;
        }
        $this->assertEquals(1, $i);
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
        $this->assertInstanceOf(TestAsset\TestEvent::class, $events->getResults('bar')[0]);
        $this->assertContains(456, $events->getResults('test'));
        $this->assertContains(789, $events->getResults('test'));
    }

    public function testCallableException()
    {
        $this->expectException('Pop\Utils\Exception');
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
        $events->on('foo', function($result = null, $event = null){
            $event->stopPropagation();
            return 'stopped';
        }, 2);
        $events->on('foo', function(){
            return 456;
        }, 1);
        $events->trigger('foo');
        $results = $events->getResults('foo');
        $this->assertEquals(2, count($results));
        $this->assertFalse(in_array(456, $results));
    }

    public function testStopDoesNotPermanentlyDisableLaterTriggers()
    {
        // Returns 'stopped' only on the first call, so the two trigger() calls
        // below can distinguish "propagation stays stopped across separate
        // trigger() calls" (a bug - Manager builds a fresh Event per call) from
        // "the chain runs fully on the second call" (correct).
        $callCount = 0;
        $events    = new Manager();
        $events->on('foo', function($result = null, $event = null) use (&$callCount) {
            $callCount++;
            if ($callCount === 1) {
                $event->stopPropagation();
                return 'stopped';
            }
            return 'not-stop';
        }, 2);
        $events->on('foo', function() {
            return 456;
        }, 1);

        // First call: stopPropagation() halts the second listener.
        $events->trigger('foo');
        $this->assertEquals(['stopped'], $events->getResults('foo'));

        // Second call, same event name, same Manager instance: trigger() builds
        // a fresh Event object every call, so propagation is never
        // pre-stopped going in - the chain must run fully.
        $events->trigger('foo');
        $this->assertEquals(['not-stop', 456], $events->getResults('foo'));
    }

    public function testResultChainingDoesNotLeakAcrossSeparateTriggers()
    {
        $events = new Manager();
        $seenResults = [];

        $events->on('foo', function($result = null) use (&$seenResults) {
            $seenResults[] = $result;
            return 'first-call-result';
        }, 1);

        $events->trigger('foo');
        $this->assertEquals([null], $seenResults);

        // A second, separate trigger() call for the same name must present
        // the listener with a fresh chain (no prior result) rather than the
        // previous call's leftover last result.
        $events->trigger('foo');
        $this->assertEquals([null, null], $seenResults);
    }

    public function testApplicationEventsCarryTheApplicationInstance()
    {
        $app = new \Pop\Application();

        foreach ([
            \Pop\Event\InitEvent::class,
            \Pop\Event\RoutePreEvent::class,
            \Pop\Event\DispatchPreEvent::class,
            \Pop\Event\DispatchPostEvent::class,
        ] as $class) {
            $event = new $class($app);
            $this->assertSame($app, $event->application());
        }
    }

    public function testErrorEventCarriesTheExceptionAndTheApplication()
    {
        $app       = new \Pop\Application();
        $exception = new \RuntimeException('boom');
        $event     = new \Pop\Event\ErrorEvent($app, $exception);

        $this->assertSame($app, $event->application());
        $this->assertSame($exception, $event->exception());
        $this->assertEquals(
            ['exception' => $exception, 'application' => $app],
            $event->toParams()
        );
    }

    public function testEventsAreStoppable()
    {
        $event = new \Pop\Event\RoutePreEvent(new \Pop\Application());
        $this->assertInstanceOf(\Psr\EventDispatcher\StoppableEventInterface::class, $event);
        $this->assertFalse($event->isPropagationStopped());

        $event->stopPropagation();
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testGenericEventExposesNameParamsAndGet()
    {
        $event = new \Pop\Event\Event('foo.bar', ['baz' => 123]);

        $this->assertEquals('foo.bar', $event->getName());
        $this->assertEquals(['baz' => 123], $event->toParams());
        $this->assertEquals(123, $event->get('baz'));
        $this->assertNull($event->get('missing'));
    }

    public function testAbortExceptionIsAnEventException()
    {
        $exception = new \Pop\Event\AbortException('Aborting.');
        $this->assertInstanceOf(\Pop\Event\Exception::class, $exception);
        $this->assertEquals('Aborting.', $exception->getMessage());
    }

    public function testListenRegistersListenerCalledWithTheEventObject()
    {
        $events   = new Manager();
        $received = null;

        $events->listen(\Pop\Event\RoutePreEvent::class, function($event) use (&$received) {
            $received = $event;
        });

        $event = new \Pop\Event\RoutePreEvent(new \Pop\Application());
        $events->dispatch($event);

        $this->assertSame($event, $received);
    }

    public function testListenReturnsListenersForExactEventClassOnly()
    {
        $events = new Manager();
        $calls  = [];

        $events->listen(\Pop\Event\RoutePreEvent::class, function() use (&$calls) {
            $calls[] = 'route-pre';
        });

        $app = new \Pop\Application();
        $events->dispatch(new \Pop\Event\RoutePreEvent($app));
        $events->dispatch(new \Pop\Event\DispatchPreEvent($app));

        $this->assertEquals(['route-pre'], $calls);
    }

    public function testListenRespectsPriorityOrder()
    {
        $events = new Manager();
        $order  = [];

        $events->listen(\Pop\Event\RoutePreEvent::class, function() use (&$order) { $order[] = 'low'; }, 1);
        $events->listen(\Pop\Event\RoutePreEvent::class, function() use (&$order) { $order[] = 'high'; }, 10);

        $events->dispatch(new \Pop\Event\RoutePreEvent(new \Pop\Application()));

        $this->assertEquals(['high', 'low'], $order);
    }

    public function testDispatchReturnsTheSameEventInstance()
    {
        $events = new Manager();
        $event  = new \Pop\Event\RoutePreEvent(new \Pop\Application());

        $result = $events->dispatch($event);

        $this->assertSame($event, $result);
    }

    public function testClassIndexedStopPropagationHaltsRemainingClassIndexedListeners()
    {
        $events = new Manager();
        $calls  = [];

        $events->listen(\Pop\Event\RoutePreEvent::class, function($event) use (&$calls) {
            $calls[] = 'first';
            $event->stopPropagation();
        }, 10);
        $events->listen(\Pop\Event\RoutePreEvent::class, function() use (&$calls) {
            $calls[] = 'second';
        }, 1);

        $events->dispatch(new \Pop\Event\RoutePreEvent(new \Pop\Application()));

        $this->assertEquals(['first'], $calls);
    }

    public function testDispatchCallsBothClassIndexedAndNameIndexedListeners()
    {
        $events = new Manager();
        $calls  = [];

        $events->listen(\Pop\Event\RoutePreEvent::class, function() use (&$calls) {
            $calls[] = 'class-indexed';
        });
        $events->on('app.route.pre', function() use (&$calls) {
            $calls[] = 'name-indexed';
        });

        $events->dispatch(new \Pop\Event\RoutePreEvent(new \Pop\Application()));

        $this->assertEquals(['class-indexed', 'name-indexed'], $calls);
    }

    public function testStopPropagationDuringClassIndexedListenersSkipsNameIndexedListeners()
    {
        $events = new Manager();
        $calls  = [];

        $events->listen(\Pop\Event\RoutePreEvent::class, function($event) use (&$calls) {
            $calls[] = 'class-indexed';
            $event->stopPropagation();
        });
        $events->on('app.route.pre', function() use (&$calls) {
            $calls[] = 'name-indexed';
        });

        $events->dispatch(new \Pop\Event\RoutePreEvent(new \Pop\Application()));

        $this->assertEquals(['class-indexed'], $calls);
    }

}