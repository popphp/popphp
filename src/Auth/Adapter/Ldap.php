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
 * Ldap auth adapter class
 *
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Ldap extends AbstractAdapter
{

    /**
     * Ldap host
     * @var string
     */
    protected $host = null;

    /**
     * Ldap port
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
     * Constructor
     *
     * Instantiate the Ldap auth adapter object
     *
     * @param  string $host
     * @param  string $port
     * @param  array  $options
     * @return Ldap
     */
    public function __construct($host, $port = null, array $options = null)
    {
        $this->setHost($host);
        if (null !== $port) {
            $this->setPort($port);
        }

        $host = (null !== $this->port) ? $this->host . ':' . $this->port : $this->host;
        $this->resource = ldap_connect($host);

        if (null !== $options) {
            $this->setOptions($options);
        }
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
        $this->options[$option] = $value;
        if (is_resource($this->resource)) {
            ldap_set_option($this->resource, $option, $value);
        }

        return $this;
    }

    /**
     * Set options
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
        return (isset($this->options[$option])) ? $this->options[$option] : null;
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
     * Method to authenticate
     *
     * @return int
     */
    public function authenticate()
    {
        return (int)(@ldap_bind($this->resource, $this->username, $this->password));
    }

}
