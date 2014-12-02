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
     * Constructor
     *
     * Instantiate a application object
     *
     * @param  mixed         $config
     * @param  Router\Router $router
     * @return Application
     */
    public function __construct($config = null, Router\Router $router = null)
    {
        if (null !== $config) {
            $this->loadConfig($config);
        }
        if (null !== $router) {
            $this->loadRouter($router);
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
     * Run the project.
     *
     * @return void
     */
    public function run()
    {
        if ((null !== $this->router)) {
            $this->router->route();
        }
    }

}
