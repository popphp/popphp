<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Code
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Code;

use Pop\Code\Generator;

/**
 * Reflection code class
 *
 * @category   Pop
 * @package    Pop_Code
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Reflection extends \ReflectionClass
{

    /**
     * Code to reflect
     * @var string
     */
    protected $code = null;

    /**
     * Code generator object
     * @var \Pop\Code\Generator
     */
    protected $generator = null;

    /**
     * Constructor
     *
     * Instantiate the code reflection object
     *
     * @param  string  $code
     * @return \Pop\Code\Reflection
     */
    public function __construct($code)
    {
        $this->code = $code;
        parent::__construct($code);
        $this->buildGenerator();
    }

    /**
     * Static method to instantiate the code reflection object and return itself
     * to facilitate chaining methods together.
     *
     * @param  string  $code
     * @return \Pop\Code\Reflection
     */
    public static function factory($code)
    {
        return new self($code);
    }

    /**
     * Get the code string
     *
     * @return string
     */
    public function code()
    {
        return $this->code;
    }

    /**
     * Get the code generator
     *
     * @return \Pop\Code\Generator
     */
    public function generator()
    {
        return $this->generator;
    }

    /**
     * Build the code generator based the reflection class
     *
     * @return void
     */
    protected function buildGenerator()
    {

        // Create generator object
        $type = ($this->isInterface()) ? Generator::CREATE_INTERFACE : Generator::CREATE_CLASS;
        $this->generator = new Generator($this->getShortName() . '.php', $type);

        // Get the namespace
        $this->getClassNamespace();

        // Detect and set the class doc block
        $classDocBlock = $this->getDocComment();
        if (!empty($classDocBlock) && (strpos($classDocBlock, '/*') !== false)) {
            $this->generator->code()->setDocblock(Generator\DocblockGenerator::parse($classDocBlock));
        }

        // Detect and set if the class is abstract
        if (!$this->isInterface() && $this->isAbstract()) {
            $this->generator->code()->setAbstract(true);
        }

        // Detect and set if the class is a child class
        $parent = $this->getParentClass();
        if ($parent !== false) {
            if ($parent->inNamespace()) {
                $this->generator->getNamespace()->setUse($parent->getNamespaceName() . '\\' . $parent->getShortName());
            }
            $this->generator->code()->setParent($parent->getShortName());
        }

        // Detect and set if the class implements any interfaces
        if (!$this->isInterface()) {
            $interfaces = $this->getInterfaces();
            if ($interfaces !== false) {
                $interfacesAry = [];
                foreach ($interfaces as $interface) {
                    if ($interface->inNamespace()) {
                        $this->generator->getNamespace()->setUse($interface->getNamespaceName() . '\\' . $interface->getShortName());
                    }
                    $interfacesAry[] = $interface->getShortName();
                }
                $this->generator->code()->setInterface(implode(', ', $interfacesAry));
            }
        }

        // Detect and set constants
        $constants = $this->getConstants();
        if (count($constants) > 0) {
            foreach ($constants as $key => $value) {
                $this->generator->code()->addProperty(new Generator\PropertyGenerator($key, gettype($value), $value, 'const'));
            }
        }

        // Get properties
        $this->getClassProperties();

        // Get Methods
        $this->getClassMethods();
    }

    /**
     * Get the namespace and uses, if any
     *
     * @return void
     */
    protected function getClassNamespace()
    {
        $fileContents = (file_exists($this->getFilename())) ? file_get_contents($this->getFilename()) : null;

        // Detect and set namespace
        if ($this->inNamespace()) {
            $this->generator->setNamespace(new Generator\NamespaceGenerator($this->getNamespaceName()));
            if (null !== $fileContents) {
                $matches = [];
                preg_match('/^use(.*)/m', $fileContents, $matches, PREG_OFFSET_CAPTURE);
                if (isset($matches[0][0])) {
                    $uses = substr($fileContents, $matches[0][1] + 4);
                    $uses = substr($uses, 0, strpos($uses, ';'));
                    $usesAry = explode(',', $uses);
                    foreach ($usesAry as $use) {
                        $use = trim($use);
                        $as = null;
                        if (stripos($use, 'as') !== false) {
                            $as = trim(substr($use, (strpos($use, 'as') + 2)));
                            $use = trim(substr($use, 0, strpos($use, 'as')));
                        }
                        $this->generator->getNamespace()->setUse($use, $as);
                    }
                }
            }
        }
    }

    /**
     * Get properties
     *
     * @return void
     */
    protected function getClassProperties()
    {
        // Detect and set properties
        $properties = $this->getDefaultProperties();

        if (count($properties) > 0) {
            foreach ($properties as $name => $value) {
                $property = $this->getProperty($name);
                $visibility = 'public';
                if ($property->isPublic()) {
                    $visibility = 'public';
                } else if ($property->isProtected()) {
                    $visibility = 'protected';
                } else if ($property->isPrivate()) {
                    $visibility = 'private';
                }

                $doc = $property->getDocComment();
                if ((null !== $doc) && (strpos($doc, '/*') !== false)) {
                    $docblock = Generator\DocblockGenerator::parse($doc);
                    $desc = $docblock->getDesc();
                    $type = $docblock->getTag('var');
                } else {
                    $type = strtolower(gettype($value));
                    $desc = null;
                }

                if (is_array($value)) {
                    $formattedValue = (count($value) == 0) ? null : $value;
                } else {
                    $formattedValue = $value;
                }

                $prop = new Generator\PropertyGenerator($property->getName(), $type, $formattedValue, $visibility);
                $prop->setStatic($property->isStatic());
                $prop->setDesc($desc);
                $this->generator->code()->addProperty($prop);
            }
        }
    }

    /**
     * Get methods
     *
     * @return void
     */
    protected function getClassMethods()
    {
        // Detect and set methods
        $methods = $this->getMethods();

        if (count($methods) > 0) {
            foreach ($methods as $value) {
                $methodExport = \ReflectionMethod::export($value->class, $value->name, true);

                // Get the method docblock
                if ((strpos($methodExport, '/*') !== false) && (strpos($methodExport, '*/') !== false)) {
                    $docBlock = substr($methodExport, strpos($methodExport, '/*'));
                    $docBlock = substr($docBlock, 0, (strpos($methodExport, '*/') + 2));
                } else {
                    $docBlock = null;
                }

                $method = $this->getMethod($value->name);
                $visibility = 'public';

                if ($method->isPublic()) {
                    $visibility = 'public';
                } else if ($method->isProtected()) {
                    $visibility = 'protected';
                } else if ($method->isPrivate()) {
                    $visibility = 'private';
                }

                $mthd = new Generator\MethodGenerator($value->name, $visibility, $method->isStatic());
                if ((null !== $docBlock) && (strpos($docBlock, '/*') !== false)) {
                    $mthd->setDocblock(Generator\DocblockGenerator::parse($docBlock, $mthd->getIndent()));
                }
                $mthd->setFinal($method->isFinal())
                     ->setAbstract($method->isAbstract());

                // Get the method parameters
                if (stripos($methodExport, 'Parameter') !== false) {
                    $matches = [];
                    preg_match_all('/Parameter \#(.*)\]/m', $methodExport, $matches);
                    if (isset($matches[0][0])) {
                        foreach ($matches[0] as $param) {
                            $name = null;
                            $value = null;
                            $type = null;

                            // Get name
                            $name = substr($param, strpos($param, '$'));
                            $name = trim(substr($name, 0, strpos($name, ' ')));

                            // Get value
                            if (strpos($param, '=') !== false) {
                                $value = trim(substr($param, (strpos($param, '=') + 1)));
                                $value = trim(substr($value, 0, strpos($value, ' ')));
                                $value = str_replace('NULL', 'null', $value);
                            }

                            // Get type
                            $type = substr($param, (strpos($param, '>') + 1));
                            $type = trim(substr($type, 0, strpos($type, '$')));
                            if ($type == '') {
                                $type = null;
                            }

                            $mthd->addArgument($name, $value, $type);
                        }
                    }
                }

                // Get method body
                if ((strpos($methodExport, '@@') !== false) && (file_exists($this->getFilename()))) {
                    $lineNums = substr($methodExport, (strpos($methodExport, '@@ ') + 3));
                    $start = trim(substr($lineNums, strpos($lineNums, ' ')));
                    $end = substr($start, (strpos($start, ' - ') + 3));
                    $start = substr($start, 0, strpos($start, ' '));
                    $end = (int)$end;
                    if (is_numeric($start) && is_numeric($end)) {
                        $classLines = file($this->getFilename());
                        $body = null;
                        $start = $start + 1;
                        $end = $end - 1;
                        for ($i = $start; $i < $end; $i++) {
                            if (isset($classLines[$i])) {
                                if (substr($classLines[$i], 0, 8) == '        ') {
                                    $body .= substr($classLines[$i], 8);
                                } else if (substr($classLines[$i], 0, 4) == '    ') {
                                    $body .= substr($classLines[$i], 4);
                                } else if (substr($classLines[$i], 0, 2) == "\t\t") {
                                    $body .= substr($classLines[$i], 2);
                                } else if (substr($classLines[$i], 0, 1) == "\t") {
                                    $body .= substr($classLines[$i], 1);
                                } else {
                                    $body .= $classLines[$i];
                                }
                            }
                        }
                        $mthd->setBody(rtrim($body), false);
                    }
                }

                $this->generator->code()->addMethod($mthd);
            }
        }
    }

}
