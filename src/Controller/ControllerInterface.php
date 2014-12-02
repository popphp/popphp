<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
interface ControllerInterface
{

    /**
     * Set the error action
     *
     * @param  string $error
     * @return \Pop\Controller\Controller
     */
    public function setErrorAction($error);

    /**
     * Get the error action
     *
     * @return string
     */
    public function getErrorAction();

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