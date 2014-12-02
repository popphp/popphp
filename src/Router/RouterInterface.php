<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
interface RouterInterface
{

    /**
     * Add a route
     *
     * @param  string $route
     * @param  string $controller
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
     * Add route params
     *
     * @param  string $route
     * @param  mixed  $params
     * @return RouterInterface
     */
    public function addRouteParams($route, $params);
    /**
     * Add route params
     *
     * @param  string $action
     * @param  mixed  $params
     * @return RouterInterface
     */
    public function addDispatchParams($action, $params);

    /**
     * Get the current controller object
     *
     * @return \Pop\Controller\ControllerInterface
     */
    public function getController();

    /**
     * Get route
     *
     * @return mixed
     */
    public function getRoute();

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
     * Route to the correct controller
     *
     * @param \Pop\Application $application
     * @return void
     */
    public function route(\Pop\Application $application);

}