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
namespace Pop\Router\Match;

/**
 * Pop router HTTP match class
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Http extends AbstractMatch
{

    /**
     * Base path
     * @var string
     */
    protected $basePath = null;

    /**
     * Array of segments
     * @var array
     */
    protected $segments = [];

    /**
     * Segment string
     * @var array
     */
    protected $segmentString = null;

    /**
     * Constructor
     *
     * Instantiate the HTTP match object
     *
     * @return Http
     */
    public function __construct()
    {
        $this->setBasePath();
        $this->setSegments();
    }

    /**
     * Set the base path
     *
     * @return Http
     */
    public function setBasePath()
    {
        $basePath       = str_replace([realpath($_SERVER['DOCUMENT_ROOT']), '\\'], ['', '/'], realpath(getcwd()));
        $this->basePath = (!empty($basePath) ? $basePath : '');
        return $this;
    }

    /**
     * Set the route segments
     *
     * @return Http
     */
    public function setSegments()
    {
        $path = ($this->basePath != '') ?
            substr($_SERVER['REQUEST_URI'], strlen($this->basePath)) :
            $_SERVER['REQUEST_URI'];

        // Trim query string, if present
        if (strpos($path, '?')) {
            $path = substr($path, 0, strpos($path, '?'));
        }

        // Trim trailing slash, if present
        if (substr($path, -1) == '/') {
            $path = substr($path, 0, -1);
            $trailingSlash = '/';
        } else {
            $trailingSlash = null;
        }

        if ($path == '') {
            $this->segments      = ['index'];
            $this->segmentString = '/';
        } else {
            $this->segments      = explode('/', substr($path, 1));
            $this->segmentString = '/' . implode('/', $this->segments) . $trailingSlash;
        }

        return $this;
    }

    /**
     * Get the base path
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Get the route segments
     *
     * @return array
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * Get the route segment string
     *
     * @return string
     */
    public function getSegmentString()
    {
        return $this->segmentString;
    }

    /**
     * Match the route to the controller class
     *
     * @param  array   $routes
     * @param  boolean $strict
     * @return boolean
     */
    public function match($routes, $strict = false)
    {
        $this->prepareRoutes($routes);

        if (($this->segmentString == '/') && isset($this->routes['/'])) {
            if (isset($this->routes['/']['controller']) && isset($this->routes['/']['action'])) {
                $this->controller = $this->routes['/']['controller'];
                $this->action     = $this->routes['/']['action'];
            }
            if (isset($this->routes['/']['default']) && ($this->routes['/']['default']) && isset($this->routes['/']['controller'])) {
                $this->defaultController = $this->routes['/']['controller'];
            }
        } else {
            foreach ($this->routes as $route => $controller) {
                if (($route != '/') && (substr($this->segmentString, 0, strlen($route)) == $route)) {
                    if (($strict) && ($this->segmentString == $route)) {
                        $this->controller = $controller['controller'];
                        $this->action     = $controller['action'];
                    } else if (!$strict) {
                        $suffix = (substr($route, -1) == '/') ?
                            substr($this->segmentString, (strlen($route) - 1)) : substr($this->segmentString, strlen($route));
                        if ((($suffix == '') || (substr($suffix, 0, 1) == '/')) &&
                            (isset($controller['controller']) && isset($controller['action']))) {
                            $this->controller = $controller['controller'];
                            $this->action     = $controller['action'];
                        }
                    }
                }
                if (isset($controller['default']) && ($controller['default']) && isset($controller['controller'])) {
                    $this->defaultController = $controller['controller'];
                }
            }
        }

        return ((null !== $this->controller) && (null !== $this->action));
    }

    protected function prepareRoutes($routes)
    {
        foreach ($routes as $route => $controller) {
            // Handle optional trailing slash
            if (substr($route, -3) == '[/]') {
                $this->routes[substr($route, 0, -3)]       = $controller;
                $this->routes[substr($route, 0, -3) . '/'] = $controller;
            } else if (strpos($route, '[/:') !== false) {
                $controller['optional'] = $this->getOptionalParams($route);
                $route = substr($route, 0, strpos($route, '[/:'));
                $this->routes[$route] = $controller;
            // Handle required arguments
            } else if (strpos($route, '/:') !== false) {
                $controller['required'] = $this->getRequiredParams($route);
                $route = substr($route, 0, strpos($route, '/:'));
                $this->routes[$route] = $controller;
            // Handle optional arguments
            } else {
                $this->routes[$route] = $controller;
            }
        }
    }

    /**
     * Get required parameters from the route
     *
     * @param  string $route
     * @return array
     */
    protected function getRequiredParams($route)
    {
        $route = substr($route, (strpos($route, '/:') + 2));
        return explode('/:', $route);
    }

    /**
     * Get optional parameters from the route
     *
     * @param  string $route
     * @return array
     */
    protected function getOptionalParams($route)
    {
        $route = substr($route, (strpos($route, '[/:') + 3));
        $route = substr($route, 0, -1);
        return explode('][/:', $route);
    }

}