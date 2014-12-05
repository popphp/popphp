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
     * Application module configs
     * @var array
     */
    protected $modules = [];

    /**
     * Application config
     * @var mixed
     */
    protected $config = null;

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
     * Instantiate an application object
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
            if ($arg instanceof Router\Router) {
                $this->loadRouter($arg);
            } else if ($arg instanceof Service\Locator) {
                $this->loadServices($arg);
            } else if ($arg instanceof Event\Manager) {
                $this->loadEvents($arg);
            } else {
                $this->loadConfig($arg);
            }
        }

        $this->bootstrap();
    }

    /**
     * Bootstrap the application
     *
     * @return Application
     */
    public function bootstrap()
    {
        if (null === $this->router) {
            $this->router = new Router\Router();
        }
        if (null === $this->services) {
            $this->services = new Service\Locator();
        }
        if (null === $this->events) {
            $this->events = new Event\Manager();
        }
    }

    /**
     * Initialize the application
     *
     * @return Application
     */
    public function init()
    {
        $this->trigger('app.init');
        return $this;
    }

    /**
     * Register a module config with the application object
     *
     * @param  string $name
     * @param  mixed  $module
     * @return Application
     */
    public function register($name, $module)
    {
        $this->modules[$name] = $module;
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
        return (array_key_exists($name, $this->modules));
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
     * Access all application module configs
     *
     * @return array
     */
    public function modules()
    {
        return $this->modules;
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
     * Attach an event. Default hook-points are:
     *
     *   app.init
     *   app.route.pre
     *   app.route.post
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
     * Trigger an event
     *
     * @param  string $name
     * @param  array  $args
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
     * Detach an event. Default hook-points are:
     *
     *   app.init
     *   app.route.pre
     *   app.route.post
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
     * Run the application.
     *
     * @return void
     */
    public function run()
    {
        try {
            $this->init();

            // Trigger any app.route.pre events
            $this->trigger('app.route.pre');

            if ((null !== $this->router)) {
                $this->router->route();

                // Trigger any app.route.post events
                $this->trigger('app.route.post');

                $controller = null;
                $action     = null;

                // Get the routed controller
                if (null !== $this->router->getController()) {
                    $controller = $this->router->getControllerClass();
                    $action     = $this->router->getRouteMatch()->getAction();
                }

                // Trigger any app.dispatch.post events
                $this->trigger('app.dispatch.pre');

                // If controller exists, dispatch it
                if (null !== $controller) {
                    // If the controller is a closure
                    if ($controller instanceof \Closure) {
                        // If the controller->action has dispatch parameters
                        $params = $this->router()->getDispatchParams($this->router()->getRouteMatch()->getRoute());
                        if (null !== $params) {
                            if (!is_array($params)) {
                                $params = [$params];
                            }
                            call_user_func_array($controller, $params);
                        // Else, just dispatch it
                        } else {
                            $controller();
                        }
                    // Else, if it's a class
                    } else {
                        // If the controller->action has dispatch parameters
                        $params = $this->router()->getDispatchParams($this->router()->getRouteMatch()->getRoute());
                        if (null !== $params) {
                            if (!is_array($params)) {
                                $params = [$action, [$params]];
                            } else {
                                $params = array_merge([$action], [$params]);
                            }
                            call_user_func_array([$this->router->getController(), 'dispatch'], $params);
                        // Else, just dispatch it
                        } else {
                            $this->router->getController()->dispatch($action);
                        }
                    }
                }

                // Trigger any app.dispatch.post events
                $this->trigger('app.dispatch.post');
            }
        } catch (Exception $exception) {
            // Trigger any app.error events
            $this->trigger('app.error', ['exception' => $exception]);
        }
    }

}
