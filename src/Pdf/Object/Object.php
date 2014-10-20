<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Pdf\Object;

/**
 * Pdf object class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Object implements ObjectInterface
{

    /**
     * PDF object index
     * @var int
     */
    public $index = null;

    /**
     * PDF object data
     * @var string
     */
    protected $data = null;

    /**
     * PDF object definition
     * @var string
     */
    protected $def = null;

    /**
     * PDF object stream
     * @var string
     */
    protected $stream = null;

    /**
     * Compression property
     * @var boolean
     */
    protected $compress = false;

    /**
     * Compressed flag property
     * @var boolean
     */
    protected $isCompressed = false;

    /**
     * Palette object property
     * @var boolean
     */
    protected $isPalette = false;

    /**
     * XObject object property
     * @var boolean
     */
    protected $isXObject = false;

    /**
     * Constructor
     *
     * Instantiate a PDF object.
     *
     * @param  int|string $i
     * @return \Pop\Pdf\Object\Object
     */
    public function __construct($i)
    {
        // Use default settings for a new PDF object.
        if (is_int($i)) {
            $this->index = $i;
            $this->data = "\n[{obj_index}] 0 obj\n[{obj_def}]\n[{obj_stream}]\nendobj\n\n";
        } else if (is_string($i)) {
            // Else, determine the object index.
            $this->index = substr($i, 0, strpos($i, ' '));

            // Determine the objects definition and stream, if applicable.
            $s = substr($i, (strpos($i, ' obj') + 4));
            $s = substr($s, 0, strpos($s, 'endobj'));
            if (strpos($s, 'stream') !== false) {
                $def = substr($s, 0, strpos($s, 'stream'));
                $str = substr($s, (strpos($s, 'stream') + 6));
                $str = substr($str, 0, strpos($str, 'endstream'));
                $this->define($def);
                $this->setStream($str);
            } else {
                $this->define($s);
            }

            if (stripos($this->def, '/flatedecode') !== false) {
                $this->isCompressed = true;
            }

            if (stripos($this->def, '/xobject') !== false) {
                $this->isXObject = true;
            }

            $this->data = "\n[{obj_index}] 0 obj\n[{obj_def}]\n[{obj_stream}]\nendobj\n\n";
        }
    }

    /**
     * Method to print the PDF object.
     *
     * @return string
     */
    public function __toString()
    {
        $matches = [];

        // Set the content stream.
        $stream = (null !== $this->stream) ? "stream" . $this->stream . "endstream\n" : '';

        // Set up the Length definition.
        if ((strpos($this->def, '/Length ') !== false) && (strpos($this->def, '/Length1') === false) &&
            (strpos($this->def, '/Image') === false)) {
            preg_match('/\/Length\s\d*/', $this->def, $matches);
            if (isset($matches[0])) {
                $len = $matches[0];
                $len = str_replace('/Length', '', $len);
                $len = str_replace(' ', '', $len);
                $this->def = str_replace($len, '[{byte_length}]', $this->def);
            }
        } else if (strpos($this->def, '/Length') === false) {
            $this->def .= "<</Length [{byte_length}]>>\n";
        }

        // Calculate the byte length of the content stream and swap out the placeholders.
        $byteLength = (($this->compress) && (function_exists('gzcompress')) && (strpos($this->def, ' /Image') === false) &&
            (strpos($this->def, '/FlateDecode') === false)) ?
            $this->calcByteLength($this->stream) . " /Filter /FlateDecode" : $this->calcByteLength($this->stream);
        $data = str_replace('[{obj_index}]', $this->index, $this->data);
        $data = str_replace('[{obj_stream}]', $stream, $data);
        $data = str_replace('[{obj_def}]', $this->def, $data);
        $data = str_replace('[{byte_length}]', $byteLength, $data);

        // Clear Length definition if it is zero.
        if (strpos($data, '<</Length 0>>') !== false) {
            $data = str_replace('<</Length 0>>', '', $data);
        }

        return $data;
    }

    /**
     * Method to define the PDF object.
     *
     * @param  string $str
     * @return void
     */
    public function define($str)
    {
        $this->def = $str;
        if (stripos($this->def, '/xobject') !== false) {
            $this->isXObject = true;
        }
    }

    /**
     * Method to return the PDF object definition.
     *
     * @return string
     */
    public function getDef()
    {
        return $this->def;
    }

    /**
     * Method to set the stream the PDF object.
     *
     * @param  string $str
     * @return void
     */
    public function setStream($str)
    {
        $this->stream .= $str;
    }

    /**
     * Method to return the PDF object stream.
     *
     * @return string
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Method to compress the PDF object.
     *
     * @return void
     */
    public function compress()
    {
        if (($this->stream != '') && (function_exists('gzcompress')) && (strpos($this->def, ' /Image') === false) && (strpos($this->def, '/FlateDecode') === false)) {
            $this->compress = true;
            $this->stream = "\n" . gzcompress($this->stream, 9) . "\n";
            $this->isCompressed = true;
        }
    }


    /**
     * Method to determine whether or not the PDF object is compressed.
     *
     * @return boolean
     */
    public function isCompressed()
    {
        return $this->isCompressed;
    }

    /**
     * Method to set whether the PDF object is a palette object.
     *
     * @param  boolean $is
     * @return void
     */
    public function setPalette($is)
    {
        $this->isPalette = $is;
    }

    /**
     * Method to get whether the PDF object is a palette object.
     *
     * @return boolean
     */
    public function isPalette()
    {
        return $this->isPalette;
    }

    /**
     * Method to get whether the PDF object is an XObject.
     *
     * @return boolean
     */
    public function isXObject()
    {
        return $this->isXObject;
    }

    /**
     * Method to get the PDF object byte length.
     *
     * @return int
     */
    public function getByteLength()
    {
        return $this->calcByteLength($this);
    }

    /**
     * Method to calculate the byte length.
     *
     * @param  string $str
     * @return int
     */
    protected function calcByteLength($str)
    {
        $bytes = str_replace("\n", "", $str);
        return strlen($bytes);
    }

}
