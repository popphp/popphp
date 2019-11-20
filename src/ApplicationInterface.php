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
 * Application interface
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.3.3
 */
interface ApplicationInterface
{

    /**
     * Access application config
     *
     * @return ApplicationInterface
     */
    public function config();

    /**
     * Register a new configuration with the application
     *
     * @param  mixed $config
     * @throws \InvalidArgumentException
     * @return ApplicationInterface
     */
    public function registerConfig($config);

    /**
     * Add new value to config
     *
     * @param  string $name
     * @param  string $value
     * @return ApplicationInterface
     */
    public function addConfigValue($name, $value);

    /**
     * Update existing value in config
     *
     * @param  string $name
     * @param  string $value
     * @return ApplicationInterface
     */
    public function updateConfigValue($name, $value);

    /**
     * Replace existing value in config
     *
     * @param  string $name
     * @return ApplicationInterface
     */
    public function deleteConfigValue($name);

    /**
     * Merge new or altered config values with the existing config values
     *
     * @param  mixed   $config
     * @param  boolean $preserve
     * @return ApplicationInterface
     */
    public function mergeConfig($config, $preserve = false);

}