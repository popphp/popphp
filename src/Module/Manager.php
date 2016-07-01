<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Module;

/**
 * Module manager class
 *
 * @category   Pop
 * @package    Pop_Module
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.1.0
 */
class Manager implements \ArrayAccess, \IteratorAggregate
{

    /**
     * Modules
     * @var array
     */
    protected $modules = [];

    /**
     * Constructor
     *
     * Instantiate the module manager object.
     *
     * @param  array $modules
     * @return Manager
     */
    public function __construct(array $modules = null)
    {
        if (null !== $modules) {
            $this->registerModules($modules);
        }
    }

    /**
     * Register module objects
     *
     * @param  array  $modules
     * @return Manager
     */
    public function registerModules(array $modules)
    {
        foreach ($modules as $name => $module) {
            $this->register($name, $module);
        }
        return $this;
    }

    /**
     * Register a module object
     *
     * @param  string          $name
     * @param  ModuleInterface $module
     * @return Manager
     */
    public function register($name, ModuleInterface $module)
    {
        $this->modules[$name] = $module;
        return $this;
    }

    /**
     * Determine if a module object is registered
     *
     * @param  string $name
     * @return boolean
     */
    public function isRegistered($name)
    {
        return isset($this->modules[$name]);
    }

    /**
     * Load a module
     *
     * @param  string $name
     * @throws Exception
     * @return mixed
     */
    public function load($name)
    {
        return (isset($this->modules[$name])) ? $this->modules[$name] : null;
    }

    /**
     * Unload a module
     *
     * @param  string $name
     * @return Manager
     */
    public function unload($name)
    {
        if (isset($this->modules[$name])) {
            unset($this->modules[$name]);
        }
        return $this;
    }

    /**
     * Set a module
     *
     * @param  string $offset
     * @param  mixed  $value
     * @return mixed
     */
    public function offsetSet($offset, $value) {
        return $this->register($offset, $value);
    }

    /**
     * Get a module
     *
     * @param  string $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->load($offset);
    }

    /**
     * Determine if a module exists
     *
     * @param  string $offset
     * @return mixed
     */
    public function offsetExists($offset) {
        return isset($this->modules[$offset]);
    }

    /**
     * Unset a module
     *
     * @param  string $offset
     * @return mixed
     */
    public function offsetUnset($offset) {
        return $this->unload($offset);
    }

    /**
     * Get iterator
     *
     * @return \ArrayIterator
     */
    public function getIterator() {
        return new \ArrayIterator($this->modules);
    }

}
