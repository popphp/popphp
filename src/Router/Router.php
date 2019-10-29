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
namespace Pop\Router;

/**
 * Pop router class
 *
 * @category   Pop
 * @package    Pop\Router
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.3.2
 */
class Router
{

    /**
     * Route match object
     * @var Match\MatchInterface
     */
    protected $routeMatch = null;

    /**
     * Controller object
     * @var mixed
     */
    protected $controller = null;

    /**
     * Action
     * @var mixed
     */
    protected $action = null;

    /**
     * Controller class
     * @var string
     */
    protected $controllerClass = null;

    /**
     * Constructor
     *
     * Instantiate the router object
     *
     * @param  array               $routes
     * @param  Match\AbstractMatch $match
     */
    public function __construct(array $routes = null, Match\AbstractMatch $match = null)
    {
        if (null !== $match) {
            $this->routeMatch = $match;
        } else {
            $this->routeMatch = ((stripos(php_sapi_name(), 'cli') !== false) &&
                (stripos(php_sapi_name(), 'server') === false)) ?
                new Match\Cli() : new Match\Http();
        }

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
        $this->routeMatch->addRoute($route, $controller);
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
        $this->routeMatch->addRoutes($routes);
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
        $this->routeMatch->addControllerParams($controller, $params);
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
        $this->routeMatch->appendControllerParams($controller, $params);
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
        return $this->routeMatch->getControllerParams($controller);
    }

    /**
     * Determine if the controller has params
     *
     * @param  string $controller
     * @return boolean
     */
    public function hasControllerParams($controller)
    {
        return $this->routeMatch->hasControllerParams($controller);
    }

    /**
     * Remove controller params
     *
     * @param  string $controller
     * @return Router
     */
    public function removeControllerParams($controller)
    {
        $this->routeMatch->removeControllerParams($controller);
        return $this;
    }

    /**
     * Get routes
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routeMatch->getRoutes();
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
     * Get the params discovered from the route
     *
     * @return mixed
     */
    public function getRouteParams()
    {
        return $this->routeMatch->getRouteParams();
    }

    /**
     * Determine if the route has params
     *
     * @return boolean
     */
    public function hasRouteParams()
    {
        return $this->routeMatch->hasRouteParams();
    }

    /**
     * Get the current controller object
     *
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Determine if the router has a controller
     *
     * @return boolean
     */
    public function hasController()
    {
        return (null !== $this->controller);
    }

    /**
     * Get the action
     *
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Determine if the router has an action
     *
     * @return boolean
     */
    public function hasAction()
    {
        return (null !== $this->action);
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
     * Prepare routes
     *
     * @return Router
     */
    public function prepare()
    {
        $this->routeMatch->prepare();
        return $this;
    }

    /**
     * Route to the correct controller
     *
     * @param  string $forceRoute
     * @throws Exception
     * @throws \ReflectionException
     * @return void
     */
    public function route($forceRoute = null)
    {
        if ($this->routeMatch->match($forceRoute)) {
            if ($this->routeMatch->hasController()) {
                $controller = $this->routeMatch->getController();

                if ($controller instanceof \Closure) {
                    $this->controllerClass = 'Closure';
                    $this->controller      = $controller;
                } else if (class_exists($controller)) {
                    $this->controllerClass = $controller;
                    $controllerParams      = null;

                    if ($this->routeMatch->hasControllerParams($controller)) {
                        $controllerParams = $this->routeMatch->getControllerParams($controller);
                    } else if ($this->routeMatch->hasControllerParams('*')) {
                        $controllerParams = $this->routeMatch->getControllerParams('*');
                    }

                    if (null !== $controllerParams) {
                        $this->controller = (new \ReflectionClass($controller))->newInstanceArgs($controllerParams);
                    } else {
                        $this->controller = new $controller();
                    }

                    if (!($this->controller instanceof \Pop\Controller\ControllerInterface)) {
                        throw new Exception('Error: The controller must be an instance of Pop\Controller\Interface');
                    }

                    $action       = $this->routeMatch->getAction();
                    $this->action = ((null === $action) && ($this->routeMatch->isDynamicRoute())) ? 'index' : $action;
                }
            }
        }
    }

    /**
     * Method to process if a route was not found
     *
     * @param  boolean $exit
     * @return void
     */
    public function noRouteFound($exit = true)
    {
        $this->routeMatch->noRouteFound($exit);
    }

}
