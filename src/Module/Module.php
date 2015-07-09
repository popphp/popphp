<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp
 * @category   Pop
 * @package    Pop_Module
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Module;

use Pop\Application;

/**
 * Pop module class
 *
 * @category   Pop
 * @package    Pop_Module
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Module implements ModuleInterface, \ArrayAccess
{

    /**
     * Module config
     * @var mixed
     */
    protected $config = null;

    /**
     * Application
     * @var Application
     */
    protected $application = null;

    /**
     * Constructor
     *
     * Instantiate a module object
     *
     * Optional parameters are an application instance or a configuration object or array
     *
     * @return Module
     */
    public function __construct()
    {
        $args        = func_get_args();
        $application = null;
        $config      = null;

        foreach ($args as $arg) {
            if ($arg instanceof Application) {
                $application = $arg;
            } else if (is_array($arg) || ($arg instanceof \ArrayAccess) || ($arg instanceof \ArrayObject)) {
                $config = $arg;
            }
        }

        if (null !== $config) {
            $this->loadConfig($config);
        }
        if (null !== $application) {
            $this->register($application);
        }
    }

    /**
     * Register module
     *
     * @param  Application $application
     * @return ModuleInterface
     */
    public function register(Application $application)
    {
        $this->application = $application;

        if (null !== $this->config) {
            // If the autoloader is set and the the module config has a
            // defined prefix and src, register the module with the autoloader
            if ((null !== $this->application) && (null !== $this->application->autoloader()) &&
                isset($this->config['prefix']) && isset($this->config['src']) && file_exists($this->config['src'])
            ) {
                // Register as PSR-0
                if (isset($this->config['psr-0']) && ($this->config['psr-0'])) {
                    $this->application->autoloader()->add($this->config['prefix'], $this->config['src']);
                    // Else, default to PSR-4
                } else {
                    $this->application->autoloader()->addPsr4($this->config['prefix'], $this->config['src']);
                }
            }

            // If routes are set in the module config, register them with the application
            if (isset($this->config['routes']) && (null !== $this->application) && (null !== $this->application->router())) {
                $this->application->router()->addRoutes($this->config['routes']);
            }

            // If services are set in the module config, register them with the application
            if (isset($this->config['services']) && (null !== $this->application) && (null !== $this->application->services())) {
                foreach ($this->config['services'] as $name => $service) {
                    if (isset($service['call']) && isset($service['params'])) {
                        $this->application->setService($name, $service['call'], $service['params']);
                    } else if (isset($service['call'])) {
                        $this->application->setService($name, $service['call']);
                    }
                }
            }

            // If events are set in the app config, register them with the application
            if (isset($this->config['events']) && (null !== $this->application) && (null !== $this->application->events())) {
                foreach ($this->config['events'] as $event) {
                    if (isset($event['name']) && isset($event['action'])) {
                        $this->application->on(
                            $event['name'],
                            $event['action'],
                            ((isset($event['priority'])) ? $event['priority'] : 0)
                        );
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Load module config
     *
     * @param  mixed $config
     * @throws \InvalidArgumentException
     * @return Module
     */
    public function loadConfig($config)
    {
        if (!is_array($config) && !($config instanceof \ArrayAccess) && !($config instanceof \ArrayObject)) {
            throw new \InvalidArgumentException(
                'Error: The config must be either an array itself or implement ArrayAccess or extend ArrayObject.'
            );
        }

        $this->config = $config;

        return $this;
    }

    /**
     * Merge new or altered config values with the existing config values
     *
     * @param  mixed   $config
     * @param  boolean $replace
     * @return Module
     */
    public function mergeConfig($config, $replace = false)
    {
        if (is_array($config) || ($config instanceof \ArrayAccess) || ($config instanceof \ArrayObject)) {
            if (null !== $this->config) {
                if ($replace) {
                    foreach ($config as $key => $value) {
                        $this->config[$key] = $value;
                    }
                } else {
                    $this->config = array_replace_recursive($this->config, $config);
                }
            } else {
                $this->config = $config;
            }
        }

        return $this;
    }

    /**
     * Get application
     *
     * @return Application
     */
    public function application()
    {
        return $this->application;
    }

    /**
     * Access module config
     *
     * @return mixed
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * Set a value in the array
     *
     * @param  string $offset
     * @param  mixed  $value
     * @return mixed
     */
    public function offsetSet($offset, $value) {
        return $this;
    }

    /**
     * Get a value from the array
     *
     * @param  string $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->config[$offset];
    }

    /**
     * Determine if a value exists
     *
     * @param  string $offset
     * @return mixed
     */
    public function offsetExists($offset) {
        return isset($this->config[$offset]);
    }

    /**
     * Unset a value from the array
     *
     * @param  string $offset
     * @return mixed
     */
    public function offsetUnset($offset) {
        return $this;
    }

}