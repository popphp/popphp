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

use Pop\Application;

/**
 * PSR-14 application error event class
 *
 * @category   Pop
 * @package    Pop\Event
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class ErrorEvent extends AbstractApplicationEvent
{

    /**
     * Exception object
     * @var \Throwable
     */
    protected \Throwable $exception;

    /**
     * Constructor
     *
     * Instantiate the application error event object.
     *
     * @param  Application $application
     * @param  \Throwable  $exception
     */
    public function __construct(Application $application, \Throwable $exception)
    {
        parent::__construct($application);
        $this->exception = $exception;
    }

    /**
     * Get the exception object
     *
     * @return \Throwable
     */
    public function exception(): \Throwable
    {
        return $this->exception;
    }

}
