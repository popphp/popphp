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

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Abstract manager class
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.3.8
 */
abstract class AbstractManager implements ManagerInterface, ArrayAccess, Countable, IteratorAggregate
{

    /**
     * Manager items
     * @var array
     */
    protected array $items = [];

    /**
     * Set items
     *
     * @param  array $items
     * @return static
     */
    public function setItems(array $items): static
    {
        $this->items = $items;
        return $this;
    }

    /**
     * Add items
     *
     * @param  array $items
     * @return static
     */
    public function addItems(array $items): static
    {
        foreach ($items as $name => $item) {
            $this->addItem($item, $name);
        }

        return $this;
    }

    /**
     * Add an item
     *
     * @param  mixed $item
     * @param  mixed $name
     * @return static
     */
    public function addItem(mixed $item, mixed $name = null): static
    {
        if (($name !== null) && !is_numeric($name)) {
            $this->items[$name] = $item;
        } else {
            $this->items[] = $item;
        }

        return $this;
    }

    /**
     * Remove an item
     *
     * @param  mixed $name
     * @return static
     */
    public function removeItem(mixed $name): static
    {
        if (isset($this->items[$name])) {
            unset($this->items[$name]);
        }

        return $this;
    }

    /**
     * Get an item
     *
     * @param  mixed $name
     * @return mixed
     */
    public function getItem(mixed $name): mixed
    {
        return $this->items[$name] ?? null;
    }

    /**
     * Get items
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Determine whether the manager has an item
     *
     * @param  string $name
     * @return bool
     */
    public function hasItem(string $name): bool
    {
        return (isset($this->items[$name]));
    }

    /**
     * Determine whether the manager has items
     *
     * @return bool
     */
    public function hasItems(): bool
    {
        return (!empty($this->items));
    }

    /**
     * Set an item
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->addItem($value, $name);
    }

    /**
     * Get an item
     *
     * @param  string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->getItem($name);
    }

    /**
     * Determine if an item exists
     *
     * @param  string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->hasItem($name);
    }

    /**
     * Unset an item
     *
     * @param  string $name
     * @return void
     */
    public function __unset(string $name): void
    {
        if (isset($this->items[$name])) {
            unset($this->items[$name]);
        }
    }

    /**
     * Set an item
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->addItem($value, $offset);
    }

    /**
     * Get an item
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->getItem($offset);
    }

    /**
     * Determine if an item exists
     *
     * @param  string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->hasItem($offset);
    }

    /**
     * Unset an item
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        if (isset($this->items[$offset])) {
            unset($this->items[$offset]);
        }
    }

    /**
     * Return count
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Get iterator
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }
}
