<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Dispatch;

use Pop\Application;
use Pop\Console\Console;

/**
 * Pop console trait
 *
 * @category   Pop
 * @package    Pop\Dispatch
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
trait ConsoleTrait
{

    /**
     * Application object
     * @var ?Application
     */
    protected ?Application $application = null;

    /**
     * Console object
     * @var ?Console
     */
    protected ?Console $console = null;

    /**
     * Constructor for the controller
     *
     * @param  ?Application $application
     * @param  Console      $console
     */
    public function __construct(?Application $application = null, Console $console = new Console(120))
    {
        $this->application = $application;
        $this->console     = $console;
    }

    /**
     * Get application object (alias)
     *
     * @return ?Application
     */
    public function application(): ?Application
    {
        return $this->application;
    }

    /**
     * Get console object (alias)
     *
     * @return Console
     */
    public function console(): Console
    {
        return $this->console;
    }

    /**
     * Get application object
     *
     * @return ?Application
     */
    public function getApplication(): ?Application
    {
        return $this->application;
    }

    /**
     * Get console object
     *
     * @return ?Console
     */
    public function getConsole(): ?Console
    {
        return $this->console;
    }

    /**
     * Set application object
     *
     * @param  Application $application
     * @return static
     */
    public function setApplication(Application $application): static
    {
        $this->application = $application;
        return $this;
    }

    /**
     * Set console object
     *
     * @param  Console $console
     * @return static
     */
    public function setConsole(Console $console = new Console(120)): static
    {
        $this->console = $console;
        return $this;
    }

    /**
     * Has application object
     *
     * @return bool
     */
    public function hasApplication(): bool
    {
        return !empty($this->application);
    }

    /**
     * Has console object
     *
     * @return bool
     */
    public function hasConsole(): bool
    {
        return !empty($this->console);
    }

}
