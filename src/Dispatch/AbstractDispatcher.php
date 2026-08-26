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
namespace Pop\Dispatch;

use Pop\Application;

/**
 * Abstract dispatcher class
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
abstract class AbstractDispatcher implements DispatchableInterface
{

    /**
     * Application object
     * @var ?Application
     */
    protected ?Application $application = null;

    /**
     * Default action
     * @var string
     */
    protected string $defaultAction = 'error';

    /**
     * Dispatchable constructor
     *
     * @param  ?Application $application
     */
    public function __construct(?Application $application = null)
    {
        $this->application = $application;
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
     * Get application object
     *
     * @return ?Application
     */
    public function getApplication(): ?Application
    {
        return $this->application;
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
     * Has application object
     *
     * @return bool
     */
    public function hasApplication(): bool
    {
        return !empty($this->application);
    }

    /**
     * Set the default action
     *
     * @param  string $default
     * @return static
     */
    public function setDefaultAction(string $default): static
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
     * Dispatch the controller based on the action
     *
     * @param  ?string $action
     * @param  ?array  $params
     * @throws Exception
     * @return void
     */
    public function dispatch(?string $action = null, ?array $params = null): void
    {
        // Dispatch route action
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
            throw new Exception("The action to handle the route is not defined.");
        }
    }

}
