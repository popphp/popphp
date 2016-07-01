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
 * @package    Pop_Router
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.1.0
 */
class Router implements RouterInterface
{

    /**
     * Array of available routes
     * @var array
     */
    protected $routes = [];

    /**
     * Array of controller parameters
     * @var array
     */
    protected $controllerParams = [];

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
     * @param  mixed  $controller
     * @return Router
     */
    public function addRoute($route, $controller)
    {
        if (is_callable($controller)) {
            $controller = ['controller' => $controller];
        }

        // If base url exists
        if (!isset($controller['controller'])) {
            // If dynamic routing
            if ((strpos($route, ':controller') !== false) || (strpos($route, '<controller') !== false)) {
                $this->routes[$route] = $controller;
            // Else, nested routing
            } else {
                $value = reset($controller);
                if (isset($value['controller'])) {
                    foreach ($controller as $r => $c) {
                        if ($route != '') {
                            $sep = ($this->isHttp()) ? '/' : ' ';
                            if ((substr($r, 0, 1) == $sep) || (substr($r, 0, 1) == '[')) {
                                $r = $route . $r;
                            } else {
                                $r = $route . $sep . $r;
                            }
                        }
                        $this->routes[$r] = $c;
                    }
                }
            }
        // Else, just add routes
        } else {
            $this->routes[$route] = $controller;
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
     * Add controller params to be passed into a new controller instance
     *
     *     $router->addControllerParams('MyApp\Controller\IndexController', ['foo', 'bar']);
     *
     * @param  string $controller
     * @param  mixed  $params
     * @return Router
     */
    public function addControllerParams($controller, $params = null)
    {
        // Clear parameters
        if ((null === $params) && isset($this->controllerParams[$controller])) {
            $this->controllerParams[$controller] = [];
        } else {
            if (!is_array($params)) {
                $params = [$params];
            }

            // Append parameters to any that exist
            if (isset($params['append']) && ($params['append'])) {
                unset($params['append']);
                if (isset($this->controllerParams[$controller])) {
                    $this->controllerParams[$controller] = array_merge($this->controllerParams[$controller], $params);
                } else if (isset($this->controllerParams['*'])) {
                    $this->controllerParams['*'] = array_merge($this->controllerParams['*'], $params);
                } else {
                    $this->controllerParams[$controller] = $params;
                }
            // Override existing parameters
            } else {
                $this->controllerParams[$controller] = $params;
            }
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
        return $this->routeMatch->hasRoute();
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
     * Execute the route match
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
                if ($this->routeMatch->hasControllerParams()) {
                    $this->addControllerParams($controller, $this->routeMatch->getControllerParams());
                }

                $controllerParams = [];
                if (isset($this->controllerParams[$controller])) {
                    $controllerParams = $this->controllerParams[$controller];
                } else if (isset($this->controllerParams['*'])) {
                    $controllerParams = $this->controllerParams['*'];
                }

                // If the controller has parameters
                if (is_array($controllerParams) && (count($controllerParams) > 0)) {
                    $reflect          = new \ReflectionClass($controller);
                    $this->controller = $reflect->newInstanceArgs($controllerParams);
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
