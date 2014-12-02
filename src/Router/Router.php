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
     * Current controller object
     * @var \Pop\Controller\ControllerInterface
     */
    protected $controller = null;

    /**
     * Array of available controllers class names
     * @var array
     */
    protected $controllers = [];

    /**
     * Route match object
     * @var Match\MatchInterface
     */
    protected $routeMatch = null;

    /**
     * Constructor
     *
     * Instantiate the router object
     *
     * @param  array $controllers
     * @return Router
     */
    public function __construct(array $controllers = null)
    {
        $this->routeMatch = (stripos(php_sapi_name(), 'cli') !== false) ? new Match\Cli() : new Match\Http();
        if (null !== $controllers) {
            $this->addControllers($controllers);
        }
    }

    /**
     * Add a controller route
     *
     * @param  string $route
     * @param  string $controller
     * @return Router
     */
    public function addController($route, $controller)
    {
        if (!isset($this->controllers[$route])) {
            $this->controllers[$route] = $controller;
        } else {
            if (is_array($this->controllers[$route]) && is_array($controller)) {
                $this->controllers[$route] = array_merge_recursive($this->controllers[$route], $controller);
            } else {
                $this->controllers[$route] = $controller;
            }
        }

        return $this;
    }

    /**
     * Add multiple controller routes
     *
     * @param  array $controllers
     * @return Router
     */
    public function addControllers(array $controllers)
    {
        foreach ($controllers as $route => $controller) {
            $this->addController($route, $controller);
        }

        return $this;
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
     * Get array of controller class names
     *
     * @return array
     */
    public function getControllers()
    {
        return $this->controllers;
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
     * Route to the correct controller
     *
     * @return void
     */
    public function route()
    {
        $controllerClass = $this->routeMatch->match($this->controllers);

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
