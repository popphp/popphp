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
     * Error action
     * @var string
     */
    protected $errorAction = 'error';

    /**
     * Set the error action
     *
     * @param  string $error
     * @return \Pop\Controller\Controller
     */
    public function setErrorAction($error)
    {
        $this->errorAction = $error;
        return $this;
    }

    /**
     * Get the error action
     *
     * @return string
     */
    public function getErrorAction()
    {
        return $this->errorAction;
    }

    /**
     * Dispatch the controller based on the action
     *
     * @param  string $action
     * @param  array  $params
     * @throws Exception
     * @return Controller
     */
    public function dispatch($action, array $params = null)
    {
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
