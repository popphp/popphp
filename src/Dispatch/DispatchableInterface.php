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
 * Dispatchable interface
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
interface DispatchableInterface
{

    /**
     * Set the default action
     *
     * @param  string $default
     * @return static
     */
    public function setDefaultAction(string $default): static;

    /**
     * Get the default action
     *
     * @return string
     */
    public function getDefaultAction(): string;

    /**
     * Dispatch action
     *
     * @param  ?string $action
     * @param  ?array  $params
     * @return void
     */
    public function dispatch(?string $action = null, ?array $params = null): void;

}
