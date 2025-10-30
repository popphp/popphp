<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
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
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.4.0
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
                $middlewareDisabled = $application->env('MIDDLEWARE_DISABLED');

                $routeConfig = $this->routeMatch->getRouteConfig();
                if (!empty($routeConfig['middleware']) && ($middlewareDisabled != 'route') && ($middlewareDisabled != 'all')) {
                    $application->middleware->addItems(Arr::make($routeConfig['middleware']));
                }

                if ($controller instanceof Closure) {
                    $this->controllerClass = 'Closure';
                    $this->controller      = $controller;
                } else if (class_exists($controller)) {
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
                        $this->controller = (class_uses($controller, 'Pop\Controller\HttpControllerTrait') ||
                            class_uses($controller, 'Pop\Controller\ConsoleControllerTrait')) ?
                            new $controller($application) : new $controller();
                    }

                    if (!($this->controller instanceof \Pop\Controller\ControllerInterface)) {
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
