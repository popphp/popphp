<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
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
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.3.2
 */
abstract class AbstractModule extends AbstractApplication implements ModuleInterface
{

    /**
     * Application
     * @var Application
     */
    protected $application = null;

    /**
     * Module name
     * @var string
     */
    protected $name = null;

    /**
     * Module version
     * @var string
     */
    protected $version = null;

    /**
     * Set module name
     *
     * @param  string $name
     * @return AbstractModule
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get module name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Determine if module has name
     *
     * @return boolean
     */
    public function hasName()
    {
        return (null !== $this->name);
    }

    /**
     * Set module version
     *
     * @param  string $version
     * @return AbstractModule
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Get module version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Determine if module has version
     *
     * @return boolean
     */
    public function hasVersion()
    {
        return (null !== $this->version);
    }

    /**
     * Get application
     *
     * @return Application
     */
    public function application()
    {
        return $this->application;
    }

    /**
     * Determine if the module has been registered with an application object
     *
     * @return boolean
     */
    public function isRegistered()
    {
        return ((null !== $this->application) &&
            (null !== $this->application->modules()) && ($this->application->modules()->hasModule($this)));
    }

    /**
     * Register module
     *
     * @param  Application $application
     * @return AbstractModule
     */
    abstract public function register(Application $application);

}