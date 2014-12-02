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
namespace Pop\Router;

/**
 * Pop router interface
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
interface RouterInterface
{

    /**
     * Add a controller route
     *
     * @param  string $route
     * @param  string $controller
     * @return Router
     */
    public function addController($route, $controller);

    /**
     * Add multiple controller routes
     *
     * @param  array $controllers
     * @return Router
     */
    public function addControllers(array $controllers);

    /**
     * Get the current controller object
     *
     * @return \Pop\Controller\ControllerInterface
     */
    public function getController();

    /**
     * Get array of controller class names
     *
     * @return array
     */
    public function getControllers();

    /**
     * Route to the correct controller
     *
     * @return void
     */
    public function route();

}