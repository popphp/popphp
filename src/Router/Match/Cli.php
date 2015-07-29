<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @package    Pop_Router
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
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
        $argv = $_SERVER['argv'];

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
                        $this->route      = $route;
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
                        $this->route      = $route;
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
                if ((substr($this->argumentString, 0, strlen($wc)) == $wc) && isset($wildcardController['controller'])) {
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
     * Method to process if a route was not found
     *
     * @param  boolean $exit
     * @return void
     */
    public function noRouteFound($exit = true)
    {
        if (stripos(PHP_OS, 'win') === false) {
            $string  = "    \x1b[1;37m\x1b[41m                          \x1b[0m" . PHP_EOL;
            $string .= "    \x1b[1;37m\x1b[41m    Command not found.    \x1b[0m" . PHP_EOL;
            $string .= "    \x1b[1;37m\x1b[41m                          \x1b[0m";
        } else {
            $string = 'Command Not Found.';
        }
        echo PHP_EOL . $string . PHP_EOL . PHP_EOL;
        if ($exit) {
            exit(127);
        }
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
            if ($route == '*') {
                $this->wildcards[$route] = $controller;
            } else {
                $controller['wildcard'] = false;
            }

            if ($route != '*') {
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
                    $params = (strpos($params, ' ') !== false) ? explode(' ', $params) : [$params];

                    $controller['dispatchParams'] = [];
                    foreach ($params as $param) {
                        if (strpos($param, '[') !== false) {
                            $param = substr($param, 1);
                            $param = substr($param, 0, -1);
                            $controller['dispatchParams'][] = [
                                'name'     => $param,
                                'required' => false
                            ];
                        } else {
                            if ((substr($param, 0, 1) == '<') && (substr($param, -1) == '>')) {
                                $param = substr($param, 1, -1);
                            }
                            $controller['dispatchParams'][] = [
                                'name'     => $param,
                                'required' => true
                            ];
                        }
                    }
                }

                $regex  = '/^';
                $ary    = explode(' ', $route);
                foreach ($ary as $key => $value) {
                    if ($this->isOptional($value)) {
                        if ($this->isCommand($value) || $this->isOption($value)) {
                            if ($this->hasAlternate($value)) {
                                $value = '(' .$value . ')';
                                $regex .= str_replace(['[', ']'], ['\s(', ')'], $value) . '?';
                            } else {
                                $regex .= '(' . str_replace(['[', ']'], ['\s', ''], $value) . ')?';
                            }
                        } else if ($this->isValue($value)) {
                            $regex .= '(\s.(.*))?';
                        } else if ($this->isOptionValue($value)) {
                            $regex .= '(\s' . str_replace(['[', ']'], ['', ''], $value) . '(.*))?';
                        }
                    } else {
                        if ($this->isCommand($value) || $this->isOption($value)) {
                            if ($this->hasAlternate($value)) {
                                $value = '(' .$value . ')';
                            }
                            $regex .= (($key > 0) ? ' ' : '') . $value;
                        } else if ($this->isValue($value)) {
                            $regex .= (($key > 0) ? ' ' : '') . '.(.*)';
                        } else if ($this->isOptionValue($value)) {
                            $regex .= (($key > 0) ? ' ' : '') . $value . '(.*)';
                        }
                    }
                }
                $regex .= '$/';

                if (substr($regex, -4) == ' *$/') {
                    $regex = substr($regex, 0, -4) . '(.*)$/';
                }

                $this->routes[$regex] = $controller;
            }
        }
    }

    /**
     * Determine if the route segment is optional
     *
     * @param  string $route
     * @return boolean
     */
    protected function isOptional($route) {
        return (strpos($route, '[') !== false);
    }

    /**
     * Determine if the route segment is a command
     *
     * @param  string $route
     * @return boolean
     */
    protected function isCommand($route) {
        return ((strpos($route, '-') === false) && (strpos($route, '<') === false));
    }

    /**
     * Determine if the route segment is an option
     *
     * @param  string $route
     * @return boolean
     */
    protected function isOption($route) {
        return ((strpos($route, '-') !== false) && (strpos($route, '=') === false));
    }

    /**
     * Determine if the route segment is a value
     *
     * @param  string $route
     * @return boolean
     */
    protected function isValue($route) {
        return (strpos($route, '<') !== false);
    }

    /**
     * Determine if the route segment is option value
     *
     * @param  string $route
     * @return boolean
     */
    protected function isOptionValue($route) {
        return ((strpos($route, '-') !== false) && (strpos($route, '=') !== false));
    }

    /**
     * Determine if the route segment has an alternate
     *
     * @param  string $value
     * @return boolean
     */
    protected function hasAlternate($value) {
        return (strpos($value, '|') !== false);
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

        foreach ($this->arguments as $i => $arg) {
            if (substr($arg, 0, 1) == '-') {
                if (strpos($arg, '=') !== false) {
                    $ary = explode('=', $arg);
                    $params[] = $ary[1];
                } else {
                    $params[] = true;
                }
            } else if (strpos($route, $arg) === false) {
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
            if (($param['required']) && !isset($params[$i - $offset])) {
                $result = false;
                break;
            }

            // Is option
            if ($this->isOption($param['name'])) {
                if (isset($params[$i - $offset]) && is_bool($params[$i - $offset])) {
                    $matchedParams[$param['name']] = $params[$i - $offset];
                } else {
                    $matchedParams[$param['name']] = null;
                    $offset++;
                }
            // Is option value
            } else if ($this->isOptionValue($param['name'])) {
                if (isset($params[$i - $offset])) {
                    $matchedParams[$param['name']] = $params[$i - $offset];
                } else {
                    $matchedParams[$param['name']] = null;
                    $offset++;
                }
            // Is value
            } else {
                if (isset($params[$i - $offset])) {
                    $matchedParams[$param['name']] = $params[$i - $offset];
                } else {
                    $matchedParams[$param['name']] = null;
                    $offset++;
                }
            }
        }

        return ($result) ? $matchedParams : false;
    }

}