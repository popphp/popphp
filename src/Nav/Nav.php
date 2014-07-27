<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Nav
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Nav;

use Pop\Dom\Child;

/**
 * Nav class
 *
 * @category   Pop
 * @package    Pop_Nav
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Nav
{

    /**
     * Nav tree
     * @var array
     */
    protected $tree = [];

    /**
     * Nav config
     * @var array
     */
    protected $config = [];

    /**
     * Acl object
     * @var \Pop\Acl\Acl
     */
    protected $acl = null;

    /**
     * Role object
     * @var \Pop\Acl\Role
     */
    protected $role = null;

    /**
     * Nav parent level
     * @var int
     */
    protected $parentLevel = 1;

    /**
     * Nav child level
     * @var int
     */
    protected $childLevel = 1;

    /**
     * Return false flag
     * @var boolean
     */
    protected $returnFalse = false;

    /**
     * Parent nav element
     * @var \Pop\Dom\Child
     */
    protected $nav = null;

    /**
     * Constructor
     *
     * Instantiate the nav object
     *
     * @param  array $tree
     * @param  array $config
     * @return self
     */
    public function __construct(array $tree = null, array $config = null)
    {
        $this->setTree($tree);
        $this->setConfig($config);
    }

    /**
     * Set the return false flag
     *
     * @param  boolean $return
     * @return Nav
     */
    public function returnFalse($return)
    {
        $this->returnFalse = (bool)$return;
        return $this;
    }

    /**
     * Set the nav tree
     *
     * @param  array $tree
     * @return Nav
     */
    public function setTree(array $tree = null)
    {
        $this->tree = (null !== $tree) ? $tree : [];
        return $this;
    }

    /**
     * Add to a nav tree branch
     *
     * @param  array   $branch
     * @param  boolean $prepend
     * @return Nav
     */
    public function addBranch(array $branch, $prepend = false)
    {
        if (isset($branch['name'])) {
            $branch = [$branch];
        }
        $this->tree = ($prepend) ? array_merge($branch, $this->tree) : array_merge($this->tree, $branch);
        return $this;
    }

    /**
     * Add to a leaf to nav tree branch
     *
     * @param  string  $branch
     * @param  array   $leaf
     * @param  int     $pos
     * @param  boolean $prepend
     * @return Nav
     */
    public function addLeaf($branch, array $leaf, $pos = null, $prepend = false)
    {
        $this->tree = $this->traverseTree($this->tree, $branch, $leaf, $pos, $prepend);
        $this->parentLevel = 1;
        $this->childLevel  = 1;
        return $this;
    }

    /**
     * Set the nav tree
     *
     * @param  array $config
     * @return Nav
     */
    public function setConfig(array $config = null)
    {
        if (null === $config) {
            $this->config = [
                'parent' => [
                    'node'  => 'ul'
                ],
                'child' => [
                    'node'  => 'li'
                ]
            ];
        } else {
            $this->config = $config;
        }

        return $this;
    }

    /**
     * Set the Acl object
     *
     * @param  \Pop\Acl\Acl $acl
     * @return Nav
     */
    public function setAcl(\Pop\Acl\Acl $acl = null)
    {
        $this->acl = $acl;
        return $this;
    }

    /**
     * Set the Role object
     *
     * @param  \Pop\Acl\Role $role
     * @return Nav
     */
    public function setRole(\Pop\Acl\Role $role = null)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * Set the return false flag
     *
     * @return boolean
     */
    public function isReturnFalse()
    {
        return $this->returnFalse;
    }

    /**
     * Get the nav tree
     *
     * @return array
     */
    public function getTree()
    {
        return $this->tree;
    }

    /**
     * Get the config
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get the Acl object
     *
     * @return \Pop\Acl\Acl
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * Get the Role object
     *
     * @return \Pop\Acl\Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Build the nav object
     *
     * @return Nav
     */
    public function build()
    {
        if (null === $this->nav) {
            $this->nav = $this->traverse($this->tree);
        }
        return $this;
    }

    /**
     * Re-build the nav object
     *
     * @return Nav
     */
    public function rebuild()
    {
        $this->parentLevel = 1;
        $this->childLevel  = 1;
        $this->nav = $this->traverse($this->tree);
        return $this;
    }

    /**
     * Get the nav object
     *
     * @return \Pop\Dom\Child
     */
    public function nav()
    {
        if (null === $this->nav) {
            $this->nav = $this->traverse($this->tree);
        }
        return $this->nav;
    }

    /**
     * Render the nav object
     *
     * @param  boolean $ret
     * @return mixed
     */
    public function render($ret = false)
    {
        if (null === $this->nav) {
            $this->nav = $this->traverse($this->tree);
        }

        if ($ret) {
            return ($this->nav->hasChildren()) ? $this->nav->render($ret) : '';
        } else {
            echo ($this->nav->hasChildren()) ? $this->nav->render($ret) : '';
        }
    }

    /**
     * Render Nav object to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render(true);
    }

    /**
     * Traverse tree to insert new leaf
     *
     * @param  array   $tree
     * @param  string  $branch
     * @param  array   $newLeaf
     * @param  int     $pos
     * @param  boolean $prepend
     * @param  int     $depth
     * @return array
     */
    protected function traverseTree($tree, $branch, $newLeaf, $pos = null, $prepend = false, $depth = 0)
    {
        $t = [];
        foreach ($tree as $leaf) {
            if (((null === $pos) || ($pos == $depth)) && ($leaf['name'] == $branch)) {
                if (isset($leaf['children'])) {
                    $leaf['children'] = ($prepend) ?
                        array_merge([$newLeaf], $leaf['children']) : array_merge($leaf['children'], [$newLeaf]);
                } else {
                    $leaf['children'] = [$newLeaf];
                }
            }
            if (isset($leaf['children'])) {
                $leaf['children'] = $this->traverseTree($leaf['children'], $branch, $newLeaf, $pos, $prepend, ($depth + 1));
            }
            $t[] = $leaf;
        }

        return $t;
    }

    /**
     * Traverse the config object
     *
     * @param  array  $tree
     * @param  int    $depth
     * @param  string $parentHref
     * @throws Exception
     * @return \Pop\Dom\Child
     */
    protected function traverse(array $tree, $depth = 1, $parentHref = null)
    {
        // Create overriding top level parent, if set
        if (($depth == 1) && isset($this->config['top'])) {
            $parent = (isset($this->config['top']) && isset($this->config['top']['node'])) ? $this->config['top']['node'] : 'ul';
            $child  = null;
            if (isset($this->config['child']) && isset($this->config['child']['node'])) {
                $child = $this->config['child']['node'];
            } else if ($parent == 'ul') {
                $child = 'li';
            }

            // Create parent node
            $nav = new Child($parent);

            // Set top attributes if they exist
            if (isset($this->config['top']) && isset($this->config['top']['id'])) {
                $nav->setAttribute('id', $this->config['top']['id']);
            }
            if (isset($this->config['top']) && isset($this->config['top']['class'])) {
                $nav->setAttribute('class', $this->config['top']['class']);
            }
            if (isset($this->config['top']['attributes'])) {
                foreach ($this->config['top']['attributes'] as $attrib => $value) {
                    $nav->setAttribute($attrib, $value);
                }
            }
        } else {
            // Set up parent/child node names
            $parent = (isset($this->config['parent']) && isset($this->config['parent']['node'])) ? $this->config['parent']['node'] : 'ul';
            $child  = null;
            if (isset($this->config['child']) && isset($this->config['child']['node'])) {
                $child = $this->config['child']['node'];
            } else if ($parent == 'ul') {
                $child = 'li';
            }

            // Create parent node
            $nav = new Child($parent);

            // Set parent attributes if they exist
            if (isset($this->config['parent']) && isset($this->config['parent']['id'])) {
                $nav->setAttribute('id', $this->config['parent']['id'] . '-' . $this->parentLevel);
            }
            if (isset($this->config['parent']) && isset($this->config['parent']['class'])) {
                $nav->setAttribute('class', $this->config['parent']['class'] . '-' . $depth);
            }
            if (isset($this->config['parent']['attributes'])) {
                foreach ($this->config['parent']['attributes'] as $attrib => $value) {
                    $nav->setAttribute($attrib, $value);
                }
            }
        }

        $this->parentLevel++;
        $depth++;

        // Recursively loop through the nodes
        foreach ($tree as $node) {
            $allowed = true;
            if (isset($node['acl'])) {
                if (null === $this->acl) {
                    throw new Exception('The access control object is not set.');
                }
                if (null === $this->role) {
                    throw new Exception('The current role is not set.');
                }
                $resource = (isset($node['acl']['resource'])) ? $node['acl']['resource'] : null;
                $permission = (isset($node['acl']['permission'])) ? $node['acl']['permission'] : null;
                $allowed = $this->acl->isAllowed($this->role, $resource, $permission);
            }
            if (($allowed) && isset($node['name']) && isset($node['href'])) {
                // Create child node and child link node
                $a = new Child('a', $node['name']);
                if ((substr($node['href'], 0, 1) == '/') || (substr($node['href'], 0, 1) == '#') ||
                    (substr($node['href'], -1) == '#') ||(substr($node['href'], 0, 4) == 'http')) {
                    $href = $node['href'];
                } else {
                    if (substr($parentHref, -1) == '/') {
                        $href = $parentHref . $node['href'];
                    } else {
                        $href = $parentHref . '/' . $node['href'];
                    }
                }

                $a->setAttribute('href', $href);

                if (($this->returnFalse) && (($href == '#') || (substr($href, -1) == '#'))) {
                    $a->setAttribute('onclick', 'return false;');
                }
                $url = $_SERVER['REQUEST_URI'];
                if (strpos($url, '?') !== false) {
                    $url = substr($url, strpos($url, '?'));
                }

                $linkClass = null;
                if ($href == $url) {
                    if (isset($this->config['on'])) {
                        $linkClass = $this->config['on'];
                    }
                } else {
                    if (isset($this->config['off'])) {
                        $linkClass = $this->config['off'];
                    }
                }

                // If the node has any attributes
                if (isset($node['attributes'])) {
                    foreach ($node['attributes'] as $attrib => $value) {
                        $value = (($attrib == 'class') && (null !== $linkClass)) ? $value . ' ' . $linkClass : $value;
                        $a->setAttribute($attrib, $value);
                    }
                } else if (null !== $linkClass) {
                    $a->setAttribute('class', $linkClass);
                }

                if (null !== $child) {
                    $navChild = new Child($child);

                    // Set child attributes if they exist
                    if (isset($this->config['child']) && isset($this->config['child']['id'])) {
                        $navChild->setAttribute('id', $this->config['child']['id'] . '-' . $this->childLevel);
                    }
                    if (isset($this->config['child']) && isset($this->config['child']['class'])) {
                        $navChild->setAttribute('class', $this->config['child']['class'] . '-' . ($depth - 1));
                    }
                    if (isset($this->config['child']['attributes'])) {
                        foreach ($this->config['child']['attributes'] as $attrib => $value) {
                            $navChild->setAttribute($attrib, $value);
                        }
                    }

                    // Add link node
                    $navChild->addChild($a);
                    $this->childLevel++;

                    // If there are children, loop through and add them
                    if (isset($node['children']) && is_array($node['children']) && (count($node['children']) > 0)) {
                        $childrenAllowed = true;
                        // Check if the children are allowed
                        if (isset($node['acl'])) {
                            $i = 0;
                            foreach ($node['children'] as $nodeChild) {
                                if (null === $this->acl) {
                                    throw new Exception('The access control object is not set.');
                                }
                                if (null === $this->role) {
                                    throw new Exception('The current role is not set.');
                                }
                                $resource = (isset($nodeChild['acl']['resource'])) ? $nodeChild['acl']['resource'] : null;
                                $permission = (isset($nodeChild['acl']['permission'])) ? $nodeChild['acl']['permission'] : null;
                                if (!($this->acl->isAllowed($this->role, $resource, $permission))) {
                                    $i++;
                                }
                            }
                            if ($i == count($node['children'])) {
                                $childrenAllowed = false;
                            }
                        }
                        if ($childrenAllowed) {
                            $navChild->addChild($this->traverse($node['children'], $depth, $href));
                        }
                    }
                    // Add child node
                    $nav->addChild($navChild);
                } else {
                    $nav->addChild($a);
                }
            }
        }

        return $nav;
    }

}