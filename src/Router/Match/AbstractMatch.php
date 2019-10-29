<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.3.2
 */
abstract class AbstractMatch implements MatchInterface
{

    /**
     * Route string
     * @var string
     */
    protected $routeString = null;

    /**
     * Segments of route string
     * @var array
     */
    protected $segments = [];

    /**
     * Matched route
     * @var string
     */
    protected $route = null;

    /**
     * Default route
     * @var array
     */
    protected $defaultRoute = null;

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
     * Flag for dynamic route
     * @var boolean
     */
    protected $isDynamicRoute = false;

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
     * Route parameters
     * @var array
     */
    protected $routeParams = [];

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
            $this->dynamicRoute = $route;
            if (isset($controller['prefix'])) {
                $this->dynamicRoutePrefix = $controller['prefix'];
            }
        // Else, if wildcard route
        } else if ($route == '*') {
            if (is_callable($controller)) {
                $controller = ['controller' => $controller];
            }
            $this->defaultRoute = $controller;
        // Else, regular route
        } else {
            if (is_array($controller) && !isset($controller['controller'])) {
                foreach ($controller as $r => $c) {
                    $this->addRoute($route . $r, $c);
                }
            } else {
                if (is_callable($controller)) {
                    $controller = ['controller' => $controller];
                }

                $this->routes[$route] = (isset($this->routes[$route])) ?
                    array_merge($this->routes[$route], $controller) : $controller;
            }
        }

        if (isset($controller['params'])) {
            $this->addControllerParams($controller['controller'], $controller['params']);
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
     * Get the route string
     *
     * @return string
     */
    public function getRouteString()
    {
        return $this->routeString;
    }

    /**
     * Get the route string segments
     *
     * @return array
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * Get a route string segment
     *
     * @param  int $i
     * @return string
     */
    public function getSegment($i)
    {
        return (isset($this->segments[$i])) ? $this->segments[$i] : null;
    }

    /**
     * Get original route string
     *
     * @return string
     */
    public function getOriginalRoute()
    {
        return (isset($this->preparedRoutes[$this->route]) && isset($this->preparedRoutes[$this->route]['route'])) ?
            $this->preparedRoutes[$this->route]['route'] : null;
    }

    /**
     * Get route regex
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
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
     * Get prepared routes
     *
     * @return array
     */
    public function getPreparedRoutes()
    {
        return $this->preparedRoutes;
    }

    /**
     * Get flattened routes
     *
     * @return array
     */
    public function getFlattenedRoutes()
    {
        $routes = [];
        foreach ($this->preparedRoutes as $key => $value) {
            if (isset($value['route'])) {
                $routes[$value['route']] = $value;
                unset($routes[$value['route']]['route']);
            }
        }
        return $routes;
    }

    /**
     * Get the params discovered from the route
     *
     * @return array
     */
    public function getRouteParams()
    {
        return $this->routeParams;
    }

    /**
     * Determine if the route has params
     *
     * @return boolean
     */
    public function hasRouteParams()
    {
        return (count($this->routeParams) > 0);
    }

    /**
     * Get the default route
     *
     * @return array
     */
    public function getDefaultRoute()
    {
        return $this->defaultRoute;
    }

    /**
     * Determine if there is a default route
     *
     * @return boolean
     */
    public function hasDefaultRoute()
    {
        return (null !== $this->defaultRoute);
    }

    /**
     * Get the dynamic route
     *
     * @return string
     */
    public function getDynamicRoute()
    {
        return $this->dynamicRoute;
    }

    /**
     * Get the dynamic route prefix
     *
     * @return array
     */
    public function getDynamicRoutePrefix()
    {
        return $this->dynamicRoutePrefix;
    }

    /**
     * Determine if there is a dynamic route
     *
     * @return boolean
     */
    public function hasDynamicRoute()
    {
        return (null !== $this->dynamicRoute);
    }

    /**
     * Determine if it is a dynamic route
     *
     * @return boolean
     */
    public function isDynamicRoute()
    {
        return $this->isDynamicRoute;
    }

    /**
     * Get the controller
     *
     * @return mixed
     */
    public function getController()
    {
        $controller = null;

        if ((null !== $this->route) && isset($this->preparedRoutes[$this->route]) &&
            isset($this->preparedRoutes[$this->route]['controller'])) {
            $controller = $this->preparedRoutes[$this->route]['controller'];
        } else {
            if ((null === $controller) && (null !== $this->dynamicRoute) &&
                (null !== $this->dynamicRoutePrefix) && (count($this->segments) >= 1)) {
                $controller = $this->dynamicRoutePrefix . ucfirst(strtolower($this->segments[0])) . 'Controller';
                if (!class_exists($controller)) {
                    $controller           = null;
                    $this->isDynamicRoute = false;
                } else {
                    $this->isDynamicRoute = true;
                }
            }
            if ((null === $controller) && (null !== $this->defaultRoute) && isset($this->defaultRoute['controller'])) {
                $controller = $this->defaultRoute['controller'];
            }
        }

        return $controller;
    }

    /**
     * Determine if there is a controller
     *
     * @return boolean
     */
    public function hasController()
    {
        $result = false;

        if ((null !== $this->route) && isset($this->preparedRoutes[$this->route]) &&
            isset($this->preparedRoutes[$this->route]['controller'])) {
            $result = true;
        } else if ((null !== $this->dynamicRoute) && (null !== $this->getController()) &&
            (null !== $this->dynamicRoutePrefix) && (count($this->segments) >= 1)) {
            $result = class_exists($this->getController());
        } else if ((null !== $this->defaultRoute) && isset($this->defaultRoute['controller'])) {
            $result = true;
        }

        return $result;
    }

    /**
     * Get the action
     *
     * @return mixed
     */
    public function getAction()
    {
        $action = null;

        if ((null !== $this->route) && isset($this->preparedRoutes[$this->route]) &&
            isset($this->preparedRoutes[$this->route]['action'])) {
            $action = $this->preparedRoutes[$this->route]['action'];
        } else if ((null !== $this->dynamicRoute) && (null !== $this->dynamicRoutePrefix) &&
            (count($this->segments) >= 1)) {
            $action = (isset($this->segments[1])) ? $this->segments[1] : null;
        } else if ((null !== $this->defaultRoute) && isset($this->defaultRoute['action'])) {
            $action = $this->defaultRoute['action'];
        }

        return $action;
    }

    /**
     * Determine if there is an action
     *
     * @return boolean
     */
    public function hasAction()
    {
        $result = false;

        if ((null !== $this->route) && isset($this->preparedRoutes[$this->route]) &&
            isset($this->preparedRoutes[$this->route]['action'])) {
            $result = true;
        } else {
            if (!($result) && (null !== $this->dynamicRoute) && (null !== $this->dynamicRoutePrefix) &&
                (count($this->segments) >= 2)) {
                $result = method_exists($this->getController(), $this->getAction());
            }
            if (!($result) && (null !== $this->defaultRoute) && isset($this->defaultRoute['action'])) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Determine if the route has been matched
     *
     * @return boolean
     */
    abstract public function hasRoute();

    /**
     * Prepare the routes
     *
     * @return AbstractMatch
     */
    abstract public function prepare();

    /**
     * Match the route
     *
     * @param  string $forceRoute
     * @return boolean
     */
    abstract public function match($forceRoute = null);

    /**
     * Method to process if a route was not found
     *
     * @param  boolean $exit
     * @return void
     */
    abstract public function noRouteFound($exit = true);

}
