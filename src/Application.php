<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop;

/**
 * Application class
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.3.2
 * @property   $config mixed
 */
class Application extends AbstractApplication implements \ArrayAccess
{

    /**
     * Application router
     * @var Router\Router
     */
    protected $router = null;

    /**
     * Service locator
     * @var Service\Locator
     */
    protected $services = null;

    /**
     * Event manager
     * @var Event\Manager
     */
    protected $events = null;

    /**
     * Module manager
     * @var Module\Manager
     */
    protected $modules = null;

    /**
     * Autoloader
     * @var mixed
     */
    protected $autoloader = null;

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
            } else if (is_array($arg) || ($arg instanceof \ArrayAccess) || ($arg instanceof \ArrayObject)) {
                $config = $arg;
            }
        }

        if (null !== $config) {
            $this->registerConfig($config);
        }

        $this->bootstrap($autoloader);
    }

    /**
     * Bootstrap the application, creating the required objects if they haven't been created yet
     * and registering with the autoloader, adding routes, services and events
     *
     * @param  mixed $autoloader
     * @throws Exception
     * @throws Module\Exception
     * @throws Service\Exception
     * @return Application
     */
    public function bootstrap($autoloader = null)
    {
        if (null !== $autoloader) {
            $this->registerAutoloader($autoloader);
        }
        if (null === $this->router) {
            $this->registerRouter(new Router\Router());
        }
        if (null === $this->services) {
            $this->registerServices(new Service\Locator());
        }
        if (null === $this->events) {
            $this->registerEvents(new Event\Manager());
        }
        if (null === $this->modules) {
            $this->registerModules(new Module\Manager());
        }

        // If the autoloader is set and the the application config has a
        // defined prefix and src, register with the autoloader
        if ((null !== $this->autoloader) && isset($this->config['prefix']) &&
            isset($this->config['src']) && file_exists($this->config['src'])) {
            // Register as PSR-0
            if (isset($this->config['psr-0']) && ($this->config['psr-0'])) {
                $this->autoloader->add($this->config['prefix'], $this->config['src']);
            // Else, default to PSR-4
            } else {
                $this->autoloader->addPsr4($this->config['prefix'], $this->config['src']);
            }
        }

        // If routes are set in the app config, register them with the application
        if (isset($this->config['routes']) && (null !== $this->router)) {
            $this->router->addRoutes($this->config['routes']);
        }

        // If services are set in the app config, register them with the application
        if (isset($this->config['services']) && (null !== $this->services)) {
            foreach ($this->config['services'] as $name => $service) {
                $this->setService($name, $service);
            }
        }

        // If events are set in the app config, register them with the application
        if (isset($this->config['events']) && (null !== $this->events)) {
            foreach ($this->config['events'] as $event) {
                if (isset($event['name']) && isset($event['action'])) {
                    $this->on($event['name'], $event['action'], ((isset($event['priority'])) ? $event['priority'] : 0));
                }
            }
        }

        return $this;
    }

    /**
     * Initialize the application
     *
     * @throws Event\Exception
     * @throws \ReflectionException
     * @return Application
     */
    public function init()
    {
        $this->trigger('app.init');
        return $this;
    }

    /**
     * Get the autoloader object
     *
     * @return mixed
     */
    public function autoloader()
    {
        return $this->autoloader;
    }

    /**
     * Access the application router
     *
     * @return Router\Router
     */
    public function router()
    {
        return $this->router;
    }

    /**
     * Get the service locator
     *
     * @return Service\Locator
     */
    public function services()
    {
        return $this->services;
    }

    /**
     * Get the event manager
     *
     * @return Event\Manager
     */
    public function events()
    {
        return $this->events;
    }

    /**
     * Access all application module configs
     *
     * @return Module\Manager
     */
    public function modules()
    {
        return $this->modules;
    }

    /**
     * Register a new router object with the application
     *
     * @param  Router\Router $router
     * @return Application
     */
    public function registerRouter(Router\Router $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Register a new service locator object with the application
     *
     * @param  Service\Locator $services
     * @return Application
     */
    public function registerServices(Service\Locator $services)
    {
        $this->services = $services;
        return $this;
    }

    /**
     * Register a new event manager object with the application
     *
     * @param  Event\Manager $events
     * @return Application
     */
    public function registerEvents(Event\Manager $events)
    {
        $this->events = $events;
        return $this;
    }

    /**
     * Register a new module manager object with the application
     *
     * @param  Module\Manager $modules
     * @return Application
     */
    public function registerModules(Module\Manager $modules)
    {
        $this->modules = $modules;
        return $this;
    }

    /**
     * Register the autoloader object with the application
     *
     * @param  mixed $autoloader
     * @throws Exception
     * @return Application
     */
    public function registerAutoloader($autoloader)
    {
        if (!method_exists($autoloader, 'add') || !method_exists($autoloader, 'addPsr4')) {
            throw new Exception(
                'Error: The autoloader instance must contain the methods \'add\' and \'addPsr4\', ' .
                'as with Composer\Autoload\ClassLoader or Pop\Loader\ClassLoader.'
            );
        }
        $this->autoloader = $autoloader;
        return $this;
    }

    /**
     * Access a module object
     *
     * @param  string $name
     * @return Module\ModuleInterface
     */
    public function module($name)
    {
        return (isset($this->modules[$name])) ? $this->modules[$name] : null;
    }

    /**
     * Register a module with the module manager object
     *
     * @param  mixed $module
     * @param  string $name
     * @throws Module\Exception
     * @throws Service\Exception
     * @return Application
     */
    public function register($module, $name = null)
    {
        if (!($module instanceof Module\ModuleInterface)) {
            $module = new Module\Module($module, $this);
        }

        if (null !== $name) {
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
     * @return Application
     */
    public function unregister($name)
    {
        unset($this->modules[$name]);
        return $this;
    }

    /**
     * Determine whether a module is registered with the application object
     *
     * @param  string $name
     * @return boolean
     */
    public function isRegistered($name)
    {
        return $this->modules->isRegistered($name);
    }

    /**
     * Add a route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @return Application
     */
    public function addRoute($route, $controller)
    {
        $this->router->addRoute($route, $controller);
        return $this;
    }

    /**
     * Add routes
     *
     * @param  array $routes
     * @return Application
     */
    public function addRoutes(array $routes)
    {
        $this->router->addRoutes($routes);
        return $this;
    }

    /**
     * Set a service
     *
     * @param  string $name
     * @param  mixed $service
     * @throws Service\Exception
     * @return Application
     */
    public function setService($name, $service)
    {
        $this->services->set($name, $service);
        return $this;
    }

    /**
     * Get a service
     *
     * @param  string $name
     * @throws Service\Exception
     * @throws \ReflectionException
     * @return mixed
     */
    public function getService($name)
    {
        return $this->services->get($name);
    }

    /**
     * Remove a service
     *
     * @param  string $name
     * @return Application
     */
    public function removeService($name)
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
     * @return Application
     */
    public function on($name, $action, $priority = 0)
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
     * @return Application
     */
    public function off($name, $action)
    {
        $this->events->off($name, $action);
        return $this;
    }

    /**
     * Trigger an event
     *
     * @param  string $name
     * @param  array $args
     * @throws Event\Exception
     * @throws \ReflectionException
     * @return Application
     */
    public function trigger($name, array $args = [])
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
     * Run the application.
     *
     * @param  boolean $exit
     * @param  string  $forceRoute
     * @throws Event\Exception
     * @throws Router\Exception
     * @throws \ReflectionException
     * @return void
     */
    public function run($exit = true, $forceRoute = null)
    {
        try {
            $this->init();

            // Trigger any app.route.pre events
            $this->trigger('app.route.pre');

            if ((null !== $this->router)) {
                $this->router->route($forceRoute);

                // Trigger any app.dispatch.post events
                $this->trigger('app.dispatch.pre');

                if ($this->router->hasController()) {
                    $controller = $this->router->getController();
                    if ($this->router->getControllerClass() == 'Closure') {
                        if ($this->router->hasRouteParams()) {
                            call_user_func_array($controller, $this->router->getRouteParams());
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
     * @return Application
     */
    public function __set($name, $value)
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
        return $this;
    }

    /**
     * Get a pre-designated value from the application object
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'config':
                return $this->config;
                break;
            case 'router':
                return $this->router;
                break;
            case 'services':
                return $this->services;
                break;
            case 'events':
                return $this->events;
                break;
            case 'modules':
                return $this->modules;
                break;
            case 'autoloader':
                return $this->autoloader;
                break;
            default:
                return null;
        }
    }

    /**
     * Determine if a pre-designated value in the application object exists
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        switch ($name) {
            case 'config':
                return (null !== $this->config);
                break;
            case 'router':
                return (null !== $this->router);
                break;
            case 'services':
                return (null !== $this->services);
                break;
            case 'events':
                return (null !== $this->events);
                break;
            case 'modules':
                return (null !== $this->modules);
                break;
            case 'autoloader':
                return (null !== $this->autoloader);
                break;
            default:
                return false;
        }
    }

    /**
     * Unset a pre-designated value in the application object
     *
     * @param  string $name
     * @return Application
     */
    public function __unset($name)
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

        return $this;
    }

    /**
     * Set a pre-designated value in the application object
     *
     * @param  string $offset
     * @param  mixed $value
     * @throws Exception
     * @return Application
     */
    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }

    /**
     * Get a pre-designated value from the application object
     *
     * @param  string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * Determine if a pre-designated value in the application object exists
     *
     * @param  string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * Unset a pre-designated value in the application object
     *
     * @param  string $offset
     * @return Application
     */
    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }

}
