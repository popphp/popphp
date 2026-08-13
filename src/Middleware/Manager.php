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
namespace Pop\Middleware;

use Pop\AbstractManager;

/**
 * Middleware manager class
 *
 * @category   Pop
 * @package    Pop\Middleware
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class Manager extends AbstractManager
{

    /**
     * Constructor
     *
     * Instantiate the middleware manager object.
     *
     * @param ?array $handlers
     */
    public function __construct(?array $handlers = null)
    {
        if (!empty($handlers)) {
            parent::addItems($handlers);
        }
    }

    /**
     * Add handlers
     *
     * @param  array $handlers
     * @return static
     */
    public function addHandlers(array $handlers): static
    {
        return parent::addItems($handlers);
    }

    /**
     * Add a handler
     *
     * @param  mixed $handler
     * @param  mixed $name
     * @return static
     */
    public function addHandler(mixed $handler, mixed $name = null): static
    {
        return parent::addItem($handler, $name);
    }

    /**
     * Remove a handler
     *
     * @param  mixed $name
     * @return static
     */
    public function removeHandler(mixed $name): static
    {
        return parent::removeItem($name);
    }

    /**
     * Get a handler
     *
     * @param  mixed $name
     * @return mixed
     */
    public function getHandler(mixed $name): mixed
    {
        return parent::getItem($name);
    }

    /**
     * Get handlers
     *
     * @return array
     */
    public function getHandlers(): array
    {
        return parent::getItems();
    }

    /**
     * Determine whether the manager has a handler
     *
     * @param  string $name
     * @return bool
     */
    public function hasHandler(string $name): bool
    {
        return parent::hasItem($name);
    }

    /**
     * Determine whether the manager has handlers
     *
     * @return bool
     */
    public function hasHandlers(): bool
    {
        return parent::hasItems();
    }

    /**
     * Process all middleware
     *
     * @param  mixed    $request
     * @param  \Closure $dispatch
     * @param  mixed    $dispatchParams
     * @return static
     */
    public function process(mixed $request, \Closure $dispatch, mixed $dispatchParams = null): static
    {
        $response = $this->handle($request, $this->items, $dispatch, $dispatchParams);

        self::terminate($this->items, $request, $response);

        return $this;
    }

    /**
     * Recursive method to execute all middleware handlers
     *
     * The remaining handler queue is threaded through as a plain local
     * parameter (captured by value in the continuation closure below)
     * rather than shared instance/class state - so a middleware that itself
     * triggers a nested process() call (its own, or on a different Manager
     * entirely) can never corrupt this call's remaining queue, since there
     * is no shared mutable slot for it to clobber.
     *
     * @param  mixed    $request
     * @param  array    $handlers
     * @param  \Closure $dispatch
     * @param  mixed    $dispatchParams
     * @return mixed
     */
    protected function handle(mixed $request, array $handlers, \Closure $dispatch, mixed $dispatchParams = null): mixed
    {
        $next = array_shift($handlers);

        if ($next === null) {
            return (null !== $dispatchParams) ? call_user_func_array($dispatch, $dispatchParams) : $dispatch();
        } else if (is_string($next) && class_exists($next)) {
            $next = new $next();
        }

        return $next->handle($request, function ($req) use ($handlers, $dispatch, $dispatchParams) {
            return $this->handle($req, $handlers, $dispatch, $dispatchParams);
        });
    }

    /**
     * Execute all middleware handlers terminate methods
     *
     * @param  array $handlers
     * @param  mixed $request
     * @param  mixed $response
     * @return void
     */
    public static function terminate(array $handlers, mixed $request = null, mixed $response = null): void
    {
        foreach ($handlers as $handler) {
            if (is_string($handler) && class_exists($handler)) {
                $handler = new $handler();
            }
            if ($handler instanceof TerminableInterface) {
                $handler->terminate($request, $response);
            }
        }
    }

}
