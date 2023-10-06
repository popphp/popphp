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

use Pop\Config;
use InvalidArgumentException;

/**
 * Abstract application class
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
abstract class AbstractApplication implements ApplicationInterface
{

    /**
     * Name
     * @var ?string
     */
    protected ?string $name = null;

    /**
     * Version
     * @var ?string
     */
    protected ?string $version = null;

    /**
     * Application config
     * @var mixed
     */
    protected mixed $config = null;

    /**
     * Set name
     *
     * @param  string $name
     * @return static
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Determine if the name is set
     *
     * @return bool
     */
    public function hasName(): bool
    {
        return ($this->name !== null);
    }

    /**
     * Set version
     *
     * @param  string $version
     * @return static
     */
    public function setVersion(string $version): static
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Determine if version has been set
     *
     * @return bool
     */
    public function hasVersion(): bool
    {
        return ($this->version !== null);
    }

    /**
     * Access application config
     *
     * @return mixed
     */
    public function config(): mixed
    {
        return $this->config;
    }

    /**
     * Optional method that can be used to load custom operations/configurations for an application to run
     *
     * @return AbstractApplication
     */
    public function load(): AbstractApplication
    {
        return $this;
    }

    /**
     * Register a new configuration with the application
     *
     * @param  mixed $config
     * @throws InvalidArgumentException
     * @return AbstractApplication
     */
    public function registerConfig(mixed $config): AbstractApplication
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
    public function addConfigValue(string $name, string $value): AbstractApplication
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
    public function updateConfigValue(string $name, string $value): AbstractApplication
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
    public function deleteConfigValue(string $name): AbstractApplication
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
     * @param  bool  $preserve
     * @throws Config\Exception
     * @return AbstractApplication
     */
    public function mergeConfig(mixed $config, bool $preserve = false): AbstractApplication
    {
        if ($this->config instanceof Config\Config) {
            $this->config->merge($config, $preserve);
        } else if (is_array($config) || ($config instanceof \ArrayAccess) || ($config instanceof \ArrayObject)) {
            if ($this->config !== null) {
                $this->config = ($preserve) ? array_merge_recursive($this->config, $config) :
                    array_replace_recursive($this->config, $config);
            } else {
                $this->config = $config;
            }
        }

        return $this;
    }

}
