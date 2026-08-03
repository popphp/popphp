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
namespace Pop\Dispatch;

/**
 * Dispatch maintenance interface
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.4.0
 */
interface MaintenanceInterface
{

    /**
     * Set the maintenance action
     *
     * @param  string $maintenance
     * @return static
     */
    public function setMaintenanceAction(string $maintenance): static;

    /**
     * Get the maintenance action
     *
     * @return string
     */
    public function getMaintenanceAction(): string;

    /**
     * Check the bypass maintenance check
     *
     * @param  bool $bypass
     * @return static
     */
    public function setBypassMaintenance(bool $bypass = true): static;

    /**
     * Check the bypass maintenance check
     *
     * @return bool
     */
    public function bypassMaintenance(): bool;

    /**
     * Dispatch the maintenance action
     *
     * @throws \Pop\Dispatch\Exception
     * @return void
     */
    public function dispatchMaintenance(): void;

}
