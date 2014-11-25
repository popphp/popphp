<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Dom
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Dom;

/**
 * Dom class
 *
 * @category   Pop
 * @package    Pop_Dom
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Document extends AbstractNode
{

    /**
     * Constant to use the HTML trans doctype
     * @var int
     */
    const HTML_TRANS = 0;

    /**
     * Constant to use HTML strict doctype
     * @var int
     */
    const HTML_STRICT = 1;

    /**
     * Constant to use the HTML frames doctype
     * @var int
     */
    const HTML_FRAMES = 2;

    /**
     * Constant to use the XHTML trans doctype
     * @var int
     */
    const XHTML_TRANS = 3;

    /**
     * Constant to use the XHTML strict doctype
     * @var int
     */
    const XHTML_STRICT = 4;

    /**
     * Constant to use the XHTML frames doctype
     * @var int
     */
    const XHTML_FRAMES = 5;

    /**
     * Constant to use the XHTML 1.1 doctype
     * @var int
     */
    const XHTML11 = 6;

    /**
     * Constant to use the XML doctype
     * @var int
     */
    const XML = 7;

    /**
     * Constant to use the HTML5 doctype
     * @var int
     */
    const HTML5 = 8;

    /**
     * Constant to use the RSS doctype
     * @var int
     */
    const RSS = 9;

    /**
     * Constant to use the ATOM doctype
     * @var int
     */
    const ATOM = 10;

    /**
     * Document type
     * @var string
     */
    protected $doctype = 7;

    /**
     * Document content type
     * @var string
     */
    protected $contentType = 'application/xml';

    /**
     * Document charset
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * Document doctypes
     * @var array
     */
    protected static $doctypes = [
        "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n",
        "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n",
        "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Frameset//EN\" \"http://www.w3.org/TR/html4/frameset.dtd\">\n",
        "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n",
        "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n",
        "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd\">\n",
        "<?xml version=\"1.0\" encoding=\"[{charset}]\"?>\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n",
        "<?xml version=\"1.0\" encoding=\"[{charset}]\"?>\n",
        "<!DOCTYPE html>\n",
        "<?xml version=\"1.0\" encoding=\"[{charset}]\"?>\n",
        "<?xml version=\"1.0\" encoding=\"[{charset}]\"?>\n"
    ];

    /**
     * Constructor
     *
     * Instantiate the document object
     *
     * @param  string $doctype
     * @param  mixed  $childNode
     * @param  string $indent
     * @return Document
     */
    public function __construct($doctype = null, $childNode = null, $indent = null)
    {
        $this->setDoctype($doctype);

        if (null !== $childNode) {
            $this->addChild($childNode);
        }
        if (null !== $indent) {
            $this->setIndent($indent);
        }
    }

    /**
     * Return the document type.
     *
     * @return string
     */
    public function getDoctype()
    {
        return str_replace('[{charset}]', $this->charset, Document::$doctypes[$this->doctype]);
    }

    /**
     * Return the document charset.
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Return the document charset.
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Set the document type.
     *
     * @param  string $doctype
     * @return Document
     */
    public function setDoctype($doctype = null)
    {
        if (null !== $doctype) {
            $doctype = (int)$doctype;

            if (array_key_exists($doctype, Document::$doctypes)) {
                $this->doctype = $doctype;
                switch ($this->doctype) {
                    case Document::ATOM:
                        $this->contentType = 'application/atom+xml';
                        break;
                    case Document::RSS:
                        $this->contentType = 'application/rss+xml';
                        break;
                    case Document::XML:
                        $this->contentType = 'application/xml';
                        break;
                    default:
                        $this->contentType = 'text/html';
                }
            }
        } else {
            $this->doctype = null;
        }

        return $this;
    }

    /**
     * Set the document charset.
     *
     * @param  string $char
     * @return Document
     */
    public function setCharset($char)
    {
        $this->charset = $char;
        return $this;
    }

    /**
     * Set the document charset.
     *
     * @param  string $content
     * @return Document
     */
    public function setContentType($content)
    {
        $this->contentType = $content;
        return $this;
    }

    /**
     * Render the document and its child elements.
     *
     * @param  boolean $ret
     * @return mixed
     */
    public function render($ret = false)
    {
        $this->output = null;

        if (null !== $this->doctype) {
            $this->output .= str_replace('[{charset}]', $this->charset, Document::$doctypes[$this->doctype]);
        }

        foreach ($this->childNodes as $child) {
            $this->output .= $child->render(true, 0, $this->indent);
        }

        // If the return flag is passed, return output.
        if ($ret) {
            return $this->output;
        // Else, print output.
        } else {
            if (null !== $this->doctype) {
                if (!headers_sent()) {
                    header("HTTP/1.1 200 OK");
                    header("Content-type: " . $this->contentType);
                }
            }
            echo $this->output;
        }
    }

    /**
     * Render Dom object to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render(true);
    }

}
