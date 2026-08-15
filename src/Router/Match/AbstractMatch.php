<?php
declare(strict_types=1);

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
 * Pop router match abstract class
 *
 * @category   Pop
 * @package    Pop\Router
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
abstract class AbstractMatch implements MatchInterface
{

    /**
     * Route string
     * @var ?string
     */
    protected ?string $routeString = null;

    /**
     * Segments of route string
     * @var array
     */
    protected array $segments = [];

    /**
     * Matched route
     * @var ?string
     */
    protected ?string $route = null;

    /**
     * Default route
     * @var ?array
     */
    protected ?array $defaultRoute = null;

    /**
     * Dynamic route
     * @var mixed
     */
    protected mixed $dynamicRoute = null;

    /**
     * Dynamic route prefix
     * @var mixed
     */
    protected mixed $dynamicRoutePrefix = null;

    /**
     * Flag for dynamic route
     * @var bool
     */
    protected bool $isDynamicRoute = false;

    /**
     * Routes
     * @var array
     */
    protected array $routes = [];

    /**
     * Prepared routes
     * @var array
     */
    protected array $preparedRoutes = [];

    /**
     * Dispatchable parameters
     * @var array
     */
    protected array $dispatchableParams = [];

    /**
     * Route parameters
     * @var array
     */
    protected array $routeParams = [];

    /**
     * Route names
     * @var array
     */
    protected array $routeNames = [];

    /**
     * Add a route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @return AbstractMatch
     */
    public function addRoute(string $route, mixed $controller): AbstractMatch
    {
        // If is dynamic route
        if ((($this instanceof Http) && (str_contains($route, ':controller'))) ||
            (($this instanceof Cli) && (str_contains($route, '<controller')))) {
            $this->dynamicRoute = $route;
            if (isset($controller['prefix'])) {
                $this->dynamicRoutePrefix = $controller['prefix'];
            }
        // Else, if wildcard route
        } else if (($route == '*') || (str_ends_with($route, '/*'))) {
            $routeKey = (str_ends_with($route, '/*')) ? substr($route, 0, -2) : $route;
            if (is_callable($controller)) {
                $controller = ['controller' => $controller];
            }
            $this->defaultRoute[$routeKey] = $controller;
        // Else, regular route
        } else {
            $this->routeString = urldecode($this->routeString);
            // Handle nested routes
            if (is_array($controller) && !isset($controller['controller'])) {
                foreach ($controller as $r => $c) {
                    $fullRoute = ($r == '*') ? $route . '/*' : $route . $r;
                    $this->addRoute($fullRoute, $c);
                }
            } else {
                if (is_callable($controller)) {
                    $controller = ['controller' => $controller];
                }

                $this->routes[$route] = (isset($this->routes[$route])) ?
                    array_merge($this->routes[$route], $controller) : $controller;
            }
        }

        if (isset($controller['name'])) {
            $this->name($controller['name']);
        }

        if (isset($controller['params'])) {
            $this->addDispatchableParams($controller['controller'], $controller['params']);
        }

        return $this;
    }

    /**
     * Add multiple controller routes
     *
     * @param  array $routes
     * @return AbstractMatch
     */
    public function addRoutes(array $routes): AbstractMatch
    {
        foreach ($routes as $route => $controller) {
            $this->addRoute($route, $controller);
        }

        return $this;
    }

    /**
     * Add a route name
     *
     * @param  string $routeName
     * @throws Exception
     * @return AbstractMatch
     */
    public function name(string $routeName): AbstractMatch
    {
        if (empty($this->routes)) {
            throw new Exception('Error: No routes have been added to name.');
        }

        $this->routeNames[$routeName] = key(array_slice($this->routes, -1));
        return $this;
    }

    /**
     * Has a route name
     *
     * @param  string $routeName
     * @return bool
     */
    public function hasName(string $routeName): bool
    {
        return (isset($this->routeNames[$routeName]));
    }

    /**
     * Add dispatchable params to be passed into a new dispatchable instance
     *
     * @param  string $dispatchable
     * @param  mixed  $params
     * @return AbstractMatch
     */
    public function addDispatchableParams(string $dispatchable, mixed $params): AbstractMatch
    {
        if (!is_array($params)) {
            $params = [$params];
        }
        $this->dispatchableParams[$dispatchable] = $params;

        return $this;
    }

