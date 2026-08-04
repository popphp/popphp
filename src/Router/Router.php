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
namespace Pop\Router;

use Closure;
use ReflectionException;
use Pop\App;
use Pop\Utils\Arr;

/**
 * Pop router class
 *
 * @category   Pop
 * @package    Pop\Router
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class Router
{

    /**
     * Route match object
     * @var ?Match\MatchInterface
     */
    protected ?Match\MatchInterface $routeMatch = null;

    /**
     * Controller object
     * @var mixed
     */
    protected mixed $controller = null;

    /**
     * Action
     * @var mixed
     */
    protected mixed $action = null;

    /**
     * Controller class
     * @var ?string
     */
    protected ?string $controllerClass = null;

    /**
     * Constructor
     *
     * Instantiate the router object
     *
     * @param  ?array               $routes
     * @param  ?Match\AbstractMatch $match
     */
    public function __construct(?array $routes = null, ?Match\AbstractMatch $match = null)
    {
        if ($match !== null) {
            $this->routeMatch = $match;
        } else {
            $this->routeMatch = ((stripos(php_sapi_name(), 'cli') !== false) &&
                (stripos(php_sapi_name(), 'server') === false)) ?
                new Match\Cli() : new Match\Http();
        }

        if ($routes !== null) {
            $this->addRoutes($routes);
        }
    }

    /**
     * Add a route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @return static
     */
    public function addRoute(string $route, mixed $controller): static
    {
        $this->routeMatch->addRoute($route, $controller);
        return $this;
    }

    /**
     * Add multiple controller routes
     *
     * @param  array $routes
     * @return static
     */
    public function addRoutes(array $routes): static
    {
        $this->routeMatch->addRoutes($routes);
        return $this;
    }

    /**
     * Add a route name
     *
     * @param  string $routeName
     * @return Router
     */
    public function name(string $routeName): static
    {
        $this->routeMatch->name($routeName);
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
        return $this->routeMatch->hasName($routeName);
    }

    /**
     * Get URL for the named route
     *
     * @param  string $routeName
     * @param  mixed  $params
     * @param  bool   $fqdn
     * @throws Exception
     * @return string
     */
    public function getUrl(string $routeName, mixed $params = null, bool $fqdn = false): string
    {
        if (!$this->isHttp()) {
            throw new Exception('Error: The route is not HTTP.');
        }
        return $this->routeMatch->getUrl($routeName, $params, $fqdn);
    }

    /**
     * Add controller params to be passed into a new controller instance
     *
     * @param  string $controller
     * @param  mixed  $params
     * @return static
     */
    public function addControllerParams(string $controller, mixed $params): static
    {
        $this->routeMatch->addControllerParams($controller, $params);
        return $this;
    }

    /**
     * Append controller params to be passed into a new controller instance
     *
     * @param  string $controller
     * @param  mixed  $params
     * @return static
     */
    public function appendControllerParams(string $controller, mixed $params): static
    {
        $this->routeMatch->appendControllerParams($controller, $params);
        return $this;
    }

    /**
     * Get the params assigned to the controller
     *
     * @param  string $controller
     * @return mixed
     */
    public function getControllerParams(string $controller): mixed
    {
        return $this->routeMatch->getControllerParams($controller);
    }

    /**
     * Determine if the controller has params
     *
     * @param  string $controller
     * @return bool
     */
    public function hasControllerParams(string $controller): bool
    {
        return $this->routeMatch->hasControllerParams($controller);
    }

    /**
     * Remove controller params
     *
     * @param  string $controller
     * @return static
     */
    public function removeControllerParams(string $controller): static
    {
        $this->routeMatch->removeControllerParams($controller);
        return $this;
    }

    /**
     * Get routes
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routeMatch->getRoutes();
    }

    /**
     * Get route match object
     *
     * @return Match\MatchInterface
     */
    public function getRouteMatch(): Match\MatchInterface
    {
        return $this->routeMatch;
    }

    /**
     * Determine if there is a route match
     *
     * @return bool
     */
    public function hasRoute(): bool
    {
        return $this->routeMatch->hasRoute();
    }

    /**
     * Get the params discovered from the route
     *
     * @return array
     */
    public function getRouteParams(): array
    {
        return $this->routeMatch->getRouteParams();
    }

    /**
     * Determine if the route has params
     *
     * @return bool
     */
    public function hasRouteParams(): bool
    {
        return $this->routeMatch->hasRouteParams();
    }

    /**
     * Get the current controller object
     *
     * @return mixed
     */
    public function getController(): mixed
    {
        return $this->controller;
    }

    /**
     * Determine if the router has a controller
     *
     * @return bool
     */
    public function hasController(): bool
    {
        return ($this->controller !== null);
    }

    /**
     * Get the action
     *
     * @return mixed
     */
    public function getAction(): mixed
    {
        return $this->action;
    }

    /**
     * Determine if the router has an action
     *
     * @return bool
     */
    public function hasAction(): bool
    {
        return ($this->action !== null);
    }

    /**
     * Get the current controller class name
     *
     * @return string
     */
    public function getControllerClass(): string
    {
        return $this->controllerClass;
    }

    /**
     * Determine if the route is CLI
     *
     * @return bool
     */
    public function isCli(): bool
    {
        return ($this->routeMatch instanceof Match\Cli);
    }

    /**
     * Determine if the route is HTTP
     *
     * @return bool
     */
    public function isHttp(): bool
    {
        return ($this->routeMatch instanceof Match\Http);
    }

    /**
     * Get the active HTTP match object, guarding that the router is in HTTP mode
     *
     * @throws Exception
     * @return Match\Http
     */
    protected function httpMatch(): Match\Http
    {
        if (!$this->isHttp()) {
            throw new Exception('Error: The route is not HTTP.');
        }
        return $this->routeMatch;
    }

    /**
     * Add a GET route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @throws Exception
     * @return static
     */
    public function get(string $route, mixed $controller): static
    {
        $this->httpMatch()->get($route, $controller);
        return $this;
    }

    /**
     * Add a HEAD route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @throws Exception
     * @return static
     */
    public function head(string $route, mixed $controller): static
    {
        $this->httpMatch()->head($route, $controller);
        return $this;
    }

    /**
     * Add a POST route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @throws Exception
     * @return static
     */
    public function post(string $route, mixed $controller): static
    {
        $this->httpMatch()->post($route, $controller);
        return $this;
    }

    /**
     * Add a PUT route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @throws Exception
     * @return static
     */
    public function put(string $route, mixed $controller): static
    {
        $this->httpMatch()->put($route, $controller);
        return $this;
    }

    /**
     * Add a DELETE route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @throws Exception
     * @return static
     */
    public function delete(string $route, mixed $controller): static
    {
        $this->httpMatch()->delete($route, $controller);
        return $this;
    }

    /**
     * Add a TRACE route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @throws Exception
     * @return static
     */
    public function trace(string $route, mixed $controller): static
    {
        $this->httpMatch()->trace($route, $controller);
        return $this;
    }

    /**
     * Add an OPTIONS route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @throws Exception
     * @return static
     */
    public function options(string $route, mixed $controller): static
    {
        $this->httpMatch()->options($route, $controller);
        return $this;
    }

    /**
     * Add a CONNECT route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @throws Exception
     * @return static
     */
    public function connect(string $route, mixed $controller): static
    {
        $this->httpMatch()->connect($route, $controller);
        return $this;
    }

    /**
     * Add a PATCH route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @throws Exception
     * @return static
     */
    public function patch(string $route, mixed $controller): static
    {
        $this->httpMatch()->patch($route, $controller);
        return $this;
    }

    /**
     * Add a custom HTTP method to the whitelist
     *
     * @param  string $method
     * @throws Exception
     * @return static
     */
    public function addCustomMethod(string $method): static
    {
        $this->httpMatch()->addCustomMethod($method);
        return $this;
    }

    /**
     * Add multiple custom HTTP methods to the whitelist
     *
     * @param  array $methods
     * @throws Exception
     * @return static
     */
    public function addCustomMethods(array $methods): static
    {
        $this->httpMatch()->addCustomMethods($methods);
        return $this;
    }

    /**
     * Determine if a custom HTTP method has been whitelisted
     *
     * @param  string $method
     * @throws Exception
     * @return bool
     */
    public function hasCustomMethod(string $method): bool
    {
        return $this->httpMatch()->hasCustomMethod($method);
    }

    /**
     * Determine if the last route() call matched a path whose method was rejected
     *
     * @return bool
     */
    public function hasMethodMismatch(): bool
    {
        return $this->isHttp() && $this->routeMatch->hasMethodMismatch();
    }

    /**
     * Get the methods accepted by at least one path-matching route from the last route() call
     *
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return ($this->isHttp()) ? $this->routeMatch->getAllowedMethods() : [];
    }

    /**
     * Send a 405 Method Not Allowed response
     *
     * @param  array $allowedMethods
     * @param  bool  $exit
     * @throws Exception
     * @return void
     */
    public function methodNotAllowed(array $allowedMethods, bool $exit = true): void
    {
        $this->httpMatch()->methodNotAllowed($allowedMethods, $exit);
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
        $this->httpMatch()->{$name}(...$arguments);
        return $this;
    }

    /**
     * Prepare routes
     *
     * @return static
     */
    public function prepare(): static
    {
        $this->routeMatch->prepare();
        return $this;
    }

    /**
     * Route to the correct controller
     *
     * @param  ?string $forceRoute
     * @throws Exception|ReflectionException
     * @return void
     */
    public function route(?string $forceRoute = null): void
    {
        if ($this->routeMatch->match($forceRoute)) {
            if ($this->routeMatch->hasController()) {
                $controller         = $this->routeMatch->getController();
                $application        = App::get();
                $middlewareDisabled = App::env('MIDDLEWARE_DISABLED');

                $routeConfig = $this->routeMatch->getRouteConfig();
                if (!empty($routeConfig['middleware']) && ($middlewareDisabled != 'route') && ($middlewareDisabled != 'all') &&
                    ($application !== null)) {
                    $application->middleware->addItems(Arr::make($routeConfig['middleware']));
                }

                // If controller is a plain closure
                if ($controller instanceof Closure) {
                    $this->controllerClass = 'Closure';
                    $this->controller      = $controller;
                // Else, if a controller is a plain callable object
                } else if (is_string($controller) && !is_subclass_of($controller, 'Pop\Dispatch\AbstractDispatcher', true)) {
                    $this->controllerClass = 'Pop\Utils\CallableObject';
                    $this->controller      = $controller;
                // Else, if the controller is a dispatchable controller
                } else if (class_exists($controller) && is_subclass_of($controller, 'Pop\Dispatch\AbstractDispatcher', true)) {
                    $this->controllerClass = $controller;
                    $controllerParams      = null;

                    if ($this->routeMatch->hasControllerParams($controller)) {
                        $controllerParams = $this->routeMatch->getControllerParams($controller);
                    } else if ($this->routeMatch->hasControllerParams('*')) {
                        $controllerParams = $this->routeMatch->getControllerParams('*');
                    }

                    if ($controllerParams !== null) {
                        $this->controller = (new \ReflectionClass($controller))->newInstanceArgs($controllerParams);
                    } else {
                        $controllerTraits = class_uses($controller);
                        $this->controller = (in_array('Pop\Controller\HttpControllerTrait', $controllerTraits) ||
                            in_array('Pop\Controller\ConsoleControllerTrait', $controllerTraits)) ?
                            new $controller($application) : new $controller();
                    }

                    if (!($this->controller instanceof \Pop\Dispatch\DispatchableInterface)) {
                        throw new Exception('Error: The controller must be an instance of Pop\Controller\Interface');
                    }

                    $action       = $this->routeMatch->getAction();
                    $this->action = (($action === null) && ($this->routeMatch->isDynamicRoute())) ? 'index' : $action;
                }
            }
        }
    }

    /**
     * Method to process if a route was not found
     *
     * @param  bool $exit
     * @return void
     */
    public function noRouteFound(bool $exit = true): void
    {
        $this->routeMatch->noRouteFound($exit);
    }

}
