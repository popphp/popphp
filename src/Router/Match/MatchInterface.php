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
namespace Pop\Router\Match;

/**
 * Pop router match interface
 *
 * @category   Pop
 * @package    Pop\Router
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.3.7
 */
interface MatchInterface
{

    /**
     * Add a route
     *
     * @param  string $route
     * @param  mixed  $controller
     * @return MatchInterface
     */
    public function addRoute(string $route, mixed $controller): MatchInterface;

    /**
     * Add multiple controller routes
     *
     * @param  array $routes
     * @return MatchInterface
     */
    public function addRoutes(array $routes): MatchInterface;

    /**
     * Add controller params to be passed into a new controller instance
     *
     * @param  string $controller
     * @param  mixed  $params
     * @return MatchInterface
     */
    public function addControllerParams(string $controller, mixed $params): MatchInterface;

    /**
     * Append controller params to be passed into a new controller instance
     *
     * @param  string $controller
     * @param  mixed  $params
     * @return MatchInterface
     */
    public function appendControllerParams(string $controller, mixed $params): MatchInterface;

    /**
     * Get the params assigned to the controller
     *
     * @param  string $controller
     * @return mixed
     */
    public function getControllerParams(string $controller): mixed;

    /**
     * Determine if the controller has params
     *
     * @param  string $controller
     * @return bool
     */
    public function hasControllerParams(string $controller): bool;

    /**
     * Remove controller params
     *
     * @param  string $controller
     * @return MatchInterface
     */
    public function removeControllerParams(string $controller): MatchInterface;

    /**
     * Get the route string
     *
     * @return string
     */
    public function getRouteString(): string;

    /**
     * Get the route string segments
     *
     * @return array
     */
    public function getSegments(): array;

    /**
     * Get a route string segment
     *
     * @param  int $i
     * @return ?string
     */
    public function getSegment(int $i): ?string;

    /**
     * Get original route string
     *
     * @return ?string
     */
    public function getOriginalRoute(): ?string;

    /**
     * Get route string
     *
     * @return string
     */
    public function getRoute(): string;

    /**
     * Get routes
     *
     * @return array
     */
    public function getRoutes(): array;

    /**
     * Get prepared routes
     *
     * @return array
     */
    public function getPreparedRoutes(): array;

    /**
     * Get flattened routes
     *
     * @return array
     */
    public function getFlattenedRoutes(): array;

    /**
     * Determine if there is a route match
     *
     * @return bool
     */
    public function hasRoute(): bool;

    /**
     * Get the params discovered from the route
     *
     * @return array
     */
    public function getRouteParams(): array;

    /**
     * Determine if the route has params
     *
     * @return bool
     */
    public function hasRouteParams(): bool;

    /**
     * Get the default route
     *
     * @return array
     */
    public function getDefaultRoute(): array;

    /**
     * Determine if there is a default route
     *
     * @return bool
     */
    public function hasDefaultRoute(): bool;

    /**
     * Get the dynamic route
     *
     * @return mixed
     */
    public function getDynamicRoute(): mixed;

    /**
     * Get the dynamic route prefix
     *
     * @return mixed
     */
    public function getDynamicRoutePrefix(): mixed;

    /**
     * Determine if there is a dynamic route
     *
     * @return bool
     */
    public function hasDynamicRoute(): bool;

    /**
     * Determine if it is a dynamic route
     *
     * @return bool
     */
    public function isDynamicRoute(): bool;

    /**
     * Get the controller
     *
     * @return mixed
     */
    public function getController(): mixed;

    /**
     * Determine if there is a controller
     *
     * @return bool
     */
    public function hasController(): bool;

    /**
     * Get the action
     *
     * @return mixed
     */
    public function getAction(): mixed;

    /**
     * Determine if there is an action
     *
     * @return bool
     */
    public function hasAction(): bool;

    /**
     * Match the route
     *
     * @return MatchInterface
     */
    public function prepare(): MatchInterface;

    /**
     * Prepare the routes
     *
     * @param  mixed $forceRoute
     * @return bool
     */
    public function match(mixed $forceRoute = null): bool;

    /**
     * Method to process if a route was not found
     *
     * @param  bool $exit
     * @return void
     */
    public function noRouteFound(bool $exit = true): void;

}
