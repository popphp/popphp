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
 * Namespace generator code class
 *
 * @category   Pop
 * @package    Pop_Code
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class NamespaceGenerator implements GeneratorInterface
{

    /**
     * Namespace
     * @var string
     */
    protected $namespace = null;

    /**
     * Array of namespaces to use
     * @var array
     */
    protected $use = [];

    /**
     * Docblock generator object
     * @var DocblockGenerator
     */
    protected $docblock = null;

    /**
     * Namespace indent
     * @var string
     */
    protected $indent = null;

    /**
     * Namespace output
     * @var string
     */
    protected $output = null;

    /**
     * Constructor
     *
     * Instantiate the property generator object
     *
     * @param  string $namespace
     * @return NamespaceGenerator
     */

    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Set the namespace
     *
     * @param  string $namespace
     * @return NamespaceGenerator
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Get the namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Set a namespace to use
     *
     * @param  string $use
     * @param  string $as
     * @return NamespaceGenerator
     */
    public function setUse($use, $as = null)
    {
        $this->use[$use] = $as;
        return $this;
    }

    /**
     * Set namespaces to use
     *
     * @param  array $uses
     * @return NamespaceGenerator
     */
    public function setUses(array $uses)
    {
        foreach ($uses as $use) {
            if (is_array($use)) {
                $this->use[$use[0]] = (isset($use[1])) ? $use[1] : null;
            } else {
                $this->use[$use] = null;
            }
        }
        return $this;
    }

    /**
     * Render namespace
     *
     * @param  boolean $ret
     * @return mixed
     */
    public function render($ret = false)
    {
        $this->docblock = new DocblockGenerator(null, $this->indent);
        $this->docblock->setTag('namespace');
        $this->output = $this->docblock->render(true);
        $this->output .= $this->indent . 'namespace ' . $this->namespace . ';' . PHP_EOL;

        if (count($this->use) > 0) {
            $this->output .= PHP_EOL;
            foreach ($this->use as $ns => $as) {
                $this->output .= $this->indent . 'use ';
                $this->output .= $ns;
                if (null !== $as) {
                    $this->output .= ' as ' . $as;
                }
                $this->output .= ';' . PHP_EOL;
            }
        }

        if ($ret) {
            return $this->output;
        } else {
            echo $this->output;
        }
    }

    /**
     * Print namespace
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render(true);
    }

}
