<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Mvc
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mvc;

use Pop\Application\Application;
use Pop\Http\Response;
use Pop\Http\Request;

/**
 * Mvc controller class
 *
 * @category   Pop
 * @package    Pop_Mvc
 * @author     Nick Sagona, III <info@popphp.org>
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
     * Application object
     * @var Application
     */
    protected $application = null;

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
     * @param  Request     $request
     * @param  Response    $response
     * @param  Application $application
     * @param  string      $viewPath
     * @return Controller
     */
    public function __construct(Request $request = null, Response $response = null, Application $application = null, $viewPath = null)
    {
        $this->setRequest(((null !== $request) ? $request : new Request()));
        $this->setResponse(((null !== $response) ? $response : new Response()));

        if (null !== $application) {
            $this->setApplication($application);
        }
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
     * @param  Application $application
     * @return Controller
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
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
     * Get the application object
     *
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
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
            if (null !== $this->application->logger()) {
                $this->application->log("Dispatch ['" . get_class($this) . "']->" . $action . "\t" .
                    $this->request->getRequestUri() . "\t" . $this->request->getFullRequestUri(), time());
            }
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

        if (null !== $this->application->logger()) {
            $this->application->log("Response [" . $code . "]", time());
        }
        $this->response->setCode($code);

        if (null !== $headers) {
            foreach ($headers as $name => $value) {
                $this->response->setHeader($name, $value);
            }
        }

        // Trigger any dispatch events, then send the response
        if (null !== $this->application->getEventManager()->get('dispatch')) {
            $this->application->log('[Event] Dispatch', time(), 5);
        }
        $this->application->getEventManager()->trigger('dispatch', array('controller' => $this));

        $this->response->setBody($this->view->render(true));

        if (null !== $this->application->getEventManager()->get('dispatch.send')) {
            $this->application->log('[Event] Dispatch Send', time(), 5);
        }
        $this->application->getEventManager()->trigger('dispatch.send', array('controller' => $this));
        $this->response->send();
    }

}
