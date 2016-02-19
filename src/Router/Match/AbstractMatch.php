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
 * @package    Pop_Router
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.3
 */
abstract class AbstractMatch
{

    /**
     * Matched route
     * @var string
     */
    protected $route = null;

    /**
     * Wildcard route
     * @var array
     */
    protected $wildcards = [];

    /**
     * Controller class string name or closure function
     * @var string
     */
    protected $controller = null;

    /**
     * Action name for the controller class
     * @var string
     */
    protected $action = null;

    /**
     * Matched controller parameters
     * @var array
     */
    protected $controllerParams = [];

    /**
     * Matched dispatch parameters
     * @var array
     */
    protected $dispatchParams = [];

    /**
     * Default controller class string name or closure function
     * @var mixed
     */
    protected $defaultController = null;

    /**
     * Prepared routes
     * @var array
     */
    protected $routes = [];

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
     * Get the matched route
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Get the prepared routes
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Get the matched controller class name or closure function
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Get the matched action name
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Get the matched controller params
     *
     * @return array
     */
    public function getControllerParams()
    {
        return $this->controllerParams;
    }

    /**
     * Determine if there are matched controller params
     *
     * @return boolean
     */
    public function hasControllerParams()
    {
        return (count($this->controllerParams) > 0);
    }

    /**
     * Get the matched dispatch params
     *
     * @return array
     */
    public function getDispatchParams()
    {
        return $this->dispatchParams;
    }

    /**
     * Determine if there are matched dispatch params
     *
     * @return boolean
     */
    public function hasDispatchParams()
    {
        return (count($this->dispatchParams) > 0);
    }

    /**
     * Get the default controller class name or closure function
     *
     * @return mixed
     */
    public function getDefaultController()
    {
        return $this->defaultController;
    }

    /**
     * Constructor
     *
     * Instantiate the match object
     *
     * @return AbstractMatch
     */
    abstract public function __construct();

    /**
     * Match the route to the controller class
     *
     * @param  array   $routes
     * @return boolean
     */
    abstract public function match($routes);

    /**
     * Method to process if a route was not found
     *
     * @return void
     */
    abstract public function noRouteFound();

    /**
     * Prepare the routes
     *
     * @param  array $routes
     * @return void
     */
    abstract protected function prepareRoutes($routes);

    /**
     * Get parameters from the route string
     *
     * @param  string $route
     * @return array
     */
    abstract protected function getDispatchParamsFromRoute($route);

    /**
     * Process parameters from the route string
     *
     * @param  array $params
     * @param  array $routeParams
     * @return mixed
     */
    abstract protected function processDispatchParamsFromRoute($params, $routeParams);

    /**
     * Process matched parameters
     *
     * @param  array $matchedParams
     * @param  array $controller
     * @return mixed
     */
    abstract protected function processMatchedParams(array $matchedParams, array $controller);

}