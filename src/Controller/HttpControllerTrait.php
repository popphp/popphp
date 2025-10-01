<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Controller;

use Pop\Application;
use Pop\Http\Server\Request;
use Pop\Http\Server\Response;
use Pop\Http\Uri;

/**
 * Pop HTTP controller trait
 *
 * @category   Pop
 * @package    Pop\Controller
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.3.8
 */
trait HttpControllerTrait
{

    /**
     * Application object
     * @var ?Application
     */
    protected ?Application $application = null;

    /**
     * Request object
     * @var ?Request
     */
    protected ?Request $request = null;

    /**
     * Response object
     * @var ?Response
     */
    protected ?Response $response = null;

    /**
     * Constructor for the controller
     *
     * @param  Application $application
     * @param  Request     $request
     * @param  Response    $response
     */
    public function __construct(
        Application $application, Request $request = new Request(new Uri()), Response $response = new Response()
    )
    {
        $this->application = $application;
        $this->request     = $request;
        $this->response    = $response;
    }

    /**
     * Get application object
     *
     * @return ?Application
     */
    public function application(): ?Application
    {
        return $this->application;
    }

    /**
     * Get request object
     *
     * @return ?Request
     */
    public function request(): ?Request
    {
        return $this->request;
    }

    /**
     * Get response object
     *
     * @return ?Response
     */
    public function response(): ?Response
    {
        return $this->response;
    }

}
