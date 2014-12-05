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
     *     foo*   - Turns off strict matching and allows any route that starts with 'foo ' to pass
     *
     * @param  array $routes
     * @return boolean
     */
    public function match($routes)
    {
        $this->prepareRoutes($routes);

        foreach ($this->routes as $route => $controller) {
            if ((substr($this->argumentString, 0, strlen($route)) == $route) &&
                isset($controller['controller']) && isset($controller['action'])) {
                if (isset($controller['dispatchParams'])) {
                    $params        = $this->getDispatchParamsFromRoute($route);
                    $matchedParams = $this->processDispatchParamsFromRoute($params, $controller['dispatchParams']);
                    if ($matchedParams != false) {
                        $this->controller     = $controller['controller'];
                        $this->action         = $controller['action'];
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
                        $this->action     = $controller['action'];
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
        if ((null === $this->controller) && array_key_exists('*', $this->routes) &&
            isset($this->routes['*']['controller']) && isset($this->routes['*']['action'])) {
            $this->controller = $this->routes['*']['controller'];
            $this->action     = $this->routes['*']['action'];
            if (isset($controller['dispatchParams'])) {
                $params        = $this->getDispatchParamsFromRoute('*');
                $matchedParams = $this->processDispatchParamsFromRoute($params, $controller['dispatchParams']);
                if ($matchedParams != false) {
                    $this->dispatchParams  = $matchedParams;
                }
            }
            if (isset($this->routes['*']['routeParams'])) {
                $this->routeParams = (!is_array($this->routes['*']['routeParams'])) ?
                    [$this->routes['*']['routeParams']] : $this->routes['*']['routeParams'];
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
            // Handle wildcard route
            if (substr($route, -1) == '*') {
                $route = substr($route, 0, -1);
                $controller['wildcard'] = true;
            } else {
                $controller['wildcard'] = false;
            }

            $altRoutes = [];
            // Handle optional literals
            if ((strpos($route, '[') !== false) && (substr($route, strpos($route, '['), 2) != '[-') &&
                (substr($route, strpos($route, '['), 2) != '[<')) {
                $optLiterals = substr($route, strpos($route, '['));
                $optLiterals = substr($optLiterals, 0, strpos($optLiterals, ']') + 1);
                $route = str_replace(' ' . $optLiterals, '', $route);

                $alt = substr($optLiterals, 1);
                $alt = substr($alt, 0, -1);
                $altRoutes = explode('|', $alt);
            }

            // Handle params
            $dash    = strpos($route, '-');
            $bracket = strpos($route, '[');
            $angle   = strpos($route, '<');

            $match = [];
            if ($dash !== false) {
                $match[] = $dash;
            }
            if ($bracket !== false) {
                $match[] = $bracket;
            }
            if ($angle !== false) {
                $match[] = $angle;
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

            $this->routes[$route] = $controller;
            if (count($altRoutes) > 0) {
                foreach ($altRoutes as $alt) {
                    $this->routes[$route . ' ' . $alt] = $controller;
                }
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
        $params = [];
        $route = explode(' ', $route);

        foreach ($this->arguments as $arg) {
            if (!in_array($arg, $route)) {
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
                    foreach ($p as $opt) {
                        if (in_array($opt, $params)) {
                            $whichOpt = $opt;
                        }
                    }

                    if (null !== $whichOpt) {
                        $key = array_search($whichOpt, $params);
                        // Look ahead for a value
                        if (isset($params[$key + 1]) && (substr($params[$key + 1], 0, 1) != '-')) {
                            if (isset($routeParams[($key + 1 - $offset)])) {
                                // Value is meant for the next arg place
                                if (substr($routeParams[($key + 1 - $offset)]['name'], 0, 1) == '<') {
                                    $matchedParams[str_replace('-', '', $whichOpt)] = true;
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
                            $matchedParams[str_replace('-', '', $whichOpt)] = true;
                        }
                    }
                }
            }
        }

        return ($result) ? $matchedParams : false;
    }

}