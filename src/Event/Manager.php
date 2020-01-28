<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Event;

use Pop\Utils\CallableObject;

/**
 * Event manager class
 *
 * @category   Pop
 * @package    Pop\Event
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.4.0
 */
class Manager implements \ArrayAccess, \Countable, \IteratorAggregate
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
    protected $listeners = [];

    /**
     * Event results
     * @var array
     */
    protected $results = [];

    /**
     * Event 'alive' tracking flag
     * @var boolean
     */
    protected $alive = true;

    /**
     * Constructor
     *
     * Instantiate the event manager object.
     *
     * @param  string $name
     * @param  mixed  $action
     * @param  int    $priority
     */
    public function __construct($name = null, $action = null, $priority = 0)
    {
        if ((null !== $name) && (null !== $action)) {
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
    public function on($name, $action, $priority = 0)
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
    public function off($name, $action)
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
    public function get($name)
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
     * @return boolean
     */
    public function has($name)
    {
        return (isset($this->listeners[$name]));
    }

    /**
     * Return the event results
     *
     * @param  string $name
     * @return mixed
     */
    public function getResults($name)
    {
        return (isset($this->results[$name]) ? $this->results[$name] : null);
    }

    /**
     * Determine if the project application is still alive or has been killed
     *
     * @return boolean
     */
    public function alive()
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
    public function trigger($name, array $params = [])
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
     * @return Manager
     */
    public function __set($name, $value)
    {
        return $this->on($name, $value);
    }

    /**
     * Get an event
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Determine if an event exists
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * Unset an event
     *
     * @param  string $name
     * @return Manager
     */
    public function __unset($name)
    {
        if (isset($this->listeners[$name])) {
            unset($this->listeners[$name]);
        }
        return $this;
    }

    /**
     * Set an event
     *
     * @param  string $offset
     * @param  mixed  $value
     * @return Manager
     */
    public function offsetSet($offset, $value)
    {
        return $this->on($offset, $value);
    }

    /**
     * Get an event
     *
     * @param  string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Determine if an event exists
     *
     * @param  string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Unset an event
     *
     * @param  string $offset
     * @return Manager
     */
    public function offsetUnset($offset)
    {
        if (isset($this->listeners[$offset])) {
            unset($this->listeners[$offset]);
        }
        return $this;
    }

    /**
     * Return count
     *
     * @return int
     */
    public function count()
    {
        return count($this->listeners);
    }

    /**
     * Get iterator
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->listeners);
    }

}
