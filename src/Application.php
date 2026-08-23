<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop;

use Pop\Console\Console;
use Pop\Http\Server\Request;
use Pop\Http\Uri;
use Pop\Utils\Arr;
use Pop\Utils\Helper;

/**
 * Application class
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 * @property   mixed                              $config
 * @property   ?Router\Router                     $router
 * @property   ?Service\Locator                   $services
 * @property   ?Event\Manager                     $events
 * @property   ?Middleware\Manager                $middleware
 * @property   ?Module\Manager                    $modules
 * @property   ?\Composer\Autoload\ClassLoader    $autoloader
 */
class Application extends AbstractApplication implements \ArrayAccess
{

    /**
     * Application router
     * @var ?Router\Router
     */
    protected ?Router\Router $router = null;

    /**
     * Service locator
     * @var ?Service\Locator
     */
    protected ?Service\Locator $services = null;

    /**
     * Event manager
     * @var ?Event\Manager
     */
    protected ?Event\Manager $events = null;

    /**
     * Middleware manager
     * @var ?Middleware\Manager
     */
    protected ?Middleware\Manager $middleware = null;

    /**
     * Module manager
     * @var ?Module\Manager
     */
    protected ?Module\Manager $modules = null;

    /**
     * Autoloader
     * @var ?\Composer\Autoload\ClassLoader
     */
    protected ?\Composer\Autoload\ClassLoader $autoloader = null;

    /**
     * Constructor
     *
     * Instantiate an application object
     *
     * Optional parameters are a service locator instance, a router instance,
     * an event manager instance or a configuration object or array
     */
    public function __construct()
    {
        $args       = func_get_args();
        $autoloader = null;
        $config     = null;

        foreach ($args as $arg) {
            if ($arg instanceof \Composer\Autoload\ClassLoader) {
                $autoloader = $arg;
            } else if ($arg instanceof Router\Router) {
                $this->registerRouter($arg);
            } else if ($arg instanceof Service\Locator) {
                $this->registerServices($arg);
            } else if ($arg instanceof Event\Manager) {
                $this->registerEvents($arg);
            } else if ($arg instanceof Middleware\Manager) {
                $this->registerMiddleware($arg);
            } else if ($arg instanceof Module\Manager) {
                $this->registerModules($arg);
            } else if (is_array($arg) || ($arg instanceof \ArrayAccess)) {
                $config = $arg;
            }
        }

        if ($config !== null) {
            $this->registerConfig($config);
        }

        $this->bootstrap($autoloader);
    }

    /**
     * Bootstrap the application, creating the required objects if they haven't been created yet
     * and registering with the autoloader, adding routes, services and events
     *
     * @param  ?\Composer\Autoload\ClassLoader $autoloader
     * @throws Exception|Module\Exception|Service\Exception
     * @return static
     */
    public function bootstrap(?\Composer\Autoload\ClassLoader $autoloader = null): static
    {
        if ($autoloader !== null) {
            $this->registerAutoloader($autoloader);
        }

        $this->initializeDefaultManagers();
        $this->registerConfiguredAutoloaderPrefix();
        $this->applyConfigMetadata();
        $this->loadHelperFunctions();
        $this->applyConfigRoutes();
        $this->applyConfigServices();
        $this->applyConfigEvents();
        $this->applyConfigMiddleware();

        // Register application object with App helper class
        App::set($this);

        return $this;
    }

    /**
     * Instantiate and register any manager objects not already set
     *
     * @return void
     */
    protected function initializeDefaultManagers(): void
    {
        if ($this->router === null) {
            $this->registerRouter(new Router\Router());
        }
        if ($this->services === null) {
            $this->registerServices(new Service\Locator());
        }
        if ($this->events === null) {
            $this->registerEvents(new Event\Manager());
        }
        if ($this->middleware === null) {
            $this->registerMiddleware(new Middleware\Manager());
        }
        if ($this->modules === null) {
            $this->registerModules(new Module\Manager());
        }
    }

    /**
     * If the autoloader is set and the application config has a defined
     * prefix and src, register with the autoloader
     *
     * @return void
     */
    protected function registerConfiguredAutoloaderPrefix(): void
    {
        if (($this->autoloader !== null) && isset($this->config['prefix']) &&
            isset($this->config['src']) && file_exists($this->config['src'])) {
            // Register as PSR-0
            if (isset($this->config['psr-0']) && ($this->config['psr-0'])) {
                $this->autoloader->add($this->config['prefix'], $this->config['src']);
            // Else, default to PSR-4
            } else {
                $this->autoloader->addPsr4($this->config['prefix'], $this->config['src']);
            }
        }
    }

