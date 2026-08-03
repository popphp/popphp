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

use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * PSR-14 listener provider class
 *
 * @category   Pop
 * @package    Pop\Event
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class ListenerProvider implements ListenerProviderInterface
{

    /**
     * Listeners, keyed by exact event class name
     * @var array
     */
    protected array $listeners = [];

    /**
     * Register a listener for an event class
     *
     * @param  string   $eventClass
     * @param  callable $listener
     * @param  int      $priority
     * @return static
     */
    public function listen(string $eventClass, callable $listener, int $priority = 0): static
    {
        if (!isset($this->listeners[$eventClass])) {
            $this->listeners[$eventClass] = new \SplPriorityQueue();
        }
        $this->listeners[$eventClass]->insert($listener, $priority);

        return $this;
    }

    /**
     * Get the listeners for an event, matched by exact event class only
     *
     * @param  object $event
     * @return iterable
     */
    public function getListenersForEvent(object $event): iterable
    {
        $eventClass = $event::class;

        if (!isset($this->listeners[$eventClass])) {
            return [];
        }

        // Clone before iterating - SplPriorityQueue iteration is destructive,
        // same reasoning as Event\Manager::trigger()'s existing clone.
        return clone $this->listeners[$eventClass];
    }

}
