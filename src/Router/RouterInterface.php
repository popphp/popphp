<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Router;

/**
 * Pop router interface
 *
 * @category   Pop
 * @package    Pop_Router
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.1
 */
interface RouterInterface
{

    /**
     * Add a route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @return RouterInterface
     */
    public function addRoute($route, $controller);

    /**
     * Add multiple routes
     *
     * @param  array $routes
     * @return RouterInterface
     */
    public function addRoutes(array $routes);

    /**
     * Add controller params
     *
     * @param  string $controller
     * @param  mixed  $params
     * @return RouterInterface
     */
    public function addControllerParams($controller, $params);

    /**
     * Add route params
     *
     * @param  string $action
     * @param  mixed  $params
     * @return RouterInterface
     */
    public function addDispatchParams($action, $params);

    /**
     * Get the params assigned to the controller
     *
     * @param  string $controller
     * @return mixed
     */
    public function getControllerParams($controller);

    /**
     * Get the params assigned to the dispatch
     *
     * @param  string $dispatch
     * @return mixed
     */
    public function getDispatchParams($dispatch);

    /**
     * Determine if a route is set for the current request
     *
     * @return boolean
     */
    public function hasRoute();

    /**
     * Get routes
     *
     * @return array
     */
    public function getRoutes();

    /**
     * Get the current controller object
     *
     * @return \Pop\Controller\ControllerInterface
     */
    public function getController();

    /**
     * Get the current controller class name
     *
     * @return string
     */
    public function getControllerClass();

    /**
     * Determine if the route is CLI
     *
     * @return boolean
     */
    public function isCli();

    /**
     * Determine if the route is HTTP
     *
     * @return boolean
     */
    public function isHttp();

    /**
     * Route to the correct controller
     *
     * @return void
     */
    public function route();

}