    /**
     * Set the app name and version from config, if present
     *
     * @return void
     */
    protected function applyConfigMetadata(): void
    {
        // Set the app name
        if (!empty($this->config['name'])) {
            $this->setName($this->config['name']);
        } else if (!empty(App::name())) {
            $this->setName(App::name());
        }

        // Set the app version
        if (!empty($this->config['version'])) {
            $this->setVersion($this->config['version']);
        }
    }

    /**
     * Load helper functions, unless disabled by config
     *
     * @return void
     */
    protected function loadHelperFunctions(): void
    {
        if ((!isset($this->config['helper_functions']) || ($this->config['helper_functions'] === true)) && (!Helper::isLoaded())) {
            Helper::loadFunctions();
        }
    }

    /**
     * If routes are set in the app config, register them with the application
     *
     * @return void
     */
    protected function applyConfigRoutes(): void
    {
        if (isset($this->config['routes']) && ($this->router !== null)) {
            $this->router->addRoutes($this->config['routes']);
        }
    }

    /**
     * If services are set in the app config, register them with the application
     *
     * @return void
     */
    protected function applyConfigServices(): void
    {
        if (isset($this->config['services']) && ($this->services !== null)) {
            foreach ($this->config['services'] as $name => $service) {
                $this->setService($name, $service);
            }
        }
    }

    /**
     * If events are set in the app config, register them with the application
     *
     * @return void
     */
    protected function applyConfigEvents(): void
    {
        if (isset($this->config['events']) && ($this->events !== null)) {
            foreach ($this->config['events'] as $event) {
                if (isset($event['name']) && isset($event['action'])) {
                    $this->on($event['name'], $event['action'], ((int)($event['priority'] ?? 0)));
                }
            }
        }
    }

    /**
     * If middleware is defined in the app config, register them with the application
     *
     * @return void
     */
    protected function applyConfigMiddleware(): void
    {
        $middlewareDisabled = $this->env('MIDDLEWARE_DISABLED');

        if (isset($this->config['middleware']) && ($this->middleware !== null) &&
            (empty($middlewareDisabled) || ($middlewareDisabled == 'route'))) {
            $this->middleware->addItems(Arr::make($this->config['middleware']));
        }
    }

    /**
     * Initialize the application
     *
     * @return static
     */
    public function init(): static
    {
        $this->events->dispatch(new Event\InitEvent($this));
        return $this;
    }

    /**
     * Get the autoloader object
     *
     * @return ?\Composer\Autoload\ClassLoader
     */
    public function autoloader(): ?\Composer\Autoload\ClassLoader
    {
        return $this->autoloader;
    }

    /**
     * Access the application router
     *
     * @return ?Router\Router
     */
    public function router(): ?Router\Router
    {
        return $this->router;
    }

    /**
     * Get the service locator
     *
     * @return ?Service\Locator
     */
    public function services(): ?Service\Locator
    {
        return $this->services;
    }

    /**
     * Get the event manager
     *
     * @return ?Event\Manager
     */
    public function events(): ?Event\Manager
    {
        return $this->events;
    }

    /**
     * Get the middleware manager
     *
     * @return ?Middleware\Manager
     */
    public function middleware(): ?Middleware\Manager
    {
        return $this->middleware;
    }

    /**
     * Access all application module configs
     *
     * @return ?Module\Manager
     */
    public function modules(): ?Module\Manager
    {
        return $this->modules;
    }

    /**
     * Register a new router object with the application
     *
     * @param  Router\Router $router
     * @return static
     */
    public function registerRouter(Router\Router $router): static
    {
        $this->router = $router;
        Router\Route::setRouter($router);
        return $this;
    }

    /**
     * Register a new service locator object with the application
     *
     * @param  Service\Locator $services
     * @return static
     */
    public function registerServices(Service\Locator $services): static
    {
        $this->services = $services;
        return $this;
    }

    /**
     * Register a new event manager object with the application
     *
     * @param  Event\Manager $events
     * @return static
     */
    public function registerEvents(Event\Manager $events): static
    {
        $this->events = $events;
        return $this;
    }

