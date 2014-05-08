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
 * Auth role class
 *
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Role
{

    /**
     * Role name
     * @var string
     */
    protected $name = null;

    /**
     * Role permissions
     * @var array
     */
    protected $permissions = array();

    /**
     * Role children
     * @var array
     */
    protected $children = array();

    /**
     * Role parent
     * @var \Pop\Auth\Role
     */
    protected $parent = null;

    /**
     * Constructor
     *
     * Instantiate the role object
     *
     * @param  string $name
     * @return \Pop\Auth\Role
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Static method to instantiate the role object and return itself
     * to facilitate chaining methods together.
     *
     * @param  string $name
     * @return \Pop\Auth\Role
     */
    public static function factory($name)
    {
        return new self($name);
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
     * Method to add a permission to the role
     *
     * @param  string $name
     * @return \Pop\Auth\Role
     */
    public function addPermission($name)
    {
        $this->permissions[$name] = true;
        return $this;
    }

    /**
     * Method to check if a role has a permission
     *
     * @param  string $name
     * @return boolean
     */
    public function hasPermission($name)
    {
        $result = false;

        if (isset($this->permissions[$name])) {
            $result = true;
        } else if (null !== $this->parent) {
            $parent = $this->parent;
            if ($parent->hasPermission($name)) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Method to add a child role
     *
     * @param  mixed $role
     * @return \Pop\Auth\Role
     */
    public function addChild($role)
    {
        $child = ($role instanceof Role) ? $role : new Role($role);
        $child->setParent($this);
        $this->children[] = $child;
        return $this;
    }

    /**
     * Method to set the role parent
     *
     * @param  \Pop\Auth\Role $parent
     * @return \Pop\Auth\Role
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Method to get the role parent
     *
     * @return \Pop\Auth\Role
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Method to see if the role has a parent
     *
     * @return \Pop\Auth\Role
     */
    public function hasParent()
    {
        return (null !== $this->parent);
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
