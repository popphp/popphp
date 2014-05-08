<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Mvc
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mvc;

/**
 * Mvc router class
 *
 * @category   Pop
 * @package    Pop_Mvc
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Router
{

    /**
     * Project object
     * @var \Pop\Project\Project
     */
    protected $project = null;

    /**
     * Request object
     * @var \Pop\Http\Request
     */
    protected $request = null;

    /**
     * Current controller class name string
     * @var string
     */
    protected $controllerClass = null;

    /**
     * Current controller object
     * @var \Pop\Mvc\Controller
     */
    protected $controller = null;

    /**
     * Array of available controllers class names
     * @var array
     */
    protected $controllers = array();

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
     * @param  array             $controllers
     * @param  \Pop\Http\Request $request
     * @return \Pop\Mvc\Router
     */
    public function __construct(array $controllers, \Pop\Http\Request $request = null)
    {
        $this->request = (null !== $request) ? $request : new \Pop\Http\Request();
        $this->controllers = $controllers;
    }

    /**
     * Create a Pop\Mvc\Router object
     *
     * @param  array             $controllers
     * @param  \Pop\Http\Request $request
     * @return \Pop\Mvc\Router
     */
    public static function factory(array $controllers, \Pop\Http\Request $request = null)
    {
        return new self($controllers, $request);
    }

    /**
     * Add controllers
     *
     * @param  array $controller
     * @return \Pop\Mvc\Router
     */
    public function addControllers(array $controller)
    {
        foreach ($controller as $key => $value) {
            if (!isset($this->controllers[$key])) {
                $this->controllers[$key] = $value;
            } else {
                if (is_array($this->controllers[$key]) && is_array($value)) {
                    $this->controllers[$key] = array_merge_recursive($this->controllers[$key], $value);
                } else {
                    $this->controllers[$key] = $value;
                }
            }
        }

        return $this;
    }

    /**
     * Get the project object
     *
     * @return \Pop\Project\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Get the request object
     *
     * @return \Pop\Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the current controller object
     *
     * @return \Pop\Mvc\Controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Get the project object (shorthand alias)
     *
     * @return \Pop\Project\Project
     */
    public function project()
    {
        return $this->project;
    }

    /**
     * Get the request object (shorthand alias)
     *
     * @return \Pop\Http\Request
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * Get the current controller object (shorthand alias)
     *
     * @return \Pop\Mvc\Controller
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
     * @param  \Pop\Project\Project $project
     * @return void
     */
    public function route(\Pop\Project\Project $project = null)
    {
        if (null !== $project) {
            $this->project = $project;
        }

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
            $realUri = substr($this->request->getFullUri(), strlen($this->request->getBasePath() . $this->basePath));

            // Create the controller object
            $this->controller = new $this->controllerClass(
                $this->request->setRequestUri($realUri, $realBasePath),
                null,
                $this->project
            );
            // Trigger any route events
            if (null !== $this->project) {
                if (null !== $this->project->getEventManager()->get('route')) {
                    $this->project->log('[Event] Route', time(), \Pop\Log\Logger::NOTICE);
                }
                $this->project->getEventManager()->trigger('route', array('router' => $this));
            }
        // Else, trigger any route error events
        } else {
            if (null !== $this->project) {
                if (null !== $this->project->getEventManager()->get('route.error')) {
                    $this->project->log('[Event] Route Error', time(), \Pop\Log\Logger::NOTICE);
                }
                $this->project->getEventManager()->trigger('route.error', array('router' => $this));
            }
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
