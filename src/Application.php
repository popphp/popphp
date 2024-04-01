<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop;

use Pop\Utils\Helper;
use ReflectionException;

/**
 * Application class
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.2.0
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
    protected ?Service\Locator$services = null;

    /**
     * Event manager
     * @var ?Event\Manager
     */
    protected ?Event\Manager$events = null;

    /**
     * Module manager
     * @var ?Module\Manager
     */
    protected ?Module\Manager $modules = null;

    /**
     * Autoloader
     * @var mixed
     */
    protected mixed $autoloader = null;

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
            $class = (is_object($arg)) ? get_class($arg) : '';
            if ((stripos($class, 'classload') !== false) || (stripos($class, 'autoload') !== false)) {
                $autoloader = $arg;
            } else if ($arg instanceof Router\Router) {
                $this->registerRouter($arg);
            } else if ($arg instanceof Service\Locator) {
                $this->registerServices($arg);
            } else if ($arg instanceof Event\Manager) {
                $this->registerEvents($arg);
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
     * @param  mixed $autoloader
     * @throws Exception|Module\Exception|Service\Exception
     * @return static
     */
    public function bootstrap(mixed $autoloader = null): static
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
        return $this;
    }

    /**
     * Get the autoloader object
     *
     * @return mixed
     */
    public function autoloader(): mixed
    {
        return $this->autoloader;
    }

    /**
     * Access the application router
     *
     * @return Router\Router
     */
    public function router(): Router\Router
    {
        return $this->router;
    }

    /**
     * Get the service locator
     *
     * @return Service\Locator
     */
    public function services(): Service\Locator
    {
        return $this->services;
    }

    /**
     * Get the event manager
     *
     * @return Event\Manager
     */
    public function events(): Event\Manager
    {
        return $this->events;
    }

    /**
     * Access all application module configs
     *
     * @return Module\Manager
     */
    public function modules(): Module\Manager
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
     * @param  mixed $autoloader
     * @throws Exception
     * @return static
     */
    public function registerAutoloader(mixed $autoloader): static
    {
        if (!method_exists($autoloader, 'add') || !method_exists($autoloader, 'addPsr4')) {
            throw new Exception(
                'Error: The autoloader instance must contain the methods \'add\' and \'addPsr4\', ' .
                'as with Composer\Autoload\ClassLoader.'
            );
        }
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
     * @throws Event\Exception|Router\Exception|ReflectionException
     * @return void
     */
    public function run(bool $exit = true, ?string $forceRoute = null): void
    {
        try {
            $this->init();

            // Trigger any app.route.pre events
            $this->trigger('app.route.pre');

            if (($this->router !== null)) {
                $this->router->route($forceRoute);

                // Trigger any app.dispatch.post events
                $this->trigger('app.dispatch.pre');

                if ($this->router->hasController()) {
                    $controller = $this->router->getController();
                    if ($this->router->getControllerClass() == 'Closure') {
                        if ($this->router->hasRouteParams()) {
                            call_user_func_array($controller, array_values($this->router->getRouteParams()));
                        } else {
                            $controller();
                        }
                    } else {
                        $params = ($this->router->hasRouteParams()) ? $this->router->getRouteParams() : null;
                        $controller->dispatch($this->router->getAction(), $params);
                    }
                } else {
                    $this->router->noRouteFound($exit);
                }

                // Trigger any app.dispatch.post events
                $this->trigger('app.dispatch.post');
            }
        } catch (Exception $exception) {
            // Trigger any app.error events
            $this->trigger('app.error', ['exception' => $exception]);
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
