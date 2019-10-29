<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @package    Pop\Module
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.3.2
 */
class Manager implements \ArrayAccess, \Countable, \IteratorAggregate
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
     * @throws Exception
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
     * @param  array $modules
     * @throws Exception
     * @return Manager
     */
    public function registerModules(array $modules)
    {
        foreach ($modules as $module) {
            $this->register($module);
        }
        return $this;
    }

    /**
     * Register a module object
     *
     * @param  ModuleInterface $module
     * @throws Exception
     * @return Manager
     */
    public function register(ModuleInterface $module)
    {
        if (!$module->hasName()) {
            throw new Exception('Error: The module does not have a name');
        }

        $this->modules[$module->getName()] = $module;
        return $this;
    }

    /**
     * Determine if a module object is registered with the manager by $name
     *
     * @param  string $name
     * @return boolean
     */
    public function isRegistered($name)
    {
        return isset($this->modules[$name]);
    }

    /**
     * Determine if a module object is registered with the manager by $module object comparison
     *
     * @param  ModuleInterface $module
     * @return boolean
     */
    public function hasModule(ModuleInterface $module)
    {
        $result = false;

        foreach ($this->modules as $name => $mod) {
            if ($mod === $module) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * Get a module object's registered name
     *
     * @param  ModuleInterface $module
     * @return string
     */
    public function getModuleName(ModuleInterface $module)
    {
        $moduleName = null;

        foreach ($this->modules as $name => $mod) {
            if ($mod === $module) {
                $moduleName = $name;
                break;
            }
        }

        return $moduleName;
    }

    /**
     * Get a module
     *
     * @param  string $name
     * @return mixed
     */
    public function get($name)
    {
        return (isset($this->modules[$name])) ? $this->modules[$name] : null;
    }

    /**
     * Unregister a module
     *
     * @param  string $name
     * @return Manager
     */
    public function unregister($name)
    {
        if (isset($this->modules[$name])) {
            unset($this->modules[$name]);
        }
        return $this;
    }

    /**
     * Register a module with the manager
     *
     * @param  string $name
     * @param  mixed $value
     * @throws Exception
     * @return Manager
     */
    public function __set($name, $value)
    {
        if ($value instanceof ModuleInterface) {
            $value->setName($name);
        }
        return $this->register($value);
    }

    /**
     * Get a registered module
     *
     * @param  string $name
     * @return Module
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Determine if a module is registered with the manager object
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->modules[$name]);
    }

    /**
     * Unregister a module with the manager
     *
     * @param  string $name
     * @return Manager
     */
    public function __unset($name)
    {
        return $this->unregister($name);
    }

    /**
     * Register a module with the manager
     *
     * @param  string $offset
     * @param  mixed $value
     * @throws Exception
     * @return Manager
     */
    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }

    /**
     * Get a registered module
     *
     * @param  string $offset
     * @return Module
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * Determine if a module is registered with the manager object
     *
     * @param  string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * Unregister a module with the manager
     *
     * @param  string $offset
     * @return Manager
     */
    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }

    /**
     * Return count
     *
     * @return int
     */
    public function count()
    {
        return count($this->modules);
    }

    /**
     * Get iterator
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->modules);
    }

}
