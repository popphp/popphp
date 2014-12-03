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
namespace Pop\Router\Match;

/**
 * Pop router match interface
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
interface MatchInterface
{

    /**
     * Get the matched controller class name
     *
     * @return string
     */
    public function getController();

    /**
     * Get the matched action name
     *
     * @return string
     */
    public function getAction();

    /**
     * Get the default controller class name
     *
     * @return string
     */
    public function getDefaultController();

    /**
     * Match the route to the controller class
     *
     * @param  array   $routes
     * @param  boolean $strict
     * @return boolean
     */
    public function match($routes, $strict = false);

}