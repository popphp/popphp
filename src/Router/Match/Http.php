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
     * Match the route to the controller class. Possible matches are:
     *
     *     /list/:album/:id                      - All 3 params are required
     *     /list[/:album][/:id]                  - First param required, last two are optional
     *     /list/:album[/:id]                    - First two params required, last one is optional
     *     /list/:action/:artist[/:album][/:id]  - Two required, two optional
     *     /list/:album/:id*                     - One required param, one required param that is a collection (array)
     *     /list/:album[/:id*]                   - One required param, one optional param that is a collection (array)
     *
     * @param  array $routes
     * @return boolean
     */
    public function match($routes)
    {
        $this->prepareRoutes($routes);

        foreach ($this->routes as $route => $controller) {
            if ((substr($this->segmentString, 0, strlen($route)) == $route) &&
                isset($controller['controller']) && isset($controller['action'])) {
                if (isset($controller['params'])) {
                    $params        = $this->getParamsFromRoute($route);
                    $matchedParams = $this->processParamsFromRoute($params, $controller['params']);
                    if ($matchedParams != false) {
                        $this->controller = $controller['controller'];
                        $this->action     = $controller['action'];
                        $this->params     = $this->processParamsFromRoute($params, $controller['params']);
                    }
                } else {
                    $suffix = substr($this->segmentString, strlen($route));
                    if ($suffix == '') {
                        $this->controller = $controller['controller'];
                        $this->action     = $controller['action'];
                    }
                }
            }
            if (isset($controller['default']) && ($controller['default']) && isset($controller['controller'])) {
                $this->defaultController = $controller['controller'];
            }
        }

        // If no route or controller found, check for a wildcard/default route
        if ((null === $this->controller) && array_key_exists('*', $this->routes) &&
            isset($this->routes['*']['controller']) && isset($this->routes['*']['action'])) {
            $this->controller = $this->routes['*']['controller'];
            $this->action     = $this->routes['*']['action'];
            if (isset($controller['params'])) {
                $params        = $this->getParamsFromRoute('*');
                $matchedParams = $this->processParamsFromRoute($params, $controller['params']);
                if ($matchedParams != false) {
                    $this->params     = $this->processParamsFromRoute($params, $controller['params']);
                }
            }
        }

        return ((null !== $this->controller) && (null !== $this->action));
    }

    /**
     * Prepare the routes
     *
     * @param  array $routes
     * @return void
     */
    protected function prepareRoutes($routes)
    {
        foreach ($routes as $route => $controller) {
            $hasRequiredTrailingSlash = false;
            $hasOptionalTrailingSlash = false;

            // Handle required trailing slash
            if (substr($route, -1) == '/') {
                $route = substr($route, 0, -1);
                $hasRequiredTrailingSlash = true;
            }
            // Handle optional trailing slash
            if (substr($route, -3) == '[/]') {
                $route = substr($route, 0, -3);
                $hasOptionalTrailingSlash = true;
            }

            // Handle params
            if (strpos($route, '/:') !== false) {
                $controller['params'] = [];
                $params = substr($route, (strpos($route,'/:') + 2));
                $route = substr($route, 0, strpos($route,'/:'));
                if (strpos($route, '[') !== false) {
                    $route = substr($route, 0, strpos($route, '['));
                }

                if (strpos($params, '/:') !== false) {
                    $params = explode('/:', $params);
                } else {
                    $params = [$params];
                }
                foreach ($params as $param) {
                    if (strpos($param, '*') !== false) {
                        $collection = true;
                        $param = str_replace('*', '', $param);
                    } else {
                        $collection = false;
                    }
                    if (strpos($param, ']') !== false) {
                        $controller['params'][] = [
                            'name'       => substr($param, 0, strpos($param, ']')),
                            'required'   => false,
                            'collection' => $collection
                        ];
                    } else if (strpos($param, '[') !== false) {
                        $controller['params'][] = [
                            'name'     => substr($param, 0, strpos($param, '[')),
                            'required' => true,
                            'collection' => $collection
                        ];
                    } else {
                        $controller['params'][] = [
                            'name'     => $param,
                            'required' => true,
                            'collection' => $collection
                        ];
                    }
                }
            }

            if ($hasRequiredTrailingSlash) {
                $this->routes[$route . '/'] = $controller;
            } else if ($hasOptionalTrailingSlash) {
                $this->routes[$route] = $controller;
                $this->routes[$route . '/'] = $controller;
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

    /**
     * Get parameters from the route string
     *
     * @param  string $route
     * @return array
     */
    protected function getParamsFromRoute($route)
    {
        $params = substr($this->segmentString, strlen($route));
        if (substr($params, 0, 1) == '/') {
            $params = substr($params, 1);
        }
        if (substr($params, -1) == '/') {
            $params = substr($params, 0, -1);
        }
        $params = explode('/', $params);
        if ((count($params) == 1) && ($params[0] == '')) {
            $params = [];
        }
        return $params;
    }

    /**
     * Process parameters from the route string
     *
     * @param  array $params
     * @param  array $routeParams
     * @return mixed
     */
    protected function processParamsFromRoute($params, $routeParams)
    {
        $result        = true;
        $hasCollection = false;
        $matchedParams = [];

        // If there's a direct match
        if (count($params) == count($routeParams)) {
            foreach ($params as $i => $param) {
                $matchedParams[$routeParams[$i]['name']] = ($routeParams[$i]['collection']) ? [$param] : $param;
            }
        // Else, loop through and verify the parameters
        } else if (count($params) < count($routeParams)) {
            foreach ($routeParams as $i => $param) {
                if (($param['required']) && !isset($params[$i])) {
                    $result = false;
                } else if (isset($params[$i])) {
                    $matchedParams[$param['name']] = ($param['collection']) ? [$params[$i]] : $params[$i];
                }
            }
        // Else, check for a collection of parameters
        } else if (count($params) > count($routeParams)) {
            foreach ($routeParams as $i => $param) {
                if ($param['collection']) {
                    $hasCollection = true;
                }
            }
            if ($hasCollection) {
                $collectionName = null;
                foreach ($params as $i => $param) {
                    if (isset($routeParams[$i])) {
                        if ($routeParams[$i]['collection']) {
                            $collectionName = $routeParams[$i]['name'];
                            $matchedParams[$collectionName] = [$param];
                        } else {
                            $matchedParams[$routeParams[$i]['name']] = $param;
                        }
                    } else if ((null !== $collectionName) && isset($matchedParams[$collectionName])) {
                        $matchedParams[$collectionName][] = $param;
                    }
                }
            } else {
                $result = false;
            }
        }
        return ($result) ? $matchedParams : false;
    }

}