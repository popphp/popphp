<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp
 * @category   Pop
 * @package    Pop_Service
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @package    Pop_Service
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Locator implements \ArrayAccess
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
     * @return Locator
     */
    public function __construct(array $services = null)
    {
        if (null !== $services) {
            $this->setServices($services);
        }
    }

    /**
     * Set service objects from an configuration array
     *
     * The $services parameter can contain a closure or an array of
     * call/param keys that define what to call when the service is
     * needed. Valid examples are ('params' are optional):
     *
     *     $services = [
     *         // Basic object of SomeClass, no parameters
     *         'service1' => [
     *             'call'   => 'SomeClass'
     *         ],
     *         // Object instance of SomeClass that's already instantiated
     *         'service2' => [
     *             'call' => new SomeClass('foo')
     *         ],
     *         // Object of SomeClass, parameters injected into constructor
     *         'service3' => [
     *             'call'   => 'SomeClass',
     *             'params' => ['foo', ['bar' => 'baz']]
     *         ],
     *         // Object of SomeClass method called, parameters injected into method
     *         'service4' => [
     *             'call'   => 'SomeClass->foo',
     *             'params' => function() { return 'bar'; }
     *         ],
     *         // Static call of SomeClass::foo, parameters injected into the static method
     *         'service5' => [
     *             'call'   => 'SomeClass::foo',
     *             'params' => ['foo', ['bar' => 'baz']]
     *         ],
     *         // Call a closure, injecting the parameters into it
     *         'service6' => [
     *             'call'   => function($foo, $bar) {
     *                 return new SomeClass($foo, $bar);
     *             },
     *             'params' => ['foo', 'bar']
     *         ],
     *         // Closure called that returns a services that is dependent on another service
     *         'service6' => [
     *             'call' => function($locator) {
     *                 return new SomeClass($locator->get('other.service'));
     *             }
     *         ]
     *     ];
     *
     * @param  array $services
     * @throws Exception
     * @return Locator
     */
    public function setServices(array $services)
    {
        foreach ($services as $name => $service) {
            if (isset($service['call'])) {
                $call   = $service['call'];
                $params = (isset($service['params'])) ? $service['params'] : null;
            } else {
                throw new Exception('Error: A service configuration parameter was not valid.');
            }

            $this->set($name, $call, $params);
        }

        return $this;
    }

    /**
     * Set a service object. It will overwrite
     * any previous service with the same name.
     *
     * @param  string $name
     * @param  mixed  $call
     * @param  mixed  $params
     * @return Locator
     */
    public function set($name, $call, $params = null)
    {
        $this->services[$name] = [
            'call'   => $call,
            'params' => $params
        ];

        return $this;
    }

    /**
     * Get a service
     *
     * @param  string $name
     * @throws Exception
     * @return mixed
     */
    public function get($name)
    {
        if (!isset($this->services[$name])) {
            throw new Exception('Error: That service has not been added to the service locator');
        }
        if (!isset($this->loaded[$name])) {
            $this->load($name);
        }
        return $this->loaded[$name];
    }

    /**
     * Get a service's callable
     *
     * @param  string $name
     * @return mixed
     */
    public function getCall($name)
    {
        return (isset($this->services[$name]) && isset($this->services[$name]['call'])) ? $this->services[$name]['call'] : null;
    }

    /**
     * Get a service's params
     *
     * @param  string $name
     * @return mixed
     */
    public function getParams($name)
    {
        return (isset($this->services[$name]) && isset($this->services[$name]['params'])) ? $this->services[$name]['params'] : null;
    }

    /**
     * Get a service's callable
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
     * Get a service's callable
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
     * Set a value in the array
     *
     * @param  string $offset
     * @param  mixed  $value
     * @return mixed
     */
    public function offsetSet($offset, $value) {
        return $this->setServices([$offset => $value]);
    }

    /**
     * Get a value from the array
     *
     * @param  string $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->get($offset);
    }

    /**
     * Determine if a value exists
     *
     * @param  string $offset
     * @return mixed
     */
    public function offsetExists($offset) {
        return isset($this->services[$offset]);
    }

    /**
     * Unset a value from the array
     *
     * @param  string $offset
     * @return mixed
     */
    public function offsetUnset($offset) {
        return $this->remove($offset);
    }

    /**
     * Load a service object. It will overwrite
     * any previous service with the same name.
     *
     * @param  string $name
     * @throws Exception
     * @return Locator
     */
    protected function load($name)
    {
        if (self::$depth > 60) {
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

        $call   = $this->services[$name]['call'];
        $params = $this->services[$name]['params'];

        // If the callable is a closure
        if ($call instanceof \Closure) {
            // Inject $params into the closure
            if (null !== $params) {
                if (!is_array($params)) {
                    $params = [$params];
                }
                $obj = call_user_func_array($call, $params);
            // Else, inject $this into the closure
            } else {
                $obj = call_user_func_array($call, [$this]);
            }
        // If the callable is a string
        } else if (is_string($call)) {
            // If there are params
            if (null !== $params) {
                // If the params are a closure, call the $params,
                // injecting the locator into the closure, to get the
                // required $params for the service from the closure
                if ($params instanceof \Closure) {
                    $params = call_user_func_array($params, [$this]);
                }

                if (!is_array($params)) {
                    $params = [$params];
                }

                // If the callable is a static call, i.e. SomeClass::foo,
                // injecting the $params into the static method
                if (strpos($call, '::')) {
                    $obj = call_user_func_array($call, $params);
                // If the callable is a instance call, i.e. SomeClass->foo,
                // call the object and method, injecting the $params into the method
                } else if (strpos($call, '->')) {
                    $ary    = explode('->', $call);
                    $class  = $ary[0];
                    $method = $ary[1];
                    $obj    = call_user_func_array([new $class(), $method], $params);
                // Else, if the callable is a new instance/construct call,
                // injecting the $params into the constructor
                } else {
                    $reflect = new \ReflectionClass($call);
                    $obj     = $reflect->newInstanceArgs($params);
                }
            // Else, no params, just call it
            } else {
                // If the callable is a static call
                if (strpos($call, '::')) {
                    $obj = call_user_func($call);
                // If the callable is a instance call
                } else if (strpos($call, '->')) {
                    $ary    = explode('->', $call);
                    $class  = $ary[0];
                    $method = $ary[1];
                    $obj    = call_user_func([new $class(), $method]);
                // Else, if the callable is a new instance/construct call
                } else {
                    $obj = new $call();
                }
            }
        // If the callable is already an instantiated object
        } else if (is_object($call)) {
            $obj = $call;
        // Else, throw exception
        } else {
            throw new Exception('Error: The call parameter must be an object or something callable.');
        }

        $this->loaded[$name] = $obj;
        self::$depth--;
        return $this;
    }

}
