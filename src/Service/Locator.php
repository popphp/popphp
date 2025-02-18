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
namespace Pop\Service;

use Pop\Utils\CallableObject;
use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Service locator class
 *
 * @category   Pop
 * @package    Pop\Service
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.3.8
 */
class Locator implements ArrayAccess, Countable, IteratorAggregate
{

    /**
     * Recursion depth level tracker
     * @var int
     */
    private static int $depth = 0;

    /**
     * Recursion called service name tracker
     * @var array
     */
    private static array $called = [];

    /**
     * Services
     * @var array
     */
    protected array $services = [];

    /**
     * Services that are loaded/instantiated
     * @var array
     */
    protected array $loaded = [];

    /**
     * Constructor
     *
     * Instantiate the service locator object.
     *
     * @param  ?array $services
     * @param  bool   $default
     * @throws Exception
     */
    public function __construct(?array $services = null, bool $default = true)
    {
        if ($services !== null) {
            $this->setServices($services);
        }

        if (($default) && !(Container::has('default'))) {
            Container::set('default', $this);
        }
    }

    /**
     * Set service objects from an array of services
     *
     * @param  array $services
     * @throws Exception
     * @return static
     */
    public function setServices(array $services): static
    {
        foreach ($services as $name => $service) {
            $this->set($name, $service);
        }

        return $this;
    }

    /**
     * Set a service. It will overwrite any previous service with the same name.
     *
     * A service can be a CallableObject, callable string, or an array that
     * contains a 'call' key and an optional 'params' key.
     * Valid callable strings are:
     *
     *     'someFunction'
     *     'SomeClass'
     *     'SomeClass->foo'
     *     'SomeClass::bar'
     *
     * @param  string $name
     * @param  mixed  $service
     * @throws Exception
     * @return static
     */
    public function set(string $name, mixed $service): static
    {
        if (!($service instanceof CallableObject)) {
            $call   = null;
            $params = null;

            if (!is_array($service)) {
                $call = $service;
            } else if (isset($service['call'])) {
                $call   = $service['call'];
                $params = $service['params'] ?? null;
            }

            if ($call === null) {
                throw new Exception('Error: A callable service was not passed');
            }

            $this->services[$name] = new CallableObject($call, $params);
        } else {
            $this->services[$name] = $service;
        }

        return $this;
    }

    /**
     * Get/load a service
     *
     * @param  string $name
     * @throws Exception
     * @return mixed
     */
    public function get(string $name): mixed
    {
        if (!isset($this->services[$name])) {
            throw new Exception("Error: The service '" . $name . "' has not been added to the service locator");
        }
        if (!isset($this->loaded[$name])) {
            if (self::$depth > 40) {
                throw new Exception(
                    'Error: Possible recursion loop detected when attempting to load these services: ' .
                    implode(', ', self::$called)
                );
            }

            // Keep track of the called services
            self::$depth++;
            if (!in_array($name, self::$called)) {
                self::$called[] = $name;
            }

            $this->loaded[$name] = $this->services[$name]->call();
            self::$depth--;
        }

        return $this->loaded[$name];
    }

    /**
     * Get a service's callable string or object
     *
     * @param  string $name
     * @return mixed
     */
    public function getCall(string $name): mixed
    {
        return $this->services[$name]?->getCallable();
    }

    /**
     * Check if  a service has parameters
     *
     * @param  string $name
     * @return bool
     */
    public function hasParams(string $name): bool
    {
        return (isset($this->services[$name]) && $this->services[$name]->hasParameters());
    }

    /**
     * Get a service's parameters
     *
     * @param  string $name
     * @return mixed
     */
    public function getParams(string $name): mixed
    {
        return $this->services[$name]?->getParameters();
    }

    /**
     * Set a service's callable string or object
     *
     * @param  string $name
     * @param  mixed  $call
     * @return static
     */
    public function setCall(string $name, mixed $call): static
    {
        if (isset($this->services[$name])) {
            $this->services[$name]->setCallable($call);
        }
        return $this;
    }

    /**
     * Set a service's parameters
     *
     * @param  string $name
     * @param  mixed  $params
     * @return static
     */
    public function setParams(string $name, mixed $params): static
    {
        if (isset($this->services[$name])) {
            if (is_array($params)) {
                $this->services[$name]->setParameters($params);
            } else {
                $this->services[$name]->setParameters([$params]);
            }
        }
        return $this;
    }

    /**
     * Add to a service's parameters
     *
     * @param  string $name
     * @param  mixed  $param
     * @param  mixed  $key
     * @return static
     */
    public function addParam(string $name, mixed $param, mixed $key = null): static
    {
        if (isset($this->services[$name])) {
            if ($key !== null) {
                $this->services[$name]->addNamedParameter($key, $param);
            } else {
                $this->services[$name]->addParameter($param);
            }
        }

        return $this;
    }

    /**
     * Remove a service's parameters
     *
     * @param  string $name
     * @param  mixed  $param
     * @param  mixed  $key
     * @return static
     */
    public function removeParam(string $name, mixed $param, mixed $key = null): static
    {
        if ($this->hasParams($name)) {
            if ($key !== null) {
                $this->services[$name]->removeParameter($key);
            } else {
                foreach ($this->services[$name]->getParameters() as $key => $value) {
                    if ($value == $param) {
                        $this->services[$name]->removeParameter($key);
                        break;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Determine of a service object is available (but not loaded)
     *
     * @param  string $name
     * @return bool
     */
    public function isAvailable(string $name): bool
    {
        return isset($this->services[$name]);
    }

    /**
     * Determine of a service object is loaded
     *
     * @param  string $name
     * @return bool
     */
    public function isLoaded(string $name): bool
    {
        return isset($this->loaded[$name]);
    }

    /**
     * Re-load a service object
     *
     * @param  string $name
     * @return mixed
     */
    public function reload(string $name): mixed
    {
        if (isset($this->loaded[$name])) {
            unset($this->loaded[$name]);
        }

        return $this->get($name);
    }

    /**
     * Remove a service
     *
     * @param  string $name
     * @return static
     */
    public function remove(string $name): static
    {
        if (isset($this->services[$name])) {
            unset($this->services[$name]);
        }
        if (isset($this->loaded[$name])) {
            unset($this->loaded[$name]);
        }
        return $this;
    }

    /**
     * Set a service
     *
     * @param  string $name
     * @param  mixed $value
     * @throws Exception
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    /**
     * Get a service
     *
     * @param  string $name
     * @throws Exception
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    /**
     * Determine if a service is available
     *
     * @param  string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->services[$name]);
    }

    /**
     * Unset a service
     *
     * @param  string $name
     * @return void
     */
    public function __unset(string $name): void
    {
        $this->remove($name);
    }

    /**
     * Set a service
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @throws Exception
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Get a service
     *
     * @param  mixed $offset
     * @throws Exception
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Determine if a service is available
     *
     * @param  mixed $offset
     * @return bool
     */
    public function offsetExists(mixed$offset): bool
    {
        return isset($this->services[$offset]);
    }

    /**
     * Unset a service
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }

    /**
     * Return count
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->services);
    }

    /**
     * Get iterator
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->services);
    }

}
