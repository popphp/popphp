<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Event;

use Pop\AbstractManager;
use Pop\Utils\CallableObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Event manager class
 *
 * @category   Pop
 * @package    Pop\Event
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class Manager extends AbstractManager implements EventDispatcherInterface, ListenerProviderInterface
{

    /**
     * Event results
     * @var array
     */
    protected array $results = [];

    /**
     * Class-indexed listeners, keyed by exact event class name
     * @var array<string, \SplPriorityQueue>
     */
    protected array $classListeners = [];

    /**
     * Constructor
     *
     * Instantiate the event manager object.
     *
     * @param  ?string $name
     * @param  mixed   $action
     * @param  int     $priority
     */
    public function __construct(?string $name = null, mixed $action = null, int $priority = 0)
    {
        if (($name !== null) && ($action !== null)) {
            $this->on($name, $action, $priority);
        }
    }

    /**
     * Attach an event listener
     *
     *     $event->on('event.name', 'someFunction');
     *     $event->on('event.name', function() { ... });
     *     $event->on('event.name', new SomeClass());
     *     $event->on('event.name', [new SomeClass, 'foo']);
     *     $event->on('event.name', 'SomeClass');
     *     $event->on('event.name', 'SomeClass->foo');
     *     $event->on('event.name', 'SomeClass::bar');
     *
     * @param  string $name
     * @param  mixed  $action
     * @param  int    $priority
     * @return static
     */
    public function on(string $name, mixed $action, int $priority = 0): static
    {
        if (!isset($this->items[$name])) {
            $this->items[$name] = new \SplPriorityQueue();
        }
        if (!($action instanceof CallableObject)) {
            $action = new CallableObject($action);
        }
        $this->items[$name]->insert($action, $priority);

        return $this;
    }

    /**
     * Detach an event listener
     *
     * @param  string $name
     * @param  mixed  $action
     * @return static
     */
    public function off(string $name, mixed $action): static
    {
        // If the event exists, loop through and remove the action if found.
        if (isset($this->items[$name])) {
            $newListeners = new \SplPriorityQueue();

            // Normalize the same way on() does, so a raw closure/string/etc.
            // can be compared against the CallableObject wrappers on() stored.
            if (!($action instanceof CallableObject)) {
                $action = new CallableObject($action);
            }

            $listeners = clone $this->items[$name];
            $listeners->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);

            foreach ($listeners as $item) {
                if ($action->getCallable() !== $item['data']->getCallable()) {
                    $newListeners->insert($item['data'], $item['priority']);
                }
            }

            $this->items[$name] = $newListeners;
        }

        return $this;
    }

    /**
     * Return an event
     *
     * @param  string $name
     * @return mixed
     */
    public function get(string $name): mixed
    {
        return $this->getItem($name);
    }

    /**
     * Determine whether the event manager has an event registered with it
     *
     * @param  string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return $this->hasItem($name);
    }

    /**
     * Return the event results
     *
     * @param  string $name
     * @return mixed
     */
    public function getResults(string $name): mixed
    {
        return $this->results[$name] ?? null;
    }

    /**
     * Register a listener for an event class - called with the raw event object as its sole argument
     *
     * @param  string   $eventClass
     * @param  callable $listener
     * @param  int      $priority
     * @return static
     */
    public function listen(string $eventClass, callable $listener, int $priority = 0): static
    {
        if (!isset($this->classListeners[$eventClass])) {
            $this->classListeners[$eventClass] = new \SplPriorityQueue();
        }
        $this->classListeners[$eventClass]->insert($listener, $priority);

        return $this;
    }

    /**
     * Get the class-indexed listeners for an event, matched by exact event class only
     *
     * @param  object $event
     * @return iterable
     */
    public function getListenersForEvent(object $event): iterable
    {
        $eventClass = $event::class;

        if (!isset($this->classListeners[$eventClass])) {
            return [];
        }

        // Clone before iterating - SplPriorityQueue iteration is destructive,
        // same reasoning as trigger()'s existing clone below.
        return clone $this->classListeners[$eventClass];
    }

    /**
     * Dispatch an event to its class-indexed listeners
     *
     * @param  object $event
     * @return object
     */
    public function dispatch(object $event): object
    {
        foreach ($this->getListenersForEvent($event) as $listener) {
            if (($event instanceof StoppableEventInterface) && $event->isPropagationStopped()) {
                return $event;
            }
            $listener($event);
        }

        if (($event instanceof StoppableEventInterface) && $event->isPropagationStopped()) {
            return $event;
        }

        if (($event instanceof AbstractEvent) && isset($this->items[$event->getName()])) {
            $this->dispatchNamed($event->getName(), $event);
        }

        return $event;
    }

    /**
     * Trigger an event listener
     *
     * @param  string $name
     * @param  array  $params
     * @return void
     */
    public function trigger(string $name, array $params = []): void
    {
        $this->dispatch(new Event($name, $params));
    }

    /**
     * Dispatch a name-indexed event's listeners - always called positionally,
     * sourced from $event->toParams() plus an appended 'result' (previous
     * listener's return value, same chaining as before this event object
     * existed) and 'event' (the event object itself, appended last so it
     * never shifts the positional index of a key an existing listener
     * already reads - a listener that wants stopPropagation() declares one
     * extra trailing parameter to receive it, everyone else is unaffected).
     *
     * @param  string       $name
     * @param  AbstractEvent $event
     * @return void
     */
    protected function dispatchNamed(string $name, AbstractEvent $event): void
    {
        $this->results[$name] = [];

        // Iterate a clone, not $this->items[$name] itself - SplPriorityQueue
        // iteration destructively dequeues, and an early return on a stopped
        // event would otherwise leave undequeued listeners stuck in the
        // original queue, permanently missing from every future trigger()
        // call for this name (same technique off() already uses above).
        $listeners = clone $this->items[$name];

        foreach ($listeners as $action) {
            if ($event->isPropagationStopped()) {
                return;
            }

            $params            = $event->toParams();
            $params['result']  = end($this->results[$name]);
            $params['event']   = $event;

            // Positional, not associative - CallableObject's constructor-invoking
            // call types (e.g. a 'new Class' listener) route a string-keyed
            // array into ReflectionClass::newInstanceArgs() as PHP named
            // arguments, which throws for any key that isn't a declared
            // parameter name. array_values() keeps every listener type on the
            // positional contract this method documents above.
            $result                 = $action->call(array_values($params));
            $this->results[$name][] = $result;
        }
    }

    /**
     * Set an event
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->on($name, $value);
    }

    /**
     * Set an event
     *
     * @param  mixed $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->on($offset, $value);
    }

}
