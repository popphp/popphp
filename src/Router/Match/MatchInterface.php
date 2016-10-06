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
namespace Pop\Router\Match;

/**
 * Pop router match interface
 *
 * @category   Pop
 * @package    Pop\Router
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
interface MatchInterface
{

    /**
     * Add a route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @return MatchInterface
     */
    public function addRoute($route, $controller);

    /**
     * Add multiple controller routes
     *
     * @param  array $routes
     * @return MatchInterface
     */
    public function addRoutes(array $routes);

    /**
     * Add controller params to be passed into a new controller instance
     *
     * @param  string $controller
     * @param  mixed  $params
     * @return MatchInterface
     */
    public function addControllerParams($controller, $params);

    /**
     * Append controller params to be passed into a new controller instance
     *
     * @param  string $controller
     * @param  mixed  $params
     * @return MatchInterface
     */
    public function appendControllerParams($controller, $params);

    /**
     * Get the params assigned to the controller
     *
     * @param  string $controller
     * @return mixed
     */
    public function getControllerParams($controller);

    /**
     * Determine if the controller has params
     *
     * @param  string $controller
     * @return boolean
     */
    public function hasControllerParams($controller);

    /**
     * Remove controller params
     *
     * @param  string $controller
     * @return MatchInterface
     */
    public function removeControllerParams($controller);

    /**
     * Get routes
     *
     * @return array
     */
    public function getRoutes();

    /**
     * Determine if there is a route match
     *
     * @return boolean
     */
    public function hasRoute();

    /**
     * Get the params discovered from the route
     *
     * @return array
     */
    public function getRouteParams();

    /**
     * Determine if the route has params
     *
     * @return boolean
     */
    public function hasRouteParams();

    /**
     * Get the default route
     *
     * @return array
     */
    public function getDefaultRoute();

    /**
     * Determine if there is a default route
     *
     * @return boolean
     */
    public function hasDefaultRoute();

    /**
     * Get the dynamic route
     *
     * @return array
     */
    public function getDynamicRoute();

    /**
     * Get the dynamic route prefix
     *
     * @return array
     */
    public function getDynamicRoutePrefix();

    /**
     * Determine if there is a dynamic route
     *
     * @return boolean
     */
    public function hasDynamicRoute();

    /**
     * Determine if it is a dynamic route
     *
     * @return boolean
     */
    public function isDynamicRoute();

    /**
     * Get the controller
     *
     * @return mixed
     */
    public function getController();

    /**
     * Determine if there is a controller
     *
     * @return boolean
     */
    public function hasController();

    /**
     * Get the action
     *
     * @return mixed
     */
    public function getAction();

    /**
     * Determine if there is an action
     *
     * @return boolean
     */
    public function hasAction();

    /**
     * Match the route
     *
     * @return MatchInterface
     */
    public function prepare();

    /**
     * Prepare the routes
     *
     * @return boolean
     */
    public function match();

    /**
     * Method to process if a route was not found
     *
     * @param  boolean $exit
     * @return void
     */
    public function noRouteFound($exit = true);

}
