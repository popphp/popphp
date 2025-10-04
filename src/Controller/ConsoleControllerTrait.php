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
namespace Pop\Controller;

use Pop\Application;
use Pop\Console\Console;

/**
 * Pop console controller trait
 *
 * @category   Pop
 * @package    Pop\Controller
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.3.8
 */
trait ConsoleControllerTrait
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
     * @param  Application $application
     * @param  Console     $console
     */
    public function __construct(Application $application, Console $console = new Console(120))
    {
        $this->application = $application;
        $this->console     = $console;
    }

    /**
     * Get application object
     *
     * @return Application
     */
    public function application(): Application
    {
        return $this->application;
    }

    /**
     * Get console object
     *
     * @return Console
     */
    public function console(): Console
    {
        return $this->console;
    }

}
