<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
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
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
interface ModuleInterface
{

    /**
     * Set name
     *
     * @param  string $name
     * @return static
     */
    public function setName(string $name): static;

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string;

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
