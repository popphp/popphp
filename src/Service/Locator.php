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
namespace Pop\Service;

use Pop\Utils\CallableObject;

/**
 * Service locator class
 *
 * @category   Pop
 * @package    Pop\Service
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.4.0
 */
class Locator implements \ArrayAccess, \Countable, \IteratorAggregate
{

    /**
     * Recursion depth level tracker
     * @var array
     */
    private static $depth = 0;

    /**
     * Recursion called service name tracker
     * @var array
     */
    private static $called = [];

    /**
     * Services
     * @var array
     */
    protected $services = [];

    /**
     * Services that are loaded (instantiated)
     * @var array
     */
    protected $loaded = [];

    /**
     * Constructor
     *
     * Instantiate the service locator object.
     *
     * @param  array   $services
     * @param  boolean $default
     * @throws Exception
     */
    public function __construct(array $services = null, $default = true)
    {
        if (null !== $services) {
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
     * @return Locator
     */
    public function setServices(array $services)
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
     * @return Locator
     */
    public function set($name, $service)
    {
        if (!($service instanceof CallableObject)) {
            $call   = null;
            $params = null;

            if (!is_array($service)) {
                $call   = $service;
                $params = null;
            } else if (isset($service['call'])) {
                $call   = $service['call'];
                $params = (isset($service['params'])) ? $service['params'] : null;
            }

            if (null === $call) {
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
    public function get($name)
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
    public function getCall($name)
    {
        return (isset($this->services[$name])) ? $this->services[$name]->getCallable() : null;
    }

    /**
     * Check if  a service has parameters
     *
     * @param  string $name
     * @return boolean
     */
    public function hasParams($name)
    {
        return (isset($this->services[$name]) && $this->services[$name]->hasParameters());
    }

    /**
     * Get a service's parameters
     *
     * @param  string $name
     * @return mixed
     */
    public function getParams($name)
    {
        return ($this->hasParams($name)) ? $this->services[$name]->getParameters() : null;
    }

    /**
     * Set a service's callable string or object
     *
     * @param  string $name
     * @param  mixed  $call
     * @return Locator
     */
    public function setCall($name, $call)
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
     * @return Locator
     */
    public function setParams($name, $params)
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
     * @return Locator
     */
    public function addParam($name, $param, $key = null)
    {
        if (isset($this->services[$name])) {
            if (null !== $key) {
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
     * @return Locator
     */
    public function removeParam($name, $param, $key = null)
    {
        if ($this->hasParams($name)) {
            if (null !== $key) {
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
     * @return boolean
     */
    public function isAvailable($name)
    {
        return isset($this->services[$name]);
    }

    /**
     * Determine of a service object is loaded
     *
     * @param  string $name
     * @return boolean
     */
    public function isLoaded($name)
    {
        return isset($this->loaded[$name]);
    }

    /**
     * Remove a service
     *
     * @param  string $name
     * @return Locator
     */
    public function remove($name)
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
     * @return Locator
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * Get a service
     *
     * @param  string $name
     * @throws Exception
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Determine if a service is available
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->services[$name]);
    }

    /**
     * Unset a service
     *
     * @param  string $name
     * @return Locator
     */
    public function __unset($name)
    {
        return $this->remove($name);
    }

    /**
     * Set a service
     *
     * @param  string $offset
     * @param  mixed $value
     * @throws Exception
     * @return Locator
     */
    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    /**
     * Get a service
     *
     * @param  string $offset
     * @throws Exception
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Determine if a service is available
     *
     * @param  string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->services[$offset]);
    }

    /**
     * Unset a service
     *
     * @param  string $offset
     * @return Locator
     */
    public function offsetUnset($offset)
    {
        return $this->remove($offset);
    }

    /**
     * Return count
     *
     * @return int
     */
    public function count()
    {
        return count($this->services);
    }

    /**
     * Get iterator
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->services);
    }

}
