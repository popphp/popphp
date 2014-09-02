<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Ldap
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Ldap;

/**
 * Ldap class
 *
 * @category   Pop
 * @package    Pop_Ldap
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Ldap
{

    /**
     * Ldap username (rdn or dn)
     * @var string
     */
    protected $username = null;

    /**
     * Ldap password
     * @var string
     */
    protected $password = null;

    /**
     * Ldap host
     * @var string
     */
    protected $host = null;

    /**
     * Ldap post
     * @var string
     */
    protected $port = null;

    /**
     * Ldap options
     * @var array
     */
    protected $options = [];

    /**
     * Ldap resource
     * @var resource
     */
    protected $resource = null;

    /**
     * Ldap result
     * @var resource
     */
    protected $result = null;

    /**
     * Ldap bind result
     * @var boolean
     */
    protected $bindResult = false;

    /**
     * Constructor
     *
     * Instantiate the Ldap object.
     *
     * @param  array   $options
     * @param  boolean $auto
     * @return Ldap
     */
    public function __construct(array $options = [], $auto = true)
    {
        $this->setOptions($options);

        if ($auto) {
            $this->bind();
        }
    }

    /**
     * Set the username
     *
     * @param  string $username
     * @return Ldap
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Set the password
     *
     * @param  string $password
     * @return Ldap
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Set the host
     *
     * @param  string $host
     * @return Ldap
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set the port
     *
     * @param  string $port
     * @return Ldap
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Set an option
     *
     * @param  mixed $option
     * @param  mixed $value
     * @return Ldap
     */
    public function setOption($option, $value)
    {
        switch ($option) {
            case 'username':
                $this->setUsername($value);
                break;
            case 'password':
                $this->setPassword($value);
                break;
            case 'host':
                $this->setHost($value);
                break;
            case 'port':
                $this->setPort($value);
                break;
            default:
                $this->options[$option] = $value;
                if (is_resource($this->resource)) {
                    ldap_set_option($this->resource, $option, $value);
                }
        }

        return $this;
    }

    /**
     * Set an option
     *
     * @param  array $options
     * @return Ldap
     */
    public function setOptions(array $options)
    {
        foreach ($options as $option => $value) {
            $this->setOption($option, $value);
        }

        return $this;
    }

    /**
     * Get the username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get the password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get the host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get the port
     *
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Get an option
     *
     * @param  mixed $option
     * @return mixed
     */
    public function getOption($option)
    {
        $value = null;

        switch ($option) {
            case 'username':
                $value = $this->getUsername();
                break;
            case 'password':
                $value = $this->getPassword();
                break;
            case 'host':
                $value = $this->getHost();
                break;
            case 'port':
                $value = $this->getPort();
                break;
            default:
                $value = (isset($this->options[$option])) ? $this->options[$option] : null;
        }

        return $value;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get the Ldap resource
     *
     * @return resource
     */
    public function resource()
    {
        return $this->resource;
    }

    /**
     * Connect to the Ldap resource
     *
     * @throws Exception
     * @return Ldap
     */
    public function connect()
    {
        if (null === $this->host) {
            throw new Exception('Error: The LDAP host has not been set.');
        }

        $host = (null !== $this->port) ? $this->host . ':' . $this->port : $this->host;
        $this->resource = ldap_connect($host);

        return $this;
    }

    /**
     * Get Ldap error number and message
     *
     * @return string
     */
    public function getError()
    {
        return (is_resource($this->resource)) ? ldap_errno($this->resource) . ': ' . ldap_error($this->resource) : null;
    }

    /**
     * Get Ldap error number
     *
     * @return string
     */
    public function getErrorNumber()
    {
        return (is_resource($this->resource)) ? ldap_errno($this->resource) : null;
    }

    /**
     * Get Ldap error message
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return (is_resource($this->resource)) ? ldap_error($this->resource) : null;
    }

    /**
     * Bind to the Ldap resource
     *
     * @return Ldap
     */
    public function bind()
    {
        if (!is_resource($this->resource)) {
            $this->connect();
        }

        // Bind with user/pass
        if ((null !== $this->username) && (null !== $this->password)) {
            $this->bindResult = ldap_bind($this->resource, $this->username, $this->password);
        // Else, bind anonymously
        } else {
            $this->bindResult = ldap_bind($this->resource);
        }

        return $this;
    }

    /**
     * Perform Ldap search
     *
     * @param  string $base
     * @param  string $filter
     * @param  array  $options
     * @throws Exception
     * @return Ldap
     */
    public function search($base, $filter, array $options = null)
    {
        if (!is_resource($this->resource)) {
            throw new Exception('Error: The LDAP resource has not been set.');
        }

        if (null !== $options) {
            if (isset($options['attributes']) && isset($options['attrsonly']) && isset($options['sizelimit']) && isset($options['timelimit']) && isset($options['deref'])) {
                $this->result = ldap_search($this->resource, $base, $filter, $options['attributes'], $options['attrsonly'], $options['sizelimit'], $options['timelimit'], $options['deref']);
            } else if (isset($options['attributes']) && isset($options['attrsonly']) && isset($options['sizelimit']) && isset($options['timelimit'])) {
                $this->result = ldap_search($this->resource, $base, $filter, $options['attributes'], $options['attrsonly'], $options['sizelimit'], $options['timelimit']);
            } else if (isset($options['attributes']) && isset($options['attrsonly']) && isset($options['sizelimit'])) {
                $this->result = ldap_search($this->resource, $base, $filter, $options['attributes'], $options['attrsonly'], $options['sizelimit']);
            } else if (isset($options['attributes']) && isset($options['attrsonly'])) {
                $this->result = ldap_search($this->resource, $base, $filter, $options['attributes'], $options['attrsonly']);
            } else if (isset($options['attributes'])) {
                $this->result = ldap_search($this->resource, $base, $filter, $options['attributes']);
            }
        } else {
            $this->result = ldap_search($this->resource, $base, $filter);
        }

        return $this;
    }

    /**
     * Get Ldap entries
     *
     * @throws Exception
     * @return array
     */
    public function getEntries()
    {
        if (!is_resource($this->resource)) {
            throw new Exception('Error: The LDAP resource has not been set.');
        }
        if (!is_resource($this->result)) {
            throw new Exception('Error: The LDAP result has not been set.');
        }

        return ldap_get_entries($this->resource, $this->result);
    }

    /**
     * Unbind to the Ldap resource
     *
     * @return Ldap
     */
    public function unbind()
    {
        $this->bindResult = !(ldap_unbind($this->resource));
        return $this;
    }

    /**
     * Get whether or not currently connected
     *
     * @return boolean
     */
    public function isConnected()
    {
        return is_resource($this->resource);
    }

    /**
     * Get the current bind result
     *
     * @return boolean
     */
    public function isBound()
    {
        return $this->bindResult;
    }

    /**
     * Disconnect from the Ldap resource
     *
     * @return Ldap
     */
    public function disconnect()
    {
        $this->unbind();
        $this->resource = null;
        return $this;
    }

}
