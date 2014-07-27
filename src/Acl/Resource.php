<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Acl
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Acl;

/**
 * Acl resource class
 *
 * @category   Pop
 * @package    Pop_Acl
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Resource
{

    /**
     * Resource name
     * @var string
     */
    protected $name = null;

    /**
     * Constructor
     *
     * Instantiate the resource object
     *
     * @param  string $name
     * @return \Pop\Acl\Resource
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Method to get the role name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Method to return the string value of the name of the role
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

}