    /**
     * Append dispatchable params to be passed into a new dispatchable instance
     *
     * @param  string $dispatchable
     * @param  mixed  $params
     * @return AbstractMatch
     */
    public function appendDispatchableParams(string $dispatchable, mixed $params): AbstractMatch
    {
        if (!is_array($params)) {
            $params = [$params];
        }
        $this->dispatchableParams[$dispatchable] = (isset($this->dispatchableParams[$dispatchable])) ?
            array_merge($this->dispatchableParams[$dispatchable], $params) : $params;

        return $this;
    }

    /**
     * Get the params assigned to the dispatchable
     *
     * @param  string $dispatchable
     * @return mixed
     */
    public function getDispatchableParams(string $dispatchable): mixed
    {
        return $this->dispatchableParams[$dispatchable] ?? null;
    }

    /**
     * Determine if the dispatchable has params
     *
     * @param  string $dispatchable
     * @return bool
     */
    public function hasDispatchableParams(string $dispatchable): bool
    {
        return (isset($this->dispatchableParams[$dispatchable]));
    }

    /**
     * Remove dispatchable params
     *
     * @param  string $dispatchable
     * @return AbstractMatch
     */
    public function removeDispatchableParams(string $dispatchable): AbstractMatch
    {
        if (isset($this->dispatchableParams[$dispatchable])) {
            unset($this->dispatchableParams[$dispatchable]);
        }
        return $this;
    }

    /**
     * Get the route string
     *
     * @return string
     */
    public function getRouteString(): string
    {
        return $this->routeString;
    }

    /**
     * Get the route string segments
     *
     * @return array
     */
    public function getSegments(): array
    {
        return $this->segments;
    }

    /**
     * Get a route string segment
     *
     * @param  int $i
     * @return ?string
     */
    public function getSegment(int $i): ?string
    {
        return $this->segments[$i] ?? null;
    }

    /**
     * Get original route string
     *
     * @return ?string
     */
    public function getOriginalRoute(): ?string
    {
        return $this->preparedRoutes[$this->route]['route'] ?? null;
    }

    /**
     * Get route regex
     *
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * Get routes
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Get prepared routes
     *
     * @return array
     */
    public function getPreparedRoutes(): array
    {
        return $this->preparedRoutes;
    }

    /**
     * Has route config
     *
     * @param  ?string $key
     * @return bool
     */
    public function hasRouteConfig(?string $key = null): bool
    {
        if (($this->route !== null) && isset($this->preparedRoutes[$this->route])) {
            return ((($key !== null) && isset($this->preparedRoutes[$this->route][$key])) ||
                (($key === null) && !empty($this->preparedRoutes[$this->route])));
        } else {
            return false;
        }
    }

    /**
     * Get route config
     *
     * @param  ?string $key
     * @return mixed
     */
    public function getRouteConfig(?string $key = null): mixed
    {
        if (($this->route !== null) && isset($this->preparedRoutes[$this->route])) {
            if ($key === null) {
                return $this->preparedRoutes[$this->route];
            } else {
                return $this->preparedRoutes[$this->route][$key] ?? null;
            }
        } else {
            return null;
        }
    }

    /**
     * Get flattened routes
     *
     * @return array
     */
    public function getFlattenedRoutes(): array
    {
        $routes = [];
        foreach ($this->preparedRoutes as $value) {
            if (isset($value['route'])) {
                $routes[$value['route']] = $value;
                unset($routes[$value['route']]['route']);
            }
        }
        return $routes;
    }

    /**
     * Get the params discovered from the route
     *
     * @return array
     */
    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    /**
     * Determine if the route has params
     *
     * @return bool
     */
    public function hasRouteParams(): bool
    {
        return (count($this->routeParams) > 0);
    }

    /**
     * Get the default route
     *
     * @return array
     */
    public function getDefaultRoute(): array
    {
        return $this->defaultRoute;
    }

    /**
     * Determine if there is a default route
     *
     * @return bool
     */
    public function hasDefaultRoute(): bool
    {
        return ($this->defaultRoute !== null);
    }

    /**
     * Get the dynamic route
     *
     * @return mixed
     */
    public function getDynamicRoute(): mixed
    {
        return $this->dynamicRoute;
    }

    /**
     * Get the dynamic route prefix
     *
     * @return mixed
     */
    public function getDynamicRoutePrefix(): mixed
    {
        return $this->dynamicRoutePrefix;
    }

    /**
     * Determine if there is a dynamic route
     *
     * @return bool
     */
    public function hasDynamicRoute(): bool
    {
        return ($this->dynamicRoute !== null);
    }

