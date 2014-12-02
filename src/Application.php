<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Application
{

    /**
     * Application config
     * @var mixed
     */
    protected $config = null;

    /**
     * Application module configs
     * @var array
     */
    protected $modules = [];

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
     * Constructor
     *
     * Instantiate a application object
     *
     * Optional parameters are a service locator instance, a router instance,
     * an event manager instance or a configuration object or array
     *
     * @return Application
     */
    public function __construct()
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            if ($arg instanceof Service\Locator) {
                $this->loadServices($arg);
            } else if ($arg instanceof Event\Manager) {
                $this->loadEvents($arg);
            } else if ($arg instanceof Router\Router) {
                $this->loadRouter($arg);
            } else {
                $this->loadConfig($arg);
            }
        }
    }

    /**
     * Access an application module config
     *
     * @param  string $name
     * @return mixed
     */
    public function module($name)
    {
        return (array_key_exists($name, $this->modules)) ? $this->modules[$name] : null;
    }

    /**
     * Determine whether a module is loaded
     *
     * @param  string $name
     * @return boolean
     */
    public function isLoaded($name)
    {
        return (array_key_exists($name, $this->modules));
    }

    /**
     * Access application config
     *
     * @return mixed
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * Access all application module configs
     *
     * @return array
     */
    public function modules()
    {
        return $this->modules;
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
     * Load an application config
     *
     * @param  mixed $config
     * @return Application
     */
    public function loadConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Load a module config
     *
     * @param  string $name
     * @param  mixed  $module
     * @return Application
     */
    public function loadModule($name, $module)
    {
        $this->modules[$name] = $module;
        return $this;
    }

    /**
     * Load a module config
     *
     * @param  array $modules
     * @return Application
     */
    public function loadModules(array $modules)
    {
        foreach ($modules as $name => $module) {
            $this->loadModule($name, $module);
        }

        return $this;
    }

    /**
     * Load a router
     *
     * @param  Router\Router $router
     * @return Application
     */
    public function loadRouter(Router\Router $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Load a service locator
     *
     * @param  Service\Locator $services
     * @return Application
     */
    public function loadServices(Service\Locator $services)
    {
        $this->services = $services;
        return $this;
    }

    /**
     * Load an event manager
     *
     * @param  Event\Manager $events
     * @return Application
     */
    public function loadEvents(Event\Manager $events)
    {
        $this->events = $events;
        return $this;
    }

    /**
     * Set a service
     *
     * @param  string $name
     * @param  mixed  $call
     * @param  mixed  $params
     * @return Application
     */
    public function setService($name, $call, $params = null)
    {
        $this->services->set($name, $call, $params);
        return $this;
    }

    /**
     * Get a service
     *
     * @param  string $name
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
     * Attach an event. Default project event name hook-points are:
     *
     *   route.pre
     *   route
     *   dispatch
     *   route.error
     *   route.post
     *
     * @param  string $name
     * @param  mixed  $action
     * @param  int    $priority
     * @return Application
     */
    public function attachEvent($name, $action, $priority = 0)
    {
        $this->events->on($name, $action, $priority);
        return $this;
    }

    /**
     * Trigger an event
     *
     * @param  string $name
     * @param  array  $args
     * @return Application
     */
    public function triggerEvent($name, array $args = [])
    {
        $this->events->trigger($name, $args);
        return $this;
    }

    /**
     * Detach an event. Default project event name hook-points are:
     *
     *   route.pre
     *   route
     *   dispatch
     *   route.error
     *   route.post
     *
     * @param  string $name
     * @param  mixed  $action
     * @return Application
     */
    public function detachEvent($name, $action)
    {
        $this->events->off($name, $action);
        return $this;
    }

    /**
     * Run the project.
     *
     * @return void
     */
    public function run()
    {
        // Trigger any route.pre events
        $this->events->trigger('route.pre', ['application' => $this]);

        if ((null !== $this->router)) {
            $this->router->route($this);
        }

        // Trigger any route.post events
        $this->events->trigger('route.post', ['application' => $this]);
    }

}
