<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
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
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.3.5
 */
class Http extends AbstractMatch
{

    /**
     * Base path
     * @var ?string
     */
    protected ?string $basePath = null;

    /**
     * Constructor
     *
     * Instantiate the HTTP match object
     */
    public function __construct()
    {
        $basePath       = str_replace([realpath($_SERVER['DOCUMENT_ROOT']), '\\'], ['', '/'], realpath(getcwd()));
        $this->basePath = !empty($basePath) ? $basePath : '';
        $trailingSlash  = null;

        $path = ($this->basePath != '') ?
            substr($_SERVER['REQUEST_URI'], strlen($this->basePath)) : $_SERVER['REQUEST_URI'];

        // Trim query string, if present
        if (strpos($path, '?')) {
            $path = substr($path, 0, strpos($path, '?'));
        }

        // Trim trailing slash, if present
        if (str_ends_with($path, '/')) {
            $path          = substr($path, 0, -1);
            $trailingSlash = '/';
        }

        if ($path == '') {
            $this->segments    = ['index'];
            $this->routeString = '/';
        } else {
            $this->segments    = explode('/', substr($path, 1));
            $this->routeString = '/' . implode('/', $this->segments) . $trailingSlash;
        }
    }

    /**
     * Get the base path
     *
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Prepare the routes
     *
     * @return static
     */
    public function prepare(): static
    {
        $this->flattenRoutes($this->routes);
        return $this;
    }

    /**
     * Match the route
     *
     * @param  mixed $forceRoute
     * @return bool
     */
    public function match(mixed $forceRoute = null): bool
    {
        if (count($this->preparedRoutes) == 0) {
            $this->prepare();
        }

        $routeToMatch = $forceRoute ?? $this->routeString;
        $directMatch  = null;

        if (array_key_exists($routeToMatch, $this->routes)) {
            $directMatch = $routeToMatch;
        } else if (array_key_exists($routeToMatch . '/', $this->routes)) {
            $directMatch = $routeToMatch . '/';
        } else if (array_key_exists($routeToMatch . '[/]', $this->routes)) {
            $directMatch = $routeToMatch . '[/]';
        }

        if ($directMatch !== null) {
            foreach ($this->preparedRoutes as $regex => $controller) {
                if ($directMatch == $controller['route']) {
                    $this->route = $regex;
                    break;
                }
            }
        } else {
            foreach ($this->preparedRoutes as $regex => $controller) {
                if (preg_match($regex, $routeToMatch) != 0) {
                    $this->route = $regex;
                    break;
                }
            }
        }

        $this->parseRouteParams();

        return $this->hasRoute();
    }

    /**
     * Determine if the route has been matched
     *
     * @return bool
     */
    public function hasRoute(): bool
    {
        return ($this->route !== null) || ($this->dynamicRoute !== null) || ($this->defaultRoute !== null);
    }

    /**
     * Method to process if a route was not found
     *
     * @param  bool $exit
     * @return void
     */
    public function noRouteFound(bool $exit = true): void
    {
        if (!headers_sent()) {
            header('HTTP/1.1 404 Not Found');
        }
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
     * @param  array|string $route
     * @param  mixed        $controller
     * @return void
     */
    protected function flattenRoutes(array|string $route, mixed $controller = null): void
    {
        if (is_array($route)) {
            foreach ($route as $r => $c) {
                $this->flattenRoutes($r, $c);
            }
        } else if ($controller !== null) {
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
                if (isset($controller['default']) && ($controller['default'])) {
                    if (isset($controller['action'])) {
                        unset($controller['action']);
                    }
                    $this->defaultRoute['*'] = $controller;
                }
            }
        }
    }

