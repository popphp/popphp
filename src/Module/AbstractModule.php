<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Module;

use Pop\Application;
use Pop\AbstractApplication;

/**
 * Abstract module class
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.4.0
 */
abstract class AbstractModule extends AbstractApplication implements ModuleInterface
{

    /**
     * Application
     * @var ?Application
     */
    protected ?Application $application = null;

    /**
     * Get application
     *
     * @return Application
     */
    public function application(): Application
    {
        return $this->application;
    }

    /**
     * Determine if the module has been registered with an application object
     *
     * @return bool
     */
    public function isRegistered(): bool
    {
        return (($this->application !== null) &&
            ($this->application->modules() !== null) && ($this->application->modules()->hasModule($this)));
    }

    /**
     * Register module
     *
     * @param  Application $application
     * @return AbstractModule
     */
    abstract public function register(Application $application): AbstractModule;

}
