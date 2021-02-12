<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2021 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
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
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2021 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.7.0
 */
class Route
{

    /**
     * Router object
     * @var Router
     */
    protected static $router = null;

    /**
     * Method to set the router
     *
     * @param  Router $router
     * @return void
     */
    public static function setRouter(Router $router)
    {
        static::$router = $router;
    }

    /**
     * Method to get the router
     *
     * @return Router
     */
    public static function getRouter()
    {
        return static::$router;
    }

    /**
     * Method to check if the router has been registered
     *
     * @return boolean
     */
    public static function hasRouter()
    {
        return (null !== static::$router);
    }

    /**
     * Get URL route string for the named route
     *
     * @param  string  $routeName
     * @param  mixed   $params
     * @param  boolean $fqdn
     * @throws Exception
     * @return string
     */
    public static function url($routeName, $params = null, $fqdn = false)
    {
        if (!static::$router->hasName($routeName)) {
            throw new Exception('Error: That route name does not exist.');
        }

        return static::$router->getUrl($routeName, $params, $fqdn);
    }

}
