<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Image;

/**
 * SVG image class
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Svg
{

    /**
     * SVG image resource
     * @var \SimpleXMLElement
     */
    protected $resource = null;

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
     * SVG image file extension, i.e. 'ext'
     * @var string
     */
    protected $extension = null;

    /**
     * SVG image file size in bytes
     * @var int
     */
    protected $size = 0;

    /**
     * SVG image file mime type
     * @var string
     */
    protected $mime = 'image/svg+xml';

    /**
     * SVG image file output buffer
     * @var mixed
     */
    protected $output = null;

    /**
     * SVG image width
     * @var int
     */
    protected $width = null;

    /**
     * SVG image height
     * @var int
     */
    protected $height = null;

    /**
     * Array of allowed image types.
     * @var array
     */
    protected $allowed = [
        'svg' => 'image/svg+xml'
    ];

    /**
     * SVG image fill color
     * @var mixed
     */
    protected $fillColor = null;

    /**
     * SVG image background color
     * @var mixed
     */
    protected $backgroundColor = null;

    /**
     * SVG image stroke color
     * @var mixed
     */
    protected $strokeColor = null;

    /**
     * SVG image stroke width
     * @var array
     */
    protected $strokeWidth = null;

    /**
     * Stroke dash length
     * @var int
     */
    protected $strokeDashLength = null;

    /**
     * Stroke dash gap
     * @var int
     */
    protected $strokeDashGap = null;

    /**
     * SVG image available gradients
     * @var array
     */
    protected $gradients = [];

    /**
     * Current gradient to use.
     * @var int
     */
    protected $curGradient = null;

    /**
     * SVG image available clipping paths
     * @var array
     */
    protected $clippingPaths = [];

    /**
     * Current clipping path to use.
     * @var int
     */
    protected $curClippingPath = null;

    /**
     * SVG image color opacity
     * @var float
     */
    protected $opacity = 1.0;

    /**
     * SVG image units
     * @var string
     */
    protected $units = null;

    /**
     * Array of allowed units.
     * @var array
     */
    protected $allowedUnits = ['em', 'ex', 'px', 'pt', 'pc', 'cm', 'mm', 'in', '%'];

    /**
     * Constructor
     *
     * Instantiate an SVG image file object based on either a pre-existing
     * image file on disk, or a new SVG image file.
     *
     * @param  string $img
     * @param  int    $w
     * @param  int    $h
     * @throws Exception
     * @return Svg
     */
    public function __construct($img = null, $w = null, $h = null)
    {
        // If the arguments passed are $img, $w, $h
        if ((null !== $img) && (stripos($img, '.svg') !== false)) {
            $this->setImage($img);
            if ((null !== $w) && (null !== $h)) {
                $this->parseDimensions($w, $h);
            }
            // Else, if the arguments passed are $w, $h, $img
        } else if ((null !== $h) && (stripos($h, '.svg') !== false)) {
            $imgName = (null !== $h) ? $h : 'pop-svg-image-' . time() . '.svg';
            $this->parseDimensions($img, $w);
            $this->setImage($imgName);
        }

        // If image exists
        if (file_exists($this->fullpath) && ($this->size > 0)) {
            $this->load($this->fullpath);
        // Else, if image does not exists
        } else if ((null !== $this->width) && (null !== $this->height)) {
            $this->create($this->width, $this->height, $this->basename);
        }
    }

    /**
     * Load an existing SVG image resource
     *
     * @param  string $image
     * @return Svg
     */
    public function load($image)
    {
        $this->resource = new \SimpleXMLElement($image, null, true);
        $this->parseDimensions($this->resource->attributes()->width, $this->resource->attributes()->height);
        return $this;
    }

    /**
     * Create a new SVG image resource
     *
     * @param  int    $width
     * @param  int    $height
     * @param  string $image
     * @return Svg
     */
    public function create($width, $height, $image = null)
    {
        $this->parseDimensions($width, $height);
        $svg = '<?xml version="1.0" standalone="no"?>' . PHP_EOL . '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" ' .
            '"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">' . PHP_EOL . '<svg width="' . $width . '" height="' . $height .
            '" version="1.1" xmlns="http://www.w3.org/2000/svg">' . PHP_EOL . '    <desc>' . PHP_EOL .
            '        SVG Image generated by Pop PHP Framework' . PHP_EOL . '    </desc>' . PHP_EOL . '</svg>' . PHP_EOL;

        $this->resource = new \SimpleXMLElement($svg);

        if (null !== $image) {
            $this->setImage($image);
        }

        return $this;
    }
    /**
     * Get the image resource
     *
     * @return resource
     */
    public function resource()
    {
        return $this->resource;
    }

    /**
     * Get the image full path
     *
     * @return string
     */
    public function getFullpath()
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

    /**
     * Parse and set the image dimensions and units
     *
     * @param  mixed $w
     * @param  mixed $h
     * @return void
     */
    protected function parseDimensions($w, $h)
    {
        if (is_numeric($w) && is_numeric($h)) {
            $this->width  = $w;
            $this->height = $h;
        } else {
            foreach ($this->allowedUnits as $unit) {
                if ((stripos($w, $unit) !== false) && (stripos($w, $unit) !== false)) {
                    $this->units  = $unit;
                    $this->width  = substr($w, 0, strpos($w, $unit));
                    $this->height = substr($h, 0, strpos($h, $unit));
                }
            }
        }
    }

}
