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
     *     foo bar --option1 [--option2]
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
                    $this->controller = $controller['controller'];
                    $this->action     = $controller['action'];
                    if (isset($controller['routeParams'])) {
                        $this->routeParams = (!is_array($controller['routeParams'])) ?
                            [$controller['routeParams']] : $controller['routeParams'];
                    }
                }
            }
            if (isset($controller['default']) && ($controller['default']) && isset($controller['controller'])) {
                $this->defaultController = $controller['controller'];
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
        $params = substr($this->argumentString, strlen($route) + 1);
        $params = explode(' ', $params);
        if ((count($params) == 1) && ($params[0] == '')) {
            $params = [];
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
        print_r($params);
        print_r($routeParams);


    }

}