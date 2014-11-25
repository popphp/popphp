<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Code
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Code\Generator;

/**
 * Class generator code class
 *
 * @category   Pop
 * @package    Pop_Code
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class ClassGenerator implements GeneratorInterface
{

    /**
     * Docblock generator object
     * @var DocblockGenerator
     */
    protected $docblock = null;

    /**
     * Namespace generator object
     * @var NamespaceGenerator
     */
    protected $namespace = null;

    /**
     * Class name
     * @var string
     */
    protected $name = null;

    /**
     * Parent class that is extended
     * @var string
     */
    protected $parent = null;

    /**
     * Interface that is implemented
     * @var string
     */
    protected $interface = null;

    /**
     * Class abstract flag
     * @var boolean
     */
    protected $abstract = false;

    /**
     * Array of property generator objects
     * @var array
     */
    protected $properties = [];

    /**
     * Array of method generator objects
     * @var array
     */
    protected $methods = [];

    /**
     * Class indent
     * @var string
     */
    protected $indent = null;

    /**
     * Class output
     * @var string
     */
    protected $output = null;

    /**
     * Constructor
     *
     * Instantiate the class generator object
     *
     * @param  string  $name
     * @param  string  $parent
     * @param  string  $interface
     * @param  boolean $abstract
     * @return ClassGenerator
     */
    public function __construct($name, $parent = null, $interface = null, $abstract = false)
    {
        $this->name      = $name;
        $this->parent    = $parent;
        $this->interface = $interface;
        $this->abstract  = (boolean)$abstract;
    }

    /**
     * Set the class abstract flag
     *
     * @param  boolean $abstract
     * @return ClassGenerator
     */
    public function setAbstract($abstract = false)
    {
        $this->abstract = (boolean)$abstract;
        return $this;
    }

    /**
     * Get the class abstract flag
     *
     * @return boolean
     */
    public function isAbstract()
    {
        return $this->abstract;
    }

    /**
     * Set the class indent
     *
     * @param  string $indent
     * @return ClassGenerator
     */
    public function setIndent($indent = null)
    {
        $this->indent = $indent;
        return $this;
    }

    /**
     * Get the class indent
     *
     * @return string
     */
    public function getIndent()
    {
        return $this->indent;
    }

    /**
     * Set the class name
     *
     * @param  string $name
     * @return ClassGenerator
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the class name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the class parent
     *
     * @param  string $parent
     * @return ClassGenerator
     */
    public function setParent($parent = null)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get the class parent
     *
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set the class interface
     *
     * @param  string $interface
     * @return ClassGenerator
     */
    public function setInterface($interface = null)
    {
        $this->interface = $interface;
        return $this;
    }

    /**
     * Get the class interface
     *
     * @return string
     */
    public function getInterface()
    {
        return $this->interface;
    }

    /**
     * Set the namespace generator object
     *
     * @param  NamespaceGenerator $namespace
     * @return ClassGenerator
     */
    public function setNamespace(NamespaceGenerator $namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Access the namespace generator object
     *
     * @return NamespaceGenerator
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Set the docblock generator object
     *
     * @param  DocblockGenerator $docblock
     * @return ClassGenerator
     */
    public function setDocblock(DocblockGenerator $docblock)
    {
        $this->docblock = $docblock;
        return $this;
    }

    /**
     * Access the docblock generator object
     *
     * @return DocblockGenerator
     */
    public function getDocblock()
    {
        return $this->docblock;
    }

    /**
     * Add a class property
     *
     * @param  PropertyGenerator $property
     * @return ClassGenerator
     */
    public function addProperty(PropertyGenerator $property)
    {
        $this->properties[$property->getName()] = $property;
        return $this;
    }

    /**
     * Get a class property
     *
     * @param  mixed $property
     * @return PropertyGenerator
     */
    public function getProperty($property)
    {
        $p = ($property instanceof PropertyGenerator) ? $property->getName() : $property;
        return (isset($this->properties[$p])) ? $this->properties[$p] : null;
    }

    /**
     * Get all properties
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Remove a class property
     *
     * @param  mixed $property
     * @return ClassGenerator
     */
    public function removeProperty($property)
    {
        $p = ($property instanceof PropertyGenerator) ? $property->getName() : $property;
        if (isset($this->properties[$p])) {
            unset($this->properties[$p]);
        }
        return $this;
    }

    /**
     * Add a class method
     *
     * @param  MethodGenerator $method
     * @return ClassGenerator
     */
    public function addMethod(MethodGenerator $method)
    {
        $this->methods[$method->getName()] = $method;
        return $this;
    }

    /**
     * Get a method property
     *
     * @param  mixed $method
     * @return MethodGenerator
     */
    public function getMethod($method)
    {
        $m = ($method instanceof MethodGenerator) ? $method->getName() : $method;
        return (isset($this->methods[$m])) ? $this->methods[$m] : null;
    }

    /**
     * Get all methods
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Remove a method property
     *
     * @param  mixed $method
     * @return ClassGenerator
     */
    public function removeMethod($method)
    {
        $m = ($method instanceof MethodGenerator) ? $method->getName() : $method;
        if (isset($this->methods[$m])) {
            unset($this->methods[$m]);
        }
        return $this;
    }

    /**
     * Render class
     *
     * @param  boolean $ret
     * @return mixed
     */
    public function render($ret = false)
    {
        $abstract = ($this->abstract) ? 'abstract ' : null;
        $this->output = (null !== $this->namespace) ? $this->namespace->render(true) . PHP_EOL : null;
        $this->output .= (null !== $this->docblock) ? $this->docblock->render(true) : null;
        $this->output .= $abstract . 'class ' . $this->name;

        if (null !== $this->parent) {
            $this->output .= ' extends ' . $this->parent;
        }
        if (null !== $this->interface) {
            $this->output .= ' implements ' . $this->interface;
        }

        $this->output .= PHP_EOL . '{';
        $this->output .= $this->formatProperties() . PHP_EOL;
        $this->output .= $this->formatMethods() . PHP_EOL;
        $this->output .= '}' . PHP_EOL;

        if ($ret) {
            return $this->output;
        } else {
            echo $this->output;
        }
    }

    /**
     * Format the properties
     *
     * @return string
     */
    protected function formatProperties()
    {
        $props = null;

        foreach ($this->properties as $prop) {
            $props .= PHP_EOL . $prop->render(true);
        }

        return $props;
    }

    /**
     * Format the methods
     *
     * @return string
     */
    protected function formatMethods()
    {
        $methods = null;

        foreach ($this->methods as $method) {
            $methods .= PHP_EOL . $method->render(true);
        }

        return $methods;
    }

    /**
     * Print class
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render(true);
    }

}
