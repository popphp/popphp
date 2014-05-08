<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Application
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Application;

use Pop\Config,
    Pop\Mvc\Router;

/**
 * Application class
 *
 * @category   Pop
 * @package    Pop_Application
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Application
{

    /**
     * Application config
     * @var \Pop\Config
     */
    protected $config = null;

    /**
     * Application module configs
     * @var array
     */
    protected $modules = array();

    /**
     * Application router
     * @var \Pop\Mvc\Router
     */
    protected $router = null;

    /**
     * Application events
     * @var \Pop\Event\Manager
     */
    protected $events = null;

    /**
     * Application services
     * @var \Pop\Service\Locator
     */
    protected $services = null;

    /**
     * Application logger
     * @var \Pop\Log\Logger
     */
    protected $logger = null;

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
     * @param  mixed           $config
     * @param  array           $module
     * @param  \Pop\Mvc\Router $router
     * @return \Pop\Application\Application
     */
    public function __construct($config = null, array $module = null, Router $router = null)
    {
        if (null !== $config) {
            $this->loadConfig($config);
        }

        if (null !== $module) {
            $this->loadModule($module);
        }

        if (null !== $router) {
            $this->loadRouter($router);
        }

        $this->events = new \Pop\Event\Manager();
        $this->services = new \Pop\Service\Locator();

        if (isset($this->config->log)) {
            if (!file_exists($this->config->log)) {
                touch($this->config->log);
                chmod($this->config->log, 0777);
            }
            $this->logger = new \Pop\Log\Logger(new \Pop\Log\Writer\File(realpath($this->config->log)));
        }

        if (isset($this->config->defaultDb)) {
            $default = $this->config->defaultDb;
            \Pop\Db\Record::setDb($this->config->databases->$default);
        }
    }

    /**
     * Static method to instantiate the project object and return itself
     * to facilitate chaining methods together.
     *
     * @param  mixed           $config
     * @param  array           $module
     * @param  \Pop\Mvc\Router $router
     * @return \Pop\Application\Application
     */
    public static function factory($config = null, array $module = null, Router $router = null)
    {
        return new static($config, $module, $router);
    }

    /**
     * Access the project config
     *
     * @return \Pop\Config
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * Access a project database
     *
     * @param  string $dbname
     * @return \Pop\Db\Db
     */
    public function database($dbname)
    {
        if (isset($this->config->databases) &&
            isset($this->config->databases->$dbname) &&
            ($this->config->databases->$dbname instanceof \Pop\Db\Db)) {
            return $this->config->databases->$dbname;
        } else {
            return null;
        }
    }

    /**
     * Access a project module config
     *
     * @param  string $name
     * @return \Pop\Config
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
     * @return \Pop\Mvc\Router
     */
    public function router()
    {
        return $this->router;
    }

    /**
     * Access the project logger
     *
     * @return \Pop\Log\Logger
     */
    public function logger()
    {
        return $this->logger;
    }

    /**
     * Load a project config
     *
     * @param  mixed $config
     * @throws Exception
     * @return \Pop\Application\Application
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
            throw new Exception('The project config must be either an array or an instance of Pop\\Config.');
        }

        return $this;
    }

    /**
     * Load a module config
     *
     * @param  array $module
     * @throws Exception
     * @return \Pop\Application\Application
     */
    public function loadModule(array $module)
    {
        foreach ($module as $key => $value) {
            if (is_array($value)) {
                $this->modules[$key] = new Config($value);
            } else if ($value instanceof Config) {
                $this->modules[$key] = $value;
            } else {
                throw new Exception('The module config must be either an array or an instance of Pop\\Config.');
            }
        }

        return $this;
    }

    /**
     * Load a router
     *
     * @param  \Pop\Mvc\Router $router
     * @return \Pop\Application\Application
     */
    public function loadRouter(Router $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Attach an event. Default project event name hook-points are:
     *
     *   route.pre
     *   route
     *   route.error
     *   route.post
     *
     *   dispatch.pre
     *   dispatch
     *   dispatch.send
     *   dispatch.post
     *   dispatch.error
     *
     * @param  string $name
     * @param  mixed  $action
     * @param  int    $priority
     * @return \Pop\Application\Application
     */
    public function attachEvent($name, $action, $priority = 0)
    {
        $this->events->attach($name, $action, $priority);
        return $this;
    }

    /**
     * Detach an event. Default project event name hook-points are:
     *
     *   route.pre
     *   route
     *   route.error
     *   route.post
     *
     *   dispatch.pre
     *   dispatch
     *   dispatch.send
     *   dispatch.post
     *   dispatch.error
     *
     * @param  string $name
     * @param  mixed  $action
     * @return \Pop\Application\Application
     */
    public function detachEvent($name, $action)
    {
        $this->events->detach($name, $action);
        return $this;
    }

    /**
     * Get the event Manager
     *
     * @return \Pop\Event\Manager
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
     * @return \Pop\Application\Application
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
     * @return \Pop\Service\Locator
     */
    public function getServiceLocator()
    {
        return $this->services;
    }

    /**
     * Log the project.
     *
     * @param  string $message
     * @param  int    $time
     * @param  int    $priority
     * @return void
     */
    public function log($message, $time = null, $priority = \Pop\Log\Logger::INFO)
    {
        if (null !== $this->logger) {
            if (null !== $time) {
                $end = ((stripos($message, 'send') === false) && ((stripos($message, 'kill') !== false) || (stripos($message, 'end') !== false))) ?
                    PHP_EOL : null;
                $message = "[" . ($time - $this->start) . " seconds]\t\t" . $message . $end;
            }
            $this->logger->log($priority, $message);
        }
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

            if (isset($_SERVER['REQUEST_METHOD'])) {
                $session = '[' . $_SERVER['REQUEST_METHOD'] . ']';
                if (isset($_SERVER['REMOTE_ADDR'])) {
                    $session .= ' ' . $_SERVER['REMOTE_ADDR'];
                    if (isset($_SERVER['SERVER_PORT'])) {
                        $session .= ':' . $_SERVER['SERVER_PORT'];
                    }
                    if (isset($_SERVER['HTTP_USER_AGENT'])) {
                        $session .= ' ' . $_SERVER['HTTP_USER_AGENT'];
                    }
                }
            } else {
                $session = '[CLI]';
            }

            $this->log($session, time());

            if (null !== $this->events->get('route.pre')) {
                $this->log('[Event] Pre-Route', time(), \Pop\Log\Logger::NOTICE);
            }

            // Trigger any pre-route events, route, then trigger any post-route events
            $this->events->trigger('route.pre', array('router' => $this->router));

            // If still alive after 'route.pre'
            if ($this->events->alive()) {
                $this->log('Route Start', time());
                $this->router->route($this);

                // If still alive after 'route'
                if ($this->events->alive()) {
                    if (null !== $this->events->get('route.post')) {
                        $this->log('[Event] Post-Route', time(), \Pop\Log\Logger::NOTICE);
                    }
                    $this->events->trigger('route.post', array('router' => $this->router));

                    // If still alive after 'route.post' and if a controller was properly
                    // routed and created, then dispatch it
                    if (($this->events->alive()) && (null !== $this->router->controller())) {
                        // Trigger any pre-dispatch events
                        if (null !== $this->events->get('dispatch.pre')) {
                            $this->log('[Event] Pre-Dispatch', time(), \Pop\Log\Logger::NOTICE);
                        }
                        $this->events->trigger('dispatch.pre', array('router' => $this->router));

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
                                if (null !== $this->events->get('dispatch.error')) {
                                    $this->log('[Event] Dispatch Error', time(), \Pop\Log\Logger::ERR);
                                }
                                $this->events->trigger('dispatch.error', array('router' => $this->router));
                            }
                            // If still alive after 'dispatch'
                            if ($this->events->alive()) {
                                // Trigger any post-dispatch events
                                if (null !== $this->events->get('dispatch.post')) {
                                    $this->log('[Event] Post-Dispatch', time(), \Pop\Log\Logger::NOTICE);
                                }
                                $this->events->trigger('dispatch.post', array('router' => $this->router));
                            }
                        }
                    }
                }
            }

            $this->log('Route End', time());
        }
    }

}
