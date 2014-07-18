<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Image;

/**
 * Vector image abstract class
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractVector implements ImageInterface
{

    /**
     * Constant for inner border
     * @var string
     */
    const INNER_BORDER = 'INNER_BORDER';

    /**
     * Constant for outer border
     * @var string
     */
    const OUTER_BORDER = 'OUTER_BORDER';

    /**
     * Constant for HEX format
     * @var string
     */
    const HEX = 'HEX';

    /**
     * Horizontal constant for gradients.
     * @var int
     */
    const HORIZONTAL = 'HORIZONTAL';

    /**
     * Vertical constant for gradients.
     * @var int
     */
    const VERTICAL = 'VERTICAL';

    /**
     * Radial constant for gradients
     * @var int
     */
    const RADIAL = 'RADIAL';

    /**
     * Image resource
     * @var mixed
     */
    protected $resource = null;

    /**
     * Image width
     * @var int
     */
    protected $width = null;

    /**
     * Image height
     * @var int
     */
    protected $height = null;

    /**
     * Image fill color
     * @var array
     */
    protected $fillColor = [0, 0, 0];

    /**
     * Image background color
     * @var array
     */
    protected $backgroundColor = [255, 255, 255];

    /**
     * Image stroke color
     * @var array
     */
    protected $strokeColor = null;

    /**
     * Image stroke width
     * @var array
     */
    protected $strokeWidth = null;

    /**
     * Image color opacity
     * @var mixed
     */
    protected $opacity = null;

    /**
     * Array of allowed image types.
     * @var array
     */
    protected $allowed = [];

    /**
     * Full path of image file, i.e. '/path/to/image.ext'
     * @var string
     */
    protected $fullpath = null;

    /**
     * Full, absolute directory of the image file, i.e. '/some/dir/'
     * @var string
     */
    protected $dir = null;

    /**
     * Full basename of image file, i.e. 'image.ext'
     * @var string
     */
    protected $basename = null;

    /**
     * Full filename of image file, i.e. 'image'
     * @var string
     */
    protected $filename = null;

    /**
     * Image file extension, i.e. 'ext'
     * @var string
     */
    protected $extension = null;

    /**
     * Image file size in bytes
     * @var int
     */
    protected $size = 0;

    /**
     * Image file mime type
     * @var string
     */
    protected $mime = null;

    /**
     * Image file output buffer
     * @var string
     */
    protected $output = null;

    /**
     * Constructor
     *
     * Instantiate an image object based on either a pre-existing image
     * file on disk, or a new image file.
     *
     * @param  string $img
     * @param  int    $w
     * @param  int    $h
     * @throws Exception
     * @return \Pop\Image\AbstractVector
     */
    public function __construct($img, $w = null, $h = null)
    {
        $this->setImage($img);
    }

    /**
     * Get the image full path
     *
     * @return string
     */
    public function getFullPath()
    {
        return $this->fullpath;
    }

    /**
     * Get the image directory
     *
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * Get the image basename
     *
     * @return string
     */
    public function getBasename()
    {
        return $this->basename;
    }

    /**
     * Get the image filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get the image extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Get the image size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Get the image mime type
     *
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * Get the image opacity setting
     *
     * @return mixed
     */
    public function getOpacity()
    {
        return $this->opacity;
    }

    /**
     * Get the image width.
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Get the image height.
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set the fill color.
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return AbstractVector
     */
    public function setFillColor($r = 0, $g = 0, $b = 0)
    {
        $this->fillColor = [(int)$r, (int)$g, (int)$b];
        return $this;
    }

    /**
     * Set the background color.
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return AbstractVector
     */
    public function setBackgroundColor($r = 0, $g = 0, $b = 0)
    {
        $this->backgroundColor = [(int)$r, (int)$g, (int)$b];
        return $this;
    }

    /**
     * Set the stroke color.
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return AbstractVector
     */
    public function setStrokeColor($r = 0, $g = 0, $b = 0)
    {
        $this->strokeColor = [(int)$r, (int)$g, (int)$b];
        return $this;
    }

    /**
     * Set the stroke width.
     *
     * @param  mixed $wid
     * @return AbstractVector
     */
    public function setStrokeWidth($wid = null)
    {
        $this->strokeWidth = $wid;
        return $this;
    }

    /**
     * Set the opacity.
     *
     * @param  mixed $opac
     * @return AbstractVector
     */
    abstract public function setOpacity($opac);


    /**
     * Method to add a line to the image.
     *
     * @param  int $x1
     * @param  int $y1
     * @param  int $x2
     * @param  int $y2
     * @return AbstractVector
     */
    abstract public function drawLine($x1, $y1, $x2, $y2);

    /**
     * Method to add a rectangle to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return AbstractVector
     */
    abstract public function drawRectangle($x, $y, $w, $h = null);

    /**
     * Method to add a square to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @return AbstractVector
     */
    abstract public function drawSquare($x, $y, $w);

    /**
     * Method to add an ellipse to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return AbstractVector
     */
    abstract public function drawEllipse($x, $y, $w, $h = null);

    /**
     * Method to add a circle to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @return AbstractVector
     */
    abstract public function drawCircle($x, $y, $w);

    /**
     * Method to add an arc to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $start
     * @param  int $end
     * @param  int $w
     * @param  int $h
     * @return AbstractVector
     */
    abstract public function drawArc($x, $y, $start, $end, $w, $h = null);

    /**
     * Method to add a polygon to the image.
     *
     * @param  array $points
     * @return AbstractVector
     */
    abstract public function drawPolygon($points);

    /**
     * Method to add a border to the image.
     *
     * @param  int $w
     * @return AbstractVector
     */
    abstract public function border($w);

    /**
     * Create text within the an image object.
     *
     * @param  string $str
     * @param  int    $size
     * @param  array  $options
     * @return AbstractVector
     */
    abstract public function text($str, $size, array $options = []);

    /**
     * Destroy the image object and the related image file directly.
     *
     * @param  boolean $file
     * @return void
     */
    abstract public function destroy($file = false);

    /**
     * Output the image object directly.
     *
     * @param  boolean $download
     * @return AbstractVector
     */
    abstract public function output($download = false);

    /**
     * Set the image properties
     *
     * @param  string $img
     * @throws Exception
     * @return void
     */
    protected function setImage($img)
    {
        $this->fullpath  = $img;
        $parts           = pathinfo($img);
        $this->size      = (file_exists($img) ? filesize($img) : 0);
        $this->dir       = realpath($parts['dirname']);
        $this->basename  = $parts['basename'];
        $this->filename  = $parts['filename'];
        $this->extension = (isset($parts['extension']) && ($parts['extension'] != '')) ? $parts['extension'] : null;

        if ((null === $this->extension) || (!isset($this->allowed[strtolower($this->extension)]))) {
            throw new Exception('Error: That image file does not have the correct extension.');
        } else {
            $this->mime = $this->allowed[strtolower($this->extension)];
        }
    }

}