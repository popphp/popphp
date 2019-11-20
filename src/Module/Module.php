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
namespace Pop\Module;

use Pop\Application;

/**
 * Pop module class
 *
 * @category   Pop
 * @package    Pop\Module
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.3.3
 */
class Module extends AbstractModule implements \ArrayAccess
{

    /**
     * Constructor
     *
     * Instantiate a module object
     *
     * Optional parameters are an application instance or a configuration object or array
     */
    public function __construct()
    {
        $args        = func_get_args();
        $application = null;
        $config      = null;
        $name        = null;
        $version     = null;

        foreach ($args as $arg) {
            if ($arg instanceof Application) {
                $application = $arg;
            } else if (is_array($arg) || ($arg instanceof \ArrayAccess) || ($arg instanceof \ArrayObject)) {
                $config = $arg;
            } else if (preg_match('/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(-(0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(\.(0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*)?(\+[0-9a-zA-Z-]+(\.[0-9a-zA-Z-]+)*)?$/', $arg)) {
                $version = $arg;
            } else if (is_string($arg)) {
                $name = $arg;
            }
        }

        if (null !== $name) {
            $this->setName($name);
        } else if (null === $this->name) {
            $this->setName(str_replace('\\', '_', strtolower(get_called_class())));
        }

        if (null !== $version) {
            $this->setVersion($version);
        }

        if (null !== $config) {
            $this->registerConfig($config);
        }

        if (null !== $application) {
            $this->register($application);
        }
    }

    /**
     * Register module
     *
     * @param  Application $application
     * @throws Exception
     * @throws \Pop\Service\Exception
     * @return Module
     */
    public function register(Application $application)
    {
        $this->application = $application;

        if (null !== $this->config) {
            // Set the name, if available
            if (isset($this->config['name'])) {
                $this->setName($this->config['name']);
            }

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
                    $this->application->setService($name, $service);
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

        $this->application->modules->register($this);

        return $this;
    }

    /**
     * Set a pre-designated value in the module object
     *
     * @param  string $name
     * @param  mixed  $value
     * @return Module
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'config':
                $this->registerConfig($value);
                break;

        }
        return $this;
    }

    /**
     * Get a pre-designated value from the module object
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'config':
                return $this->config;
                break;
            default:
                return null;
        }
    }

    /**
     * Determine if a pre-designated value in the module object exists
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        switch ($name) {
            case 'config':
                return (null !== $this->config);
                break;
            default:
                return false;
        }
    }

    /**
     * Unset a pre-designated value in the module object
     *
     * @param  string $name
     * @return Module
     */
    public function __unset($name)
    {
        switch ($name) {
            case 'config':
                $this->config = null;
                break;
        }

        return $this;
    }

    /**
     * Set a value in the array
     *
     * @param  string $offset
     * @param  mixed  $value
     * @return Module
     */
    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }

    /**
     * Get a value from the array
     *
     * @param  string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * Determine if a value exists
     *
     * @param  string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * Unset a value from the array
     *
     * @param  string $offset
     * @return Module
     */
    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }

}