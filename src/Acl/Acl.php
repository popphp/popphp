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
 * ACL class
 *
 * @category   Pop
 * @package    Pop_Acl
 * @author     Nick Sagona, III <dev@nolainteractive.com>
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
    protected $roles = [];

    /**
     * Array of resources
     * @var array
     */
    protected $resources = [];

    /**
     * Array of allowed roles, resources and permissions
     * @var array
     */
    protected $allowed = [];

    /**
     * Array of denied roles, resources and permissions
     * @var array
     */
    protected $denied = [];

    /**
     * Constructor
     *
     * Instantiate the ACL object
     *
     * @param  Role     $role
     * @param  Resource $resource
     * @return Acl
     */
    public function __construct(Role $role = null, Resource $resource = null)
    {
        if (null !== $role) {
            $this->addRole($role);
        }

        if (null !== $resource) {
            $this->addResource($resource);
        }
    }

    /**
     * Get a role
     *
     * @param  string $role
     * @return Role
     */
    public function getRole($role)
    {
        return (isset($this->roles[$role])) ? $this->roles[$role] : null;
    }

    /**
     * See if a role has been added
     *
     * @param  string $role
     * @return boolean
     */
    public function hasRole($role)
    {
        return (isset($this->roles[$role]));
    }

    /**
     * Add a role
     *
     * @param  Role $role
     * @return Acl
     */
    public function addRole(Role $role)
    {
        $this->roles[$role->getName()] = $role;
        return $this;
    }

    /**
     * Add roles
     *
     * @param  array $roles
     * @throws Exception
     * @return Acl
     */
    public function addRoles(array $roles)
    {
        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * Get a resource
     *
     * @param  string $resource
     * @return Resource
     */
    public function getResource($resource)
    {
        return (isset($this->resources[$resource])) ? $this->resources[$resource] : null;
    }

    /**
     * See if a resource has been added
     *
     * @param  string $resource
     * @return boolean
     */
    public function hasResource($resource)
    {
        return (isset($this->resources[$resource]));
    }

    /**
     * Add a resource
     *
     * @param  Resource $resource
     * @return Acl
     */
    public function addResource(Resource $resource)
    {
        $this->resources[$resource->getName()] = $resource;
        return $this;
    }

    /**
     * Add resources
     *
     * @param  array $resources
     * @throws Exception
     * @return Acl
     */
    public function addResources(array $resources)
    {
        foreach ($resources as $resource) {
            $this->addResource($resource);
        }

        return $this;
    }

    /**
     * Allow a user role permission to a resource or resources
     *
     * @param  string|array  $roles
     * @param  string|array  $resources
     * @param  string|array  $permissions
     * @throws Exception
     * @return Acl
     */
    public function allow($roles, $resources = null, $permissions = null)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        // Check if the roles has been added
        foreach ($roles as $role) {
            if (!isset($this->roles[$role])) {
                throw new Exception('Error: That role has not been added.');
            }

            if (!isset($this->allowed[$role])) {
                $this->allowed[$role] = [];
            }

            // Check if the resource(s) have been added
            if (null !== $resources) {
                if (!is_array($resources)) {
                    $resources = [$resources];
                }
                foreach ($resources as $resource) {
                    if (!isset($this->resources[$resource])) {
                        $this->addResource(new Resource($resource));
                    }
                    $this->allowed[$role][$resource] = [];
                    if (null != $permissions) {
                        if (!is_array($permissions)) {
                            $permissions = [$permissions];
                        }
                        foreach ($permissions as $permission) {
                            if (!$this->roles[$role]->hasPermission($permission)) {
                                throw new Exception("Error: The role '" . $role . "' does not have the permission '" . $permission . "'.");
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
     * Remove an allow rule
     *
     * @param  mixed  $roles
     * @param  mixed  $resources
     * @param  mixed  $permissions
     * @throws Exception
     * @return Acl
     */
    public function removeAllow($roles, $resources = null, $permissions = null)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
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
                    $resources = [$resources];
                }
                foreach ($resources as $resource) {
                    if (!isset($this->resources[$resource])) {
                        $this->addResource(new Resource($resource));
                    }
                    if (isset($this->allowed[$role][$resource])) {
                        if (null != $permissions) {
                            if (!is_array($permissions)) {
                                $permissions = [$permissions];
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
     * Deny a user role permission to a resource or resources
     *
     * @param  mixed $roles
     * @param  mixed $resources
     * @param  mixed $permissions
     * @throws Exception
     * @return Acl
     */
    public function deny($roles, $resources = null, $permissions = null)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        // Check if the roles has been added
        foreach ($roles as $role) {
            if (!isset($this->roles[$role])) {
                throw new Exception('Error: That role has not been added.');
            }

            if (!isset($this->denied[$role])) {
                $this->denied[$role] = [];
            }

            // Check if the resource(s) have been added
            if (null !== $resources) {
                if (!is_array($resources)) {
                    $resources = [$resources];
                }
                foreach ($resources as $resource) {
                    if (!isset($this->resources[$resource])) {
                        $this->addResource(new Resource($resource));
                    }
                    $this->denied[$role][$resource] = [];
                    if (null != $permissions) {
                        if (!is_array($permissions)) {
                            $permissions = [$permissions];
                        }
                        foreach ($permissions as $permission) {
                            //if (!$this->roles[$role]->hasPermission($permission)) {
                            //    throw new Exception("Error: The role '" . $role . "' does not have the permission '" . $permission . "'.");
                            //}
                            $this->denied[$role][$resource][] = $permission;
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Remove a deny rule
     *
     * @param  mixed $roles
     * @param  mixed $resources
     * @param  mixed $permissions
     * @throws Exception
     * @return Acl
     */
    public function removeDeny($roles, $resources = null, $permissions = null)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
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
                    $resources = [$resources];
                }
                foreach ($resources as $resource) {
                    if (!isset($this->resources[$resource])) {
                        $this->addResource($resource);
                    }
                    if (isset($this->denied[$role][$resource])) {
                        if (null != $permissions) {
                            if (!is_array($permissions)) {
                                $permissions = [$permissions];
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
     * Determine if the user is allowed
     *
     * @param  Role   $role
     * @param  string $resource
     * @param  string $permission
     * @throws Exception
     * @return boolean
     */
    public function isAllowed(Role $role, $resource = null, $permission = null)
    {
        $result = false;

        if (!isset($this->roles[$role->getName()])) {
            throw new Exception('Error: That role has not been added.');
        }

        if ((null !== $resource) && !isset($this->resources[$resource])) {
            $this->addResource(new Resource($resource));
        }

        if (!$this->isDenied($role, $resource, $permission)) {
            if ((null !== $resource) && (null !== $permission)) {
                // Full access, no resource or permission defined OR
                // Full access to the resource if no permission defined OR
                // determine access based on resource and permission passed
                if ((isset($this->allowed[$role->getName()]) && (count($this->allowed[$role->getName()]) == 0)) ||
                    (isset($this->allowed[$role->getName()]) && isset($this->allowed[$role->getName()][$resource]) &&
                        (count($this->allowed[$role->getName()][$resource]) == 0)) ||
                    ($role->hasPermission($permission) &&
                     isset($this->allowed[$role->getName()]) &&
                     isset($this->allowed[$role->getName()][$resource]) &&
                     in_array($permission, $this->allowed[$role->getName()][$resource]))) {
                    $result = true;
                }
            } else if (null !== $resource) {
                // Full access, no resource defined OR
                // determine access based on resource passed
                if ((isset($this->allowed[$role->getName()]) && (count($this->allowed[$role->getName()]) == 0)) ||
                    (isset($this->allowed[$role->getName()]) &&
                    isset($this->allowed[$role->getName()][$resource]))) {
                    $result = true;
                }
            } else {
                if (isset($this->allowed[$role->getName()])) {
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * Determine if the user is denied
     *
     * @param  Role   $role
     * @param  string $resource
     * @param  string $permission
     * @throws Exception
     * @return boolean
     */
    public function isDenied(Role $role, $resource = null, $permission = null)
    {
        $result = false;

        if (!isset($this->roles[$role->getName()])) {
            throw new Exception('Error: That role has not been added.');
        }

        if ((null !== $resource) && !isset($this->resources[$resource])) {
            $this->addResource(new Resource($resource));
        }

        // Check if the user, resource and/or permission is denied
        if (isset($this->denied[$role->getName()])) {
            if (count($this->denied[$role->getName()]) > 0) {
                if ((null !== $resource) && array_key_exists($resource, $this->denied[$role->getName()])) {
                    if (count($this->denied[$role->getName()][$resource]) > 0) {
                        if ((null !== $permission) && in_array($permission, $this->denied[$role->getName()][$resource])) {
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
