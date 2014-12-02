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
 * Pop router class
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Router implements RouterInterface
{

    /**
     * Array of available routes
     * @var array
     */
    protected $routes = [];

    /**
     * Route match object
     * @var Match\MatchInterface
     */
    protected $routeMatch = null;

    /**
     * Controller object
     * @var \Pop\Controller\ControllerInterface
     */
    protected $controller = null;

    /**
     * Constructor
     *
     * Instantiate the router object
     *
     * @param  array $routes
     * @return Router
     */
    public function __construct(array $routes = null)
    {
        $this->routeMatch = (stripos(php_sapi_name(), 'cli') !== false) ? new Match\Cli() : new Match\Http();
        if (null !== $routes) {
            $this->addRoutes($routes);
        }
    }

    /**
     * Add a controller route
     *
     * @param  string $route
     * @param  string $controller
     * @return Router
     */
    public function addRoute($route, $controller)
    {
        if (!isset($this->routes[$route])) {
            $this->routes[$route] = $controller;
        } else {
            if (is_array($this->routes[$route]) && is_array($controller)) {
                $this->routes[$route] = array_merge_recursive($this->routes[$route], $controller);
            } else {
                $this->routes[$route] = $controller;
            }
        }

        return $this;
    }

    /**
     * Add multiple controller routes
     *
     * @param  array $routes
     * @return Router
     */
    public function addRoutes(array $routes)
    {
        foreach ($routes as $route => $controller) {
            $this->addRoute($route, $controller);
        }

        return $this;
    }

    /**
     * Get route
     *
     * @return mixed
     */
    public function getRoute()
    {
        return $this->routeMatch->match($this->routes);
    }

    /**
     * Determine if a route is set for the current request
     *
     * @return boolean
     */
    public function hasRoute()
    {
        return (null !== $this->getRoute());
    }

    /**
     * Get routes
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Get route match object
     *
     * @return Match\MatchInterface
     */
    public function getRouteMatch()
    {
        return $this->routeMatch;
    }

    /**
     * Get the current controller object
     *
     * @return \Pop\Controller\ControllerInterface
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Route to the correct controller
     *
     * @return void
     */
    public function route()
    {
        $controllerClass = $this->getRoute();

        if ((null !== $controllerClass) && class_exists($controllerClass)) {
            $this->controller = new $controllerClass();
            $action           = $this->routeMatch->getAction();
            $errorAction      = $this->controller->getErrorAction();

            // If action exists in the controller, dispatch it
            if ((null !== $action) && method_exists($this->controller, $action)) {
                $this->controller->dispatch($action);
            // Else, if an error action exists in the controller, dispatch it
            } else if ((null !== $errorAction) && method_exists($this->controller, $errorAction)) {
                $this->controller->dispatch($errorAction);
            }
        }
    }

}
