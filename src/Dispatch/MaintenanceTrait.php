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

/**
 * Maintenance trait
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
trait MaintenanceTrait
{

    /**
     * Maintenance action
     * @var string
     */
    protected string $maintenanceAction = 'maintenance';

    /**
     * Bypass maintenance false
     * @var bool
     */
    protected bool $bypassMaintenance = false;

    /**
     * Set the maintenance action
     *
     * @param  string $maintenance
     * @return static
     */
    public function setMaintenanceAction(string $maintenance): static
    {
        $this->maintenanceAction = $maintenance;
        return $this;
    }

    /**
     * Get the maintenance action
     *
     * @return string
     */
    public function getMaintenanceAction(): string
    {
        return $this->maintenanceAction;
    }

    /**
     * Check the bypass maintenance check
     *
     * @param  bool $bypass
     * @return static
     */
    public function setBypassMaintenance(bool $bypass = true): static
    {
        $this->bypassMaintenance = $bypass;
        return $this;
    }

    /**
     * Check the bypass maintenance check
     *
     * @return bool
     */
    public function bypassMaintenance(): bool
    {
        return $this->bypassMaintenance;
    }

    /**
     * Dispatch the maintenance action
     *
     * @throws Exception
     * @return void
     */
    public function dispatchMaintenance(): void
    {
        if (method_exists($this, $this->maintenanceAction)) {
            $action = $this->maintenanceAction;
            $this->$action();
        } else {
            throw new Exception(
                "The application is currently in maintenance mode. The maintenance action is not defined."
            );
        }
    }

}
