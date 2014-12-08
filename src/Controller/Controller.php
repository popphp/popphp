<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp
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
 * Pop controller class
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Controller implements ControllerInterface
{

    /**
     * Default action
     * @var string
     */
    protected $defaultAction = 'error';

    /**
     * Set the default action
     *
     * @param  string $default
     * @return \Pop\Controller\Controller
     */
    public function setDefaultAction($default)
    {
        $this->defaultAction = $default;
        return $this;
    }

    /**
     * Get the default action
     *
     * @return string
     */
    public function getDefaultAction()
    {
        return $this->defaultAction;
    }

    /**
     * Dispatch the controller based on the action
     *
     * @param  string $action
     * @param  array  $params
     * @throws Exception
     * @return void
     */
    public function dispatch($action = null, array $params = null)
    {
        if (null === $action) {
            $action = $this->defaultAction;
        }
        if (method_exists($this, $action)) {
            if (null !== $params) {
                call_user_func_array([$this, $action], $params);
            } else {
                $this->$action();
            }
        } else {
            throw new Exception('That action is not defined in the controller.');
        }
    }

}