    /**
     * Register a new middleware manager object with the application
     *
     * @param  Middleware\Manager $middleware
     * @return static
     */
    public function registerMiddleware(Middleware\Manager $middleware): static
    {
        $this->middleware = $middleware;
        return $this;
    }

    /**
     * Register a new module manager object with the application
     *
     * @param  Module\Manager $modules
     * @return static
     */
    public function registerModules(Module\Manager $modules): static
    {
        $this->modules = $modules;
        return $this;
    }

    /**
     * Register the autoloader object with the application
     *
     * @param  \Composer\Autoload\ClassLoader $autoloader
     * @return static
     */
    public function registerAutoloader(\Composer\Autoload\ClassLoader $autoloader): static
    {
        $this->autoloader = $autoloader;
        return $this;
    }

    /**
     * Merge another service locator's items into this application's service locator
     *
     * @param  Service\Locator $services
     * @return static
     */
    public function mergeServices(Service\Locator $services): static
    {
        $this->services()?->addItems($services->getItems());
        return $this;
    }

    /**
     * Merge another middleware manager's items into this application's middleware manager
     *
     * @param  Middleware\Manager $middleware
     * @return static
     */
    public function mergeMiddleware(Middleware\Manager $middleware): static
    {
        $this->middleware()?->addItems($middleware->getItems());
        return $this;
    }

