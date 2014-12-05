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
     * Array of route parameters
     * @var array
     */
    protected $routeParams = [];

    /**
     * Array of dispatch parameters
     * @var array
     */
    protected $dispatchParams = [];

    /**
     * Route match object
     * @var Match\MatchInterface
     */
    protected $routeMatch = null;

    /**
     * Controller class name
     * @var string
     */
    protected $controllerClass = null;

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
     * @param  array   $routes
     * @return Router
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
     * @param  array  $controller
     * @throws Exception
     * @return Router
     */
    public function addRoute($route, array $controller)
    {
        if (!isset($controller['controller']) && !isset($controller['action'])) {
            throw new Exception("Error: The 'controller' and 'action' keys of the controller array must be set.");
        }
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
     * Add route params to be passed into a new controller instance
     *
     *     $router->addRouteParams('MyApp\Controller\IndexController', ['foo', 'bar']);
     *
     * @param  string $route
     * @param  mixed  $params
     * @return Router
     */
    public function addRouteParams($route, $params)
    {
        if (isset($this->routeParams[$route])) {
            if (is_array($params)) {
                $this->routeParams[$route] = array_merge($this->routeParams[$route], $params);
            } else {
                $this->routeParams[$route][] = $params;
            }
        } else {
            $this->routeParams[$route] = (!is_array($params)) ? [$params] : $params;
        }

        return $this;
    }

    /**
     * Add dispatch params to be passed into the dispatched method of the controller instance
     *
     *     $router->addDispatchParams('MyApp\Controller\IndexController->foo', ['bar', 'baz']);
     *
     * @param  string $dispatch
     * @param  mixed  $params
     * @return Router
     */
    public function addDispatchParams($dispatch, $params)
    {
        if (isset($this->dispatchParams[$dispatch])) {
            if (is_array($params)) {
                $this->dispatchParams[$dispatch] = array_merge($this->dispatchParams[$dispatch], $params);
            } else {
                $this->dispatchParams[$dispatch][] = $params;
            }
        } else {
            $this->dispatchParams[$dispatch] = (!is_array($params)) ? [$params] : $params;
        }

        return $this;
    }

    /**
     * Get the params assigned to the route
     *
     * @param  string $route
     * @return mixed
     */
    public function getRouterParams($route)
    {
        return (isset($this->routeParams[$route])) ? $this->routeParams[$route] : null;
    }

    /**
     * Get the params assigned to the dispatch
     *
     * @param  string $dispatch
     * @return mixed
     */
    public function getDispatchParams($dispatch)
    {
        return (isset($this->dispatchParams[$dispatch])) ? $this->dispatchParams[$dispatch] : null;
    }

    /**
     * Determine if a route is set for the current request
     *
     * @return boolean
     */
    public function hasRoute()
    {
        return $this->routeMatch->match($this->routes);
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
     * Get the current controller class name
     *
     * @return string
     */
    public function getControllerClass()
    {
        return $this->controllerClass;
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
     * Route to the correct controller
     *
     * @return void
     */
    public function route()
    {
        $this->routeMatch->match($this->routes);

        $controller = $this->routeMatch->getController();
        if (null === $controller) {
            $controller = $this->routeMatch->getDefaultController();
        }

        if (null !== $controller) {
            // If controller is a closure
            if ($controller instanceof \Closure) {
                $this->controller      = $controller;
                $this->controllerClass = $controller;
                if ($this->routeMatch->hasDispatchParams()) {
                    $this->addDispatchParams(
                        $this->routeMatch->getRoute(), $this->routeMatch->getDispatchParams()
                    );
                }
            // Else if controller is a class
            } else if (class_exists($controller)) {
                $this->controllerClass = $controller;

                // If parameters are found, add them for dispatch
                if ($this->routeMatch->hasRouteParams()) {
                    $this->addRouteParams($controller, $this->routeMatch->getRouteParams());
                }

                // If the controller has route parameters
                if (isset($this->routeParams[$controller])) {
                    $reflect          = new \ReflectionClass($controller);
                    $this->controller = $reflect->newInstanceArgs($this->routeParams[$controller]);
                // Else, just instantiate the controller
                } else {
                    $this->controller = new $controller();
                }

                if ($this->routeMatch->hasDispatchParams() && (null !== $this->routeMatch->getAction())) {
                    $this->addDispatchParams(
                        $this->routeMatch->getRoute(), $this->routeMatch->getDispatchParams()
                    );
                }
            }
        }
    }

}
