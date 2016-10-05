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
     * Add dispatch params to be passed into the dispatch method of the controller instance
     *
     * @param  string $controller
     * @param  string $action
     * @param  mixed  $params
     * @return MatchInterface
     */
    public function addDispatchParams($controller, $action, $params);

    /**
     * Append dispatch params to be passed into the dispatch method of the controller instance
     *
     * @param  string $controller
     * @param  string $action
     * @param  mixed  $params
     * @return MatchInterface
     */
    public function appendDispatchParams($controller, $action, $params);

    /**
     * Get the params assigned to the dispatch
     *
     * @param  string $controller
     * @param  string $action
     * @return mixed
     */
    public function getDispatchParams($controller, $action);

    /**
     * Determine if the dispatch has params
     *
     * @param  string $controller
     * @param  string $action
     * @return boolean
     */
    public function hasDispatchParams($controller, $action);

    /**
     * Remove dispatch params from a dispatch method
     *
     * @param  string $controller
     * @param  string $action
     * @return MatchInterface
     */
    public function removeDispatchParams($controller, $action);

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
