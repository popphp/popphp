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
 * @package    Pop\Router
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
     * URI segments
     * @var array
     */
    protected $segments = [];

    /**
     * URI  string
     * @var string
     */
    protected $uriString = null;

    /**
     * Constructor
     *
     * Instantiate the HTTP match object
     */
    public function __construct()
    {
        $basePath       = str_replace([realpath($_SERVER['DOCUMENT_ROOT']), '\\'], ['', '/'], realpath(getcwd()));
        $this->basePath = (!empty($basePath) ? $basePath : '');

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
            $this->segments  = ['index'];
            $this->uriString = '/';
        } else {
            $this->segments  = explode('/', substr($path, 1));
            $this->uriString = '/' . implode('/', $this->segments) . $trailingSlash;
        }
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
    public function getUriString()
    {
        return $this->uriString;
    }

    /**
     * Prepare the routes
     *
     * @return Http
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
            if (preg_match($regex, $this->uriString) != 0) {
                $this->route = $regex;
                break;
            }
        }

        if ($this->hasRoute()) {
            $this->parseRouteParams();
        }

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

        if ($exit) {
            exit();
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
        $required = [];
        $optional = [];
        $params   = [];
        $offsets  = [];

        preg_match_all('/\[\/\:[^\[]+\]/', $route, $optional, PREG_OFFSET_CAPTURE);
        preg_match_all('/(?<!\[)\/\:+\w*/', $route, $required, PREG_OFFSET_CAPTURE);

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
    }

    /**
     * Parse route dispatch parameters
     *
     * @return void
     */
    protected function parseRouteParams()
    {
        if (count($this->preparedRoutes[$this->route]['params']) > 0) {
            $offset = 0;
            foreach ($this->preparedRoutes[$this->route]['params'] as $i => $param) {
                $value = substr($this->uriString, ($param['offset'] + $offset + 1));
                if (strpos($value, '/') !== false) {
                    $value = substr($value, 0, strpos($value, '/'));
                    $offset += strlen($value) - strlen($param['param']) + 1;
                }
                if (!empty($value)) {
                    $this->routeParams[$param['name']] = $value;
                }
            }
        }
    }

}
