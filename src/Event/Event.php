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
namespace Pop\Event;

/**
 * Generic event class - used by trigger() for any ad-hoc, non-typed event name
 *
 * @category   Pop
 * @package    Pop\Event
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class Event extends AbstractEvent
{

    /**
     * Event name
     * @var string
     */
    protected string $name;

    /**
     * Event params
     * @var array
     */
    protected array $params;

    /**
     * Constructor
     *
     * Instantiate the generic event object.
     *
     * @param  string $name
     * @param  array  $params
     */
    public function __construct(string $name, array $params = [])
    {
        $this->name   = $name;
        $this->params = $params;
    }

    /**
     * Get the event name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the params array
     *
     * @return array
     */
    public function toParams(): array
    {
        return $this->params;
    }

    /**
     * Get a single param by key
     *
     * @param  string $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        return $this->params[$key] ?? null;
    }

}
