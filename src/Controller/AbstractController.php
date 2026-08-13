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
namespace Pop\Controller;

use Pop\Dispatch;

/**
 * Pop abstract controller class
 *
 * @category   Pop
 * @package    Pop\Controller
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
abstract class AbstractController extends Dispatch\AbstractDispatcher implements Dispatch\DispatchableInterface, Dispatch\MaintenanceInterface
{

    /**
     * Traits
     */
    use Dispatch\MaintenanceTrait;

}
