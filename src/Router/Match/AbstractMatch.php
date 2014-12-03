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
 * Pop router match abstract class
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractMatch
{

    /**
     * Controller string
     * @var string
     */
    protected $controller = null;

    /**
     * Action string
     * @var string
     */
    protected $action = null;

    /**
     * Get the matched controller string
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Get the matched action string
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Constructor
     *
     * Instantiate the match object
     *
     * @return AbstractMatch
     */
    abstract public function __construct();

    /**
     * Match the route to the controller class
     *
     * @param  array $routes
     * @return boolean
     */
    abstract public function match($routes);

}