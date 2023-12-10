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
namespace Pop\Controller;

use Pop\App;

/**
 * Pop abstract controller class
 *
 * @category   Pop
 * @package    Pop\Controller
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
abstract class AbstractController implements ControllerInterface
{

    /**
     * Default action
     * @var string
     */
    protected string $defaultAction = 'error';


    /**
     * Maintenance action
     * @var string
     */
    protected string $maintenanceAction = 'maintenance';

    /**
     * Set the default action
     *
     * @param  string $default
     * @return AbstractController
     */
    public function setDefaultAction(string $default): AbstractController
    {
        $this->defaultAction = $default;
        return $this;
    }

    /**
     * Get the default action
     *
     * @return string
     */
    public function getDefaultAction(): string
    {
        return $this->defaultAction;
    }

    /**
     * Set the maintenance action
     *
     * @param  string $maintenance
     * @return AbstractController
     */
    public function setMaintenanceAction(string $maintenance): AbstractController
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
     * Dispatch the controller based on the action
     *
     * @param  ?string $action
     * @param  ?array  $params
     * @throws Exception
     * @return void
     */
    public function dispatch(?string $action = null, ?array $params = null): void
    {
        // Handle maintenance mode
        if (App::isDown()) {
            if (method_exists($this, $this->maintenanceAction)) {
                $action = $this->maintenanceAction;
                $this->$action();
            } else {
                throw new Exception(
                    "The application is currently in maintenance mode. The action '" .
                    $action . "' is not defined in the controller."
                );
            }
        }

        // Else, dispatch route action
        if (($action !== null) && method_exists($this, $action)) {
            if ($params !== null) {
                call_user_func_array([$this, $action], array_values($params));
            } else {
                $this->$action();
            }
        // Else, fallback to default route action
        } else if (method_exists($this, $this->defaultAction)) {
            $action = $this->defaultAction;
            $this->$action();
        } else {
            throw new Exception("The action '" . $action . "' is not defined in the controller.");
        }
    }

}
