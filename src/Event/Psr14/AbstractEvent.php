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
namespace Pop\Event\Psr14;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * PSR-14 abstract event class
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
