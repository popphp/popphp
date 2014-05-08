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
 * ACL class
 *
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Acl
{

    /**
     * Array of roles
     * @var array
     */
    protected $roles = array();

    /**
     * Array of resources
     * @var array
     */
    protected $resources = array();

    /**
     * Array of allowed roles, resources and permissions
     * @var array
     */
    protected $allowed = array();

    /**
     * Array of denied roles, resources and permissions
     * @var array
     */
    protected $denied = array();

    /**
     * Constructor
     *
     * Instantiate the auth object
     *
     * @param  mixed $roles
     * @param  mixed $resources
     * @return \Pop\Auth\Acl
     */
    public function __construct($roles = null, $resources = null)
    {
        if (null !== $roles) {
            $this->addRoles($roles);
        }

        if (null !== $resources) {
            $this->addResources($resources);
        }
    }

    /**
     * Static method to instantiate the ACL object and return itself
     * to facilitate chaining methods together.
     *
     * @param  mixed $roles
     * @param  mixed $resources
     * @return \Pop\Auth\Acl
     */
    public static function factory($roles = null, $resources = null)
    {
        return new self($roles, $resources);
    }

    /**
     * Method to get a role
     *
     * @param  string $role
     * @return \Pop\Auth\Role
     */
    public function getRole($role)
    {
        return (isset($this->roles[$role])) ? $this->roles[$role] : null;
    }

    /**
     * Method to is if a role has been added
     *
     * @param  string $role
     * @return boolean
     */
    public function hasRole($role)
    {
        return (isset($this->roles[$role]));
    }

    /**
     * Method to add a role
     *
     * @param  mixed $role
     * @return \Pop\Auth\Acl
     */
    public function addRole($role)
    {
        $this->addRoles($role);
        return $this;
    }

    /**
     * Method to add roles
     *
     * @param  mixed $roles
     * @return \Pop\Auth\Acl
     */
    public function addRoles($roles)
    {
        if (is_array($roles)) {
            foreach ($roles as $r) {
                if ($r instanceof Role) {
                    $this->roles[$r->getName()] = $r;
                } else {
                    $this->roles[$r] = Role::factory($r);
                }
            }
        } else if ($roles instanceof Role) {
            $this->roles[$roles->getName()] = $roles;
        } else {
            $this->roles[$roles] = Role::factory($roles);
        }

        return $this;
    }

    /**
     * Method to get a resource
     *
     * @param  string $resource
     * @return \Pop\Auth\Resource
     */
    public function getResource($resource)
    {
        return (isset($this->resources[$resource])) ? $this->resources[$resource] : null;
    }

    /**
     * Method to is if a resource has been added
     *
     * @param  string $resource
     * @return boolean
     */
    public function hasResource($resource)
    {
        return (isset($this->resources[$resource]));
    }

    /**
     * Method to add a resource
     *
     * @param  mixed $resource
     * @return \Pop\Auth\Acl
     */
    public function addResource($resource)
    {
        $this->addResources($resource);
        return $this;
    }

    /**
     * Method to add a resource
     *
     * @param  mixed $resources
     * @return \Pop\Auth\Acl
     */
    public function addResources($resources)
    {
        if (is_array($resources)) {
            foreach ($resources as $r) {
                if ($r instanceof Resource) {
                    $this->resources[$r->getName()] = $r;
                } else {
                    $this->resources[$r] = Resource::factory($r);
                }
            }
        } else if ($resources instanceof Resource) {
            $this->resources[$resources->getName()] = $resources;
        } else {
            $this->resources[$resources] = Resource::factory($resources);
        }

        return $this;
    }

    /**
     * Method to allow a user role permission to a resource or resources
     *
     * @param  mixed  $roles
     * @param  mixed  $resources
     * @param  mixed  $permissions
     * @throws \Pop\Auth\Exception
     * @return \Pop\Auth\Acl
     */
    public function allow($roles, $resources = null, $permissions = null)
    {
        if (!is_array($roles)) {
            $roles = array($roles);
        }

        // Check if the roles has been added
        foreach ($roles as $role) {
            if (!isset($this->roles[$role])) {
                throw new Exception('Error: That role has not been added.');
            }

            if (!isset($this->allowed[$role])) {
                $this->allowed[$role] = array();
            }

            // Check if the resource(s) have been added
            if (null !== $resources) {
                if (!is_array($resources)) {
                    $resources = array($resources);
                }
                foreach ($resources as $resource) {
                    if (!isset($this->resources[$resource])) {
                        $this->addResource($resource);
                    }
                    $this->allowed[$role][$resource] = array();
                    if (null != $permissions) {
                        if (!is_array($permissions)) {
                            $permissions = array($permissions);
                        }
                        foreach ($permissions as $permission) {
                            if (!$this->roles[$role]->hasPermission($permission)) {
                                throw new Exception("Error: That role does not have the permission '" . $permission . "'.");
                            }
                            $this->allowed[$role][$resource][] = $permission;
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Method to remove an allow rule
     *
     * @param  mixed  $roles
     * @param  mixed  $resources
     * @param  mixed  $permissions
     * @throws \Pop\Auth\Exception
     * @return \Pop\Auth\Acl
     */
    public function removeAllow($roles, $resources = null, $permissions = null)
    {
        if (!is_array($roles)) {
            $roles = array($roles);
        }

        // Check if the roles has been added
        foreach ($roles as $role) {
            if (!isset($this->roles[$role])) {
                throw new Exception('Error: That role has not been added.');
            }

            if (!isset($this->allowed[$role])) {
                throw new Exception('Error: That role has no allow rules associated with it.');
            }

            // Check if the resource(s) have been added
            if (null !== $resources) {
                if (!is_array($resources)) {
                    $resources = array($resources);
                }
                foreach ($resources as $resource) {
                    if (!isset($this->resources[$resource])) {
                        $this->addResource($resource);
                    }
                    if (isset($this->allowed[$role][$resource])) {
                        if (null != $permissions) {
                            if (!is_array($permissions)) {
                                $permissions = array($permissions);
                            }
                            foreach ($permissions as $permission) {
                                if (in_array($permission, $this->allowed[$role][$resource])) {
                                    $key = array_search($permission, $this->allowed[$role][$resource]);
                                    unset($this->allowed[$role][$resource][$key]);
                                }
                            }
                        } else {
                            unset($this->allowed[$role][$resource]);
                        }
                    }
                }
            } else {
                unset($this->allowed[$role]);
            }
        }

        return $this;
    }

    /**
     * Method to deny a user role permission to a resource or resources
     *
     * @param  mixed $roles
     * @param  mixed $resources
     * @param  mixed $permissions
     * @throws \Pop\Auth\Exception
     * @return \Pop\Auth\Acl
     */
    public function deny($roles, $resources = null, $permissions = null)
    {
        if (!is_array($roles)) {
            $roles = array($roles);
        }

        // Check if the roles has been added
        foreach ($roles as $role) {
            if (!isset($this->roles[$role])) {
                throw new Exception('Error: That role has not been added.');
            }

            if (!isset($this->denied[$role])) {
                $this->denied[$role] = array();
            }

            // Check if the resource(s) have been added
            if (null !== $resources) {
                if (!is_array($resources)) {
                    $resources = array($resources);
                }
                foreach ($resources as $resource) {
                    if (!isset($this->resources[$resource])) {
                        $this->addResource($resource);
                    }
                    $this->denied[$role][$resource] = array();
                    if (null != $permissions) {
                        if (!is_array($permissions)) {
                            $permissions = array($permissions);
                        }
                        foreach ($permissions as $permission) {
                            if (!$this->roles[$role]->hasPermission($permission)) {
                                throw new Exception("Error: That role does not have the permission '" . $permission . "'.");
                            }
                            $this->denied[$role][$resource][] = $permission;
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Method to remove a deny rule
     *
     * @param  mixed $roles
     * @param  mixed $resources
     * @param  mixed $permissions
     * @throws \Pop\Auth\Exception
     * @return \Pop\Auth\Acl
     */
    public function removeDeny($roles, $resources = null, $permissions = null)
    {
        if (!is_array($roles)) {
            $roles = array($roles);
        }

        // Check if the roles has been added
        foreach ($roles as $role) {
            if (!isset($this->roles[$role])) {
                throw new Exception('Error: That role has not been added.');
            }

            if (!isset($this->denied[$role])) {
                throw new Exception('Error: That role has no allow rules associated with it.');
            }

            // Check if the resource(s) have been added
            if (null !== $resources) {
                if (!is_array($resources)) {
                    $resources = array($resources);
                }
                foreach ($resources as $resource) {
                    if (!isset($this->resources[$resource])) {
                        $this->addResource($resource);
                    }
                    if (isset($this->denied[$role][$resource])) {
                        if (null != $permissions) {
                            if (!is_array($permissions)) {
                                $permissions = array($permissions);
                            }
                            foreach ($permissions as $permission) {
                                if (in_array($permission, $this->denied[$role][$resource])) {
                                    $key = array_search($permission, $this->denied[$role][$resource]);
                                    unset($this->denied[$role][$resource][$key]);
                                }
                            }
                        } else {
                            unset($this->denied[$role][$resource]);
                        }
                    }
                }
            } else {
                unset($this->denied[$role]);
            }
        }

        return $this;
    }

    /**
     * Method to determine if the user is allowed
     *
     * @param  \Pop\Auth\Role $user
     * @param  string         $resource
     * @param  string         $permission
     * @throws \Pop\Auth\Exception
     * @return boolean
     */
    public function isAllowed(\Pop\Auth\Role $user, $resource = null, $permission = null)
    {
        $result = false;

        if (!isset($this->roles[$user->getName()])) {
            throw new Exception('Error: That role has not been added.');
        }

        if ((null !== $resource) && !isset($this->resources[$resource])) {
            $this->addResource($resource);
        }

        if (!$this->isDenied($user, $resource, $permission)) {
            if ((null !== $resource) && (null !== $permission)) {
                // Full access, no resource or permission defined OR
                // Full access to the resource if no permission defined OR
                // determine access based on resource and permission passed
                if ((isset($this->allowed[$user->getName()]) && (count($this->allowed[$user->getName()]) == 0)) ||
                    (isset($this->allowed[$user->getName()]) && isset($this->allowed[$user->getName()][$resource]) && (count($this->allowed[$user->getName()][$resource]) == 0)) ||
                    ($user->hasPermission($permission) &&
                     isset($this->allowed[$user->getName()]) &&
                     isset($this->allowed[$user->getName()][$resource]) &&
                     in_array($permission, $this->allowed[$user->getName()][$resource]))) {
                    $result = true;
                }
            } else if (null !== $resource) {
                // Full access, no resource defined OR
                // determine access based on resource passed
                if ((isset($this->allowed[$user->getName()]) && (count($this->allowed[$user->getName()]) == 0)) ||
                    (isset($this->allowed[$user->getName()]) &&
                    isset($this->allowed[$user->getName()][$resource]))) {
                    $result = true;
                }
            } else {
                if (isset($this->allowed[$user->getName()])) {
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * Method to determine if the user is denied
     *
     * @param  \Pop\Auth\Role $user
     * @param  string         $resource
     * @param  string         $permission
     * @throws \Pop\Auth\Exception
     * @return boolean
     */
    public function isDenied(\Pop\Auth\Role $user, $resource = null, $permission = null)
    {
        $result = false;

        if (!isset($this->roles[$user->getName()])) {
            throw new Exception('Error: That role has not been added.');
        }

        if ((null !== $resource) && !isset($this->resources[$resource])) {
            $this->addResource($resource);
        }

        // Check if the user, resource and/or permission is denied
        if (isset($this->denied[$user->getName()])) {
            if (count($this->denied[$user->getName()]) > 0) {
                if ((null !== $resource) && array_key_exists($resource, $this->denied[$user->getName()])) {
                    if (count($this->denied[$user->getName()][$resource]) > 0) {
                        if ((null !== $permission) && in_array($permission, $this->denied[$user->getName()][$resource])) {
                            $result = true;
                        }
                    } else {
                        $result = true;
                    }
                }
            } else {
                $result = true;
            }
        }

        return $result;
    }

}