    /**
     * Merge another event manager's listeners into this application's event manager,
     * combining listeners for shared event names instead of replacing them
     *
     * @param  Event\Manager $events
     * @return static
     */
    public function mergeEvents(Event\Manager $events): static
    {
        foreach ($events->getItems() as $name => $queue) {
            $clone = clone $queue;
            $clone->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);
            foreach ($clone as $entry) {
                $this->events()?->on($name, $entry['data'], $entry['priority']);
            }
        }
        return $this;
    }

    /**
     * Merge another application's services, middleware, events and config into this application
     *
     * @param  Application $application
     * @param  bool        $preserveConfig
     * @param  array       $configExclude
     * @return static
     */
    public function mergeApplication(Application $application, bool $preserveConfig = false, array $configExclude = []): static
    {
        if ($application->services() !== null) {
            $this->mergeServices($application->services());
        }
        if ($application->middleware() !== null) {
            $this->mergeMiddleware($application->middleware());
        }
        if ($application->events() !== null) {
            $this->mergeEvents($application->events());
        }
        if ($application->config() !== null) {
            $this->mergeConfig($application->config(), $preserveConfig, $configExclude);
        }

        return $this;
    }

    /**
     * Access a module object
     *
     * @param  string $name
     * @return ?Module\ModuleInterface
     */
    public function module(string $name): ?Module\ModuleInterface
    {
        return $this->modules[$name] ?? null;
    }

    /**
     * Register a module with the module manager object
     *
     * @param  mixed   $module
     * @param  ?string $name
     * @throws Module\Exception|Service\Exception
     * @return static
     */
    public function register(mixed $module, ?string $name = null): static
    {
        if (!($module instanceof Module\ModuleInterface)) {
            $module = new Module\Module($module, $this);
        }

        if ($name !== null) {
            $module->setName($name);
        }

        if (!$module->isRegistered()) {
            $module->register($this);
        }

        return $this;
    }

    /**
     * Unregister a module with the module manager object
     *
     * @param  string $name
     * @return static
     */
    public function unregister(string $name): static
    {
        unset($this->modules[$name]);
        return $this;
    }

    /**
     * Determine whether a module is registered with the application object
     *
     * @param  string $name
     * @return bool
     */
    public function isRegistered(string $name): bool
    {
        return $this->modules->isRegistered($name);
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
        $this->router->addRoute($route, $controller);
        return $this;
    }

    /**
     * Add routes
     *
     * @param  array $routes
     * @return static
     */
    public function addRoutes(array $routes): static
    {
        $this->router->addRoutes($routes);
        return $this;
    }

    /**
     * Get the active HTTP router, guarding that the application is routed for HTTP
     *
     * @throws Exception
     * @return Router\Router
     */
    protected function httpRouter(): Router\Router
    {
        if (($this->router === null) || (!$this->router->isHttp())) {
            throw new Exception('Error: The application is not routed for HTTP.');
        }
        return $this->router;
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
        $this->httpRouter()->get($route, $controller);
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
        $this->httpRouter()->head($route, $controller);
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
        $this->httpRouter()->post($route, $controller);
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
        $this->httpRouter()->put($route, $controller);
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
        $this->httpRouter()->delete($route, $controller);
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
        $this->httpRouter()->trace($route, $controller);
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
        $this->httpRouter()->options($route, $controller);
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
        $this->httpRouter()->connect($route, $controller);
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
        $this->httpRouter()->patch($route, $controller);
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
        $this->httpRouter()->addCustomMethod($method);
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
        $this->httpRouter()->addCustomMethods($methods);
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
        return $this->httpRouter()->hasCustomMethod($method);
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
        $this->httpRouter()->{$name}(...$arguments);
        return $this;
    }

    /**
     * Set a service
     *
     * @param  string $name
     * @param  mixed  $service
     * @throws Service\Exception
     * @return static
     */
    public function setService(string $name, mixed $service): static
    {
        $this->services->set($name, $service);
        return $this;
    }

    /**
     * Get a service
     *
     * @param  string $name
     * @throws Service\Exception
     * @return mixed
     */
    public function getService(string $name): mixed
    {
        return $this->services->get($name);
    }

    /**
     * Remove a service
     *
     * @param  string $name
     * @return static
     */
    public function removeService(string $name): static
    {
        $this->services->remove($name);
        return $this;
    }

    /**
     * Attach an event. Default hook-points are:
     *
     *   app.init
     *   app.route.pre
     *   app.dispatch.pre
     *   app.dispatch.post
     *   app.error
     *
     * @param  string $name
     * @param  mixed  $action
     * @param  int    $priority
     * @return static
     */
    public function on(string $name, mixed $action, int $priority = 0): static
    {
        $this->events->on($name, $action, $priority);
        return $this;
    }

    /**
     * Detach an event. Default hook-points are:
     *
     *   app.init
     *   app.route.pre
     *   app.dispatch.pre
     *   app.dispatch.post
     *   app.error
     *
     * @param  string $name
     * @param  mixed  $action
     * @return static
     */
    public function off(string $name, mixed $action): static
    {
        $this->events->off($name, $action);
        return $this;
    }

    /**
     * Trigger an event
     *
     * @param  string $name
     * @param  array $args
     * @return static
     */
    public function trigger(string $name, array $args = []): static
    {
        if (count($args) == 0) {
            $args = ['application' => $this];
        } else if (!in_array($this, $args, true)) {
            $args['application'] = $this;
        }
        $this->events->trigger($name, $args);
        return $this;
    }

    /**
     * Add a middleware handler
     *
     * @param  mixed $handler
     * @param  mixed $name
     * @return static
     */
    public function addMiddleware(mixed $handler, mixed $name = null): static
    {
        $this->middleware->addHandler($handler, $name);
        return $this;
    }

    /**
     * Get middleware
     *
     * @param  mixed $name
     * @return mixed
     */
    public function getMiddleware(mixed $name): mixed
    {
        return $this->middleware->getHandler($name);
    }

    /**
     * Remove middleware
     *
     * @param  mixed $name
     * @return static
     */
    public function removeMiddleware(mixed $name): static
    {
        $this->middleware->removeHandler($name);
        return $this;
    }

    /**
     * Get environment value
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function env(string $key, mixed $default = null): mixed
    {
        return App::env($key, $default);
    }

    /**
     * Get application environment
     *
     * @param  mixed $env
     * @return string|null|bool
     */
    public function environment(mixed $env = null): string|null|bool
    {
        return App::environment($env);
    }

    /**
     * Get application name (alias method)
     *
     * @return ?string
     */
    public function name(): ?string
    {
        return $this->name;
    }

    /**
     * Get application URL
     *
     * @return ?string
     */
    public function url(): ?string
    {
        return App::url();
    }

    /**
     * Check if application environment is local
     *
     * @return bool
     */
    public function isLocal(): bool
    {
        return App::isLocal();
    }

    /**
     * Check if application environment is dev
     *
     * @return bool
     */
    public function isDev(): bool
    {
        return App::isDev();
    }

    /**
     * Check if application environment is testing
     *
     * @return bool
     */
    public function isTesting(): bool
    {
        return App::isTesting();
    }

    /**
     * Check if application environment is staging
     *
     * @return bool
     */
    public function isStaging(): bool
    {
        return App::isStaging();
    }

    /**
     * Check if application environment is production
     *
     * @return bool
     */
    public function isProduction(): bool
    {
        return App::isProduction();
    }

    /**
     * Check if application is in maintenance mode
     *
     * @return bool
     */
    public function isDown(): bool
    {
        return App::isDown();
    }

    /**
     * Check if application is in not maintenance mode
     *
     * @return bool
     */
    public function isUp(): bool
    {
        return App::isUp();
    }

    /**
     * Run the application
     *
     * @param  bool              $exit
     * @param  string|array|null $forceRoute
     * @throws \Throwable
     * @return void
     */
    public function run(bool $exit = true, string|array|null $forceRoute = null): void
    {
        try {
            $this->init();

            // Fire any app.route.pre listeners
            $this->events->dispatch(new Event\RoutePreEvent($this));

            if (($this->router !== null)) {
                $this->router->route($forceRoute);

                // Fire any app.dispatch.pre listeners
                $this->events->dispatch(new Event\DispatchPreEvent($this));

                // Dispatch
                if ($this->router->hasDispatchable()) {
                    $dispatchable = $this->router->getDispatchable();

                    // Handle maintenance mode uniformly, regardless of route target shape
                    if (App::isDown() && !App::isSecretRequest() &&
                        !(($dispatchable instanceof Dispatch\MaintenanceInterface) && $dispatchable->bypassMaintenance())) {
                        if ($dispatchable instanceof Dispatch\MaintenanceInterface) {
                            $dispatchable->dispatchMaintenance();
                        } else {
                            $this->renderMaintenanceResponse($exit);
                        }
                    // Process middleware
                    } else if (($this->middleware !== null) && ($this->middleware->hasHandlers())) {
                        [$dispatch, $dispatchParams] = $this->buildDispatch($dispatchable);
                        $request = $this->resolveMiddlewareRequest($dispatchable);

                        if ($request === null) {
                            throw new Exception('Error: Unable to retrieve the request object for the middleware.');
                        }

                        $this->middleware->process($request, $dispatch, $dispatchParams);
                    // Skip middleware or process as normal
                    } else {
                        $this->invokeDispatchable($dispatchable);
                    }
                // Else, no route found
                } else {
                    if ($this->router->isHttp() && $this->router->hasMethodMismatch()) {
                        $this->router->methodNotAllowed($this->router->getAllowedMethods(), $exit);
                    } else {
                        $this->router->noRouteFound($exit);
                    }
                }

                // Fire any app.dispatch.post listeners
                $this->events->dispatch(new Event\DispatchPostEvent($this));
            }
        } catch (Event\AbortException) {
            return;
        } catch (\Throwable $exception) {
            // Fire any app.error listeners
            $this->events->dispatch(new Event\ErrorEvent($this, $exception));
            throw $exception;
        }
    }

    /**
     * Invoke the dispatchable directly, bypassing the middleware pipeline
     *
     * @param  mixed $dispatchable
     * @return void
     */
    protected function invokeDispatchable(mixed $dispatchable): void
    {
        [$dispatch, $dispatchParams] = $this->buildDispatch($dispatchable);

        if ($dispatchParams !== null) {
            call_user_func_array($dispatch, $dispatchParams);
        } else {
            $dispatch();
        }
    }

    /**
     * Build the dispatch closure and its params for the given dispatchable -
     * used both as the deferred callable the middleware pipeline invokes once
     * its handler chain completes, and by invokeDispatchable() to call the
     * same logic immediately when there's no middleware to defer to
     *
     * @param  mixed $dispatchable
     * @return array
     */
    protected function buildDispatch(mixed $dispatchable): array
    {
        if ($this->router->getDispatchableClass() == 'Closure') {
            $dispatch       = $dispatchable;
            $dispatchParams = ($this->router->hasRouteParams()) ? array_values($this->router->getRouteParams()) : null;
        } else if ($this->router->getDispatchableClass() == 'Pop\Utils\CallableObject') {
            $params         = ($this->router->hasRouteParams()) ? $this->router->getRouteParams() : null;
            $dispatch       = function() use ($dispatchable, $params) {
                $callableObject = new \Pop\Utils\CallableObject($dispatchable, $params);
                $callableObject->call();
            };
            $dispatchParams = null;
        } else {
            $params         = ($this->router->hasRouteParams()) ? $this->router->getRouteParams() : null;
            $dispatch       = function() use ($dispatchable, $params) {
                $dispatchable->dispatch($this->router->getAction(), $params);
            };
            $dispatchParams = null;
        }

        return [$dispatch, $dispatchParams];
    }

    /**
     * Resolve the request object to pass into the middleware pipeline
     *
     * @param  mixed $dispatchable
     * @return mixed
     */
    protected function resolveMiddlewareRequest(mixed $dispatchable): mixed
    {
        if (is_object($dispatchable) && in_array('Pop\Dispatch\HttpTrait', class_uses($dispatchable))) {
            return $dispatchable->request();
        } else if (is_object($dispatchable) && in_array('Pop\Dispatch\ConsoleTrait', class_uses($dispatchable))) {
            return $dispatchable->console();
        } else if ($this->router->isHttp()) {
            return new Request(new Uri());
        } else if ($this->router->isCli()) {
            return new Console(120);
        }

        return null;
    }

    /**
     * Render a default maintenance-mode response for route targets that
     * aren't a Dispatch\MaintenanceInterface (closures, callables) and so
     * have no custom maintenance action of their own to run
     *
     * @param  bool $exit
     * @return void
     */
    protected function renderMaintenanceResponse(bool $exit): void
    {
        if ($this->router->isHttp() && $this->router->acceptsHtml()) {
            if (!headers_sent()) {
                header('HTTP/1.1 503 Service Unavailable');
            }
            echo '<!DOCTYPE html>' . PHP_EOL;
            echo '<html>' . PHP_EOL;
            echo '    <head>' . PHP_EOL;
            echo '        <title>Service Unavailable</title>' . PHP_EOL;
            echo '    </head>' . PHP_EOL;
            echo '<body>' . PHP_EOL;
            echo '    <h1>Service Unavailable</h1>' . PHP_EOL;
            echo '</body>' . PHP_EOL;
            echo '</html>' . PHP_EOL;
        } else if ($this->router->isHttp()) {
            if (!headers_sent()) {
                header('HTTP/1.1 503 Service Unavailable');
                header('Content-Type: application/json');
            }
            echo json_encode(['error' => 'Service Unavailable'], JSON_PRETTY_PRINT) . PHP_EOL;
        } else {
            echo PHP_EOL . 'Service Unavailable.' . PHP_EOL . PHP_EOL;
        }

        if ($exit) {
            exit();
        }
    }

    /**
     * Set a pre-designated value in the application object
     *
     * @param  string $name
     * @param  mixed $value
     * @throws Exception
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        switch ($name) {
            case 'config':
                $this->registerConfig($value);
                break;
            case 'router':
                $this->registerRouter($value);
                break;
            case 'services':
                $this->registerServices($value);
                break;
            case 'events':
                $this->registerEvents($value);
                break;
            case 'middleware':
                $this->registerMiddleware($value);
                break;
            case 'modules':
                $this->registerModules($value);
                break;
            case 'autoloader':
                $this->registerAutoloader($value);
                break;
        }
    }

    /**
     * Get a pre-designated value from the application object
     *
     * @param  string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'config'     => $this->config,
            'router'     => $this->router,
            'services'   => $this->services,
            'events'     => $this->events,
            'middleware' => $this->middleware,
            'modules'    => $this->modules,
            'autoloader' => $this->autoloader,
            default      => null,
        };
    }

    /**
     * Determine if a pre-designated value in the application object exists
     *
     * @param  string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return match ($name) {
            'config'     => ($this->config !== null),
            'router'     => ($this->router !== null),
            'services'   => ($this->services !== null),
            'events'     => ($this->events !== null),
            'middleware' => ($this->middleware !== null),
            'modules'    => ($this->modules !== null),
            'autoloader' => ($this->autoloader !== null),
            default      => false,
        };
    }

    /**
     * Unset a pre-designated value in the application object
     *
     * @param  string $name
     * @return void
     */
    public function __unset(string $name): void
    {
        switch ($name) {
            case 'config':
                $this->config = null;
                break;
            case 'router':
                $this->router = null;
                break;
            case 'services':
                $this->services = null;
                break;
            case 'events':
                $this->events = null;
                break;
            case 'middleware':
                $this->middleware = null;
                break;
            case 'modules':
                $this->modules = null;
                break;
            case 'autoloader':
                $this->autoloader = null;
                break;
        }
    }

    /**
     * Set a pre-designated value in the application object
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @throws Exception
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->__set($offset, $value);
    }

    /**
     * Get a pre-designated value from the application object
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get($offset);
    }

    /**
     * Determine if a pre-designated value in the application object exists
     *
     * @param  mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->__isset($offset);
    }

    /**
     * Unset a pre-designated value in the application object
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->__unset($offset);
    }

}
