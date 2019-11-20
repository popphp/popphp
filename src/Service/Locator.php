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

/**
 * Service locator class
 *
 * @category   Pop
 * @package    Pop\Service
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.3.3
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
     * @param  array $services
     * @throws Exception
     */
    public function __construct(array $services = null)
    {
        if (null !== $services) {
            $this->setServices($services);
        }

        if (!Container::has('default')) {
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
     * A service can be a callable string, or an array that contains a 'call' key and
     * an optional 'params' key. Valid callable strings are:
     *
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
        $call   = null;
        $params = null;

        if (!is_array($service)) {
            $call   = $service;
            $params = null;
        } else if (isset($service['call'])) {
            $call   = $service['call'];
            $params = (isset($service['params']) ? $service['params'] : null);
        }

        if (null === $call) {
            throw new Exception('Error: A callable service was not passed');
        }

        $this->services[$name] = [
            'call'   => $call,
            'params' => $params
        ];

        return $this;
    }

    /**
     * Get/load a service
     *
     * @param  string $name
     * @throws Exception
     * @throws \ReflectionException
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

            $obj    = null;
            $called = false;
            $call   = $this->services[$name]['call'];
            $params = $this->services[$name]['params'];

            // If the callable is a closure
            if ($call instanceof \Closure) {
                // Inject $params into the closure
                if (null !== $params) {
                    if (!is_array($params)) {
                        $params = [$params];
                    }
                    switch (count($params)) {
                        case 1:
                            $obj = $call($params[0]);
                            break;
                        case 2:
                            $obj = $call($params[0], $params[1]);
                            break;
                        case 3:
                            $obj = $call($params[0], $params[1], $params[2]);
                            break;
                        case 4:
                            $obj = $call($params[0], $params[1], $params[2], $params[3]);
                            break;
                        default:
                            $obj = call_user_func_array($call, $params);
                    }
                    $called = true;
                // Else, inject $this into the closure
                } else {
                    $obj    = $call();
                    $called = true;
                }
            // If the callable is a string
            } else if (is_string($call)) {
                // If there are params
                if (null !== $params) {
                    // If the params are a closure, call the $params, injecting the locator into the closure,
                    // to get the required $params for the service from the closure
                    if ($params instanceof \Closure) {
                        $params = call_user_func_array($params, [$this]);
                    }

                    if (!is_array($params)) {
                        $params = [$params];
                    }

                    // If the callable is a static call, i.e. SomeClass::foo,
                    // injecting the $params into the static method
                    if (strpos($call, '::')) {
                        $obj    = call_user_func_array($call, $params);
                        $called = true;
                    // If the callable is a instance call, i.e. SomeClass->foo,
                    // call the object and method, injecting the $params into the method
                    } else if (strpos($call, '->')) {
                        [$class, $method] = explode('->', $call);
                        if (class_exists($class) && method_exists($class, $method)) {
                            $obj    = call_user_func_array([new $class(), $method], $params);
                            $called = true;
                        }
                    // Else, if the callable is a new instance/construct call,
                    // injecting the $params into the constructor
                    } else if (class_exists($call)) {
                        $reflect = new \ReflectionClass($call);
                        $obj     = $reflect->newInstanceArgs($params);
                        $called  = true;
                    }
                // Else, no params, just call it
                } else {
                    // If the callable is a static call
                    if (strpos($call, '::')) {
                        $obj    = call_user_func($call);
                        $called = true;
                    // If the callable is a instance call
                    } else if (strpos($call, '->')) {
                        [$class, $method] = explode('->', $call);
                        if (class_exists($class) && method_exists($class, $method)) {
                            $obj    = call_user_func([new $class(), $method]);
                            $called = true;
                        }
                    // Else, if the callable is a new instance/construct call
                    } else if (class_exists($call)) {
                        $obj    = new $call();
                        $called = true;
                    }
                }
            // If the callable is already an instantiated object
            } else if (is_object($call)) {
                $obj    = $call;
                $called = true;
            }

            if (!$called) {
                throw new Exception(
                    'Error: Unable to call service. The call parameter must be an object or something callable'
                );
            }

            $this->loaded[$name] = $obj;
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
        return (isset($this->services[$name]) && isset($this->services[$name]['call'])) ?
            $this->services[$name]['call'] : null;
    }

    /**
     * Get a service's parameters
     *
     * @param  string $name
     * @return mixed
     */
    public function getParams($name)
    {
        return (isset($this->services[$name]) && isset($this->services[$name]['params'])) ?
            $this->services[$name]['params'] : null;
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
            $this->services[$name]['call'] = $call;
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
            $this->services[$name]['params'] = $params;
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
     * @throws \ReflectionException
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
     * @throws \ReflectionException
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
