<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop;

/**
 * Abstract application class
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.4.0
 */
abstract class AbstractApplication implements ApplicationInterface
{

    /**
     * Application config
     * @var mixed
     */
    protected $config = null;

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
     * Register a new configuration with the application
     *
     * @param  mixed $config
     * @throws \InvalidArgumentException
     * @return AbstractApplication
     */
    public function registerConfig($config)
    {
        if (!is_array($config) && !($config instanceof \ArrayAccess) && !($config instanceof \ArrayObject)) {
            throw new \InvalidArgumentException(
                'Error: The config must be either an array itself, implement ArrayAccess or extend ArrayObject'
            );
        }

        $this->config = $config;

        return $this;
    }

    /**
     * Add new value to config
     *
     * @param  string $name
     * @param  string $value
     * @return AbstractApplication
     */
    public function addConfigValue($name, $value)
    {
        if (!isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
        return $this;
    }

    /**
     * Update existing value in config
     *
     * @param  string $name
     * @param  string $value
     * @return AbstractApplication
     */
    public function updateConfigValue($name, $value)
    {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
        return $this;
    }

    /**
     * Replace existing value in config
     *
     * @param  string $name
     * @return AbstractApplication
     */
    public function deleteConfigValue($name)
    {
        if (isset($this->config[$name])) {
            unset($this->config[$name]);
        }
        return $this;
    }

    /**
     * Merge new or altered config values with the existing config values
     *
     * @param  mixed $config
     * @param  boolean $preserve
     * @throws Config\Exception
     * @return AbstractApplication
     */
    public function mergeConfig($config, $preserve = false)
    {
        if ($this->config instanceof \Pop\Config\Config) {
            $this->config->merge($config, $preserve);
        } else if (is_array($config) || ($config instanceof \ArrayAccess) || ($config instanceof \ArrayObject)) {
            if (null !== $this->config) {
                $this->config = ($preserve) ? array_merge_recursive($this->config, $config) :
                    array_replace_recursive($this->config, $config);
            } else {
                $this->config = $config;
            }
        }

        return $this;
    }

}
