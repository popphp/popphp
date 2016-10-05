<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @package    Pop\Router
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
abstract class AbstractMatch implements MatchInterface
{

    /**
     * Matched route
     * @var string
     */
    protected $route = null;

    /**
     * Determine if the route has been matched
     *
     * @return boolean
     */
    public function hasRoute()
    {
        return (null !== $this->route);
    }

    /**
     * Match the route
     *
     * @param  array $routes
     * @return boolean
     */
    abstract public function match($routes);

    /**
     * Method to process if a route was not found
     *
     * @return void
     */
    abstract public function noRouteFound();

}
