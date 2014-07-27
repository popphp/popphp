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
 * Acl role class
 *
 * @category   Pop
 * @package    Pop_Acl
 * @author     Nick Sagona, III <dev@nolainteractive.com>
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
     * @var Role
     */
    protected $parent = null;

    /**
     * Constructor
     *
     * Instantiate the role object
     *
     * @param  string $name
     * @return Role
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
     * @return Role
     */
    public function addPermission($name)
    {
        $this->permissions[$name] = true;
        return $this;
    }

    /**
     * Method to remove a permission from the role
     *
     * @param  string $name
     * @return Role
     */
    public function removePermission($name)
    {
        if (isset($this->permissions[$name])) {
            unset($this->permissions[$name]);
        }
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
     * @param  Role $role
     * @return Role
     */
    public function addChild(Role $role)
    {
        $this->children[] = $role;
        if ($role->getName() !== $this) {
            $role->setParent($this);
        }
        return $this;
    }

    /**
     * Method to set the inherited role
     *
     * @param  Role $parent
     * @return Role
     */
    public function inheritsFrom(Role $parent)
    {
        $this->parent = $parent;
        $this->parent->addChild($this);
        return $this;
    }

    /**
     * Method to set the parent role
     *
     * @param  Role $parent
     * @return Role
     */
    public function setParent(Role $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Method to get the role parent
     *
     * @return Role
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Method to see if the role has a parent
     *
     * @return Role
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
