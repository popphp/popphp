<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Module;

use InvalidArgumentException;
use Pop\AbstractManager;

/**
 * Module manager class
 *
 * @category   Pop
 * @package    Pop\Module
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.3.8
 */
class Manager extends AbstractManager
{

    /**
     * Constructor
     *
     * Instantiate the module manager object.
     *
     * @param  ?array $modules
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
        return $this->addItem($module, $module->getName());
    }

    /**
     * Determine if a module object is registered with the manager by $name
     *
     * @param  string $name
     * @return bool
     */
    public function isRegistered(string $name): bool
    {
        return $this->hasItem($name);
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

        foreach ($this->items as $name => $mod) {
            if ($mod === $module) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * Get a module
     *
     * @param  string $name
     * @return mixed
     */
    public function get(string $name): mixed
    {
        return $this->getItem($name);
    }

    /**
     * Unregister a module
     *
     * @param  string $name
     * @return static
     */
    public function unregister(string $name): static
    {
        return $this->removeItem($name);
    }

    /**
     * Register a module with the manager
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        if (!($value instanceof ModuleInterface)) {
            throw new InvalidArgumentException('Error: The value passed must be instance of ModuleInterface');
        }
        $value->setName($name);
        $this->register($value);
    }

}
