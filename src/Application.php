<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
     *
     * @return Application
     */
    public function __construct()
    {
        $args       = func_get_args();
        $autoloader = null;
        $config     = null;

        foreach ($args as $arg) {
            $class = get_class($arg);
            if ((stripos($class, 'classload') !== false) || (stripos($class, 'autoload') !== false)) {
                $autoloader = $arg;
            } else if ($arg instanceof Router\Router) {
                $this->loadRouter($arg);
            } else if ($arg instanceof Service\Locator) {
                $this->loadServices($arg);
            } else if ($arg instanceof Event\Manager) {
                $this->loadEvents($arg);
            } else if (is_array($arg) || ($arg instanceof \ArrayAccess) || ($arg instanceof \ArrayObject)) {
                $config = $arg;
            }
        }

        $this->bootstrap($autoloader);

        if (null !== $config) {
            $this->loadConfig($config);
        }
    }

    /**
     * Bootstrap the application
     *
     * @param  mixed $autoloader
     * @return Application
     */
    public function bootstrap($autoloader = null)
    {
        if (null !== $autoloader) {
            $this->registerAutoloader($autoloader);
        }
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
     * Merge new or altered config values with the existing config values
     *
     * @param  mixed   $config
     * @param  boolean $replace
     * @return Application
     */
    public function mergeConfig($config, $replace = false)
    {
        if (is_array($config) || ($config instanceof \ArrayAccess) || ($config instanceof \ArrayObject)) {
            if (null !== $this->config) {
                if ($replace) {
                    foreach ($config as $key => $value) {
                        $this->config[$key] = $value;
                    }
                } else {
                    $this->config = array_replace_recursive($this->config, $config);
                }
            } else {
                $this->config = $config;
            }
        }

        return $this;
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
     * @param  mixed  $moduleConfig
     * @throws Exception
     * @return Application
     */
    public function register($name, $moduleConfig)
    {
        if (!is_array($moduleConfig) && !($moduleConfig instanceof \ArrayAccess) &&
            !($moduleConfig instanceof \ArrayObject)) {
            throw new Exception(
                'Error: The module config must be either an array itself or implement ArrayAccess or extend ArrayObject.'
            );
        }

        $this->modules[$name] = $moduleConfig;

        // If the autoloader is set and the the module config has a
        // defined prefix and src, register the module with the autoloader
        if ((null !== $this->autoloader) && isset($moduleConfig['prefix']) &&
            isset($moduleConfig['src']) && file_exists($moduleConfig['src'])) {
            // Register as PSR-0
            if (isset($moduleConfig['psr-0']) && ($moduleConfig['psr-0'])) {
                $this->autoloader->add($moduleConfig['prefix'], $moduleConfig['src']);
            // Else, default to PSR-4
            } else {
                $this->autoloader->addPsr4($moduleConfig['prefix'], $moduleConfig['src']);
            }
        }

        // If routes are set in the module config, register them with the application
        if (isset($moduleConfig['routes']) && (null !== $this->router)) {
            $this->router->addRoutes($moduleConfig['routes']);
        }

        // If services are set in the module config, register them with the application
        if (isset($moduleConfig['services']) && (null !== $this->services)) {
            foreach ($moduleConfig['services'] as $name => $service) {
                if (isset($service['call']) && isset($service['params'])) {
                    $this->setService($name, $service['call'], $service['params']);
                } else if (isset($service['call'])) {
                    $this->setService($name, $service['call']);
                }
            }
        }

        // If events are set in the module config, register them with the application
        if (isset($moduleConfig['events']) && (null !== $this->events)) {
            foreach ($moduleConfig['events'] as $event) {
                if (isset($event['name']) && isset($event['action'])) {
                    $this->on($event['name'], $event['action'], ((isset($event['priority'])) ? $event['priority'] : 0));
                }
            }
        }

        return $this;
    }

    /**
     * Merge new or altered module config values with the existing module config values
     *
     * @param  string  $name
     * @param  mixed   $moduleConfig
     * @param  boolean $replace
     * @return Application
     */
    public function mergeModuleConfig($name, $moduleConfig, $replace = false)
    {
        if (!$this->isRegistered($name)) {
            $this->register($name, $moduleConfig);
        } else {
            if ($replace) {
                foreach ($moduleConfig as $key => $value) {
                    $this->modules[$name][$key] = $value;
                }
            } else {
                $this->modules[$name] = array_replace_recursive($this->modules[$name], $moduleConfig);
            }
        }

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
     * Get the autoloader object
     *
     * @return mixed
     */
    public function autoloader()
    {
        return $this->autoloader;
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
     * @throws Exception
     * @return Application
     */
    public function loadConfig($config)
    {
        if (!is_array($config) && !($config instanceof \ArrayAccess) && !($config instanceof \ArrayObject)) {
            throw new Exception(
                'Error: The config must be either an array itself or implement ArrayAccess or extend ArrayObject.'
            );
        }

        $this->config = $config;

        // If routes are set in the app config, register them with the application
        if (isset($this->config['routes']) && (null !== $this->router)) {
            $this->router->addRoutes($this->config['routes']);
        }

        // If services are set in the app config, register them with the application
        if (isset($this->config['services']) && (null !== $this->services)) {
            foreach ($this->config['services'] as $name => $service) {
                if (isset($service['call']) && isset($service['params'])) {
                    $this->setService($name, $service['call'], $service['params']);
                } else if (isset($service['call'])) {
                    $this->setService($name, $service['call']);
                }
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
     * Register the autoloader object
     *
     * @param  mixed $autoloader
     * @throws Exception
     * @return Application
     */
    public function registerAutoloader($autoloader)
    {
        if (!method_exists($autoloader, 'add') || !method_exists($autoloader, 'addPsr4')) {
            throw new Exception(
                'Error: The autoloader instance must contain the methods \'add\' and \'addPsr4\', as with Composer\Autoload\ClassLoader or Pop\Loader\ClassLoader.'
            );
        }
        $this->autoloader = $autoloader;
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
