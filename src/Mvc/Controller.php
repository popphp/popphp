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

use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Project\Project;

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
     * @var \Pop\Http\Request
     */
    protected $request = null;

    /**
     * Response
     * @var \Pop\Http\Response
     */
    protected $response = null;

    /**
     * Project config object
     * @var \Pop\Project\Project
     */
    protected $project = null;

    /**
     * View object
     * @var \Pop\Mvc\View
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
     * @param \Pop\Http\Request    $request
     * @param \Pop\Http\Response   $response
     * @param \Pop\Project\Project $project
     * @param string               $viewPath
     * @return \Pop\Mvc\Controller
     */
    public function __construct(Request $request = null, Response $response = null, Project $project = null, $viewPath = null)
    {
        $this->request = (null !== $request) ? $request : new Request();
        $this->response = (null !== $response) ? $response : new Response();

        if (null !== $project) {
            $this->project = $project;
        }

        if (null !== $viewPath) {
            $this->viewPath = $viewPath;
        }
    }

    /**
     * Set the request object
     *
     * @param  \Pop\Http\Request $request
     * @return \Pop\Mvc\Controller
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Set the response object
     *
     * @param  \Pop\Http\Response $response
     * @return \Pop\Mvc\Controller
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Set the response object
     *
     * @param  \Pop\Project\Project $project
     * @return \Pop\Mvc\Controller
     */
    public function setProject(Project $project)
    {
        $this->project = $project;
        return $this;
    }

    /**
     * Set the response object
     *
     * @param  string $viewPath
     * @return \Pop\Mvc\Controller
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
     * @return \Pop\Mvc\Controller
     */
    public function setErrorAction($error)
    {
        $this->errorAction = $error;
        return $this;
    }

    /**
     * Get the request object
     *
     * @return \Pop\Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the response object
     *
     * @return \Pop\Http\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get the project object
     *
     * @return \Pop\Project\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Get the view object
     *
     * @return \Pop\Mvc\View
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
     * @throws \Pop\Mvc\Exception
     * @return \Pop\Mvc\Controller
     */
    public function dispatch($action = 'index')
    {
        if (method_exists($this, $action)) {
            if (null !== $this->project->logger()) {
                $this->project->log("Dispatch ['" . get_class($this) . "']->" . $action . "\t" . $this->request->getRequestUri() . "\t" . $this->request->getFullUri(), time());
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
     * @throws \Pop\Mvc\Exception
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

        if (null !== $this->project->logger()) {
            $this->project->log("Response [" . $code . "]", time());
        }
        $this->response->setCode($code);

        if (null !== $headers) {
            foreach ($headers as $name => $value) {
                $this->response->setHeader($name, $value);
            }
        }

        // Trigger any dispatch events, then send the response
        if (null !== $this->project->getEventManager()->get('dispatch')) {
            $this->project->log('[Event] Dispatch', time(), \Pop\Log\Logger::NOTICE);
        }
        $this->project->getEventManager()->trigger('dispatch', array('controller' => $this));

        $this->response->setBody($this->view->render(true));

        if (null !== $this->project->getEventManager()->get('dispatch.send')) {
            $this->project->log('[Event] Dispatch Send', time(), \Pop\Log\Logger::NOTICE);
        }
        $this->project->getEventManager()->trigger('dispatch.send', array('controller' => $this));
        $this->response->send();
    }

    /**
     * Method to send a JSON response
     *
     * @param  mixed $values
     * @param  int   $code
     * @param  array $headers
     * @return void
     */
    public function sendJson($values, $code = 200, array $headers = null)
    {
        // Build the response and send it
        $response = new Response();
        $this->response->setCode($code);

        if (null !== $headers) {
            foreach ($headers as $name => $value) {
                $this->response->setHeader($name, $value);
            }
        }

        // Force JSON content-type header
        $response->setHeader('Content-Type', 'application/json')
                 ->setBody(json_encode($values));
        $response->send();
    }

}
