<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Service
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Locator
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
     * Instantiate the service locator object. The optional $services
     * parameter can contain a closure or an array of call/param keys
     * that define what to call when the service is needed.
     * Valid examples are ('params' are optional):
     *
     *     $services = [
     *         'service1' => function($locator) {...},
     *         'service2' => [
     *             'call'   => 'SomeClass',
     *             'params' => [...]
     *         ],
     *         'service3' => [
     *             'call'   => 'SomeClass',
     *             'params' => function() {...}
     *         ]
     *     ];
     *
     * @param  array $services
     * @throws Exception
     * @return Locator
     */
    public function __construct(array $services = null)
    {
        if (null !== $services) {
            foreach ($services as $name => $service) {
                if ($service instanceof \Closure) {
                    $call   = $service;
                    $params = null;
                } else if (is_object($service)) {
                    $call   = $service;
                    $params = null;
                } else if (isset($service['call'])) {
                    $call   = $service['call'];
                    $params = (isset($service['params'])) ? $service['params'] : null;
                } else {
                    throw new Exception('Error: A service configuration parameter was not valid.');
                }

                $this->set($name, $call, $params);
            }
        }
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
     * Get a service object.
     *
     * @param  string $name
     * @return mixed
     */
    public function get($name)
    {
        if (!isset($this->services[$name])) {
            return null;
        } else {
            if (!isset($this->loaded[$name])) {
                $this->load($name);
            }
            return $this->loaded[$name];
        }
    }

    /**
     * Determine of a service object is available (but not loaded).
     *
     * @param  string $name
     * @return boolean
     */
    public function isAvailable($name)
    {
        return isset($this->services[$name]);
    }

    /**
     * Determine of a service object is loaded.
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
     * Load a service object. It will overwrite
     * any previous service with the same name.
     *
     * @param  string $name
     * @throws Exception
     * @return Locator
     */
    protected function load($name)
    {
        if (self::$depth > 99) {
            throw new Exception(
                'Error: Possible recursion loop detected when attempting to load these services: ' .
                implode(', ', self::$called)
            );
        }

        self::$depth++;

        $call   = $this->services[$name]['call'];
        $params = $this->services[$name]['params'];

        // If the callable is a closure
        if ($call instanceof \Closure) {
            if (!in_array($name, self::$called)) {
                self::$called[] = $name;
            }
            $obj = call_user_func_array($call, [$this]);
        // If the callable is a string
        } else if (is_string($call)) {
            // If there are params
            if (null !== $params) {
                // If the params are a closure
                if ($params instanceof \Closure) {
                    $params = call_user_func_array($params, [$this]);
                }
                // If the callable is a static call
                if (strpos($call, '::')) {
                    $obj = call_user_func_array($call, $params);
                // If the callable is a instance call
                } else if (strpos($call, '->')) {
                    $ary    = explode('->', $call);
                    $class  = $ary[0];
                    $method = $ary[1];
                    $obj    = call_user_func_array([new $class(), $method], $params);
                // Else, if the callable is a new instance/construct call
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
