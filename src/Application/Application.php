<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Application
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Application;

use Pop\Config;
use Pop\Event\Manager;
use Pop\Mvc\Router;
use Pop\Service\Locator;

/**
 * Application class
 *
 * @category   Pop
 * @package    Pop_Application
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Application
{

    /**
     * Application config
     * @var Config
     */
    protected $config = null;

    /**
     * Application module configs
     * @var array
     */
    protected $modules = [];

    /**
     * Application router
     * @var Router
     */
    protected $router = null;

    /**
     * Application events
     * @var Manager
     */
    protected $events = null;

    /**
     * Application services
     * @var Locator
     */
    protected $services = null;

    /**
     * Application start timestamp
     * @var int
     */
    protected $start = null;

    /**
     * Constructor
     *
     * Instantiate a project object
     *
     * @param  mixed  $config
     * @param  Router $router
     * @param  mixed  $module
     * @return Application
     */
    public function __construct($config = null, Router $router = null, $module = null)
    {
        if (null !== $config) {
            $this->loadConfig($config);
        }

        if (null !== $router) {
            $this->loadRouter($router);
        }

        if (null !== $module) {
            $this->loadModule($module);
        }

        $this->events   = new Manager();
        $this->services = new Locator();

        if (isset($this->config->defaultDb)) {
            $default = $this->config->defaultDb;
            \Pop\Db\Record::setDb($this->config->databases->$default);
        }
    }

    /**
     * Access the project config
     *
     * @return Config
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * Access a project database
     *
     * @param  string $dbname
     * @return \Pop\Db\Adapter\AbstractAdapter
     */
    public function db($dbname)
    {
        if (isset($this->config->databases) &&
            isset($this->config->databases->{$dbname}) &&
            ($this->config->databases->{$dbname} instanceof \Pop\Db\Adapter\AbstractAdapter)) {
            return $this->config->databases->{$dbname};
        } else {
            return null;
        }
    }

    /**
     * Access a project module config
     *
     * @param  string $name
     * @return Config
     */
    public function module($name)
    {
        $module = null;
        if (array_key_exists($name, $this->modules)) {
            $module =  $this->modules[$name];
        }
        return $module;
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
     * Access all project module configs
     *
     * @return array
     */
    public function modules()
    {
        return $this->modules;
    }

    /**
     * Access the project router
     *
     * @return Router
     */
    public function router()
    {
        return $this->router;
    }

    /**
     * Load a project config
     *
     * @param  mixed $config
     * @throws Exception
     * @return Application
     */
    public function loadConfig($config)
    {
        // Test to see if the config is already set and changes are allowed.
        if ((null !== $this->config) && (!$this->config->changesAllowed())) {
            throw new Exception('Real-time configuration changes are not allowed.');
        }

        // Else, set the new config
        if (is_array($config)) {
            $this->config = new Config($config);
        } else if ($config instanceof Config) {
            $this->config = $config;
        } else {
            throw new Exception('The project config must be either an array or an instance of Pop\Config.');
        }

        return $this;
    }

    /**
     * Load a module config
     *
     * @param  mixed $module
     * @throws Exception
     * @return Application
     */
    public function loadModule($module)
    {
        foreach ($module as $key => $value) {
            if (is_array($value)) {
                $this->modules[$key] = new Config($value);
            } else if ($value instanceof Config) {
                $this->modules[$key] = $value;
            } else {
                throw new Exception('The module config must be either an array or an instance of Pop\Config.');
            }
        }

        return $this;
    }

    /**
     * Load a router
     *
     * @param  Router $router
     * @return Application
     */
    public function loadRouter(Router $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Attach an event. Default event name hook-points are:
     *
     *   route.pre
     *   route.post
     *   route.error
     *
     *   dispatch.pre
     *   dispatch.post
     *   dispatch.error
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
     * Detach an event. Default event name hook-points are:
     *
     *   route.pre
     *   route.post
     *   route.error
     *
     *   dispatch.pre
     *   dispatch.post
     *   dispatch.error
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
     * Get the event Manager
     *
     * @return Manager
     */
    public function getEventManager()
    {
        return $this->events;
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
     * Get the service Locator
     *
     * @return Locator
     */
    public function getServiceLocator()
    {
        return $this->services;
    }

    /**
     * Get the application start time
     *
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Run the project.
     *
     * @return void
     */
    public function run()
    {
        // If router exists, then route the project to the appropriate controller
        if (null !== $this->router) {
            $this->start = time();

            // Trigger any pre-route events, route, then trigger any post-route events
            $this->events->trigger('route.pre', ['router' => $this->router]);

            // If still alive after 'route.pre'
            if ($this->events->alive()) {
                $this->router->route($this);

                // If still alive after 'route'
                if ($this->events->alive()) {
                    $this->events->trigger('route.post', ['router' => $this->router]);

                    // If still alive after 'route.post' and if a controller was properly
                    // routed and created, then dispatch it
                    if (($this->events->alive()) && (null !== $this->router->controller())) {
                        // Trigger any pre-dispatch events
                        $this->events->trigger('dispatch.pre', ['router' => $this->router]);

                        // If still alive after 'dispatch.pre'
                        if ($this->events->alive()) {
                            // Get the action and dispatch it
                            $action = $this->router->getAction();

                            // Dispatch the found action, the error action or trigger the dispatch error events
                            if ((null !== $action) && method_exists($this->router->controller(), $action)) {
                                $this->router->controller()->dispatch($action);
                            } else if (method_exists($this->router->controller(), $this->router->controller()->getErrorAction())) {
                                $this->router->controller()->dispatch($this->router->controller()->getErrorAction());
                            } else {
                                $this->events->trigger('dispatch.error', ['router' => $this->router]);
                            }
                            // If still alive after 'dispatch'
                            if ($this->events->alive()) {
                                // Trigger any post-dispatch events
                                $this->events->trigger('dispatch.post', ['router' => $this->router]);
                            }
                        }
                    }
                } else {
                    // Trigger any route error events
                    $this->events->trigger('route.error', ['router' => $this->router]);
                }
            }
        }
    }

}
