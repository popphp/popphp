<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop;

use ArrayIterator;

/**
 * Manager interface
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.3.8
 */
interface ManagerInterface
{

    /**
     * Set items
     *
     * @param  array $items
     * @return static
     */
    public function setItems(array $items): static;

    /**
     * Add items
     *
     * @param  array $items
     * @return static
     */
    public function addItems(array $items): static;

    /**
     * Add an item
     *
     * @param  mixed $item
     * @param  mixed $name
     * @return static
     */
    public function addItem(mixed $item, mixed $name = null): static;

    /**
     * Remove an item
     *
     * @param  mixed $name
     * @return static
     */
    public function removeItem(mixed $name): static;

    /**
     * Get an item
     *
     * @param  mixed $name
     * @return mixed
     */
    public function getItem(mixed $name): mixed;

    /**
     * Determine whether the manager has an item
     *
     * @param  string $name
     * @return bool
     */
    public function hasItem(string $name): bool;

    /**
     * Set an item
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function __set(string $name, mixed $value): void;

    /**
     * Get an item
     *
     * @param  string $name
     * @return mixed
     */
    public function __get(string $name): mixed;

    /**
     * Determine if an item exists
     *
     * @param  string $name
     * @return bool
     */
    public function __isset(string $name): bool;

    /**
     * Unset an item
     *
     * @param  string $name
     * @return void
     */
    public function __unset(string $name): void;

    /**
     * Set an item
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void;

    /**
     * Get an item
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed;

    /**
     * Determine if an item exists
     *
     * @param  string $offset
     * @return bool
     */
    public function offsetExists($offset): bool;

    /**
     * Unset an item
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void;

    /**
     * Return count
     *
     * @return int
     */
    public function count(): int;

    /**
     * Get iterator
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator;

}
