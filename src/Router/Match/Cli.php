<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp
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
 * Pop router CLI match class
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Cli extends AbstractMatch
{

    /**
     * Array of arguments
     * @var array
     */
    protected $arguments = [];

    /**
     * Argument string
     * @var string
     */
    protected $argumentString = null;

    /**
     * Constructor
     *
     * Instantiate the CLI match object
     *
     * @return Cli
     */
    public function __construct()
    {
        $this->setArguments();
    }

    /**
     * Set the route arguments
     *
     * @return Cli
     */
    public function setArguments()
    {
        global $argv;

        // Trim the script name out of the arguments array
        array_shift($argv);

        $this->arguments      = $argv;
        $this->argumentString = implode(' ', $argv);

        return $this;
    }

    /**
     * Get the route arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Get the route argument string
     *
     * @return string
     */
    public function getArgumentString()
    {
        return $this->argumentString;
    }

    /**
     * Match the route to the controller class. Possible matches are:
     *
     *     foo bar
     *     foo [bar|baz]
     *     foo bar -o1 [-o2]
     *     foo bar --option1|-o1 [--option2|-o2]
     *     foo bar <name> [<email>]
     *     foo bar --name= [--email=]
     *
     *     - OR -
     *
     *     foo*   - Turns off strict matching and allows any route that starts with 'foo' to pass
     *
     * @param  array $routes
     * @return boolean
     */
    public function match($routes)
    {
        $this->prepareRoutes($routes);

        foreach ($this->routes as $route => $controller) {
            if (preg_match($route, $this->argumentString) && isset($controller['controller'])) {
                if (isset($controller['dispatchParams'])) {
                    $params        = $this->getDispatchParamsFromRoute($route);
                    $matchedParams = $this->processDispatchParamsFromRoute($params, $controller['dispatchParams']);
                    if ($matchedParams !== false) {
                        $this->controller = $controller['controller'];
                        if (isset($controller['action'])) {
                            $this->action = $controller['action'];
                        }
                        $this->dispatchParams = $matchedParams;
                        if (isset($controller['routeParams'])) {
                            $this->routeParams = (!is_array($controller['routeParams'])) ?
                                [$controller['routeParams']] : $controller['routeParams'];
                        }
                    }
                } else {
                    $suffix = substr($this->argumentString, strlen($route));
                    if (($suffix == '') || ($controller['wildcard'])) {
                        $this->controller = $controller['controller'];
                        if (isset($controller['action'])) {
                            $this->action = $controller['action'];
                        }
                        if (isset($controller['routeParams'])) {
                            $this->routeParams = (!is_array($controller['routeParams'])) ?
                                [$controller['routeParams']] : $controller['routeParams'];
                        }
                    }
                }
            }
            if (isset($controller['default']) && ($controller['default']) && isset($controller['controller'])) {
                $this->defaultController = $controller['controller'];
            }
        }

        // If no route or controller found, check for a wildcard/default route
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
            if (isset($this->routes[$this->route]['routeParams'])) {
                $this->routeParams = (!is_array($this->routes[$this->route]['routeParams'])) ?
                    [$this->routes[$this->route]['routeParams']] : $this->routes[$this->route]['routeParams'];
            }
        }

        return ((null !== $this->controller) || (null !== $this->defaultController));
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
            $dash     = strpos($route, '-');
            $optDash  = strpos($route, '[-');
            $angle    = strpos($route, '<');
            $optAngle = strpos($route, '[<');

            $match = [];
            if ($dash !== false) {
                $match[] = $dash;
            }
            if ($optDash !== false) {
                $match[] = $optDash;
            }
            if ($angle !== false) {
                $match[] = $angle;
            }
            if ($optAngle !== false) {
                $match[] = $optAngle;
            }

            if (count($match) > 0) {
                $params = substr($route, min($match));
                $route  = substr($route, 0, min($match) - 1);
                $params = (strpos($params, ' ') !== false) ? explode(' ', $params) : [$params];

                $controller['dispatchParams'] = [];
                foreach ($params as $param) {
                    if (strpos($param, '[') !== false) {
                        $param = substr($param, 1);
                        $param = substr($param, 0, -1);
                        $controller['dispatchParams'][] = [
                            'name'       => $param,
                            'required'   => false
                        ];
                    } else {
                        $controller['dispatchParams'][] = [
                            'name'       => $param,
                            'required'   => true
                        ];
                    }
                }
            }

            // Handle optional literals, create regex for route matching
            if (strpos($route, '[') !== false) {
                $route = '/' . str_replace(['[', ']', '|', ') '], ['(', ')','\s|', '\s)?'], $route) . '(.*)/';
            } else {
                $route = '/' . $route . '(.*)/';
            }

            $this->routes[$route] = $controller;
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
        $params = [];

        foreach ($this->arguments as $arg) {
            if (strpos($route, $arg) === false) {
                $params[] = $arg;
            }
        }

        return $params;
    }

    /**
     * Process parameters from the route string
     *
     * @param  array  $params
     * @param  array  $routeParams
     * @return mixed
     */
    protected function processDispatchParamsFromRoute($params, $routeParams)
    {
        $result        = true;
        $matchedParams = [];
        $offset        = 0;

        foreach ($routeParams as $i => $param) {
            if (($param['required']) && !isset($params[$i])) {
                $result = false;
            } else if (isset($params[$i])) {
                // If value
                if (substr($param['name'], 0, 1) == '<') {
                    $p = substr($param['name'], 1);
                    $p = substr($p, 0, -1);
                    $matchedParams[$p] = $params[$i];
                // Option with value
                } else if (substr($param['name'], -1) == '=') {
                    foreach ($params as $value) {
                        if (substr($value, 0, strlen($param['name'])) == $param['name']) {
                            $p = explode('=', $value);
                            $matchedParams[str_replace('-', '', $p[0])] = $p[1];
                        }
                    }
                // Option
                } else if (substr($param['name'], 0, 1) == '-') {
                    $p = explode('|', $param['name']);
                    $whichOpt = null;
                    $optValue = null;
                    foreach ($p as $opt) {
                        if (in_array($opt, $params)) {
                            $whichOpt = $opt;
                        } else {
                            // Check if a value is passed with the parameter option

                            foreach ($params as $searchParam) {
                                if (substr($searchParam, 0, strlen($opt)) == $opt) {
                                    $whichOpt = $opt;
                                    if (strlen($searchParam) > strlen($opt)) {
                                        $optValue = (strpos($searchParam, '=') !== false) ?
                                            substr($searchParam, (strpos($searchParam, '=') + 1)) :
                                            substr($searchParam, (strpos($searchParam, $whichOpt) + strlen($whichOpt)));
                                    }
                                }
                            }
                        }
                    }

                    if (null !== $whichOpt) {
                        $key = array_search($whichOpt, $params);
                        // Look ahead for a value
                        if (isset($params[$key + 1]) && (substr($params[$key + 1], 0, 1) != '-')) {
                            if (isset($routeParams[($key + 1 - $offset)])) {
                                // Value is meant for the next arg place
                                if (substr($routeParams[($key + 1 - $offset)]['name'], 0, 1) == '<') {
                                    $matchedParams[str_replace('-', '', $whichOpt)] = (null !== $optValue) ? $optValue : true;
                                // Value is meant for the option
                                } else {
                                    $matchedParams[str_replace('-', '', $whichOpt)] = $params[$key + 1];
                                    $offset++;
                                }
                            } else {
                                $matchedParams[str_replace('-', '', $whichOpt)] = $params[$key + 1];
                                $offset++;
                            }
                        // Else, just set to true
                        } else {
                            $matchedParams[str_replace('-', '', $whichOpt)] = (null !== $optValue) ? $optValue : true;
                        }
                    }
                }
            }
        }

        return ($result) ? $matchedParams : false;
    }

}