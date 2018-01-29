<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Module;

use Pop\Application;

/**
 * Pop router interface
 *
 * @category   Pop
 * @package    Pop\Module
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.2.2
 */
interface ModuleInterface
{

    /**
     * Set module name
     *
     * @param  string $name
     * @return ModuleInterface
     */
    public function setName($name);

    /**
     * Get module name
     *
     * @return string
     */
    public function getName();

    /**
     * Determine if module has name
     *
     * @return boolean
     */
    public function hasName();

    /**
     * Register a configuration with the module object
     *
     * @param  mixed $config
     * @throws \InvalidArgumentException
     * @return Module
     */
    public function registerConfig($config);

    /**
     * Register the module
     *
     * @param  Application $application
     * @return ModuleInterface
     */
    public function register(Application $application);

}