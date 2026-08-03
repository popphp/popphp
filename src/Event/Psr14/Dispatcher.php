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
namespace Pop\Event\Psr14;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * PSR-14 event dispatcher class
 *
 * @category   Pop
 * @package    Pop\Event
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.4.0
 */
class Dispatcher implements EventDispatcherInterface
{

    /**
     * Listener provider
     * @var ListenerProvider
     */
    protected ListenerProvider $listeners;

    /**
     * Constructor
     *
     * Instantiate the dispatcher object.
     *
     * @param  ListenerProvider $listeners
     */
    public function __construct(ListenerProvider $listeners)
    {
        $this->listeners = $listeners;
    }

    /**
     * Get the listener provider
     *
     * @return ListenerProvider
     */
    public function listeners(): ListenerProvider
    {
        return $this->listeners;
    }

    /**
     * Dispatch an event to all its registered listeners
     *
     * @param  object $event
     * @return object
     */
    public function dispatch(object $event): object
    {
        foreach ($this->listeners->getListenersForEvent($event) as $listener) {
            if (($event instanceof StoppableEventInterface) && $event->isPropagationStopped()) {
                break;
            }
            $listener($event);
        }

        return $event;
    }

}
