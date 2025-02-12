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
namespace Pop\Module;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Module manager class
 *
 * @category   Pop
 * @package    Pop\Module
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.3.7
 */
class Manager implements ArrayAccess, Countable, IteratorAggregate
{

    /**
     * Modules
     * @var array
     */
    protected array $modules = [];

    /**
     * Constructor
     *
     * Instantiate the module manager object.
     *
     * @param  ?array $modules
     * @throws Exception
     */
    public function __construct(?array $modules = null)
    {
        if ($modules !== null) {
            $this->registerModules($modules);
        }
    }

    /**
     * Register module objects
     *
     * @param  array $modules
     * @throws Exception
     * @return static
     */
    public function registerModules(array $modules): static
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
     * @return static
     */
    public function register(ModuleInterface $module): static
    {
        $this->modules[$module->getName()] = $module;
        return $this;
    }

    /**
     * Determine if a module object is registered with the manager by $name
     *
     * @param  string $name
     * @return bool
     */
    public function isRegistered(string $name): bool
    {
        return isset($this->modules[$name]);
    }

    /**
     * Determine if a module object is registered with the manager by $module object comparison
     *
     * @param  ModuleInterface $module
     * @return bool
     */
    public function hasModule(ModuleInterface $module): bool
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
    public function getModuleName(ModuleInterface $module): string
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
    public function get(string $name): mixed
    {
        return $this->modules[$name] ?? null;
    }

    /**
     * Unregister a module
     *
     * @param  string $name
     * @return static
     */
    public function unregister(string $name): static
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
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        if ($value instanceof ModuleInterface) {
            $value->setName($name);
        }
        $this->register($value);
    }

    /**
     * Get a registered module
     *
     * @param  string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    /**
     * Determine if a module is registered with the manager object
     *
     * @param  string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->modules[$name]);
    }

    /**
     * Unregister a module with the manager
     *
     * @param  string $name
     * @return void
     */
    public function __unset(string $name): void
    {
        $this->unregister($name);
    }

    /**
     * Register a module with the manager
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @throws Exception
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->__set($offset, $value);
    }

    /**
     * Get a registered module
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get($offset);
    }

    /**
     * Determine if a module is registered with the manager object
     *
     * @param  mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->__isset($offset);
    }

    /**
     * Unregister a module with the manager
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->__unset($offset);
    }

    /**
     * Return count
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->modules);
    }

    /**
     * Get iterator
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->modules);
    }

}
