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
namespace Pop\Event;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Abstract event class
 *
 * @category   Pop
 * @package    Pop\Event
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
abstract class AbstractEvent implements StoppableEventInterface
{

    /**
     * Propagation stopped flag
     * @var bool
     */
    protected bool $propagationStopped = false;

    /**
     * Get the event name, used to match name-indexed (on()/trigger()) listeners
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Get the params array passed to name-indexed listeners, positionally
     *
     * @return array
     */
    abstract public function toParams(): array;

    /**
     * Stop propagation of this event to any remaining listeners
     *
     * @return void
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * Determine if propagation of this event has been stopped
     *
     * @return bool
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

}
