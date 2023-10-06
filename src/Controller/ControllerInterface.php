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

/**
 * Pop controller interface
 *
 * @category   Pop
 * @package    Pop\Controller
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
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
     * Dispatch the controller based on the action
     *
     * @param ?string $action
     * @param ?array  $params
     * @throws Exception
     * @return void
     */
    public function dispatch(string $action = null, ?array $params = null): void;

}