<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
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
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class Http extends AbstractMatch
{

    /**
     * Base path
     * @var ?string
     */
    protected ?string $basePath = null;

    /**
     * Map of synthetic method-scoped route keys (see addRoute()) back to their
     * real route string, used only internally by flattenRoutes() to recover the
     * real path for regex generation
     * @var array
     */
    protected array $methodRoutes = [];

    /**
     * Whitelisted custom HTTP methods, for use with __call()
     * @var array
     */
    protected array $customMethods = [];

    /**
     * Specificity score per prepared route, keyed the same as preparedRoutes.
     * Kept out of the preparedRoutes entries themselves so it doesn't leak into
     * getRouteConfig()/getPreparedRoutes() public output.
     * @var array
     */
    protected array $routeSpecificity = [];

    /**
     * Flag set by match(): true when the request path matched at least one
     * route whose method constraint rejected the request, and nothing else
     * (no wildcard/dynamic fallback) ended up handling it
     * @var bool
     */
    protected bool $methodMismatch = false;

    /**
     * Methods accepted by at least one path-matching route, populated by
     * match() for use in a 405 response's Allow header
     * @var array
     */
    protected array $allowedMethods = [];

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
     * Add a route
     *
     * Overrides AbstractMatch::addRoute() only for a regular route (not
     * wildcard, not dynamic) that carries a 'method' constraint - those are
     * stored under a synthetic composite key (route + a null-byte-separated
     * method signature) instead of the plain route string, so registering the
     * same path for multiple methods with different controllers (GET and POST
     * on the same URI) doesn't collide with AbstractMatch::addRoute()'s
     * merge-on-duplicate-key behavior, which would otherwise silently
     * overwrite the earlier registration. Every other case (no method key,
     * wildcard routes, dynamic routes, nested route arrays) falls through to
     * the parent implementation unchanged.
     *
     * @param  string $route
     * @param  mixed  $controller
     * @return AbstractMatch
     */
    public function addRoute(string $route, mixed $controller): AbstractMatch
    {
        // Popcorn-style method-grouped nested routes, e.g.:
        //   'options,get' => ['/users' => [...], '/roles' => [...]]
        // Each nested route is re-registered under its own key with the group's
        // method list injected - overriding any 'method' key the nested route
        // config already has, since the group is authoritative (matches
        // Popcorn's original grouping semantics).
        if ($this->isMethodGroupKey($route) && is_array($controller) && !isset($controller['controller'])) {
            foreach ($controller as $nestedRoute => $nestedController) {
                if (is_callable($nestedController)) {
                    $nestedController = ['controller' => $nestedController];
                }
                $nestedController['method'] = $route;
                $this->addRoute($nestedRoute, $nestedController);
            }

            return $this;
        }

        if (is_callable($controller)) {
            $controller = ['controller' => $controller];
        }

        if (!empty($controller['method']) && isset($controller['controller']) && ($route !== '*') &&
            !str_ends_with($route, '/*') && !str_contains($route, ':controller')) {
            $methods = $this->normalizeMethods($controller['method']);
            $key     = $route . "\0" . implode(',', $methods);

            $this->methodRoutes[$key] = $route;
            $this->routes[$key]       = $controller;

            return $this;
        }

        return parent::addRoute($route, $controller);
    }

    /**
     * Normalize a 'method' config value (a method string, a comma-separated
     * string, or an array of method strings) into a sorted, deduplicated,
     * lowercase array. Returns null for an empty/absent value ("any method").
     *
     * @param  mixed $method
     * @return ?array
     */
    protected function normalizeMethods(mixed $method): ?array
    {
        if (empty($method)) {
            return null;
        }

        if (is_string($method)) {
            $method = array_map('trim', explode(',', $method));
        }

        $method = array_unique(array_map('strtolower', (array)$method));
        sort($method);

        return array_values($method);
    }

    /**
     * Determine if a route key is a Popcorn-style method-group key, e.g.
     * 'get', 'options,get', 'options,post' - a bare, comma-separated list of
     * recognized HTTP methods only, never a real route path (which always
     * starts with '/', is '*', or contains ':controller').
     *
     * @param  string $route
     * @return bool
     */
    protected function isMethodGroupKey(string $route): bool
    {
        if (($route === '') || ($route === '*') || str_starts_with($route, '/') ||
            str_ends_with($route, '/*') || str_contains($route, ':controller')) {
            return false;
        }

        $standardMethods = ['get', 'head', 'post', 'put', 'delete', 'trace', 'options', 'connect', 'patch'];

        foreach (explode(',', strtolower($route)) as $method) {
            $method = trim($method);
            if (($method === '') || (!in_array($method, $standardMethods) && !$this->hasCustomMethod($method))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Prepare the routes
     *
     * @return static
     */
    public function prepare(): static
    {
        $this->flattenRoutes($this->routes);

        uksort($this->preparedRoutes, function($keyA, $keyB) {
            $scoreA = $this->routeSpecificity[$keyA] ?? 0;
            $scoreB = $this->routeSpecificity[$keyB] ?? 0;
            return $scoreB <=> $scoreA;
        });

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

        $this->route          = null;
        $this->methodMismatch = false;
        $this->allowedMethods = [];

        $routeToMatch  = $forceRoute ?? $this->routeString;
        $requestMethod = strtolower((string)($_SERVER['REQUEST_METHOD'] ?? 'get'));
        $pathMatched   = false;
        $directMatch   = null;

        if (array_key_exists($routeToMatch, $this->routes)) {
            $directMatch = $routeToMatch;
        } else if (array_key_exists($routeToMatch . '/', $this->routes)) {
            $directMatch = $routeToMatch . '/';
        } else if (array_key_exists($routeToMatch . '[/]', $this->routes)) {
            $directMatch = $routeToMatch . '[/]';
        }

        if ($directMatch !== null) {
            foreach ($this->preparedRoutes as $key => $controller) {
                if ($directMatch !== $controller['route']) {
                    continue;
                }
                $pathMatched = true;
                if (($controller['method'] === null) || in_array($requestMethod, $controller['method'])) {
                    $this->route = $key;
                    break;
                }
                $this->allowedMethods = array_merge($this->allowedMethods, $controller['method']);
            }
        }

        if ($this->route === null) {
            foreach ($this->preparedRoutes as $key => $controller) {
                if (preg_match($controller['regex'], $routeToMatch) == 0) {
                    continue;
                }
                $pathMatched = true;
                if (($controller['method'] === null) || in_array($requestMethod, $controller['method'])) {
                    $this->route = $key;
                    break;
                }
                $this->allowedMethods = array_merge($this->allowedMethods, $controller['method']);
            }
        }

        $this->allowedMethods = array_values(array_unique($this->allowedMethods));
        $this->methodMismatch = ($this->route === null) && $pathMatched;

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
     * Determine if match() found a route whose path matched but whose method
     * constraint rejected the request, with nothing else available to handle it
     *
     * @return bool
     */
    public function hasMethodMismatch(): bool
    {
        return $this->methodMismatch;
    }

    /**
     * Get the methods accepted by at least one path-matching route from the
     * last match() call, for use in a 405 response's Allow header
     *
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
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
     * Method to process if a route matched the path but not the HTTP method
     *
     * @param  array $allowedMethods
     * @param  bool  $exit
     * @return void
     */
    public function methodNotAllowed(array $allowedMethods, bool $exit = true): void
    {
        if (!headers_sent()) {
            header('HTTP/1.1 405 Method Not Allowed');
            header('Allow: ' . implode(', ', array_map('strtoupper', $allowedMethods)));
        }
        echo '<!DOCTYPE html>' . PHP_EOL;
        echo '<html>' . PHP_EOL;
        echo '    <head>' . PHP_EOL;
        echo '        <title>Method Not Allowed</title>' . PHP_EOL;
        echo '    </head>' . PHP_EOL;
        echo '<body>' . PHP_EOL;
        echo '    <h1>Method Not Allowed</h1>' . PHP_EOL;
        echo '</body>' . PHP_EOL;
        echo '</html>'. PHP_EOL;

        if ($exit) {
            exit();
        }
    }

    /**
     * Add a GET route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @return static
     */
    public function get(string $route, mixed $controller): static
    {
        return $this->addVerbRoute('get', $route, $controller);
    }

    /**
     * Add a HEAD route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @return static
     */
    public function head(string $route, mixed $controller): static
    {
        return $this->addVerbRoute('head', $route, $controller);
    }

    /**
     * Add a POST route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @return static
     */
    public function post(string $route, mixed $controller): static
    {
        return $this->addVerbRoute('post', $route, $controller);
    }

    /**
     * Add a PUT route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @return static
     */
    public function put(string $route, mixed $controller): static
    {
        return $this->addVerbRoute('put', $route, $controller);
    }

    /**
     * Add a DELETE route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @return static
     */
    public function delete(string $route, mixed $controller): static
    {
        return $this->addVerbRoute('delete', $route, $controller);
    }

    /**
     * Add a TRACE route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @return static
     */
    public function trace(string $route, mixed $controller): static
    {
        return $this->addVerbRoute('trace', $route, $controller);
    }

    /**
     * Add an OPTIONS route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @return static
     */
    public function options(string $route, mixed $controller): static
    {
        return $this->addVerbRoute('options', $route, $controller);
    }

    /**
     * Add a CONNECT route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @return static
     */
    public function connect(string $route, mixed $controller): static
    {
        return $this->addVerbRoute('connect', $route, $controller);
    }

    /**
     * Add a PATCH route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @return static
     */
    public function patch(string $route, mixed $controller): static
    {
        return $this->addVerbRoute('patch', $route, $controller);
    }

    /**
     * Register a route constrained to a single HTTP method
     *
     * @param  string $method
     * @param  string $route
     * @param  mixed  $controller
     * @return static
     */
    protected function addVerbRoute(string $method, string $route, mixed $controller): static
    {
        if (is_callable($controller)) {
            $controller = ['controller' => $controller];
        }
        $controller['method'] = $method;

        $this->addRoute($route, $controller);

        return $this;
    }

    /**
     * Add a custom HTTP method to the whitelist
     *
     * @param  string $method
     * @return static
     */
    public function addCustomMethod(string $method): static
    {
        $method = strtolower($method);
        if (!in_array($method, $this->customMethods)) {
            $this->customMethods[] = $method;
        }
        return $this;
    }

    /**
     * Add multiple custom HTTP methods to the whitelist
     *
     * @param  array $methods
     * @return static
     */
    public function addCustomMethods(array $methods): static
    {
        foreach ($methods as $method) {
            $this->addCustomMethod($method);
        }
        return $this;
    }

    /**
     * Determine if a custom HTTP method has been whitelisted
     *
     * @param  string $method
     * @return bool
     */
    public function hasCustomMethod(string $method): bool
    {
        return in_array(strtolower($method), $this->customMethods);
    }

    /**
     * Magic method to register a route for a whitelisted custom HTTP method
     *
     * @param  string $name
     * @param  array  $arguments
     * @throws Exception
     * @return static
     */
    public function __call(string $name, array $arguments): static
    {
        $method = strtolower($name);

        if (!$this->hasCustomMethod($method)) {
            throw new Exception("Error: The custom method '" . strtoupper($name) . "' is not allowed.");
        }
        if (count($arguments) != 2) {
            throw new Exception('Error: You must pass a route and a controller.');
        }

        [$route, $controller] = $arguments;

        return $this->addVerbRoute($method, $route, $controller);
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
                $realRoute  = $this->methodRoutes[$route] ?? $route;
                $routeRegex = $this->getRouteRegex($realRoute);
                $methods    = isset($this->methodRoutes[$route]) ?
                    $this->normalizeMethods($controller['method'] ?? null) : null;
                $key        = $routeRegex['regex'] . (($methods !== null) ? "\0" . implode(',', $methods) : '');

                $this->preparedRoutes[$key] = array_merge($controller, [
                    'route'  => $realRoute,
                    'params' => $routeRegex['params'],
                    'regex'  => $routeRegex['regex'],
                    'method' => $methods,
                ]);
                $this->routeSpecificity[$key] = $routeRegex['specificity'];

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

        $requiredCount = 0;
        $optionalCount = 0;
        $hasArrayParam = false;
        foreach ($params as $param) {
            if ($param['array']) {
                $hasArrayParam = true;
            } elseif ($param['required']) {
                $requiredCount++;
            } else {
                $optionalCount++;
            }
        }
        $specificity = 1000 - ($requiredCount * 5) - ($optionalCount * 10) - ($hasArrayParam ? 500 : 0);

        return [
            'regex'       => '/' . $route . '/u',
            'params'      => $params,
            'specificity' => $specificity,
        ];
    }

    /**
     * Parse route dispatch parameters
     *
     * @return void
     */
    protected function parseRouteParams(): void
    {
        if (($this->route !== null) && isset($this->preparedRoutes[$this->route]['params']) &&
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