    /**
     * Determine if it is a dynamic route
     *
     * @return bool
     */
    public function isDynamicRoute(): bool
    {
        return $this->isDynamicRoute;
    }

    /**
     * Get the dispatchable
     *
     * @return mixed
     */
    public function getDispatchable(): mixed
    {
        $routeDispatchable = null;

        if (($this->route !== null) && isset($this->preparedRoutes[$this->route]) &&
            isset($this->preparedRoutes[$this->route]['controller'])) {
            $routeDispatchable = $this->preparedRoutes[$this->route]['controller'];
        } else {
            if (($this->dynamicRoute !== null) && ($this->dynamicRoutePrefix !== null) && (count($this->segments) >= 1)) {
                $routeDispatchable = $this->dynamicRoutePrefix . ucfirst(strtolower($this->segments[0])) . 'Controller';
                if (!class_exists($routeDispatchable)) {
                    $routeDispatchable    = null;
                    $this->isDynamicRoute = false;
                } else {
                    $this->isDynamicRoute = true;
                }
            }
            if (($routeDispatchable === null) && !empty($this->defaultRoute)) {
                foreach ($this->defaultRoute as $routeKey => $controller) {
                    if ($routeKey != '*') {
                        if (str_starts_with($this->routeString, $routeKey) && isset($controller['controller'])) {
                            $routeDispatchable = $controller['controller'];
                        }
                    }
                }
                if (($routeDispatchable === null) && isset($this->defaultRoute['*']) && isset($this->defaultRoute['*']['controller'])) {
                    $routeDispatchable = $this->defaultRoute['*']['controller'];
                }
            }
        }

        return $routeDispatchable;
    }

    /**
     * Determine if there is a dispatchable
     *
     * @return bool
     */
    public function hasDispatchable(): bool
    {
        $result = false;

        if (($this->route !== null) && isset($this->preparedRoutes[$this->route]) &&
            isset($this->preparedRoutes[$this->route]['controller'])) {
            $result = true;
        } else if (($this->dynamicRoute !== null) && ($this->getDispatchable() !== null) &&
            ($this->dynamicRoutePrefix !== null) && (count($this->segments) >= 1)) {
            $result = class_exists($this->getDispatchable());
        } else if (!empty($this->defaultRoute)) {
            foreach ($this->defaultRoute as $routeKey => $controller) {
                if (($routeKey != '*') && str_starts_with($this->routeString, $routeKey) && isset($controller['controller'])) {
                    $result = true;
                    break;
                }
            }
            if ((!$result) && isset($this->defaultRoute['*']) && isset($this->defaultRoute['*']['controller'])) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Get the action
     *
     * @return mixed
     */
    public function getAction(): mixed
    {
        $action = null;

        if (($this->route !== null) && isset($this->preparedRoutes[$this->route]) &&
            isset($this->preparedRoutes[$this->route]['action'])) {
            $action = $this->preparedRoutes[$this->route]['action'];
        } else if (($this->dynamicRoute !== null) && ($this->dynamicRoutePrefix !== null) &&
            (count($this->segments) >= 1)) {
            $action = (isset($this->segments[1])) ? $this->segments[1] : null;
        } else if (($this->defaultRoute !== null) && isset($this->defaultRoute['action'])) {
            $action = $this->defaultRoute['action'];
        }

        return $action;
    }

    /**
     * Determine if there is an action
     *
     * @return bool
     */
    public function hasAction(): bool
    {
        $result = false;

        if (($this->route !== null) && isset($this->preparedRoutes[$this->route]) &&
            isset($this->preparedRoutes[$this->route]['action'])) {
            $result = true;
        } else {
            if (($this->dynamicRoute !== null) && ($this->dynamicRoutePrefix !== null) &&
                (count($this->segments) >= 2)) {
                $result = method_exists($this->getDispatchable(), $this->getAction());
            }
            if (!($result) && ($this->defaultRoute !== null) && isset($this->defaultRoute['action'])) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Determine if the route has been matched
     *
     * @return bool
     */
    abstract public function hasRoute(): bool;

    /**
     * Prepare the routes
     *
     * @return AbstractMatch
     */
    abstract public function prepare(): AbstractMatch;

    /**
     * Match the route
     *
     * @param  string|array|null $forceRoute
     * @return bool
     */
    abstract public function match(mixed $forceRoute = null): bool;

    /**
     * Method to process if a route was not found
     *
     * @param  bool $exit
     * @return void
     */
    abstract public function noRouteFound(bool $exit = true): void;

}
