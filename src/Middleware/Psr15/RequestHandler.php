<?php
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
namespace Pop\Middleware\Psr15;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * PSR-15 request handler class
 *
 * Wraps a plain closure (Pop's own "what happens when the middleware chain is
 * exhausted" concept) as a PSR-15 terminal request handler.
 *
 * @category   Pop
 * @package    Pop\Middleware
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class RequestHandler implements RequestHandlerInterface
{

    /**
     * Dispatch closure
     * @var \Closure
     */
    protected \Closure $dispatch;

    /**
     * Constructor
     *
     * Instantiate the request handler object.
     *
     * @param  \Closure $dispatch
     */
    public function __construct(\Closure $dispatch)
    {
        $this->dispatch = $dispatch;
    }

    /**
     * Handle the request
     *
     * @param  ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->dispatch)($request);
    }

}
