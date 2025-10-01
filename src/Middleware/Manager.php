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
namespace Pop\Middleware;

use Pop\AbstractManager;

/**
 * Middleware manager class
 *
 * @category   Pop
 * @package    Pop\Middleware
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.3.8
 */
class Manager extends AbstractManager
{

    /**
     * Handlers
     * @var array
     */
    protected static array $handlers = [];

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
        self::$handlers = $this->items;
        $response = self::handle($request, $dispatch, $dispatchParams);

        self::terminate($this->items, $request, $response);

        return $this;
    }

    /**
     * Recursive method to execute all middleware handlers
     *
     * @param  mixed    $request
     * @param  \Closure $dispatch
     * @param  mixed    $dispatchParams
     * @return mixed
     */
    public static function handle(mixed $request, \Closure $dispatch, mixed $dispatchParams = null): mixed
    {
        $next = array_shift(self::$handlers);

        if ($next === null) {
            return (null !== $dispatchParams) ? call_user_func_array($dispatch, $dispatchParams) : $dispatch();
        } else if (is_string($next) && class_exists($next)) {
            $next = new $next();
        }

        return $next->handle($request, function ($req) use ($dispatch, $dispatchParams) {
            return self::handle($req, $dispatch, $dispatchParams);
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
