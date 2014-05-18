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
     * @var Adapter\AbstractAdapter
     */
    protected $adapter = null;

    /**
     * Constructor
     *
     * Instantiate the auth object
     *
     * @param Adapter\AbstractAdapter $adapter
     * @return \Pop\Auth\Auth
     */
    public function __construct(Adapter\AbstractAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Method to get the auth adapter
     *
     * @return \Pop\Auth\Adapter\AbstractAdapter
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
     * Method to set the username
     *
     * @param  string $username
     * @return \Pop\Auth\Auth
     */
    public function setUsername($username)
    {
        $this->adapter->setUsername($username);
        return $this;
    }

    /**
     * Method to set the password
     *
     * @param  string $password
     * @return \Pop\Auth\Auth
     */
    public function setPassword($password)
    {
        $this->adapter->setPassword($password);
        return $this;
    }

    /**
     * Method to authenticate
     *
     * @return \Pop\Auth\Auth
     */
    public function authenticate()
    {
        $this->result = $this->adapter->authenticate();
        return $this;
    }

}
