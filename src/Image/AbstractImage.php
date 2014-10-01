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
 * Image class
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
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
     * Image adjust object
     * @var Adjust\AdjustInterface
     */
    protected $adjust = null;

    /**
     * Image draw object
     * @var Draw\DrawInterface
     */
    protected $draw = null;

    /**
     * Image effect object
     * @var Effect\EffectInterface
     */
    protected $effect = null;

    /**
     * Image filter object
     * @var Filter\FilterInterface
     */
    protected $filter = null;

    /**
     * Image layer object
     * @var Layer\LayerInterface
     */
    protected $layer = null;

    /**
     * Image transform object
     * @var Transform\TransformInterface
     */
    protected $transform = null;

    /**
     * Image type object
     * @var Type\TypeInterface
     */
    protected $type = null;

    /**
     * Constructor
     *
     * Instantiate an image object based on either a pre-existing image
     * file on disk, or a new image file.
     *
     * @param  string $img
     * @param  int    $w
     * @param  int    $h
     * @return AbstractImage
     */
    public function __construct($img = null, $w = null, $h = null)
    {
        // If the arguments passed are $img, $w, $h
        if ((null !== $img) && !is_numeric($img) && is_string($img)) {
            $this->setImage($img);
            if ((null !== $w) && (null !== $h) && is_numeric($w) && is_numeric($h)) {
                $this->width  = $w;
                $this->height = $h;
            }
        // Else, if the arguments passed are $w, $h, $img
        } else if ((null !== $img) && (null !== $w) && is_numeric($img) && is_numeric($w)) {
            $this->width  = $img;
            $this->height = $w;
            $imgName      = ((null !== $h) && !is_numeric($h) && is_string($h)) ? $h : 'pop-image-' . time() . '.jpg';
            $this->setImage($imgName);
        }
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
     * Set the image adjust object
     *
     * @param  Adjust\AdjustInterface $adjust
     * @return AbstractImage
     */
    public function setAdjust(Adjust\AdjustInterface $adjust)
    {
        $this->adjust = $adjust;
        return $this;
    }

    /**
     * Set the image draw object
     *
     * @param  Draw\DrawInterface $draw
     * @return AbstractImage
     */
    public function setDraw(Draw\DrawInterface $draw)
    {
        $this->draw = $draw;
        return $this;
    }

    /**
     * Set the image effect object
     *
     * @param  Effect\EffectInterface $effect
     * @return AbstractImage
     */
    public function setEffect(Effect\EffectInterface $effect)
    {
        $this->effect = $effect;
        return $this;
    }

    /**
     * Set the image filter object
     *
     * @param  Filter\FilterInterface $filter
     * @return AbstractImage
     */
    public function setFilter(Filter\FilterInterface $filter)
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * Set the image layer object
     *
     * @param  Layer\LayerInterface $layer
     * @return AbstractImage
     */
    public function setLayer(Layer\LayerInterface $layer)
    {
        $this->layer = $layer;
        return $this;
    }

    /**
     * Set the image transform object
     *
     * @param  Transform\TransformInterface $transform
     * @return AbstractImage
     */
    public function setTransform(Transform\TransformInterface $transform)
    {
        $this->transform = $transform;
        return $this;
    }

    /**
     * Set the image type object
     *
     * @param  Type\TypeInterface $type
     * @return AbstractImage
     */
    public function setType(Type\TypeInterface $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Set the image adjust object
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return AbstractImage
     */
    public function setBackgroundColor($r, $g, $b)
    {
        $this->effect()->fill($r, $g, $b);
        return $this;
    }

    /**
     * Create a new image resource
     *
     * @param  int    $width
     * @param  int    $height
     * @param  string $image
     * @return AbstractImage
     */
    abstract public function create($width, $height, $image = null);

    /**
     * Load an existing image as a resource
     *
     * @param  string $image
     * @throws Exception
     * @return AbstractImage
     */
    abstract public function load($image);

    /**
     * Load an existing image resource
     *
     * @param  resource $resource
     * @return Gd
     */
    abstract public function loadResource($resource);

    /**
     * Get the image adjust object
     *
     * @return Adjust\AdjustInterface
     */
    abstract public function adjust();

    /**
     * Get the image draw object
     *
     * @return Draw\DrawInterface
     */
    abstract public function draw();

    /**
     * Get the image effect object
     *
     * @return Effect\EffectInterface
     */
    abstract public function effect();

    /**
     * Get the image filter object
     *
     * @return Filter\FilterInterface
     */
    abstract public function filter();

    /**
     * Get the image layer object
     *
     * @return Layer\LayerInterface
     */
    abstract public function layer();

    /**
     * Get the image transform object
     *
     * @return Transform\TransformInterface
     */
    abstract public function transform();

    /**
     * Get the image type object
     *
     * @return Type\TypeInterface
     */
    abstract public function type();

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
     * Rotate the image object
     *
     * @param  int   $degrees
     * @param  array $bgColor
     * @throws Exception
     * @return Gd
     */
    abstract public function rotate($degrees, array $bgColor = [255, 255, 255]);

    /**
     * Method to flip the image over the x-axis.
     *
     * @return Gd
     */
    abstract public function flip();

    /**
     * Method to flip the image over the y-axis.
     *
     * @return Gd
     */
    abstract public function flop();

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
