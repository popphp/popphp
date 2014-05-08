<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Web
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Web;

/**
 * Cookie class
 *
 * @category   Pop
 * @package    Pop_Web
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Cookie
{

    /**
     * Instance of the cookie object
     * @var \Pop\Web\Cookie
     */
    static private $instance;

    /**
     * Cookie IP
     * @var string
     */
    private $ip = null;

    /**
     * Cookie Expiration
     * @var int
     */
    private $expire = 0;

    /**
     * Cookie Path
     * @var string
     */
    private $path = '/';

    /**
     * Cookie Domain
     * @var string
     */
    private $domain = null;

    /**
     * Cookie Secure Flag
     * @var boolean
     */
    private $secure = false;

    /**
     * Cookie HTTP Only Flag
     * @var boolean
     */
    private $httponly = false;

    /**
     * Constructor
     *
     * Private method to instantiate the cookie object.
     *
     * @param  array $options
     * @return \Pop\Web\Cookie
     */
    private function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * Private method to set options
     *
     * @param  array $options
     * @return \Pop\Web\Cookie
     */
    private function setOptions(array $options = [])
    {
        // Set the cookie owner's IP address and domain.
        $this->ip     = $_SERVER['REMOTE_ADDR'];
        $this->domain = $_SERVER['HTTP_HOST'];

        if (isset($options['expire'])) {
            $this->expire = (int)$options['expire'];
        }
        if (isset($options['path'])) {
            $this->path = $options['path'];
        }
        if (isset($options['domain'])) {
            $this->domain = $options['domain'];
        }
        if (isset($options['secure'])) {
            $this->secure = (bool)$options['secure'];
        }
        if (isset($options['httponly'])) {
            $this->httponly = (bool)$options['httponly'];
        }
    }

    /**
     * Determine whether or not an instance of the cookie object exists
     * already, and instantiate the object if it does not exist.
     *
     * @param  array $options
     * @return \Pop\Web\Cookie
     */
    public static function getInstance(array $options = [])
    {
        if (empty(self::$instance)) {
            self::$instance = new Cookie($options);
        }

        return self::$instance;
    }

    /**
     * Set a cookie.
     *
     * @param  string  $name
     * @param  mixed   $value
     * @param  array   $options
     * @return \Pop\Web\Cookie
     */
    public function set($name, $value, array $options = null)
    {
        if (null !== $options) {
            $this->setOptions($options);
        }

        if (!is_string($value) && !is_numeric($value)) {
            $value = json_encode($value);
        }

        setcookie($name, $value, $this->expire, $this->path, $this->domain, $this->secure, $this->httponly);
        return $this;
    }

    /**
     * Return the current cookie expiration
     *
     * @return int
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * Return the current cookie path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Return the current cookie domain.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Return if the cookie is secure
     *
     * @return boolean
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * Return if the cookie is HTTP only
     *
     * @return boolean
     */
    public function isHttpOnly()
    {
        return $this->httponly;
    }

    /**
     * Return the current IP address.
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Delete a cookie
     *
     * @param  string $name
     * @param  array  $options
     * @return void
     */
    public function delete($name, array $options = null)
    {
        if (null !== $options) {
            $this->setOptions($options);
        }
        if (isset($_COOKIE[$name])) {
            setcookie($name, $_COOKIE[$name], (time() - 3600), $this->path, $this->domain, $this->secure, $this->httponly);
        }
    }

    /**
     * Clear (delete) all cookies via unset($cookie)
     *
     * @param  array $options
     * @return void
     */
    public function clear(array $options = null)
    {
        if (null !== $options) {
            $this->setOptions($options);
        }
        foreach ($_COOKIE as $name => $value) {
            if (isset($_COOKIE[$name])) {
                setcookie($name, $_COOKIE[$name], (time() - 3600), $this->path, $this->domain, $this->secure, $this->httponly);
            }
        }
    }

    /**
     * Get method to return the value of the $_COOKIE global variable.
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        $value = null;
        if (isset($_COOKIE[$name])) {
            $value = (substr($_COOKIE[$name], 0, 1) == '{') ? json_decode($_COOKIE[$name]) : $_COOKIE[$name];
        }
        return $value;
    }

    /**
     * Return the isset value of the $_COOKIE global variable.
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * Unset the value in the $_COOKIE global variable.
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        if (isset($_COOKIE[$name])) {
            setcookie($name, $_COOKIE[$name], (time() - 3600), $this->path, $this->domain, $this->secure, $this->httponly);
        }
    }

}
