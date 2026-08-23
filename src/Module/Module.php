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
namespace Pop\Module;

use Pop\App;
use Pop\Application;
use Pop\Utils\Arr;
use Pop\Utils\Helper;

/**
 * Pop module class
 *
 * @category   Pop
 * @package    Pop\Module
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 * @property   mixed $config
 */
class Module extends AbstractModule implements \ArrayAccess
{

    /**
     * Constructor
     *
     * Instantiate a module object
     *
     * Optional parameters are an application instance or a configuration object or array
     */
    public function __construct()
    {
        $args        = func_get_args();
        $application = null;
        $config      = null;
        $name        = null;
        $version     = null;

        foreach ($args as $arg) {
            if ($arg instanceof Application) {
                $application = $arg;
            } else if (is_array($arg) || ($arg instanceof \ArrayAccess)) {
                $config = $arg;
            } else if (preg_match('/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(-(0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(\.(0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*)?(\+[0-9a-zA-Z-]+(\.[0-9a-zA-Z-]+)*)?$/', $arg)) {
                $version = $arg;
            } else if (is_string($arg)) {
                $name = $arg;
            }
        }

        if ($name !== null) {
            $this->setName($name);
        } else if ($this->name === null) {
            $this->setName(str_replace('\\', '_', strtolower(get_called_class())));
        }

        if ($version !== null) {
            $this->setVersion($version);
        }

        if ($config !== null) {
            $this->registerConfig($config);
        }

        if ($application !== null) {
            $this->register($application);
        }
    }

    /**
     * Register module
     *
     * @param  Application $application
     * @throws Exception|\Pop\Service\Exception
     * @return static
     */
    public function register(Application $application): static
    {
        $this->application = $application;

        if ($this->config !== null) {
            $this->applyConfigMetadata();
            $this->registerConfiguredAutoloaderPrefix();
            $this->applyConfigRoutes();
            $this->applyConfigServices();
            $this->applyConfigEvents();
            $this->applyConfigMiddleware();
        }

        $this->application->modules->register($this);

        return $this;
    }

    /**
     * Set the module name and version from config, if available
     *
     * @return void
     */
    protected function applyConfigMetadata(): void
    {
        if (isset($this->config['name'])) {
            $this->setName($this->config['name']);
        }
        if (!empty($this->config['version'])) {
            $this->setVersion($this->config['version']);
        }
    }

    /**
     * If the autoloader is set and the module config has a defined prefix
     * and src, register the module with the autoloader
     *
     * @return void
     */
    protected function registerConfiguredAutoloaderPrefix(): void
    {
        if (($this->application->autoloader() !== null) &&
            isset($this->config['prefix']) && isset($this->config['src']) && file_exists($this->config['src'])
        ) {
            // Register as PSR-0
            if (isset($this->config['psr-0']) && ($this->config['psr-0'])) {
                $this->application->autoloader()->add($this->config['prefix'], $this->config['src']);
            // Else, default to PSR-4
            } else {
                $this->application->autoloader()->addPsr4($this->config['prefix'], $this->config['src']);
            }
        }
    }

    /**
     * If routes are set in the module config, register them with the application
     *
     * @return void
     */
    protected function applyConfigRoutes(): void
    {
        if (isset($this->config['routes']) && ($this->application->router() !== null)) {
            $this->application->router()->addRoutes($this->config['routes']);
        }
    }

    /**
     * If services are set in the module config, register them with the application
     *
     * @return void
     */
    protected function applyConfigServices(): void
    {
        if (isset($this->config['services']) && ($this->application->services() !== null)) {
            foreach ($this->config['services'] as $name => $service) {
                $this->application->setService($name, $service);
            }
        }
    }

    /**
     * If events are set in the module config, register them with the application
     *
     * @return void
     */
    protected function applyConfigEvents(): void
    {
        if (isset($this->config['events']) && ($this->application->events() !== null)) {
            foreach ($this->config['events'] as $event) {
                if (isset($event['name']) && isset($event['action'])) {
                    $this->application->on(
                        $event['name'],
                        $event['action'],
                        ((isset($event['priority'])) ? $event['priority'] : 0)
                    );
                }
            }
        }
    }

    /**
     * If middleware is defined in the module config, register them with the application
     *
     * @return void
     */
    protected function applyConfigMiddleware(): void
    {
        $middlewareDisabled = App::env('MIDDLEWARE_DISABLED');

        if (isset($this->config['middleware']) && ($this->application->middleware() !== null) &&
            (empty($middlewareDisabled) || ($middlewareDisabled == 'route'))) {
            $this->application->middleware()->addItems(Arr::make($this->config['middleware']));
        }
    }

    /**
     * Set a pre-designated value in the module object
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        if ($name == 'config') {
            $this->registerConfig($value);
        }
    }

    /**
     * Get a pre-designated value from the module object
     *
     * @param  string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'config' => $this->config,
            default  => null,
        };
    }

    /**
     * Determine if a pre-designated value in the module object exists
     *
     * @param  string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return match ($name) {
            'config' => ($this->config !== null),
            default  => false,
        };
    }

    /**
     * Unset a pre-designated value in the module object
     *
     * @param  string $name
     * @return void
     */
    public function __unset(mixed $name): void
    {
        if ($name == 'config') {
            $this->config = null;
        }
    }

    /**
     * Set a value in the array
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->__set($offset, $value);
    }

    /**
     * Get a value from the array
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get($offset);
    }

    /**
     * Determine if a value exists
     *
     * @param  mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->__isset($offset);
    }

    /**
     * Unset a value from the array
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->__unset($offset);
    }

}
