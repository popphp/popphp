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
 * Abstract image class
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractRasterImage extends AbstractImage implements RasterImageInterface
{

    /**
     * Image extension info
     * @var \ArrayObject
     */
    protected $info = null;

    /**
     * Image quality
     * @var int
     */
    protected $quality = null;

    /**
     * Image adjust object
     * @var Adjust\AdjustInterface
     */
    protected $adjust = null;

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
     * Constructor
     *
     * Instantiate an image object based on either a pre-existing image
     * file on disk, or a new image file.
     *
     * @param  string $img
     * @param  int    $w
     * @param  int    $h
     * @return AbstractRasterImage
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
     * Get the image quality.
     *
     * @return int
     */
    public function getQuality()
    {
        return $this->quality;
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
     * Get the image adjust object
     *
     * @return Adjust\AdjustInterface
     */
    abstract public function adjust();

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
     * Set the image quality.
     *
     * @param  int $quality
     * @return AbstractImage
     */
    abstract public function setQuality($quality);

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
     * value of the $px argument. The optional $offset argument allows for the
     * adjustment of the crop to select a certain area of the image to be
     * cropped.
     *
     * @param  int $px
     * @param  int $offset
     * @return AbstractImage
     */
    abstract public function cropThumb($px, $offset = null);

    /**
     * Rotate the image object
     *
     * @param  int   $degrees
     * @param  array $bgColor
     * @throws Exception
     * @return AbstractImage
     */
    abstract public function rotate($degrees, array $bgColor = [255, 255, 255]);

    /**
     * Method to flip the image over the x-axis.
     *
     * @return AbstractImage
     */
    abstract public function flip();

    /**
     * Method to flip the image over the y-axis.
     *
     * @return AbstractImage
     */
    abstract public function flop();

    /**
     * Convert the image object to another format.
     *
     * @param  string $type
     * @throws Exception
     * @return AbstractImage
     */
    abstract public function convert($type);

    /**
     * Create and return a color.
     *
     * @param  array   $color
     * @throws Exception
     * @return mixed
     */
    abstract public function getColor(array $color);

}
