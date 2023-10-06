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
namespace Pop\Module;

use Pop\Application;

/**
 * Pop router interface
 *
 * @category   Pop
 * @package    Pop\Module
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
interface ModuleInterface
{

    /**
     * Get application
     *
     * @return Application
     */
    public function application(): Application;

    /**
     * Determine if the module has been registered with an application object
     *
     * @return bool
     */
    public function isRegistered(): bool;

    /**
     * Register the module
     *
     * @param  Application $application
     * @return ModuleInterface
     */
    public function register(Application $application): ModuleInterface;

}