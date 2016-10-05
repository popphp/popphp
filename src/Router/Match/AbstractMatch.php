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
 * Pop router match abstract class
 *
 * @category   Pop
 * @package    Pop\Router
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
abstract class AbstractMatch implements MatchInterface
{

    /**
     * Matched route
     * @var string
     */
    protected $route = null;

    /**
     * Dynamic route
     * @var string
     */
    protected $dynamicRoute = null;

    /**
     * Dynamic route prefix
     * @var array
     */
    protected $dynamicRoutePrefix = null;

    /**
     * Routes
     * @var array
     */
    protected $routes = [];

    /**
     * Prepared routes
     * @var array
     */
    protected $preparedRoutes = [];

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
     * Add a route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @return AbstractMatch
     */
    public function addRoute($route, $controller)
    {
        // If is dynamic route
        if ((strpos($route, ':controller') !== false) || (strpos($route, '<controller') !== false)) {
            $this->dynamicRoute       = $route;
            $this->dynamicRoutePrefix = $controller;
        } else {
            if (is_callable($controller)) {
                $controller = ['controller' => $controller];
            }
            $this->routes[$route] = $controller;
        }
        return $this;
    }

    /**
     * Add multiple controller routes
     *
     * @param  array $routes
     * @return AbstractMatch
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
     * @return AbstractMatch
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
     * @return AbstractMatch
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
     * @return AbstractMatch
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
     * @return AbstractMatch
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
     * @return AbstractMatch
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
     * @return AbstractMatch
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
     * Determine if the route has been matched
     *
     * @return boolean
     */
    public function hasRoute()
    {
        return (null !== $this->route);
    }

    /**
     * Prepare the routes
     *
     * @return AbstractMatch
     */
    abstract public function prepare();

    /**
     * Match the route
     *
     * @return boolean
     */
    abstract public function match();

    /**
     * Method to process if a route was not found
     *
     * @param  boolean $exit
     * @return void
     */
    abstract public function noRouteFound($exit = true);

}
