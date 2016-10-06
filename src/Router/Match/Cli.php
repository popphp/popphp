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
 * Pop router CLI match class
 *
 * @category   Pop
 * @package    Pop\Router
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class Cli extends AbstractMatch
{

    /**
     * Constructor
     *
     * Instantiate the CLI match object
     */
    public function __construct()
    {
        $argv = $_SERVER['argv'];

        // Trim the script name out of the arguments array
        array_shift($argv);

        $this->segments    = $argv;
        $this->routeString = implode(' ', $argv);

        return $this;
    }

    /**
     * Prepare the routes
     *
     * @return Cli
     */
    public function prepare()
    {
        $this->flattenRoutes($this->routes);
        return $this;
    }

    /**
     * Match the route
     *
     * @return boolean
     */
    public function match()
    {
        if (count($this->preparedRoutes) == 0) {
            $this->prepare();
        }

        foreach ($this->preparedRoutes as $regex => $controller) {
            if (preg_match($regex, $this->routeString) != 0) {
                $this->route = $regex;
                break;
            }
        }

        $this->parseRouteParams();

        return $this->hasRoute();
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
     * Flatten the nested routes
     *
     * @param  string $route
     * @param  mixed  $controller
     * @return void
     */
    protected function flattenRoutes($route, $controller = null)
    {
        if (is_array($route)) {
            foreach ($route as $r => $c) {
                $this->flattenRoutes($r, $c);
            }
        } else if (null !== $controller) {
            if (!isset($controller['controller'])) {
                foreach ($controller as $r => $c) {
                    $this->flattenRoutes($route . $r, $c);
                }
            } else {
                $routeRegex = $this->getRouteRegex($route);
                if (isset($controller['default']) && ($controller['default'])) {
                    $this->defaultRoute = $controller;
                }
                $this->preparedRoutes[$routeRegex['regex']] = array_merge($controller, [
                    'route'  => $route,
                    'params' => $routeRegex['params'],
                ]);
            }
        }
    }

    /**
     * Get the REGEX pattern for the route string
     *
     * @param  string $route
     * @return array
     */
    protected function getRouteRegex($route)
    {

        //$required = [];
        //$optional = [];
        //$params   = [];
        //$offsets  = [];

        //preg_match_all('/\[\/\:[^\[]+\]/', $route, $optional, PREG_OFFSET_CAPTURE);
        //preg_match_all('/(?<!\[)\/\:+\w*/', $route, $required, PREG_OFFSET_CAPTURE);
        /*
        foreach ($required[0] as $req) {
            $name      = substr($req[0], (strpos($req[0], ':') + 1));
            $route     = str_replace($req[0], '/.[a-zA-Z0-9_-]*', $route);
            $offsets[] = $req[1];
            $params[]  = [
                'param'    => $req[0],
                'name'     => $name,
                'offset'   => $req[1],
                'required' => true
            ];
        }
        foreach ($optional[0] as $opt) {
            $name      = substr($opt[0], (strpos($opt[0], ':') + 1), -1);
            $route     = str_replace($opt[0], '(|/[a-zA-Z0-9_-]*)', $route);
            $offsets[] = $opt[1];
            $params[]  = [
                'param'    => $opt[0],
                'name'     => $name,
                'offset'   => $opt[1],
                'required' => false
            ];
        }

        $route = '^' . str_replace('/', '\/', $route) . '$';

        if (substr($route, -5) == '[\/]$') {
            $route = str_replace('[\/]$', '(|\/)$', $route);
        }

        array_multisort($offsets, SORT_ASC, $params);

        return [
            'regex'  => '/' . $route . '/',
            'params' => $params
        ];
        */
    }

    /**
     * Parse route dispatch parameters
     *
     * @return void
     */
    protected function parseRouteParams()
    {

    }

}
