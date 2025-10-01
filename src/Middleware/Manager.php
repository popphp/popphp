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
     * Constructor
     *
     * Instantiate the middleware manager object.
     *
     * @param ?array $middlewares
     */
    public function __construct(?array $middlewares = null)
    {
        if (!empty($middlewares)) {
            $this->addItems($middlewares);
        }
    }

}
