<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Dispatch;

use Pop\Application;
use Pop\Http\Server\Request;
use Pop\Http\Server\Response;
use Pop\Http\Uri;

/**
 * Pop HTTP trait
 *
 * @category   Pop
 * @package    Pop\Dispatch
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
trait HttpTrait
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
     * @param  ?Application $application
     * @param  Request     $request
     * @param  Response    $response
     */
    public function __construct(
        ?Application $application = null, Request $request = new Request(new Uri()), Response $response = new Response()
    )
    {
        $this->application = $application;
        $this->request     = $request;
        $this->response    = $response;
    }

    /**
     * Get application object (alias)
     *
     * @return ?Application
     */
    public function application(): ?Application
    {
        return $this->application;
    }

    /**
     * Get request object(alias)
     *
     * @return ?Request
     */
    public function request(): ?Request
    {
        return $this->request;
    }

    /**
     * Get response object(alias)
     *
     * @return ?Response
     */
    public function response(): ?Response
    {
        return $this->response;
    }

    /**
     * Get application object
     *
     * @return ?Application
     */
    public function getApplication(): ?Application
    {
        return $this->application;
    }

    /**
     * Set application object
     *
     * @param  Application $application
     * @return static
     */
    public function setApplication(Application $application): static
    {
        $this->application = $application;
        return $this;
    }

    /**
     * Set request object
     *
     * @param  Request $request
     * @return static
     */
    public function setRequest(Request $request = new Request(new Uri())): static
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Set response object
     *
     * @param  Response $response
     * @return static
     */
    public function setResponse(Response $response = new Response()): static
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Has application object
     *
     * @return bool
     */
    public function hasApplication(): bool
    {
        return !empty($this->application);
    }

    /**
     * Has request object
     *
     * @return bool
     */
    public function hasRequest(): bool
    {
        return !empty($this->request);
    }

    /**
     * Has response object
     *
     * @return bool
     */
    public function hasResponse(): bool
    {
        return !empty($this->response);
    }

}
