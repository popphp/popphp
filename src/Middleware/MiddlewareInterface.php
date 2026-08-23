<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Middleware;

/**
 * Middleware interface
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
interface MiddlewareInterface
{

    /**
     * Process middleware
     *
     * @param  mixed   $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle(mixed $request, \Closure $next): mixed;

}
