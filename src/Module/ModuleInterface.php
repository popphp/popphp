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
namespace Pop\Module;

use Pop\Application;

/**
 * Pop router interface
 *
 * @category   Pop
 * @package    Pop\Module
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.4.0
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
     * Set module version
     *
     * @param  string $version
     * @return ModuleInterface
     */
    public function setVersion($version);

    /**
     * Get module version
     *
     * @return string
     */
    public function getVersion();

    /**
     * Determine if module has version
     *
     * @return boolean
     */
    public function hasVersion();

    /**
     * Get application
     *
     * @return Application
     */
    public function application();

    /**
     * Determine if the module has been registered with an application object
     *
     * @return boolean
     */
    public function isRegistered();

    /**
     * Register the module
     *
     * @param  Application $application
     * @return ModuleInterface
     */
    public function register(Application $application);

}