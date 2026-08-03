<?php

namespace Pop\Test;

use Pop\Application;
use Pop\Event\Psr14\DispatchPostEvent;
use Pop\Event\Psr14\DispatchPreEvent;
use Pop\Event\Psr14\ErrorEvent;
use Pop\Event\Psr14\InitEvent;
use Pop\Event\Psr14\RoutePreEvent;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\StoppableEventInterface;

class EventPsr14Test extends TestCase
{
    public function testApplicationEventsCarryTheApplicationInstance()
    {
        $app = new Application();

        foreach ([InitEvent::class, RoutePreEvent::class, DispatchPreEvent::class, DispatchPostEvent::class] as $class) {
            $event = new $class($app);
            $this->assertSame($app, $event->application());
        }
    }

    public function testErrorEventCarriesTheExceptionAndTheApplication()
    {
        $app       = new Application();
        $exception = new \RuntimeException('boom');
        $event     = new ErrorEvent($app, $exception);

        $this->assertSame($app, $event->application());
        $this->assertSame($exception, $event->exception());
    }

    public function testEventsAreStoppable()
    {
        $event = new RoutePreEvent(new Application());
        $this->assertInstanceOf(StoppableEventInterface::class, $event);
        $this->assertFalse($event->isPropagationStopped());

        $event->stopPropagation();
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testListenerProviderReturnsListenersForExactEventClassOnly()
    {
        $provider = new \Pop\Event\Psr14\ListenerProvider();
        $calls    = [];

        $provider->listen(RoutePreEvent::class, function($event) use (&$calls) {
            $calls[] = 'route-pre';
        });

        $routeEvent    = new RoutePreEvent(new Application());
        $dispatchEvent = new DispatchPreEvent(new Application());

        foreach ($provider->getListenersForEvent($routeEvent) as $listener) {
            $listener($routeEvent);
        }
        foreach ($provider->getListenersForEvent($dispatchEvent) as $listener) {
            $listener($dispatchEvent);
        }

        $this->assertEquals(['route-pre'], $calls);
    }

    public function testListenerProviderRespectsPriorityOrder()
    {
        $provider = new \Pop\Event\Psr14\ListenerProvider();
        $order    = [];

        $provider->listen(RoutePreEvent::class, function() use (&$order) { $order[] = 'low'; }, 1);
        $provider->listen(RoutePreEvent::class, function() use (&$order) { $order[] = 'high'; }, 10);

        $event = new RoutePreEvent(new Application());
        foreach ($provider->getListenersForEvent($event) as $listener) {
            $listener($event);
        }

        $this->assertEquals(['high', 'low'], $order);
    }

    public function testDispatcherStopsPropagationAndReturnsTheSameEventInstance()
    {
        $provider = new \Pop\Event\Psr14\ListenerProvider();
        $calls    = [];

        $provider->listen(RoutePreEvent::class, function($event) use (&$calls) {
            $calls[] = 'first';
            $event->stopPropagation();
        }, 10);
        $provider->listen(RoutePreEvent::class, function() use (&$calls) {
            $calls[] = 'second';
        }, 1);

        $dispatcher = new \Pop\Event\Psr14\Dispatcher($provider);
        $event      = new RoutePreEvent(new Application());
        $result     = $dispatcher->dispatch($event);

        $this->assertEquals(['first'], $calls);
        $this->assertSame($event, $result);
    }
}