    /**
     * Get the REGEX pattern for the route string
     *
     * @param  string $route
     * @return array
     */
    protected function getRouteRegex(string $route): array
    {
        $required   = [];
        $optional   = [];
        $params     = [];
        $offsets    = [];
        $paramArray = false;

        if (str_contains($route, '*')) {
            $paramArray = true;
            $route      = str_replace('*', '', $route);
        }

        preg_match_all('/\[\/\:[^\[]+\]/', $route, $optional, PREG_OFFSET_CAPTURE);
        preg_match_all('/(?<!\[)\/\:+\w*/', $route, $required, PREG_OFFSET_CAPTURE);

        foreach ($required[0] as $req) {
            $name      = substr($req[0], (strpos($req[0], ':') + 1));
            $route     = str_replace($req[0], '/.[a-zA-Z0-9_\.\-\p{L}]*', $route);
            $offsets[] = $req[1];
            $params[]  = [
                'param'    => $req[0],
                'name'     => $name,
                'offset'   => $req[1],
                'required' => true,
                'array'    => false
            ];
        }

        foreach ($optional[0] as $opt) {
            $name      = substr($opt[0], (strpos($opt[0], ':') + 1), -1);
            $route     = str_replace($opt[0], '(|/[a-zA-Z0-9_\.\-\p{L}]*)', $route);
            $offsets[] = $opt[1];
            $params[]  = [
                'param'    => $opt[0],
                'name'     => $name,
                'offset'   => $opt[1],
                'required' => false,
                'array'    => false
            ];
        }

        $route = '^' . str_replace('/', '\/', $route) . '$';
        if (str_ends_with($route, '[\/]$')) {
            $route = str_replace('[\/]$', '(|\/)$', $route);
        }

        array_multisort($offsets, SORT_ASC, $params);

        if (($paramArray) && (count($params) > 0)) {
            $params[(count($params) - 1)]['array'] = true;
            $route = str_replace('$', '.*', $route);
        }

        return [
            'regex'  => '/' . $route . '/u',
            'params' => $params
        ];
    }

    /**
     * Parse route dispatch parameters
     *
     * @return void
     */
    protected function parseRouteParams(): void
    {
        if (isset($this->preparedRoutes[$this->route]['params']) &&
            (count($this->preparedRoutes[$this->route]['params']) > 0)) {
            $offset = 0;
            foreach ($this->preparedRoutes[$this->route]['params'] as $i => $param) {
                $value = substr($this->routeString, ($param['offset'] + $offset + 1));
                if ($param['array']) {
                    if (!$value) {
                        $value = [];
                    } else {
                        $value = (str_contains($value, '/')) ? explode('/', $value) : [$value];
                    }
                } else {
                    if (str_contains($value, '/')) {
                        $value   = substr($value, 0, strpos($value, '/'));
                        $offset += strlen($value) - strlen($param['param']) + 1;
                    } else {
                        $offset += strlen($value) - strlen($param['param']) + 1;
                    }
                }
                if ($value != '') {
                    $this->routeParams[$param['name']] = $value;
                }
            }
        } else if (($this->dynamicRoute !== null) && (count($this->segments) >= 3)) {
            $this->routeParams = (str_contains($this->dynamicRoute, '/:param*')) ?
                [array_slice($this->segments, 2)] : array_slice($this->segments, 2);
        }
    }

    /**
     * Get URL for the named route
     *
     * @param  string $routeName
     * @param  mixed  $params
     * @param  bool   $fqdn
     * @return string
     */
    public function getUrl(string $routeName, mixed $params = null, bool $fqdn = false): string
    {
        $url     = '';
        $baseUrl = '';

        if ($fqdn) {
            $baseUrl .= (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443)) ? 'https://' : 'http://';
            if (isset($_SERVER['HTTP_HOST'])) {
                $baseUrl .= $_SERVER['HTTP_HOST'];
            }
        }

        $baseUrl .= $this->basePath;

        if (isset($this->routeNames[$routeName]) && isset($this->routes[$this->routeNames[$routeName]])) {
            $route         = $this->routeNames[$routeName];
            $preparedRoute = null;

            if (count($this->preparedRoutes) == 0) {
                $this->prepare();
            }

            foreach ($this->preparedRoutes as $prepRoute) {
                if ($prepRoute['route'] == $route) {
                    $preparedRoute = $prepRoute;
                    break;
                }
            }

            if (!empty($params) && !empty($preparedRoute['params'])) {
                foreach ($preparedRoute['params'] as $param) {
                    $paramName    = $param['name'];
                    $paramString  = null;
                    $paramUrlName = null;

                    if (is_object($params) && isset($params->{$paramName})) {
                        if (is_array($params->{$paramName})) {
                            $paramString  = implode('/', $params->{$paramName});
                            $paramUrlName = $param['param'] . '*';
                        } else {
                            $paramString  = $params->{$paramName};
                            $paramUrlName = $param['param'];
                        }
                    } else if (is_array($params) && isset($params[$paramName])) {
                        if (is_array($params[$paramName])) {
                            $paramString  = implode('/', $params[$paramName]);
                            $paramUrlName = $param['param'] . '*';
                        } else {
                            $paramString  = $params[$paramName];
                            $paramUrlName = $param['param'];
                        }
                    }

                    $route = $baseUrl . str_replace($paramUrlName, '/' . $paramString, $route);
                }
            }

            $url = $route;
        }

        return $url;
    }

}
