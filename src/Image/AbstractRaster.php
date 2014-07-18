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
 * Raster image abstract class
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractRaster extends AbstractVector
{

    /**
     * Constant for regular blur
     * @var string
     */
    const BLUR = 'BLUR';

    /**
     * Constant for gaussian blur
     * @var string
     */
    const GAUSSIAN_BLUR = 'GAUSSIAN_BLUR';

    /**
     * Constant for motion blur
     * @var int
     */
    const MOTION_BLUR = 'MOTION_BLUR';

    /**
     * Constant for radial blur
     * @var int
     */
    const RADIAL_BLUR = 'RADIAL_BLUR';

    /**
     * Array of allowed image types.
     * @var array
     */
    protected $allowed = [
        'gif'  => 'image/gif',
        'jpe'  => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png'
    ];

    /**
     * Image extension info
     * @var \ArrayObject
     */
    protected $info = null;

    /**
     * Image channels
     * @var int
     */
    protected $channels = null;

    /**
     * Image bit depth
     * @var int
     */
    protected $depth = null;

    /**
     * Image mode
     * @var string
     */
    protected $mode = null;

    /**
     * Image alpha
     * @var boolean
     */
    protected $alpha = false;

    /**
     * Image quality
     * @var int|string
     */
    protected $quality = null;

    /**
     * Constructor
     *
     * Instantiate an image file object based on either a pre-existing
     * image file on disk, or a new image file.
     *
     * @param  string $img
     * @param  int    $w
     * @param  int    $h
     * @param  array  $types
     * @return AbstractRaster
     */
    public function __construct($img, $w = null, $h = null, array $types = null)
    {
        if (null !== $types) {
            $this->allowed = $types;
        }
        parent::__construct($img, $w, $h);
    }

    /**
     * Get formats
     *
     * @return array
     */
    public static function formats()
    {
        $i = new static('i.jpg', 1, 1);
        return $i->getFormats();
    }

    /**
     * Get the image resource info
     *
     * @return \ArrayObject
     */
    public function info()
    {
        return $this->info;
    }

    /**
     * Get the array of supported image formats.
     *
     * @return array
     */
    abstract public function getFormats();

    /**
     * Get the number of supported image formats.
     *
     * @return int
     */
    abstract public function getNumberOfFormats();

    /**
     * Get the image compression quality
     *
     * @return mixed
     */
    abstract public function getCompression();

    /**
     * Set the image quality.
     *
     * @param  mixed $q
     * @return AbstractRaster
     */
    abstract public function setQuality($q = null);

    /**
     * Set the image compression.
     *
     * @param  mixed $comp
     * @return AbstractRaster
     */
    abstract public function setCompression($comp = null);

    /**
     * Get the number of image channels.
     *
     * @return int
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * Get the image bit depth.
     *
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * Get the image color mode.
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Get the current image quality.
     *
     * @return mixed
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * Get whether or not the image has an alpha channel.
     *
     * @return boolean
     */
    public function hasAlpha()
    {
        return $this->alpha;
    }

    /**
     * Resize the image object to the width parameter passed.
     *
     * @param  int|string $wid
     * @return AbstractRaster
     */
    abstract public function resizeToWidth($wid);

    /**
     * Resize the image object to the height parameter passed.
     *
     * @param  int|string $hgt
     * @return AbstractRaster
     */
    abstract public function resizeToHeight($hgt);

    /**
     * Resize the image object, allowing for the largest dimension to be scaled
     * to the value of the $px argument. For example, if the value of $px = 200,
     * and the image is 800px X 600px, then the image will be scaled to
     * 200px X 150px.
     *
     * @param  int|string $px
     * @return AbstractRaster
     */
    abstract public function resize($px);

    /**
     * Scale the image object, allowing for the dimensions to be scaled
     * proportionally to the value of the $scl argument. For example, if the
     * value of $scl = 0.50, and the image is 800px X 600px, then the image
     * will be scaled to 400px X 300px.
     *
     * @param  float|string $scl
     * @return AbstractRaster
     */
    abstract public function scale($scl);

    /**
     * Crop the image object to a image whose dimensions are based on the
     * value of the $wid and $hgt argument. The optional $x and $y arguments
     * allow for the adjustment of the crop to select a certain area of the
     * image to be cropped.
     *
     * @param  int|string $wid
     * @param  int|string $hgt
     * @param  int|string $x
     * @param  int|string $y
     * @return AbstractRaster
     */
    abstract public function crop($wid, $hgt, $x = 0, $y = 0);

    /**
     * Crop the image object to a square image whose dimensions are based on the
     * value of the $px argument. The optional $x and $y arguments allow for the
     * adjustment of the crop to select a certain area of the image to be
     * cropped. For example, if the values of $px = 50, $x = 20, $y = 0 are
     * passed, then a 50px X 50px image will be created from the original image,
     * with its origins starting at the (20, 0) x-y coordinates.
     *
     * @param  int|string $px
     * @param  int|string $x
     * @param  int|string $y
     * @return AbstractRaster
     */
    abstract public function cropThumb($px, $x = 0, $y = 0);

    /**
     * Rotate the image object, using simple degrees, i.e. -90,
     * to rotate the image.
     *
     * @param  int|string $deg
     * @return AbstractRaster
     */
    abstract public function rotate($deg);

    /**
     * Method to adjust the brightness of the image.
     *
     * @param  int $b
     * @return AbstractRaster
     */
    abstract public function brightness($b);

    /**
     * Method to adjust the contrast of the image.
     *
     * @param  int $amount
     * @return AbstractRaster
     */
    abstract public function contrast($amount);

    /**
     * Method to desaturate the image.
     *
     * @return AbstractRaster
     */
    abstract public function desaturate();

    /**
     * Overlay an image onto the current image.
     *
     * @param  string $image
     * @param  int    $x
     * @param  int    $y
     * @return AbstractRaster
     */
    abstract public function overlay($image, $x = 0, $y = 0);

    /**
     * Method to colorize the image with the color passed.
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return AbstractRaster
     */
    abstract public function colorize($r = 0, $g = 0, $b = 0);

    /**
     * Method to invert the image (create a negative.)
     *
     * @return AbstractRaster
     */
    abstract public function invert();

    /**
     * Method to flip the image over the x-axis.
     *
     * @return AbstractRaster
     */
    abstract public function flip();

    /**
     * Method to flip the image over the y-axis.
     *
     * @return AbstractRaster
     */
    abstract public function flop();

    /**
     * Return the number of colors in the palette of indexed images.
     * Returns 0 for true color images.
     *
     * @return int
     */
    abstract public function colorTotal();

    /**
     * Return all of the colors in the palette in an array format, omitting any
     * repeats. It is strongly advised that this method only be used for smaller
     * image files, preferably with small palettes, as any large images with
     * many colors will cause this method to run slowly. Default format of the
     * values in the returned array is the 6-digit HEX value, but if 'RGB' is
     * passed, then the format of the values in the returned array will be
     * 'R,G,B', i.e. '235,123,12'.
     *
     * @param  string $format
     * @return array
     */
    abstract public function getColors($format = self::HEX);

    /**
     * Convert the image object to the new specified image type.
     *
     * @param  string     $type
     * @throws Exception
     * @return mixed
     */
    abstract public function convert($type);

    /**
     * Set and return a color identifier.
     *
     * @param  array $color
     * @return mixed
     */
    abstract protected function setColor(array $color);

}
