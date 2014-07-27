<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Mvc
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mvc;

use Pop\Http\Response;
use Pop\Http\Request;

/**
 * Mvc controller class
 *
 * @category   Pop
 * @package    Pop_Mvc
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Controller
{

    /**
     * Request
     * @var Request
     */
    protected $request = null;

    /**
     * Response
     * @var Response
     */
    protected $response = null;

    /**
     * View object
     * @var View
     */
    protected $view = null;

    /**
     * View path
     * @var string
     */
    protected $viewPath = null;

    /**
     * Error action
     * @var string
     */
    protected $errorAction = 'error';

    /**
     * Constructor
     *
     * Instantiate the controller object
     *
     * @param  Request  $request
     * @param  Response $response
     * @param  string   $viewPath
     * @return Controller
     */
    public function __construct(Request $request = null, Response $response = null, $viewPath = null)
    {
        $this->setRequest(((null !== $request)   ? $request  : new Request()));
        $this->setResponse(((null !== $response) ? $response : new Response()));

        if (null !== $viewPath) {
            $this->setViewPath($viewPath);
        }
    }

    /**
     * Set the request object
     *
     * @param  Request $request
     * @return Controller
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Set the response object
     *
     * @param  Response $response
     * @return Controller
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Set the response object
     *
     * @param  string $viewPath
     * @return Controller
     */
    public function setViewPath($viewPath)
    {
        $this->viewPath = $viewPath;
        return $this;
    }

    /**
     * Set the error action
     *
     * @param  string $error
     * @return Controller
     */
    public function setErrorAction($error)
    {
        $this->errorAction = $error;
        return $this;
    }

    /**
     * Get the request object
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the response object
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get the view object
     *
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Get the view path
     *
     * @return string
     */
    public function getViewPath()
    {
        return $this->viewPath;
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
     * @throws Exception
     * @return Controller
     */
    public function dispatch($action = 'index')
    {
        if (method_exists($this, $action)) {
            $this->$action();
        } else {
            throw new Exception('That action is not defined in the controller.');
        }
    }

    /**
     * Finalize the request and send the response.
     *
     * @param  int   $code
     * @param  array $headers
     * @throws Exception
     * @return void
     */
    public function send($code = 200, array $headers = null)
    {
        if (null === $this->view) {
            throw new Exception('The view object is not defined.');
        }

        if (!($this->view instanceof View)) {
            throw new Exception('The view object is not an instance of Pop\Mvc\View.');
        }

        $this->response->setCode($code);

        if (null !== $headers) {
            foreach ($headers as $name => $value) {
                $this->response->setHeader($name, $value);
            }
        }

        // Trigger any dispatch events, then send the response
        $this->response->setBody($this->view->render(true));
        $this->response->send();
    }

}
