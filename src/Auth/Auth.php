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
namespace Pop\Auth;

/**
 * Auth class
 *
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
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
     * @return Auth
     */
    public function __construct(Adapter\AbstractAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Get the auth adapter
     *
     * @return Adapter\AbstractAdapter
     */
    public function adapter()
    {
        return $this->adapter;
    }

    /**
     * Get the authentication result
     *
     * @return int
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Determine if the authentication attempt was valid
     *
     * @return boolean
     */
    public function isValid()
    {
        return ($this->result == 1);
    }

    /**
     * Get the username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->adapter->getUsername();
    }

    /**
     * Get the password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->adapter->getPassword();
    }

    /**
     * Set the username
     *
     * @param  string $username
     * @return Auth
     */
    public function setUsername($username)
    {
        $this->adapter->setUsername($username);
        return $this;
    }

    /**
     * Set the password
     *
     * @param  string $password
     * @return Auth
     */
    public function setPassword($password)
    {
        $this->adapter->setPassword($password);
        return $this;
    }

    /**
     * Method to authenticate
     *
     * @param  string $username
     * @param  string $password
     * @return Auth
     */
    public function authenticate($username = null, $password = null)
    {
        if (null !== $username) {
            $this->adapter->setUsername($username);
        }
        if (null !== $password) {
            $this->adapter->setPassword($password);
        }
        $this->result = $this->adapter->authenticate();
        return $this;
    }

}
