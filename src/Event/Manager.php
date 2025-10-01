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

use Pop\AbstractManager;
use Pop\Utils\CallableObject;

/**
 * Event manager class
 *
 * @category   Pop
 * @package    Pop\Event
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.3.8
 */
class Manager extends AbstractManager
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
     * @return Manager
     */
    public function off(string $name, mixed $action): static
    {
        // If the event exists, loop through and remove the action if found.
        if (isset($this->items[$name])) {
            $newListeners = new \SplPriorityQueue();

            $listeners = clone $this->items[$name];
            $listeners->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);

            foreach ($listeners as $value) {
                $item = $listeners->current();
                if ($action !== $item['data']) {
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
     * Determine if the project application is still alive or has been killed
     *
     * @return bool
     */
    public function alive(): bool
    {
        return $this->alive;
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
        if (isset($this->items[$name])) {
            if (!isset($this->results[$name])) {
                $this->results[$name] = [];
            }

            foreach ($this->items[$name] as $action) {
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
