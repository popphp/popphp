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
 * Interface generator code class
 *
 * @category   Pop
 * @package    Pop_Code
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class InterfaceGenerator implements GeneratorInterface
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
     * Parent interface that is extended
     * @var string
     */
    protected $parent = null;

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
     * Instantiate the interface generator object
     *
     * @param  string  $name
     * @param  string  $parent
     * @return InterfaceGenerator
     */
    public function __construct($name, $parent = null)
    {
        $this->name   = $name;
        $this->parent = $parent;
    }

    /**
     * Set the interface indent
     *
     * @param  string $indent
     * @return InterfaceGenerator
     */
    public function setIndent($indent = null)
    {
        $this->indent = $indent;
        return $this;
    }

    /**
     * Get the interface indent
     *
     * @return string
     */
    public function getIndent()
    {
        return $this->indent;
    }

    /**
     * Set the interface name
     *
     * @param  string $name
     * @return InterfaceGenerator
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the interface name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the interface parent
     *
     * @param  string $parent
     * @return InterfaceGenerator
     */
    public function setParent($parent = null)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get the interface parent
     *
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
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
     * @return /Pop\Code\MethodGenerator
     */
    public function getMethod($method)
    {
        $m = ($method instanceof MethodGenerator) ? $method->getName() : $method;
        return (isset($this->methods[$m])) ? $this->methods[$m] : null;
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
     * Render interface
     *
     * @param  boolean $ret
     * @return mixed
     */
    public function render($ret = false)
    {
        $this->output = (null !== $this->namespace) ? $this->namespace->render(true) . PHP_EOL : null;
        $this->output .= (null !== $this->docblock) ? $this->docblock->render(true) : null;
        $this->output .= 'interface ' . $this->name;

        if (null !== $this->parent) {
            $this->output .= ' extends ' . $this->parent;
        }

        $this->output .= PHP_EOL . '{' . PHP_EOL;
        $this->output .= $this->formatMethods() . PHP_EOL;
        $this->output .= '}' . PHP_EOL;

        if ($ret) {
            return $this->output;
        } else {
            echo $this->output;
        }
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
            $method->setInterface(true);
            $methods .= PHP_EOL . $method->render(true);
        }

        return $methods;
    }

    /**
     * Print interface
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render(true);
    }

}
