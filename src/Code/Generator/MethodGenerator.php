<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Code
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Code\Generator;

/**
 * Method generator code class
 *
 * @category   Pop
 * @package    Pop_Code
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class MethodGenerator
{

    /**
     * Docblock generator object
     * @var \Pop\Code\Generator\DocblockGenerator
     */
    protected $docblock = null;

    /**
     * Method arguments
     * @var array
     */
    protected $arguments = array();

    /**
     * Method name
     * @var string
     */
    protected $name = null;

    /**
     * Method visibility
     * @var string
     */
    protected $visibility = 'public';

    /**
     * Method static flag
     * @var boolean
     */
    protected $static = false;

    /**
     * Method abstract flag
     * @var boolean
     */
    protected $abstract = false;

    /**
     * Method final flag
     * @var boolean
     */
    protected $final = false;

    /**
     * Method interface flag
     * @var boolean
     */
    protected $interface = false;

    /**
     * Method body
     * @var string
     */
    protected $body = null;

    /**
     * Method indent
     * @var string
     */
    protected $indent = '    ';

    /**
     * Method output
     * @var string
     */
    protected $output = null;

    /**
     * Constructor
     *
     * Instantiate the method generator object
     *
     * @param  string  $name
     * @param  string  $visibility
     * @param  boolean $static
     * @return \Pop\Code\Generator\MethodGenerator
     */
    public function __construct($name, $visibility = 'public', $static = false)
    {
        $this->name = $name;
        $this->visibility = $visibility;
        $this->static = (boolean)$static;
    }

    /**
     * Static method to instantiate the method generator object and return itself
     * to facilitate chaining methods together.
     *
     * @param  string  $name
     * @param  string  $visibility
     * @param  boolean $static
     * @return \Pop\Code\Generator\MethodGenerator
     */
    public static function factory($name, $visibility = 'public', $static = false)
    {
        return new self($name, $visibility, $static);
    }

    /**
     * Set the method static flag
     *
     * @param  boolean $static
     * @return \Pop\Code\Generator\MethodGenerator
     */
    public function setStatic($static = false)
    {
        $this->static = (boolean)$static;
        return $this;
    }

    /**
     * Get the method static flag
     *
     * @return boolean
     */
    public function isStatic()
    {
        return $this->static;
    }

    /**
     * Set the method abstract flag
     *
     * @param  boolean $abstract
     * @return \Pop\Code\Generator\MethodGenerator
     */
    public function setAbstract($abstract = false)
    {
        $this->abstract = (boolean)$abstract;
        return $this;
    }

    /**
     * Get the method abstract flag
     *
     * @return boolean
     */
    public function isAbstract()
    {
        return $this->abstract;
    }

    /**
     * Set the method final flag
     *
     * @param  boolean $final
     * @return \Pop\Code\Generator\MethodGenerator
     */
    public function setFinal($final = false)
    {
        $this->final = (boolean)$final;
        return $this;
    }

    /**
     * Get the method final flag
     *
     * @return boolean
     */
    public function isFinal()
    {
        return $this->final;
    }

    /**
     * Set the method interface flag
     *
     * @param  boolean $interface
     * @return \Pop\Code\Generator\MethodGenerator
     */
    public function setInterface($interface = false)
    {
        $this->interface = (boolean)$interface;
        return $this;
    }

    /**
     * Get the method interface flag
     *
     * @return boolean
     */
    public function isInterface()
    {
        return $this->interface;
    }

    /**
     * Set the method description
     *
     * @param  string $desc
     * @return \Pop\Code\Generator\MethodGenerator
     */
    public function setDesc($desc = null)
    {
        if (null !== $this->docblock) {
            $this->docblock->setDesc($desc);
        } else {
            $this->docblock = new DocblockGenerator($desc, $this->indent);
        }
        return $this;
    }

    /**
     * Get the method description
     *
     * @return string
     */
    public function getDesc()
    {
        $desc = null;
        if (null !== $this->docblock) {
            $desc = $this->docblock->getDesc();
        }
        return $desc;
    }

    /**
     * Set the method indent
     *
     * @param  string $indent
     * @return \Pop\Code\Generator\MethodGenerator
     */
    public function setIndent($indent = null)
    {
        $this->indent = $indent;
        return $this;
    }

    /**
     * Get the method indent
     *
     * @return string
     */
    public function getIndent()
    {
        return $this->indent;
    }

    /**
     * Set the method name
     *
     * @param  string $name
     * @return \Pop\Code\Generator\MethodGenerator
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the method name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the method body
     *
     * @param  string $body
     * @param  boolean $newline
     * @return \Pop\Code\Generator\MethodGenerator
     */
    public function setBody($body, $newline = true)
    {
        $this->body = $this->indent . '    ' .  str_replace(PHP_EOL, PHP_EOL . $this->indent . '    ', $body);
        if ($newline) {
            $this->body .= PHP_EOL;
        }
        return $this;
    }

    /**
     * Append to the method body
     *
     * @param  string  $body
     * @param  boolean $newline
     * @return \Pop\Code\Generator\MethodGenerator
     */
    public function appendToBody($body, $newline = true)
    {
        $body = str_replace(PHP_EOL, PHP_EOL . $this->indent . '    ', $body);
        $this->body .= $this->indent . '    ' . $body;
        if ($newline) {
            $this->body .= PHP_EOL;
        }
        return $this;
    }

    /**
     * Get the method body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set the docblock generator object
     *
     * @param  DocblockGenerator $docblock
     * @return \Pop\Code\Generator\ClassGenerator
     */
    public function setDocblock(DocblockGenerator $docblock)
    {
        $this->docblock = $docblock;
        return $this;
    }

    /**
     * Access the docblock generator object
     *
     * @return \Pop\Code\Generator\DocblockGenerator
     */
    public function getDocblock()
    {
        return $this->docblock;
    }

    /**
     * Set the method visibility
     *
     * @param  string $visibility
     * @return \Pop\Code\Generator\MethodGenerator
     */
    public function setVisibility($visibility = 'public')
    {
        $this->visibility = $visibility;
        return $this;
    }

    /**
     * Get the method visibility
     *
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Add a method argument
     *
     * @param string  $name
     * @param mixed   $value
     * @param string  $type
     * @return \Pop\Code\Generator\MethodGenerator
     */
    public function addArgument($name, $value = null, $type = null)
    {
        $typeHintsNotAllowed = array(
            'int',
            'integer',
            'boolean',
            'float',
            'string',
            'mixed'
        );
        $argType = (!in_array($type, $typeHintsNotAllowed)) ? $type : null;
        $this->arguments[$name] = array('value' => $value, 'type' => $argType);
        if (null === $this->docblock) {
            $this->docblock = new DocblockGenerator(null, $this->indent);
        }
        if (null !== $type) {
            if (substr($name, 0, 1) != '$') {
                $name = '$' . $name;
            }
            $this->docblock->setParam($type, $name);
        }
        return $this;
    }

    /**
     * Add method arguments
     *
     * @param array $args
     * @return \Pop\Code\Generator\MethodGenerator
     */
    public function addArguments(array $args)
    {
        foreach ($args as $arg) {
            $value = (isset($arg['value'])) ? $arg['value'] : null;
            $type = (isset($arg['type'])) ? $arg['type'] : null;
            $this->addArgument($arg['name'], $value, $type);
        }
        return $this;
    }

    /**
     * Add a method argument (synonym method for convenience)
     *
     * @param string  $name
     * @param mixed   $value
     * @param string  $type
     * @return \Pop\Code\Generator\MethodGenerator
     */
    public function addParameter($name, $value = null, $type = null)
    {
        $this->addArgument($name, $value, $type);
        return $this;
    }

    /**
     * Add method arguments (synonym method for convenience)
     *
     * @param array $args
     * @return \Pop\Code\Generator\MethodGenerator
     */
    public function addParameters(array $args)
    {
        $this->addArguments($args);
        return $this;
    }

    /**
     * Get a method argument
     *
     * @param  string $name
     * @return array
     */
    public function getArgument($name)
    {
        return (isset($this->arguments[$name])) ? $this->arguments[$name] : null;
    }

    /**
     * Get the method arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Get a method argument (synonym method for convenience)
     *
     * @param  string $name
     * @return array
     */
    public function getParameter($name)
    {
        return (isset($this->arguments[$name])) ? $this->arguments[$name] : null;
    }

    /**
     * Get the method arguments (synonym method for convenience)
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->arguments;
    }

    /**
     * Render method
     *
     * @param  boolean $ret
     * @return mixed
     */
    public function render($ret = false)
    {
        $final = ($this->final) ? 'final ' : null;
        $abstract = ($this->abstract) ? 'abstract ' : null;
        $static = ($this->static) ? ' static' : null;
        $args = $this->formatArguments();

        $this->output = PHP_EOL . ((null !== $this->docblock) ? $this->docblock->render(true) : null);
        $this->output .= $this->indent . $final . $abstract . $this->visibility .
           $static . ' function ' . $this->name . '(' . $args . ')';

        if ((!$this->abstract) && (!$this->interface)) {
            $this->output .= PHP_EOL . $this->indent . '{' . PHP_EOL;
            $this->output .= $this->body. PHP_EOL;
            $this->output .= $this->indent . '}';
        } else {
            $this->output .= ';';
        }

        $this->output .= PHP_EOL;

        if ($ret) {
            return $this->output;
        } else {
            echo $this->output;
        }
    }

    /**
     * Method to format the arguments
     *
     * @return string
     */
    protected function formatArguments()
    {
        $args = null;

        $i = 0;
        foreach ($this->arguments as $name => $arg) {
            $i++;
            $args .= (null !== $arg['type']) ? $arg['type'] . ' ' : null;
            $args .= (substr($name, 0, 1) != '$') ? "\$" . $name : $name;
            $args .= (null !== $arg['value']) ? " = " . $arg['value'] : null;
            if ($i < count($this->arguments)) {
                $args .= ', ';
            }
        }

        return $args;
    }

    /**
     * Print method
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render(true);
    }

}
