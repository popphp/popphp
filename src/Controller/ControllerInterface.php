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
namespace Pop\Controller;

/**
 * Pop controller interface
 *
 * @category   Pop
 * @package    Pop\Controller
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.3.8
 */
interface ControllerInterface
{

    /**
     * Set the default action
     *
     * @param  string $default
     * @return ControllerInterface
     */
    public function setDefaultAction(string $default): ControllerInterface;

    /**
     * Get the default action
     *
     * @return string
     */
    public function getDefaultAction(): string;


    /**
     * Set the maintenance action
     *
     * @param  string $maintenance
     * @return AbstractController
     */
    public function setMaintenanceAction(string $maintenance): ControllerInterface;

    /**
     * Get the maintenance action
     *
     * @return string
     */
    public function getMaintenanceAction(): string;

    /**
     * Set bypass maintenance flag
     *
     * @param  bool $bypass
     * @return static
     */
    public function setBypassMaintenance(bool $bypass = true): static;

    /**
     * Check the bypass maintenace check
     *
     * @return bool
     */
    public function bypassMaintenance(): bool;

    /**
     * Dispatch the controller based on the action
     *
     * @param ?string $action
     * @param ?array  $params
     * @throws Exception
     * @return void
     */
    public function dispatch(string $action = null, ?array $params = null): void;

}
