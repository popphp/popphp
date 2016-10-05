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
namespace Pop\Router;

/**
 * Pop router class
 *
 * @category   Pop
 * @package    Pop\Router
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class Router
{

    /**
     * Available routes
     * @var array
     */
    protected $routes = [];

    /**
     * Route match object
     * @var Match\MatchInterface
     */
    protected $routeMatch = null;

    /**
     * Controller parameters
     * @var array
     */
    protected $controllerParams = [];

    /**
     * Dispatch parameters
     * @var array
     */
    protected $dispatchParams = [];

    /**
     * Constructor
     *
     * Instantiate the router object
     *
     * @param  array $routes
     */
    public function __construct(array $routes = null)
    {
        $this->routeMatch = ((stripos(php_sapi_name(), 'cli') !== false) && (stripos(php_sapi_name(), 'server') === false)) ?
            new Match\Cli() : new Match\Http();

        if (null !== $routes) {
            $this->addRoutes($routes);
        }
    }

    /**
     * Add a route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @return Router
     */
    public function addRoute($route, $controller)
    {
        $this->routes[$route] = $controller;
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
     * Add controller params to be passed into a new controller instance
     *
     * @param  string $controller
     * @param  mixed  $params
     * @return Router
     */
    public function addControllerParams($controller, $params)
    {
        if (!is_array($params)) {
            $params = [$params];
        }
        $this->controllerParams[$controller] = $params;

        return $this;
    }

    /**
     * Append controller params to be passed into a new controller instance
     *
     * @param  string $controller
     * @param  mixed  $params
     * @return Router
     */
    public function appendControllerParams($controller, $params)
    {
        if (!is_array($params)) {
            $params = [$params];
        }
        if (isset($this->controllerParams[$controller])) {
            $this->controllerParams[$controller] = array_merge($this->controllerParams[$controller], $params);
        } else {
            $this->controllerParams[$controller] = $params;
        }
        return $this;
    }

    /**
     * Get the params assigned to the controller
     *
     * @param  string $controller
     * @return mixed
     */
    public function getControllerParams($controller)
    {
        return (isset($this->controllerParams[$controller])) ? $this->controllerParams[$controller] : null;
    }

    /**
     * Determine if the controller has params
     *
     * @param  string $controller
     * @return boolean
     */
    public function hasControllerParams($controller)
    {
        return (isset($this->controllerParams[$controller]));
    }

    /**
     * Remove controller params
     *
     * @param  string $controller
     * @return Router
     */
    public function removeControllerParams($controller)
    {
        if (isset($this->controllerParams[$controller])) {
            unset($this->controllerParams[$controller]);
        }
        return $this;
    }

    /**
     * Add dispatch params to be passed into the dispatch method of the controller instance
     *
     * @param  string $controller
     * @param  string $action
     * @param  mixed  $params
     * @return Router
     */
    public function addDispatchParams($controller, $action, $params)
    {
        if (!is_array($params)) {
            $params = [$params];
        }
        if (!isset($this->dispatchParams[$controller])) {
            $this->dispatchParams[$controller] = [];
        }
        $this->dispatchParams[$controller][$action] = $params;

        return $this;
    }

    /**
     * Append dispatch params to be passed into the dispatch method of the controller instance
     *
     * @param  string $controller
     * @param  string $action
     * @param  mixed  $params
     * @return Router
     */
    public function appendDispatchParams($controller, $action, $params)
    {
        if (!is_array($params)) {
            $params = [$params];
        }
        if (isset($this->dispatchParams[$controller]) && isset($this->dispatchParams[$controller][$action])) {
            $this->dispatchParams[$controller][$action] = array_merge($this->dispatchParams[$controller][$action], $params);
        } else {
            $this->addDispatchParams($controller, $action, $params);
        }
        return $this;
    }

    /**
     * Get the params assigned to the dispatch
     *
     * @param  string $controller
     * @param  string $action
     * @return mixed
     */
    public function getDispatchParams($controller, $action)
    {
        return (isset($this->dispatchParams[$controller]) && isset($this->dispatchParams[$controller][$action])) ?
            $this->dispatchParams[$controller][$action] : null;
    }

    /**
     * Determine if the dispatch has params
     *
     * @param  string $controller
     * @param  string $action
     * @return boolean
     */
    public function hasDispatchParams($controller, $action)
    {
        return (isset($this->dispatchParams[$controller]) && isset($this->dispatchParams[$controller][$action]));
    }

    /**
     * Remove dispatch params from a dispatch method
     *
     * @param  string $controller
     * @param  string $action
     * @return Router
     */
    public function removeDispatchParams($controller, $action)
    {
        if (isset($this->dispatchParams[$controller]) && isset($this->dispatchParams[$controller][$action])) {
            unset($this->dispatchParams[$controller][$action]);
            if (count($this->dispatchParams[$controller]) == 0) {
                unset($this->dispatchParams[$controller]);
            }
        }
        return $this;
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
     * Determine if there is a route match
     *
     * @return boolean
     */
    public function hasRoute()
    {
        return $this->routeMatch->hasRoute();
    }

    /**
     * Determine if the route is CLI
     *
     * @return boolean
     */
    public function isCli()
    {
        return ($this->routeMatch instanceof Match\Cli);
    }

    /**
     * Determine if the route is HTTP
     *
     * @return boolean
     */
    public function isHttp()
    {
        return ($this->routeMatch instanceof Match\Http);
    }

    /**
     * Execute the route match on the available routes
     *
     * @return Router
     */
    public function match()
    {
        $this->routeMatch->match($this->routes);
        return $this;
    }

    /**
     * Route to the correct controller
     *
     * @return void
     */
    public function route()
    {
        $this->match();
    }

}
