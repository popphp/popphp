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
     * Instantiate the Ldap object.
     *
     * @param  array $options
     * @return Ldap
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Get an option
     *
     * @param  string $name
     * @return mixed
     */
    public function getOption($name)
    {
        return (isset($this->options[$name])) ? $this->options[$name] : null;
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
     * Connect to the Ldap resource
     *
     * @return Ldap
     */
    public function connect()
    {

    }

    /**
     * Bind to the Ldap resource
     *
     * @return Ldap
     */
    public function bind()
    {

    }

    /**
     * Disconnect from the Ldap resource
     *
     * @return Ldap
     */
    public function disconnect()
    {

    }

}
