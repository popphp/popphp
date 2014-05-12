<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth;

/**
 * Auth class
 *
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Auth
{

    /**
     * Constant for credentials not being valid
     * @var int
     */
    const NOT_VALID = 0;

    /**
     * Constant for credentials being valid
     * @var int
     */
    const VALID = 1;

    /**
     * Authentication result
     * @var int
     */
    protected $result = 0;

    /**
     * Auth adapter object
     * @var Adapter\AdapterInterface
     */
    protected $adapter = null;

    /**
     * Constructor
     *
     * Instantiate the auth object
     *
     * @param Adapter\AdapterInterface $adapter
     * @return \Pop\Auth\Auth
     */
    public function __construct(Adapter\AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Method to get the auth adapter
     *
     * @return \Pop\Auth\Adapter\AdapterInterface
     */
    public function adapter()
    {
        return $this->adapter;
    }

    /**
     * Method to get the authentication result
     *
     * @return int
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Method to determine if the authentication attempt was valid
     *
     * @return boolean
     */
    public function isValid()
    {
        return (bool)$this->result;
    }

    /**
     * Method to authenticate a user
     *
     * @param  string $username
     * @param  string $password
     * @return \Pop\Auth\Auth
     */
    public function authenticate($username, $password)
    {
        $this->result = $this->adapter->authenticate($username, $password);
        return $this;
    }

}
