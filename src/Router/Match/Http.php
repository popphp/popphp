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
 * Pop router HTTP match class
 *
 * @category   Pop
 * @package    Pop_Router
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
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
     *     /foo/:bar/:baz    - Route with 2 required params
     *     /foo/:bar[/:baz]  - Route with 1 required param and 1 optional param
     *     /foo/:bar/:baz*   - 1 required param, 1 required param that is a collection (array)
     *     /foo/:bar[/:baz*] - 1 required param, 1 optional param that is a collection (array)
     *
     *     - OR -
     *
     *     /foo/*   - Turns off strict matching and allows any route that starts with /foo/ to pass
     *
     * @param  array $routes
     * @return boolean
     */
    public function match($routes)
    {
        $this->prepareRoutes($routes);

        $wildcardRoutes = [];

        foreach ($this->routes as $route => $controller) {
            if (substr($route, -2) == '/*') {
                $wildcardRoutes[] = $route;
            } else if (($route != '') && (substr($this->segmentString, 0, strlen($route)) == $route)) {
                if (isset($controller['dispatchParams'])) {
                    $params        = $this->getDispatchParamsFromRoute($route);
                    $matchedParams = $this->processDispatchParamsFromRoute($params, $controller['dispatchParams']);
                    if ($matchedParams !== false) {
                        $this->route = $route;
                        $this->processMatchedParams($matchedParams, $controller);
                    } else if (isset($controller['dispatchParams'][0]) && ($controller['dispatchParams'][0]['name'] == 'controller') &&
                        isset($controller['dispatchParams'][1]) && ($controller['dispatchParams'][1]['name'] == 'action')) {
                        $this->route = $route;
                        $this->resetSegments();
                        $this->processMatchedParams([
                            'controller' => $this->segments[0],
                            'action'     => (isset($this->segments[1]) ? $this->segments[1] : 'index')
                        ], $controller);
                    }
                } else {
                    $suffix = substr($this->segmentString, strlen($route));
                    if (($suffix == '') || ($controller['wildcard'])) {
                        $this->route      = $route;
                        $this->controller = $controller['controller'];
                        if (isset($controller['action'])) {
                            $this->action = $controller['action'];
                        }
                        if (isset($controller['controllerParams'])) {
                            $this->controllerParams = (!is_array($controller['controllerParams'])) ?
                                [$controller['controllerParams']] : $controller['controllerParams'];
                        }
                    }
                }
            }
            if (isset($controller['default']) && ($controller['default']) && isset($controller['controller'])) {
                $this->defaultController = $controller['controller'];
            }
        }

        // Check any possible wildcard routes
        if ((null === $this->controller) && (count($wildcardRoutes) > 0)) {
            foreach ($wildcardRoutes as $wildcardRoute) {
                if (isset($this->routes[$wildcardRoute])) {
                    $route = substr($wildcardRoute, 0, -1);
                    if ((substr($this->segmentString, 0, strlen($route)) == $route) && isset($this->routes[$wildcardRoute]['controller'])) {
                        $this->route      = $wildcardRoute;
                        $this->controller = $this->routes[$wildcardRoute]['controller'];
                        if (isset($this->routes[$wildcardRoute]['action'])) {
                            $this->action = $this->routes[$wildcardRoute]['action'];
                        }
                    }
                }
            }
        }

        // If no route/controller defined yet, check for top level default route
        if ((null === $this->controller) && isset($this->routes[''])) {
            $route      = '';
            $controller = $this->routes[''];
            if (isset($controller['dispatchParams'])) {
                $params        = $this->getDispatchParamsFromRoute($route);
                $matchedParams = $this->processDispatchParamsFromRoute($params, $controller['dispatchParams']);
                if ($matchedParams !== false) {
                    $this->route = $route;
                    $this->processMatchedParams($matchedParams, $controller);
                } else if (isset($controller['dispatchParams'][0]) && ($controller['dispatchParams'][0]['name'] == 'controller') &&
                    isset($controller['dispatchParams'][1]) && ($controller['dispatchParams'][1]['name'] == 'action') && isset($this->segments[0])) {
                    $this->route = $route;
                    $this->processMatchedParams([
                        'controller' => $this->segments[0],
                        'action'     => (isset($this->segments[1]) ? $this->segments[1] : 'index')
                    ], $controller);
                }
            } else {
                $suffix = ($route != '') ? substr($this->segmentString, strlen($route)) : '';
                if (($suffix == '') || ($controller['wildcard'])) {
                    $this->route      = $route;
                    if (isset($controller['controller'])) {
                        $this->controller = $controller['controller'];
                    }
                    if (isset($controller['action'])) {
                        $this->action = $controller['action'];
                    }
                    if (isset($controller['controllerParams'])) {
                        $this->controllerParams = (!is_array($controller['controllerParams'])) ?
                            [$controller['controllerParams']] : $controller['controllerParams'];
                    }
                }
            }
        }

        // If no route or controller found yet, check for a wildcard/default route
        if ((null === $this->controller) && (count($this->wildcards) > 0)) {
            foreach ($this->wildcards as $wildcardRoute => $wildcardController) {
                $wc = substr($wildcardRoute, 0, -1);
                if ((substr($this->segmentString, 0, strlen($wc)) == $wc) && isset($wildcardController['controller'])) {
                    $this->route      = $wildcardRoute;
                    $this->controller = $wildcardController['controller'];
                    $controller       = $wildcardController;
                }
            }
            if ((null === $this->controller) && isset($this->wildcards['*']) && isset($this->wildcards['*']['controller'])) {
                $this->route      = '*';
                $this->controller = $this->wildcards['*']['controller'];
                $controller       = $this->wildcards['*'];
            }

            if (isset($controller['action'])) {
                $this->action = $controller['action'];
            }
            if (isset($controller['dispatchParams'])) {
                $params        = $this->getDispatchParamsFromRoute($this->route);
                $matchedParams = $this->processDispatchParamsFromRoute($params, $controller['dispatchParams']);
                if ($matchedParams !== false) {
                    $this->dispatchParams  = $matchedParams;
                }
            }
            if (isset($this->routes[$this->route]['controllerParams'])) {
                $this->controllerParams = (!is_array($this->routes[$this->route]['controllerParams'])) ?
                    [$this->routes[$this->route]['controllerParams']] : $this->routes[$this->route]['controllerParams'];
            }
        }

        return ((null !== $this->controller) || (null !== $this->defaultController));
    }

    /**
     * Method to process if a route was not found
     *
     * @return void
     */
    public function noRouteFound()
    {
        header('HTTP/1.1 404 Not Found');
        echo '<!DOCTYPE html>' . PHP_EOL;
        echo '<html>' . PHP_EOL;
        echo '    <head>' . PHP_EOL;
        echo '        <title>Page Not Found</title>' . PHP_EOL;
        echo '    </head>' . PHP_EOL;
        echo '<body>' . PHP_EOL;
        echo '    <h1>Page Not Found</h1>' . PHP_EOL;
        echo '</body>' . PHP_EOL;
        echo '</html>'. PHP_EOL;
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
            // Handle wildcard route
            if (($route != '*') && (substr($route, -1) == '*')) {
                $this->wildcards[$route] = $controller;
                $controller['wildcard']  = true;
            } else if ($route == '*') {
                $this->wildcards[$route] = $controller;
            } else {
                $controller['wildcard'] = false;
            }

            // Handle params
            if (strpos($route, '/:') !== false) {
                $controller['dispatchParams'] = [];
                $params = substr($route, (strpos($route,'/:') + 2));
                $route  = substr($route, 0, strpos($route,'/:'));
                if (strpos($route, '[') !== false) {
                    $route = substr($route, 0, strpos($route, '['));
                }

                $params = (strpos($params, '/:') !== false) ? explode('/:', $params) : [$params];

                foreach ($params as $param) {
                    if (strpos($param, '*') !== false) {
                        $collection = true;
                        $param      = str_replace('*', '', $param);
                    } else {
                        $collection = false;
                    }

                    if (strpos($param, ']') !== false) {
                        $paramName     = substr($param, 0, strpos($param, ']'));
                        $paramRequired = false;
                    } else if (strpos($param, '[') !== false) {
                        $paramName     = substr($param, 0, strpos($param, '['));
                        $paramRequired = true;
                    } else {
                        $paramName     = $param;
                        $paramRequired = true;
                    }

                    $controller['dispatchParams'][] = [
                        'name'       => $paramName,
                        'required'   => $paramRequired,
                        'collection' => $collection
                    ];
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
     * Get parameters from the route string
     *
     * @param  string $route
     * @return array
     */
    protected function getDispatchParamsFromRoute($route)
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
     * @param  array $dispatchParams
     * @return mixed
     */
    protected function processDispatchParamsFromRoute($params, $dispatchParams)
    {
        $result        = true;
        $hasCollection = false;
        $matchedParams = [];

        // If there's a direct match
        if (count($params) == count($dispatchParams)) {
            foreach ($params as $i => $param) {
                $matchedParams[$dispatchParams[$i]['name']] = ($dispatchParams[$i]['collection']) ? [$param] : $param;
            }
        // Else, loop through and verify the parameters
        } else if (count($params) < count($dispatchParams)) {
            foreach ($dispatchParams as $i => $param) {
                if (($param['required']) && !isset($params[$i])) {
                    $result = false;
                } else if (isset($params[$i])) {
                    $matchedParams[$param['name']] = ($param['collection']) ? [$params[$i]] : $params[$i];
                }
            }
        // Else, check for a collection of parameters
        } else if (count($params) > count($dispatchParams)) {
            foreach ($dispatchParams as $param) {
                if ($param['collection']) {
                    $hasCollection = true;
                }
            }
            if ($hasCollection) {
                $collectionName = null;
                foreach ($params as $i => $param) {
                    if (isset($dispatchParams[$i])) {
                        if ($dispatchParams[$i]['collection']) {
                            $collectionName = $dispatchParams[$i]['name'];
                            $matchedParams[$collectionName] = [$param];
                        } else {
                            $matchedParams[$dispatchParams[$i]['name']] = $param;
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

    /**
     * Process matched parameters
     *
     * @param  array $matchedParams
     * @param  array $controller
     * @return mixed
     */
    protected function processMatchedParams(array $matchedParams, array $controller)
    {
        $dynamicController = null;

        if (isset($matchedParams['controller'])) {
            $dynamicController = ucwords(str_replace(['-', '_'], [' ', ' '], $matchedParams['controller']));
            $dynamicController = str_replace(' ', '', $dynamicController) . 'Controller';
            if (isset($controller['prefix'])) {
                $dynamicController = $controller['prefix'] . $dynamicController;
            }
            if (class_exists($dynamicController, true)) {
                $this->controller = $dynamicController;
            } else {
                $dynamicController = null;
            }
            unset($matchedParams['controller']);
        } else if (isset($controller['controller'])) {
            $this->controller = $controller['controller'];
        }

        if (isset($matchedParams['action'])) {
            $action = lcfirst(ucwords(str_replace(['-', '_'], [' ', ' '], $matchedParams['action'])));
            $action = str_replace(' ', '', $action);
            $this->action = $action;
            unset($matchedParams['action']);
        } else if (null !== $dynamicController) {
            if (method_exists($dynamicController, 'index')) {
                $this->action = 'index';
            }
        } else if (isset($controller['action'])) {
            $this->action = $controller['action'];
        }

        $this->dispatchParams = $matchedParams;
        if (isset($controller['controllerParams'])) {
            $this->controllerParams = (!is_array($controller['controllerParams'])) ?
                [$controller['controllerParams']] : $controller['controllerParams'];
        }
    }

    /**
     * Reset URL segments based on route
     *
     * @return void
     */
    protected function resetSegments()
    {
        if (substr($this->segmentString, 0, strlen($this->route)) == $this->route) {
            $this->segmentString = substr($this->segmentString, strlen($this->route));
        }

        // Trim trailing slash, if present
        if (substr($this->segmentString, -1) == '/') {
            $this->segmentString = substr($this->segmentString, 0, -1);
            $trailingSlash = '/';
        } else {
            $trailingSlash = null;
        }

        if ($this->segmentString == '') {
            $this->segments      = ['index'];
            $this->segmentString = '/';
        } else {
            $this->segments      = explode('/', substr($this->segmentString, 1));
            $this->segmentString = '/' . implode('/', $this->segments) . $trailingSlash;
        }
    }

}