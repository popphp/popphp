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
     * Dispatchable object
     * @var mixed
     */
    protected mixed $dispatchable = null;

    /**
     * Action
     * @var mixed
     */
    protected mixed $action = null;

    /**
     * Dispatchable class
     * @var ?string
     */
    protected ?string $dispatchableClass = null;

    /**
     * Cache of resolved trait names (own + inherited) per dispatchable class name.
     * A class's trait/inheritance shape never changes at runtime, so this is safe
     * to share across all Router instances for the life of the process.
     * @var array<string, array<string>>
     */
    protected static array $dispatchableTraitsCache = [];

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
     * @return static
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
     * Add dispatchable params to be passed into a new dispatchable instance
     *
     * @param  string $dispatchable
     * @param  mixed  $params
     * @return static
     */
    public function addDispatchableParams(string $dispatchable, mixed $params): static
    {
        $this->routeMatch->addDispatchableParams($dispatchable, $params);
        return $this;
    }

    /**
     * Append dispatchable params to be passed into a new dispatchable instance
     *
     * @param  string $dispatchable
     * @param  mixed  $params
     * @return static
     */
    public function appendDispatchableParams(string $dispatchable, mixed $params): static
    {
        $this->routeMatch->appendDispatchableParams($dispatchable, $params);
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
        return $this->routeMatch->getDispatchableParams($dispatchable);
    }

    /**
     * Determine if the dispatchable has params
     *
     * @param  string $dispatchable
     * @return bool
     */
    public function hasDispatchableParams(string $dispatchable): bool
    {
        return $this->routeMatch->hasDispatchableParams($dispatchable);
    }

    /**
     * Remove dispatchable params
     *
     * @param  string $dispatchable
     * @return static
     */
    public function removeDispatchableParams(string $dispatchable): static
    {
        $this->routeMatch->removeDispatchableParams($dispatchable);
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
     * Get the current dispatchable object
     *
     * @return mixed
     */
    public function getDispatchable(): mixed
    {
        return $this->dispatchable;
    }

    /**
     * Determine if the router has a dispatchable
     *
     * @return bool
     */
    public function hasDispatchable(): bool
    {
        return ($this->dispatchable !== null);
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
     * Get the current dispatchable class name
     *
     * @return string
     */
    public function getDispatchableClass(): string
    {
        return $this->dispatchableClass;
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
     * @phpstan-assert-if-true Match\Http $this->routeMatch
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
     * Determine if the inbound request has a real preference for an HTML response
     *
     * @throws Exception
     * @return bool
     */
    public function acceptsHtml(): bool
    {
        return $this->httpMatch()->acceptsHtml();
    }

    /**
     * Magic method to register a route for a whitelisted custom HTTP method
     *
     * Only forwards to the HTTP match object when $name is either a real
     * method there or a whitelisted custom verb - not in HTTP mode at all, or
     * in HTTP mode but neither of those, both mean $name isn't a real method
     * on Router, so it's reported as such rather than misdiagnosed as an
     * HTTP/CLI mode mismatch or an unregistered custom HTTP verb (the
     * explicit HTTP-only proxy methods like get()/post()/addCustomMethod()
     * are unaffected - they call httpMatch() directly and still correctly
     * throw "not HTTP" when called against a CLI-mode router).
     *
     * @param  string $name
     * @param  array  $arguments
     * @throws Exception
     * @return static
     */
    public function __call(string $name, array $arguments): static
    {
        if (!$this->isHttp() ||
            (!method_exists($this->routeMatch, $name) && !$this->routeMatch->hasCustomMethod($name))) {
            throw new Exception('Error: Call to undefined method ' . static::class . '::' . $name . '()');
        }

        $this->routeMatch->{$name}(...$arguments);
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
     * @param  string|array|null $forceRoute
     * @throws Exception|ReflectionException
     * @return void
     */
    public function route(string|array|null $forceRoute = null): void
    {
        $this->dispatchable      = null;
        $this->dispatchableClass = null;
        $this->action            = null;

        if ($this->routeMatch->match($forceRoute)) {
            if ($this->routeMatch->hasDispatchable()) {
                $dispatchable       = $this->routeMatch->getDispatchable();
                $application        = App::get();
                $middlewareDisabled = App::env('MIDDLEWARE_DISABLED');

                $routeConfig = $this->routeMatch->getRouteConfig();
                if (!empty($routeConfig['middleware']) && ($middlewareDisabled != 'route') && ($middlewareDisabled != 'all') &&
                    ($application !== null)) {
                    $application->middleware->addItems(Arr::make($routeConfig['middleware']));
                }

                // If the dispatchable is a plain closure
                if ($dispatchable instanceof Closure) {
                    $this->dispatchableClass = 'Closure';
                    $this->dispatchable      = $dispatchable;
                // Else, if the dispatchable is a plain callable object
                } else if (is_string($dispatchable) && !is_subclass_of($dispatchable, 'Pop\Dispatch\AbstractDispatcher', true)) {
                    $this->dispatchableClass = 'Pop\Utils\CallableObject';
                    $this->dispatchable      = $dispatchable;
                // Else, if the dispatchable is a Dispatch\AbstractDispatcher subclass
                } else if (class_exists($dispatchable) && is_subclass_of($dispatchable, 'Pop\Dispatch\AbstractDispatcher', true)) {
                    $this->dispatchableClass = $dispatchable;
                    $dispatchableParams      = null;

                    if ($this->routeMatch->hasDispatchableParams($dispatchable)) {
                        $dispatchableParams = $this->routeMatch->getDispatchableParams($dispatchable);
                    } else if ($this->routeMatch->hasDispatchableParams('*')) {
                        $dispatchableParams = $this->routeMatch->getDispatchableParams('*');
                    }

                    // Use user pre-defined dispatchable parameters
                    if ($dispatchableParams !== null) {
                        $this->dispatchable = (new \ReflectionClass($dispatchable))->newInstanceArgs($dispatchableParams);
                    // Else, write in the dispatchable parameters
                    } else {
                        if (!isset(self::$dispatchableTraitsCache[$dispatchable])) {
                            $dispatchableTraits = class_uses($dispatchable);
                            $parentClass        = get_parent_class($dispatchable);

                            while ($parentClass !== false) {
                                $dispatchableTraits = array_merge($dispatchableTraits, class_uses($parentClass));
                                $parentClass        = get_parent_class($parentClass);
                            }

                            self::$dispatchableTraitsCache[$dispatchable] = $dispatchableTraits;
                        }

                        $dispatchableTraits = self::$dispatchableTraitsCache[$dispatchable];

                        $this->dispatchable = (in_array('Pop\Dispatch\HttpTrait', $dispatchableTraits) ||
                            in_array('Pop\Dispatch\ConsoleTrait', $dispatchableTraits)) ?
                            new $dispatchable($application) : new $dispatchable();
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
