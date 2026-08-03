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

use Pop\Middleware\MiddlewareInterface;
use Psr\Http\Server\MiddlewareInterface as Psr15MiddlewareInterface;

/**
 * PSR-15 middleware adapter class
 *
 * Bridges a real PSR-15 Psr\Http\Server\MiddlewareInterface handler into Pop's
 * native middleware queue, translating Pop's handle(mixed $request, \Closure
 * $next) call shape into PSR-15's process(ServerRequestInterface $request,
 * RequestHandlerInterface $handler) shape.
 *
 * @category   Pop
 * @package    Pop\Middleware
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class MiddlewareAdapter implements MiddlewareInterface
{

    /**
     * PSR-15 middleware
     * @var Psr15MiddlewareInterface
     */
    protected Psr15MiddlewareInterface $middleware;

    /**
     * Constructor
     *
     * Instantiate the middleware adapter object.
     *
     * @param  Psr15MiddlewareInterface $middleware
     */
    public function __construct(Psr15MiddlewareInterface $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * Handle the request
     *
     * @param  mixed    $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle(mixed $request, \Closure $next): mixed
    {
        return $this->middleware->process($request, new RequestHandler($next));
    }

}
