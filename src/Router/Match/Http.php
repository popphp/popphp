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
     * URI Segment string
     * @var string
     */
    protected $segmentString = null;

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
            $this->segments      = ['index'];
            $this->segmentString = '/';
        } else {
            $this->segments      = explode('/', substr($path, 1));
            $this->segmentString = '/' . implode('/', $this->segments) . $trailingSlash;
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
    public function getSegmentString()
    {
        return $this->segmentString;
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
        $matched = false;

        if (count($this->preparedRoutes) == 0) {
            $this->prepare();
        }

        foreach ($this->preparedRoutes as $regex => $controller) {
            if (preg_match($regex, $this->segmentString) != 0) {
                $this->route = $regex;
                break;
            }
        }

        return $matched;
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
                $regex = $this->getRouteRegex($route);
                $this->preparedRoutes[$regex['route']] = array_merge($controller, [
                    'required' => $regex['required'],
                    'optional' => $regex['optional'],
                    'original' => $route
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
        $required       = [];
        $optional       = [];
        $requiredParams = [];
        $optionalParams = [];

        preg_match_all('/\[\/\:[^\[]+\]/', $route, $optional, PREG_OFFSET_CAPTURE);
        preg_match_all('/(?<!\[)\/\:+\w*/', $route, $required, PREG_OFFSET_CAPTURE);

        foreach ($required[0] as $req) {
            $route = str_replace($req[0], '/.[a-zA-Z0-9_-]*', $route);
            $requiredParams[] = [
                'param'  => $req[0],
                'offset' => $req[1]
            ];
        }
        foreach ($optional[0] as $opt) {
            $route = str_replace($opt[0], '(|/[a-zA-Z0-9_-]*)', $route);
            $optionalParams[] = [
                'param'  => $opt[0],
                'offset' => $opt[1]
            ];
        }

        $route = '^' . str_replace('/', '\/', $route) . '$';

        if (substr($route, -5) == '[\/]$') {
            $route = str_replace('[\/]$', '(|\/)$', $route);
        }

        return [
            'route'    => '/' . $route . '/',
            'required' => $requiredParams,
            'optional' => $optionalParams
        ];
    }

}
