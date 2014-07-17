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
namespace Pop\Code\Generator;

/**
 * Docblock generator code class
 *
 * @category   Pop
 * @package    Pop_Code
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class DocblockGenerator implements GeneratorInterface
{

    /**
     * Docblock description
     * @var string
     */
    protected $desc = null;

    /**
     * Docblock tags
     * @var array
     */
    protected $tags = ['param' => []];

    /**
     * Docblock indent
     * @var string
     */
    protected $indent = null;

    /**
     * Docblock output
     * @var string
     */
    protected $output = null;

    /**
     * Constructor
     *
     * Instantiate the docblock generator object
     *
     * @param  string $desc
     * @param  string $indent
     * @return DocblockGenerator
     */
    public function __construct($desc = null, $indent = null)
    {
        $this->desc   = $desc;
        $this->indent = $indent;
    }

    /**
     * Static method to parse a docblock string and return a new
     * docblock generator object.
     *
     * @param  string $docblock
     * @param  string $forceIndent
     * @throws Exception
     * @return DocblockGenerator
     */
    public static function parse($docblock, $forceIndent = null)
    {
        if ((strpos($docblock, '/*') === false) || (strpos($docblock, '*/') === false)) {
            throw new Exception('The docblock is not in the correct format.');
        }

        $desc          = null;
        $formattedDesc = null;
        $indent        = null;
        $tags          = null;

        // Parse the description, if any
        if (strpos($docblock, '@') !== false) {
            $desc    = substr($docblock, 0, strpos($docblock, '@'));
            $desc    = str_replace('/*', '', $desc);
            $desc    = str_replace('*/', '', $desc);
            $desc    = str_replace(PHP_EOL . ' * ', ' ', $desc);
            $desc    = trim(str_replace('*', '', $desc));
            $descAry = explode("\n", $desc);

            $formattedDesc = null;
            foreach ($descAry as $line) {
                $formattedDesc .= ' ' . trim($line);
            }

            $formattedDesc = trim($formattedDesc);
        }

        // Get the indentation, if any, and create docblock object
        $indent      = (null === $forceIndent) ? substr($docblock, 0, strpos($docblock, '/')) : $forceIndent;
        $newDocblock = new self($formattedDesc, $indent);

        // Get the tags, if any
        if (strpos($docblock, '@') !== false) {
            $tags    = substr($docblock, strpos($docblock, '@'));
            $tags    = substr($tags, 0, strpos($tags, '*/'));
            $tags    = str_replace('*', '', $tags);
            $tagsAry = explode("\n", $tags);

            foreach ($tagsAry as $key => $value) {
                $value = trim(str_replace('@', '', $value));
                // Param tags
                if (stripos($value, 'param') !== false) {
                    $paramtag  = trim(str_replace('param', '', $value));
                    $paramtype = trim(substr($paramtag, 0, strpos($paramtag, ' ')));
                    $varname   = null;
                    $paramdesc = null;
                    if (strpos($paramtag, ' ') !== false) {
                        $varname = trim(substr($paramtag, strpos($paramtag, ' ')));
                        if (strpos($varname, ' ') !== false) {
                            $paramdesc = trim(substr($varname, strpos($varname, ' ')));
                        }
                    } else {
                        $paramtype = $paramtag;
                    }
                    $newDocblock->setParam($paramtype, $varname, $paramdesc);
                // Else, return tags
                } else if (stripos($value, 'return') !== false) {
                    $returntag = trim(str_replace('return', '', $value));
                    if (strpos($returntag, ' ') !== false) {
                        $returntype = substr($returntag, 0, strpos($returntag, ' '));
                        $returndesc = trim(str_replace($returntype, '', $returntag));
                    } else {
                        $returntype = $returntag;
                        $returndesc = null;
                    }
                    $newDocblock->setReturn($returntype, $returndesc);
                // Else, all other tags
                } else {
                    $tagname = trim(substr($value, 0, strpos($value, ' ')));
                    $tagdesc = trim(str_replace($tagname, '', $value));
                    if (!empty($tagname) && !empty($tagdesc)) {
                        $newDocblock->setTag($tagname, $tagdesc);
                    } else {
                        unset($tagsAry[$key]);
                    }
                }
            }
        }

        return $newDocblock;
    }

    /**
     * Set the docblock description
     *
     * @param  string $desc
     * @return DocblockGenerator
     */
    public function setDesc($desc = null)
    {
        $this->desc = $desc;
        return $this;
    }

    /**
     * Get the docblock description
     *
     * @return string
     */
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * Set the docblock indent
     *
     * @param  string $indent
     * @return DocblockGenerator
     */
    public function setIndent($indent = null)
    {
        $this->indent = $indent;
        return $this;
    }

    /**
     * Get the docblock indent
     *
     * @return string
     */
    public function getIndent()
    {
        return $this->indent;
    }

    /**
     * Add a basic tag
     *
     * @param  string $name
     * @param  string $desc
     * @return DocblockGenerator
     */
    public function setTag($name, $desc = null)
    {
        $this->tags[$name] = $desc;
        return $this;
    }

    /**
     * Add basic tags
     *
     * @param  array $tags
     * @return DocblockGenerator
     */
    public function setTags(array $tags)
    {
        foreach ($tags as $name => $desc) {
            $this->tags[$name] = $desc;
        }
        return $this;
    }

    /**
     * Get a tag
     *
     * @param  string $name
     * @return string
     */
    public function getTag($name)
    {
        return (isset($this->tags[$name])) ? $this->tags[$name] : null;
    }

    /**
     * Add a param tag
     *
     * @param  string $type
     * @param  string $var
     * @param  string $desc
     * @return DocblockGenerator
     */
    public function setParam($type, $var = null, $desc = null)
    {
        $this->tags['param'][] = ['type' => $type, 'var' => $var, 'desc' => $desc];
        return $this;
    }

    /**
     * Add a param tag
     *
     * @param  array $params
     * @return DocblockGenerator
     */
    public function setParams(array $params)
    {
        $params = (isset($params[0]) && is_array($params[0])) ? $params : [$params];
        foreach ($params as $param) {
            $this->tags['param'][] = $param;
        }
        return $this;
    }

    /**
     * Get a param
     *
     * @param  int $index
     * @return array
     */
    public function getParam($index)
    {
        return (isset($this->tags['param'][$index])) ? $this->tags['param'][$index] : null;
    }

    /**
     * Add a return tag
     *
     * @param  string $type
     * @param  string $desc
     * @return DocblockGenerator
     */
    public function setReturn($type, $desc = null)
    {
        $this->tags['return'] = ['type' => $type, 'desc' => $desc];
        return $this;
    }

    /**
     * Get the return
     *
     * @return array
     */
    public function getReturn()
    {
        return (isset($this->tags['return'])) ? $this->tags['return'] : null;
    }

    /**
     * Render docblock
     *
     * @param  boolean $ret
     * @return mixed
     */
    public function render($ret = false)
    {
        $this->output = $this->indent . '/**' . PHP_EOL;

        if (!empty($this->desc)) {
            $desc = trim($this->desc);
            $descAry = explode("\n", $desc);
            $i = 0;
            foreach ($descAry as $d) {
                $i++;
                $this->output .= $this->indent . ' * ' . wordwrap($d, 70, PHP_EOL . $this->indent . " * ") . PHP_EOL;
                if ($i < count($descAry)) {
                     $this->output .= $this->indent . ' * ' . PHP_EOL;
                }
            }
        }

        $this->output .= $this->formatTags();
        $this->output .= $this->indent . ' */' . PHP_EOL;

        if ($ret) {
            return $this->output;
        } else {
            echo $this->output;
        }
    }

    /**
     * Format the docblock tags
     *
     * @return string
     */
    protected function formatTags()
    {
        $tags      = null;
        $tagLength = $this->getTagLength();

        // Format basic tags
        foreach ($this->tags as $tag => $desc) {
            if (($tag != 'param') && ($tag != 'return') && ($tag != 'throws')) {
                $tags .= $this->indent . ' * @' . $tag .
                    str_repeat(' ', $tagLength - strlen($tag) + 1) .
                    $desc . PHP_EOL;
            }
        }

        // Format param tags
        foreach ($this->tags['param'] as $param) {
            $paramLength = $this->getParamLength();
            $tags .= $this->indent . ' * @param' .
                str_repeat(' ', $tagLength - 4) . $param['type'] .
                str_repeat(' ', $paramLength - strlen($param['type']) + 1) .
                $param['var'];
            if (null !== $param['desc']) {
                $tags .= ' ' . $param['desc'] . PHP_EOL;
            } else {
                $tags .= PHP_EOL;
            }
        }

        // Format throw tag
        if (array_key_exists('throws', $this->tags)) {
            $tags .= $this->indent . ' * @throws' .
                 str_repeat(' ', $tagLength - 5) .
                 $this->tags['throws'] . PHP_EOL;
        }

        // Format return tag
        if (array_key_exists('return', $this->tags)) {
            $tags .= $this->indent . ' * @return' .
                 str_repeat(' ', $tagLength - 5) .
                 $this->tags['return']['type'];
            if (null !== $this->tags['return']['desc']) {
                $tags .= ' ' . $this->tags['return']['desc'] . PHP_EOL;
            } else {
                $tags .= PHP_EOL;
            }
        }

        return ((null !== $tags) && (null !== $this->desc)) ? $this->indent . ' * ' . PHP_EOL . $tags : $tags;
    }

    /**
     * Get the longest tag length
     *
     * @return int
     */
    protected function getTagLength()
    {
        $length = 0;

        foreach ($this->tags as $key => $value) {
            if (strlen($key) > $length) {
                $length = strlen($key);
            }
        }

        return $length;
    }

    /**
     * Get the longest param type length
     *
     * @return int
     */
    protected function getParamLength()
    {
        $length = 0;

        foreach ($this->tags['param'] as $param) {
            if (strlen($param['type']) > $length) {
                $length = strlen($param['type']);
            }
        }

        return $length;
    }

    /**
     * Print docblock
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render(true);
    }

}
