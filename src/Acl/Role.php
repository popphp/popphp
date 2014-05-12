<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Acl
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Acl;

/**
 * Acl role class
 *
 * @category   Pop
 * @package    Pop_Acl
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
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
    protected $permissions = [];

    /**
     * Role children
     * @var array
     */
    protected $children = [];

    /**
     * Role parent
     * @var \Pop\Acl\Role
     */
    protected $parent = null;

    /**
     * Constructor
     *
     * Instantiate the role object
     *
     * @param  string $name
     * @return \Pop\Acl\Role
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
     * Method to add a permission to the role
     *
     * @param  string $name
     * @return \Pop\Acl\Role
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
     * @return \Pop\Acl\Role
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
     * @param  \Pop\Acl\Role $parent
     * @return \Pop\Acl\Role
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Method to get the role parent
     *
     * @return \Pop\Acl\Role
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Method to see if the role has a parent
     *
     * @return \Pop\Acl\Role
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
