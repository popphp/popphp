<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
interface ControllerInterface
{

    /**
     * Set the default action
     *
     * @param  string $default
     * @return \Pop\Controller\Controller
     */
    public function setDefaultAction($default);

    /**
     * Get the default action
     *
     * @return string
     */
    public function getDefaultAction();

    /**
     * Dispatch the controller based on the action
     *
     * @param  string $action
     * @param  array  $params
     * @throws Exception
     * @return Controller
     */
    public function dispatch($action, array $params = null);

}