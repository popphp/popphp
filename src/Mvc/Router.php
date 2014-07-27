<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Mvc
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mvc;

use Pop\Http\Request;

/**
 * Mvc router class
 *
 * @category   Pop
 * @package    Pop_Mvc
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Router
{

    /**
     * Request object
     * @var Request
     */
    protected $request = null;

    /**
     * Current controller class name string
     * @var string
     */
    protected $controllerClass = null;

    /**
     * Current controller object
     * @var Controller
     */
    protected $controller = null;

    /**
     * Array of available controllers class names
     * @var array
     */
    protected $controllers = [];

    /**
     * Base path URI
     * @var string
     */
    protected $basePath = null;

    /**
     * Constructor
     *
     * Instantiate the router object
     *
     * @param  array   $controllers
     * @param  Request $request
     * @return Router
     */
    public function __construct(array $controllers = [], Request $request = null)
    {
        $this->request     = (null !== $request) ? $request : new Request();
        $this->controllers = $controllers;
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
     * Get the request object
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the current controller object
     *
     * @return Controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Get the request object (shorthand alias)
     *
     * @return Request
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * Get the current controller object (shorthand alias)
     *
     * @return Controller
     */
    public function controller()
    {
        return $this->controller;
    }

    /**
     * Get the current controller class name string
     *
     * @return string
     */
    public function getControllerClass()
    {
        return $this->controllerClass;
    }

    /**
     * Get a controller class name string, if available
     *
     * @param  string $controller
     * @return string
     */
    public function getControllerName($controller)
    {
        return (isset($this->controllers[$controller])) ? $this->controllers[$controller] : null;
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
     * Get action from request within the current controller
     *
     * @return string
     */
    public function getAction()
    {
        $action = null;

        if ((null !== $this->controller) && (null !== $this->controller->getRequest())) {
            // If the URI is root '/', then set to 'index'
            if ($this->controller->getRequest()->getRequestUri() == '/') {
                $action = 'index';
            // Else, figure out the action from the path stems
            } else if ($this->controller->getRequest()->getPath(0) != '') {
                $path = $this->controller->getRequest()->getPath();
                $basePath = explode('/', substr($this->basePath, 1));
                $pathDiff = array_values(array_diff($path, $basePath));
                if (isset($pathDiff[0])) {
                    $realBasePath = (substr($this->controller->getRequest()->getBasePath(), -1) == '/') ?
                        substr($this->controller->getRequest()->getBasePath(), 0, -1) : $this->controller->getRequest()->getBasePath();
                    $this->controller->getRequest()->setRequestUri('/' . implode('/', $pathDiff), $realBasePath);
                    $action = $pathDiff[0];
                }
            }
        }

        return $action;
    }

    /**
     * Route to the correct controller
     *
     * @return void
     */
    public function route()
    {
        // If the request isn't root '/', traverse the URI path
        if ($this->request->getPath(0) != '') {
            $this->controllerClass = $this->traverseControllers($this->controllers);
        // Else, use root '/'
        } else {
            $this->controllerClass = (isset($this->controllers['/'])) ? $this->controllers['/'] : null;
        }

        // If found, create the controller object
        if ((null !== $this->controllerClass) && class_exists($this->controllerClass)) {
            // Push the real base path and URI into the request object
            $realBasePath = $this->request->getBasePath() . $this->basePath;
            $realUri      = substr($this->request->getFullRequestUri(), strlen($this->request->getBasePath() . $this->basePath));

            // Create the controller object
            $this->controller = new $this->controllerClass(
                $this->request->setRequestUri($realUri, $realBasePath)
            );
        }
    }

    /**
     * Traverse the controllers based on the path
     *
     * @param  array $controllers
     * @param  int $depth
     * @return string
     */
    protected function traverseControllers($controllers, $depth = 0)
    {
        $next = $depth + 1;

        // If the path stem exists in the controllers, the traverse it
        if (($this->request->getPath($depth) != '') &&
            (array_key_exists('/' . $this->request->getPath($depth), $controllers))) {
            $this->basePath .= '/' . $this->request->getPath($depth);
            // If the next level is an array, traverse it
            if (is_array($controllers['/' . $this->request->getPath($depth)])) {
                return $this->traverseControllers($controllers['/' . $this->request->getPath($depth)], $next);
            // Else, return the controller class name
            } else {
                return (isset($controllers['/' . $this->request->getPath($depth)])) ?
                    $controllers['/' . $this->request->getPath($depth)] : null;
            }
        // Else check for the root '/' path
        } else if (array_key_exists('/', $controllers)) {
            $this->basePath .= '/';
            // If the next level is an array, traverse it
            if (is_array($controllers['/'])) {
                return $this->traverseControllers($controllers['/'], $next);
            // Else, return the controller class name
            } else {
                return (isset($controllers['/'])) ? $controllers['/'] : null;
            }
        }
    }

}
