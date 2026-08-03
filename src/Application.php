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
namespace Pop;

use Pop\Console\Console;
use Pop\Http\Server\Request;
use Pop\Http\Uri;
use Pop\Utils\Arr;
use Pop\Utils\Helper;
use ReflectionException;

/**
 * Application class
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.4.0
 * @property   $config mixed
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
     * PSR-14 event dispatcher (additive - does not replace the event manager above)
     * @var ?Event\Psr14\Dispatcher
     */
    protected ?Event\Psr14\Dispatcher $psr14Dispatcher = null;

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
        if ($this->router === null) {
            $this->registerRouter(new Router\Router());
        }
        if ($this->services === null) {
            $this->registerServices(new Service\Locator());
        }
        if ($this->events === null) {
            $this->registerEvents(new Event\Manager());
        }
        if ($this->psr14Dispatcher === null) {
            $this->psr14Dispatcher = new Event\Psr14\Dispatcher(new Event\Psr14\ListenerProvider());
        }
        if ($this->middleware === null) {
            $this->registerMiddleware(new Middleware\Manager());
        }
        if ($this->modules === null) {
            $this->registerModules(new Module\Manager());
        }

        // If the autoloader is set and the application config has a
        // defined prefix and src, register with the autoloader
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

        // Load helper functions
        if ((!isset($this->config['helper_functions']) || ($this->config['helper_functions'] === true)) && (!Helper::isLoaded())) {
            Helper::loadFunctions();
        }

        // If routes are set in the app config, register them with the application
        if (isset($this->config['routes']) && ($this->router !== null)) {
            $this->router->addRoutes($this->config['routes']);
        }

        // If services are set in the app config, register them with the application
        if (isset($this->config['services']) && ($this->services !== null)) {
            foreach ($this->config['services'] as $name => $service) {
                $this->setService($name, $service);
            }
        }

        // If events are set in the app config, register them with the application
        if (isset($this->config['events']) && ($this->events !== null)) {
            foreach ($this->config['events'] as $event) {
                if (isset($event['name']) && isset($event['action'])) {
                    $this->on($event['name'], $event['action'], ((int)$event['priority'] ?? 0));
                }
            }
        }

        $middlewareDisabled = $this->env('MIDDLEWARE_DISABLED');

        // If middleware is defined in the app config, register them with the application
        if (isset($this->config['middleware']) && ($this->middleware !== null) &&
            (empty($middlewareDisabled) || ($middlewareDisabled == 'route'))) {
            $this->middleware->addItems(Arr::make($this->config['middleware']));
        }

        // Register application object with App helper class
        App::set($this);

        return $this;
    }

    /**
     * Initialize the application
     *
     * @return static
     */
    public function init(): static
    {
        $this->trigger('app.init');
        $this->psr14Dispatcher?->dispatch(new Event\Psr14\InitEvent($this));
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
     * Get the PSR-14 event dispatcher (additive - does not replace the event manager)
     *
     * @return ?Event\Psr14\Dispatcher
     */
    public function dispatcher(): ?Event\Psr14\Dispatcher
    {
        return $this->psr14Dispatcher;
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
     * @param  bool    $exit
     * @param  ?string $forceRoute
     * @throws \Throwable
     * @return void
     */
    public function run(bool $exit = true, ?string $forceRoute = null): void
    {
        try {
            $this->init();

            // Trigger any app.route.pre events
            $this->trigger('app.route.pre');
            $this->psr14Dispatcher?->dispatch(new Event\Psr14\RoutePreEvent($this));

            if (($this->router !== null)) {
                $this->router->route($forceRoute);

                // Trigger any app.dispatch.post events
                $this->trigger('app.dispatch.pre');
                $this->psr14Dispatcher?->dispatch(new Event\Psr14\DispatchPreEvent($this));

                // Dispatch
                if ($this->router->hasController()) {
                    $controller = $this->router->getController();

                    // Handle maintenance mode uniformly, regardless of route target shape
                    if (App::isDown() && !App::isSecretRequest() &&
                        !(($controller instanceof Dispatch\MaintenanceInterface) && $controller->bypassMaintenance())) {
                        if ($controller instanceof Dispatch\MaintenanceInterface) {
                            $controller->dispatchMaintenance();
                        } else {
                            $this->renderMaintenanceResponse($exit);
                        }
                    // Process middleware
                    } else if (($this->middleware !== null) && ($this->middleware->hasHandlers())) {
                        $request        = null;
                        $dispatchParams = null;
                        if ($this->router->getControllerClass() == 'Closure') {
                            $dispatch       = $controller;
                            $dispatchParams = ($this->router->hasRouteParams()) ? array_values($this->router->getRouteParams()) : null;
                        } else if ($this->router->getControllerClass() == 'Pop\Utils\CallableObject') {
                            $params   = ($this->router->hasRouteParams()) ? $this->router->getRouteParams() : null;
                            $dispatch = function() use ($controller, $params) {
                                $callableObject = new \Pop\Utils\CallableObject($controller, $params);
                                $callableObject->call();
                            };
                        } else {
                            $params   = ($this->router->hasRouteParams()) ? $this->router->getRouteParams() : null;
                            $dispatch = function() use ($controller, $params) {
                                $controller->dispatch($this->router->getAction(), $params);
                            };
                        }

                        // Retrieve request object, or create one
                        if (is_object($controller) && in_array('Pop\Controller\HttpControllerTrait', class_uses($controller))) {
                            $request = $controller->request();
                        } else if (is_object($controller) && in_array('Pop\Controller\ConsoleControllerTrait', class_uses($controller))) {
                            $request = $controller->console();
                        } else if ($this->router->isHttp()) {
                            $request = new Request(new Uri());
                        } else if ($this->router->isCli()) {
                            $request = new Console(120);
                        }

                        if ($request === null) {
                            throw new Exception('Error: Unable to retrieve the request object for the middleware.');
                        }

                        $this->middleware->process($request, $dispatch, $dispatchParams);
                    // Skip middleware or process as normal
                    } else {
                        if ($this->router->getControllerClass() == 'Closure') {
                            if ($this->router->hasRouteParams()) {
                                call_user_func_array($controller, array_values($this->router->getRouteParams()));
                            } else {
                                $controller();
                            }
                        } else if ($this->router->getControllerClass() == 'Pop\Utils\CallableObject') {
                            $params         = ($this->router->hasRouteParams()) ? $this->router->getRouteParams() : null;
                            $callableObject = new \Pop\Utils\CallableObject($controller, $params);
                            $callableObject->call();
                        } else {
                            $params = ($this->router->hasRouteParams()) ? $this->router->getRouteParams() : null;
                            $controller->dispatch($this->router->getAction(), $params);
                        }
                    }
                // Else, no route found
                } else {
                    if ($this->router->isHttp() && $this->router->hasMethodMismatch()) {
                        $this->router->methodNotAllowed($this->router->getAllowedMethods(), $exit);
                    } else {
                        $this->router->noRouteFound($exit);
                    }
                }

                // Trigger any app.dispatch.post events
                $this->trigger('app.dispatch.post');
                $this->psr14Dispatcher?->dispatch(new Event\Psr14\DispatchPostEvent($this));
            }
        } catch (\Throwable $exception) {
            // Trigger any app.error events
            $this->trigger('app.error', ['exception' => $exception]);
            $this->psr14Dispatcher?->dispatch(new Event\Psr14\ErrorEvent($this, $exception));
        }
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
        if ($this->router->isHttp()) {
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
