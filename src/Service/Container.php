<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Service;

/**
 * Service container class
 *
 * @category   Pop
 * @package    Pop\Service
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class Container
{

    /**
     * Array service locators
     * @var array
     */
    private static array $locators = ['default' => null];

    /**
     * Set a service locator
     *
     * @param  string  $name
     * @param  Locator $locator
     * @return void
     */
    public static function set(string $name, Locator $locator): void
    {
        self::$locators[$name] = $locator;
    }

    /**
     * Determine if a service locator has been set
     *
     * @param  string $name
     * @return bool
     */
    public static function has(string $name): bool
    {
        return (!empty(self::$locators[$name]) && (self::$locators[$name] instanceof Locator));
    }

    /**
     * Get a service locator
     *
     * @param  string $name
     * @throws Exception
     * @return Locator
     */
    public static function get(string $name = 'default'): Locator
    {
        if (empty(self::$locators[$name])) {
            throw new Exception("Error: The service locator '" . $name . "' has not been added");
        }
        return self::$locators[$name];
    }

    /**
     * Remove a service locator
     *
     * @param  string $name
     * @return void
     */
    public static function remove(string $name): void
    {
        if (isset(self::$locators[$name])) {
            unset(self::$locators[$name]);
        }
    }

}