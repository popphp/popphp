<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth\Adapter;

/**
 * Auth adapter interface
 *
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
interface AdapterInterface
{

    /**
     * Get the username
     *
     * @return string
     */
    public function getUsername();

    /**
     * Get the password
     *
     * @return string
     */
    public function getPassword();

    /**
     * Set the username
     *
     * @param  string $username
     * @return AdapterInterface
     */
    public function setUsername($username);

    /**
     * Set the password
     *
     * @param  string $password
     * @return AdapterInterface
     */
    public function setPassword($password);

    /**
     * Method to authenticate
     *
     * @return int
     */
    public function authenticate();

}
