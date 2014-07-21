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
 * Image class
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractImage implements ImageInterface
{

    /**
     * Image resource
     * @var mixed
     */
    protected $resource = null;

    /**
     * Image extension info
     * @var \ArrayObject
     */
    protected $info = null;

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
     * Image opacity
     * @var int
     */
    protected $opacity = null;

    /**
     * Image quality
     * @var int
     */
    protected $quality = null;

    /**
     * Image compression
     * @var int
     */
    protected $compression = null;

    /**
     * Array of allowed image types.
     * @var array
     */
    protected $allowed = [];

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
     * @return AbstractImage
     */
    public function __construct($img, $w = null, $h = null)
    {
        $this->setImage($img);
    }

    /**
     * Get the available image library adapters
     *
     * @return array
     */
    public static function getAvailableAdapters()
    {
        return [
            'gd'      => function_exists('gd_info'),
            'gmagick' => (class_exists('Gmagick', false)),
            'imagick' => (class_exists('Imagick', false))
        ];
    }

    /**
     * Get the available image library adapters
     *
     * @param  string $adapter
     * @return boolean
     */
    public static function isAvailable($adapter)
    {
        $result = false;

        switch (strtolower($adapter)) {
            case 'gd':
                $result = function_exists('gd_info');
                break;
            case 'graphicsmagick':
            case 'gmagick':
                $result = (class_exists('Gmagick', false));
                break;
            case 'imagemagick':
            case 'imagick':
                $result = (class_exists('Imagick', false));
                break;
        }

        return $result;
    }

    /**
     * Get the allowed image types
     *
     * @return array
     */
    public function getAllowedTypes()
    {
        return $this->allowed;
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
     * Get the image extension info
     *
     * @return \ArrayObject
     */
    public function info()
    {
        return $this->info;
    }

    /**
     * Get the image extension version
     *
     * @return string
     */
    public function version()
    {
        return $this->info->version;
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
     * Get the image opacity.
     *
     * @return int
     */
    public function getOpacity()
    {
        return $this->opacity;
    }

    /**
     * Get the image quality.
     *
     * @return int
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * Get the image compression.
     *
     * @return int
     */
    public function getCompression()
    {
        return $this->compression;
    }

    /**
     * Get the image adjust object
     *
     * @return mixed
     */
    abstract public function adjust();

    /**
     * Get the image draw object
     *
     * @return mixed
     */
    abstract public function draw();

    /**
     * Get the image effect object
     *
     * @return mixed
     */
    abstract public function effect();

    /**
     * Get the image filter object
     *
     * @return mixed
     */
    abstract public function filter();

    /**
     * Get the image layer object
     *
     * @return mixed
     */
    abstract public function layer();

    /**
     * Get the image transform object
     *
     * @return mixed
     */
    abstract public function transform();

    /**
     * Get the image type object
     *
     * @return mixed
     */
    abstract public function type();

    /**
     * Set the image opacity.
     *
     * @param  int $opacity
     * @return AbstractImage
     */
    abstract public function setOpacity($opacity);

    /**
     * Set the image quality.
     *
     * @param  int $quality
     * @return AbstractImage
     */
    abstract public function setQuality($quality);

    /**
     * Set the image compression.
     *
     * @param  int $compression
     * @return AbstractImage
     */
    abstract public function setCompression($compression);

    /**
     * Resize the image object to the width parameter passed.
     *
     * @param  int $w
     * @return AbstractImage
     */
    abstract public function resizeToWidth($w);

    /**
     * Resize the image object to the height parameter passed.
     *
     * @param  int $h
     * @return AbstractImage
     */
    abstract public function resizeToHeight($h);

    /**
     * Resize the image object, allowing for the largest dimension to be scaled
     * to the value of the $px argument.
     *
     * @param  int $px
     * @return AbstractImage
     */
    abstract public function resize($px);

    /**
     * Scale the image object, allowing for the dimensions to be scaled
     * proportionally to the value of the $scl argument.
     *
     * @param  float $scale
     * @return AbstractImage
     */
    abstract public function scale($scale);

    /**
     * Crop the image object to a image whose dimensions are based on the
     * value of the $wid and $hgt argument. The optional $x and $y arguments
     * allow for the adjustment of the crop to select a certain area of the
     * image to be cropped.
     *
     * @param  int $w
     * @param  int $h
     * @param  int $x
     * @param  int $y
     * @return AbstractImage
     */
    abstract public function crop($w, $h, $x = 0, $y = 0);

    /**
     * Crop the image object to a square image whose dimensions are based on the
     * value of the $px argument. The optional $x and $y arguments allow for the
     * adjustment of the crop to select a certain area of the image to be
     * cropped.
     *
     * @param  int $px
     * @param  int $x
     * @param  int $y
     * @return AbstractImage
     */
    abstract public function cropThumb($px, $x = 0, $y = 0);

    /**
     * Save the image object to disk.
     *
     * @param  string $to
     * @return void
     */
    abstract public function save($to = null);

    /**
     * Output the image object directly.
     *
     * @param  boolean $download
     * @return void
     */
    abstract public function output($download = false);

    /**
     * Destroy the image object and the related image file directly.
     *
     * @param  boolean $delete
     * @return void
     */
    abstract public function destroy($delete = false);

    /**
     * Create and return a color.
     *
     * @param  array   $color
     * @throws Exception
     * @return mixed
     */
    abstract public function getColor(array $color);

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
