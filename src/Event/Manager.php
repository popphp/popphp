<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Event;

use Pop\Utils\CallableObject;
use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Event manager class
 *
 * @category   Pop
 * @package    Pop\Event
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.3.5
 */
class Manager implements ArrayAccess, Countable, IteratorAggregate
{

    /**
     * Constant to stop the event manager
     * @var string
     */
    const STOP = 'Pop\Event\Manager::STOP';

    /**
     * Constant to send a kill signal to the application
     * @var string
     */
    const KILL = 'Pop\Event\Manager::KILL';

    /**
     * Event listeners
     * @var array
     */
    protected array $listeners = [];

    /**
     * Event results
     * @var array
     */
    protected array $results = [];

    /**
     * Event 'alive' tracking flag
     * @var bool
     */
    protected bool $alive = true;

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
     * @return Manager
     */
    public function on(string $name, mixed $action, int $priority = 0): static
    {
        if (!isset($this->listeners[$name])) {
            $this->listeners[$name] = new \SplPriorityQueue();
        }
        $this->listeners[$name]->insert(new CallableObject($action), (int)$priority);

        return $this;
    }

    /**
     * Detach an event listener
     *
     * @param  string $name
     * @param  mixed  $action
     * @return Manager
     */
    public function off(string $name, mixed $action): static
    {
        // If the event exists, loop through and remove the action if found.
        if (isset($this->listeners[$name])) {
            $newListeners = new \SplPriorityQueue();

            $listeners = clone $this->listeners[$name];
            $listeners->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);

            foreach ($listeners as $value) {
                $item = $listeners->current();
                if ($action !== $item['data']) {
                    $newListeners->insert($item['data'], $item['priority']);
                }
            }

            $this->listeners[$name] = $newListeners;
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
        $listener = null;
        if (isset($this->listeners[$name])) {
            $listener = $this->listeners[$name];
        }

        return $listener;
    }

    /**
     * Determine whether the event manage has an event registered with it
     *
     * @param  string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return (isset($this->listeners[$name]));
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
     * Determine if the project application is still alive or has been killed
     *
     * @return bool
     */
    public function alive(): bool
    {
        return $this->alive;
    }

    /**
     * Trigger an event listener priority
     *
     * @param  string $name
     * @param  array  $params
     * @return void
     */
    public function trigger(string $name, array $params = []): void
    {
        if (isset($this->listeners[$name])) {
            if (!isset($this->results[$name])) {
                $this->results[$name] = [];
            }

            foreach ($this->listeners[$name] as $action) {
                if (end($this->results[$name]) == self::STOP) {
                    return;
                }

                $params['result']       = end($this->results[$name]);
                $result                 = $action->call($params);
                $this->results[$name][] = $result;

                if ($result == self::KILL) {
                    $this->alive = false;
                }
            }
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
     * Get an event
     *
     * @param  string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    /**
     * Determine if an event exists
     *
     * @param  string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    /**
     * Unset an event
     *
     * @param  string $name
     * @return void
     */
    public function __unset(string $name): void
    {
        if (isset($this->listeners[$name])) {
            unset($this->listeners[$name]);
        }
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

    /**
     * Get an event
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Determine if an event exists
     *
     * @param  string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Unset an event
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        if (isset($this->listeners[$offset])) {
            unset($this->listeners[$offset]);
        }
    }

    /**
     * Return count
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->listeners);
    }

    /**
     * Get iterator
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->listeners);
    }

}
