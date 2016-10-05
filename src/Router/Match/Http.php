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
     * Match the route
     *
     * @param  array $routes
     * @return boolean
     */
    public function match($routes)
    {
        $matched = false;

        return $matched;
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

}
