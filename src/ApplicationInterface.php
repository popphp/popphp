<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop;

use InvalidArgumentException;

/**
 * Application interface
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.3.7
 */
interface ApplicationInterface
{

    /**
     * Set name
     *
     * @param  string $name
     * @return static
     */
    public function setName(string $name) : static;

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Determine if name is set
     *
     * @return bool
     */
    public function hasName(): bool;

    /**
     * Set version
     *
     * @param  string $version
     * @return static
     */
    public function setVersion(string $version): static;

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion(): string;

    /**
     * Determine if version is set
     *
     * @return bool
     */
    public function hasVersion(): bool;

    /**
     * Access application config
     *
     * @return mixed
     */
    public function config(): mixed;

    /**
     * Load application
     *
     * @return ApplicationInterface
     */
    public function load(): ApplicationInterface;

    /**
     * Register a new configuration with the application
     *
     * @param  mixed $config
     * @throws InvalidArgumentException
     * @return ApplicationInterface
     */
    public function registerConfig(mixed $config): ApplicationInterface;

    /**
     * Add new value to config
     *
     * @param  string $name
     * @param  string $value
     * @return ApplicationInterface
     */
    public function addConfigValue(string $name, string $value): ApplicationInterface;

    /**
     * Update existing value in config
     *
     * @param  string $name
     * @param  string $value
     * @return ApplicationInterface
     */
    public function updateConfigValue(string $name, string $value): ApplicationInterface;

    /**
     * Replace existing value in config
     *
     * @param  string $name
     * @return ApplicationInterface
     */
    public function deleteConfigValue(string $name): ApplicationInterface;

    /**
     * Merge new or altered config values with the existing config values
     *
     * @param  mixed $config
     * @param  bool  $preserve
     * @return ApplicationInterface
     */
    public function mergeConfig(mixed $config, bool $preserve = false): ApplicationInterface;

}
