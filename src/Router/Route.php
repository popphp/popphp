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
namespace Pop\Router;

/**
 * Pop route class
 *
 * @category   Pop
 * @package    Pop\Router
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.3.7
 */
class Route
{

    /**
     * Router object
     * @var ?Router
     */
    protected static ?Router $router = null;

    /**
     * Method to set the router
     *
     * @param  Router $router
     * @return void
     */
    public static function setRouter(Router $router): void
    {
        static::$router = $router;
    }

    /**
     * Method to get the router
     *
     * @return Router
     */
    public static function getRouter(): Router
    {
        return static::$router;
    }

    /**
     * Method to check if the router has been registered
     *
     * @return bool
     */
    public static function hasRouter(): bool
    {
        return (static::$router !== null);
    }

    /**
     * Get URL route string for the named route
     *
     * @param  string $routeName
     * @param  mixed  $params
     * @param  bool   $fqdn
     * @throws Exception
     * @return string
     */
    public static function url(string $routeName, mixed $params = null, bool $fqdn = false): string
    {
        if (!static::$router->hasName($routeName)) {
            throw new Exception('Error: That route name does not exist.');
        }

        return static::$router->getUrl($routeName, $params, $fqdn);
    }

}
