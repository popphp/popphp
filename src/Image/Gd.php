<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
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

use Pop\Color\Space;

/**
 * GD image class
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Gd extends AbstractImage
{

    /**
     * Image color opacity
     * @var int
     */
    protected $opacity = 0;

    /**
     * Constructor
     *
     * Instantiate an image file object based on either a pre-existing
     * image file on disk, or a new image file.
     *
     * @param  string               $img
     * @param  int                  $w
     * @param  int                  $h
     * @param  Space\ColorInterface $color
     * @param  array                $types
     * @throws Exception
     * @return Gd
     */
    public function __construct($img, $w = null, $h = null, Space\ColorInterface $color = null, $types = null)
    {
        parent::__construct($img, $w, $h, $color, $types);

        // Check to see if GD is installed.
        if (!self::isInstalled()) {
            throw new Exception('Error: The GD library extension must be installed to use the Gd adapter.');
        }

        $this->getInfo();

        // If image exists, get image info and store in an array.
        if (file_exists($this->fullpath) && ($this->size > 0)) {
            $imgSize = getimagesize($img);

            // Set image object properties.
            $this->width    = $imgSize[0];
            $this->height   = $imgSize[1];
            $this->channels = (isset($imgSize['channels'])) ? $imgSize['channels'] : null;
            $this->depth    = (isset($imgSize['bits'])) ? $imgSize['bits'] : null;
            $this->setQuality(100);

            // If the image is a GIF
            if ($this->mime == 'image/gif') {
                $this->mode = 'Indexed';
            // Else if the image is a PNG
            } else if ($this->mime == 'image/png') {
                $imgData   = file_get_contents($this->fullpath);
                $colorType = ord($imgData[25]);
                switch ($colorType) {
                    case 0:
                        $this->channels = 1;
                        $this->mode     = 'Gray';
                        break;
                    case 2:
                        $this->channels = 3;
                        $this->mode     = 'RGB';
                        break;
                    case 3:
                        $this->channels = 3;
                        $this->mode     = 'Indexed';
                        break;
                    case 4:
                        $this->channels = 1;
                        $this->mode     = 'Gray';
                        $this->alpha    = true;
                        break;
                    case 6:
                        $this->channels = 3;
                        $this->mode     = 'RGB';
                        $this->alpha    = true;
                        break;
                }
            // Else if the image is a JPEG.
            } else if ($this->channels == 1) {
                $this->mode = 'Gray';
            } else if ($this->channels == 3) {
                $this->mode = 'RGB';
            } else if ($this->channels == 4) {
                $this->mode = 'CMYK';
            }
        // If image does not exists, check to make sure the width and
        // height properties of the new image have been passed.
        } else {
            if ((null === $w) || (null === $h)) {
                throw new Exception('Error: You must define a width and height for a new image object.');
            }

            // Set image object properties.
            $this->width    = $w;
            $this->height   = $h;
            $this->channels = null;

            // Create a new image and allocate the background color.
            if ($this->mime == 'image/gif') {
                $this->resource = imagecreate($w, $h);
                $this->setBackgroundColor((null === $color) ? new Space\Rgb(255, 255, 255) : $color);
                $clr = $this->setColor($this->backgroundColor);
            } else {
                $this->resource = imagecreatetruecolor($w, $h);
                $this->setBackgroundColor((null === $color) ? new Space\Rgb(255, 255, 255) : $color);
                $clr = $this->setColor($this->backgroundColor);
                imagefill($this->resource, 0, 0, $clr);
            }

            // Set the quality and create a new, blank image file.
            unset($clr);
            $this->setQuality(100);
            $this->output = $this->resource;
        }
    }

    /**
     * Check if GD is installed.
     *
     * @return boolean
     */
    public static function isInstalled()
    {
        return function_exists('gd_info');
    }

    /**
     * Get the image resource to directly interact with it
     *
     * @return resource
     */
    public function resource()
    {
        return $this->resource;
    }

    /**
     * Set the image quality based on the type of image.
     *
     * @param  mixed $q
     * @return Gd
     */
    public function setQuality($q = null)
    {
        switch ($this->mime) {
            case 'image/png':
                $this->quality = ($q < 10) ? 9 : 10 - round(($q / 10), PHP_ROUND_HALF_DOWN);
                break;
            case 'image/jpeg':
                $this->quality = round($q);
                break;
            default:
                $this->quality = null;
        }

        return $this;
    }

    /**
     * Set the image compression quality.
     * Alias to setQuality()
     *
     * @param  mixed $comp
     * @return GD
     */
    public function setCompression($comp = null)
    {
        $this->setQuality($comp);
        return $this;
    }

    /**
     * Set the opacity.
     *
     * @param  int $opac
     * @return Gd
     */
    public function setOpacity($opac)
    {
        $this->opacity = round((127 - (127 * ($opac / 100))));
        return $this;
    }

    /**
     * Resize the image object to the width parameter passed.
     *
     * @param  int $wid
     * @return Gd
     */
    public function resizeToWidth($wid)
    {
        $scale = $wid / $this->width;
        $hgt = round($this->height * $scale);

        // Create a new image output resource.
        $this->createResource();
        $this->output = imagecreatetruecolor($wid, $hgt);

        // Copy newly sized image to the output resource.
        $this->copyImage($wid, $hgt);

        return $this;
    }

    /**
     * Resize the image object to the height parameter passed.
     *
     * @param  int $hgt
     * @return Gd
     */
    public function resizeToHeight($hgt)
    {
        $scale = $hgt / $this->height;
        $wid = round($this->width * $scale);

        // Create a new image output resource.
        $this->createResource();
        $this->output = imagecreatetruecolor($wid, $hgt);

        // Copy newly sized image to the output resource.
        $this->copyImage($wid, $hgt);

        return $this;
    }

    /**
     * Resize the image object to the largest dimension
     *
     * @param  int $px
     * @return Gd
     */
    public function resize($px)
    {
        // Determine whether or not the image is landscape or portrait and set
        // the scale, new width and new height accordingly, with the largest
        // dimension being scaled to the value of the $px argument.
        $scale = ($this->width > $this->height) ? ($px / $this->width) : ($px / $this->height);

        $wid = round($this->width * $scale);
        $hgt = round($this->height * $scale);

        // Create a new image output resource.
        $this->createResource();
        $this->output = imagecreatetruecolor($wid, $hgt);

        // Copy newly sized image to the output resource.
        $this->copyImage($wid, $hgt);

        return $this;
    }

    /**
     * Scale the image object
     *
     * @param  float|string $scl
     * @return Gd
     */
    public function scale($scl)
    {
        // Determine the new width and height of the image based on the
        // value of the $scl argument.
        $wid = round($this->width * $scl);
        $hgt = round($this->height * $scl);

        // Create a new image output resource.
        $this->createResource();
        $this->output = imagecreatetruecolor($wid, $hgt);

        // Copy newly sized image to the output resource.
        $this->copyImage($wid, $hgt);

        return $this;
    }

    /**
     * Crop the image object
     *
     * @param  int $wid
     * @param  int $hgt
     * @param  int $x
     * @param  int $y
     * @return Gd
     */
    public function crop($wid, $hgt, $x = 0, $y = 0)
    {
        // Create a new image output resource.
        $this->createResource();
        $this->output = imagecreatetruecolor($wid, $hgt);

        // Copy newly sized image to the output resource.
        $this->copyImage($this->width, $this->height, $x, $y);

        return $this;
    }

    /**
     * Crop the image object to a square image
     *
     * @param  int $px
     * @param  int $x
     * @param  int $y
     * @return Gd
     */
    public function cropThumb($px, $x = 0, $y = 0)
    {
        // Determine whether or not the image is landscape or portrait and set
        // the scale, new width and new height accordingly, with the smallest
        // dimension being scaled to the value of the $px argument to allow
        // for a complete crop.
        $scale = ($this->width > $this->height) ? ($px / $this->height) : ($px / $this->width);

        $wid = round($this->width * $scale);
        $hgt = round($this->height * $scale);

        // Create a new image output resource.
        $this->createResource();
        $this->output = imagecreatetruecolor($px, $px);

        // Copy newly sized image to the output resource.
        $this->copyImage($wid, $hgt, $x, $y);

        return $this;
    }

    /**
     * Rotate the image object, using simple degrees, i.e. -90,
     * to rotate the image.
     *
     * @param  int $deg
     * @return Gd
     */
    public function rotate($deg)
    {
        // Create a new image resource and rotate it.
        $this->createResource();
        $color = $this->setColor($this->backgroundColor);
        $this->output = imagerotate($this->resource, $deg, $color);
        $this->resource = $this->output;

        return $this;
    }

    /**
     * Create text within the an image object
     *
     * @param  string     $str
     * @param  int $size
     * @param  int $x
     * @param  int $y
     * @param  string     $font
     * @param  int $rotate
     * @param  boolean    $stroke
     * @return Gd
     */
    public function text($str, $size, $x, $y, $font = null, $rotate = null, $stroke = false)
    {
        // Set the image resource and color.
        $this->createResource();
        $color = $this->setColor($this->fillColor);
        $fontSize = (int)$size;

        // Write the text to the image.
        if ((null !== $font) && function_exists('imagettftext')) {
            if ($stroke) {
                $stroke = $this->setColor($this->strokeColor);
                imagettftext($this->resource, $size, $rotate, $x, ($y - 1), $stroke, $font, $str);
                imagettftext($this->resource, $size, $rotate, $x, ($y + 1), $stroke, $font, $str);
                imagettftext($this->resource, $size, $rotate, ($x - 1), $y, $stroke, $font, $str);
                imagettftext($this->resource, $size, $rotate, ($x + 1), $y, $stroke, $font, $str);
            }
            imagettftext($this->resource, $fontSize, $rotate, $x, $y, $color, $font, $str);
        } else {
            // Cap the system font size between 1 and 5
            if ($fontSize > 5) {
                $fontSize = 5;
            } else if ($fontSize < 1) {
                $fontSize = 1;
            }
            imagestring($this->resource, $fontSize, $x, $y,  $str, $color);
        }

        $this->output = $this->resource;

        return $this;
    }

    /**
     * Method to add a line to the image.
     *
     * @param  int $x1
     * @param  int $y1
     * @param  int $x2
     * @param  int $y2
     * @return Gd
     */
    public function drawLine($x1, $y1, $x2, $y2)
    {
        // Create an image resource and set the stroke color.
        $this->createResource();

        $strokeWidth = (null === $this->strokeWidth) ? 1 : $this->strokeWidth;
        $strokeColor = (null === $this->strokeColor) ? $this->setColor(new Space\Rgb(0, 0, 0)) : $this->setColor($this->strokeColor);

        // Draw the line.
        imagesetthickness($this->resource, $strokeWidth);
        imageline($this->resource, $x1, $y1, $x2, $y2, $strokeColor);
        $this->output = $this->resource;

        return $this;
    }

    /**
     * Method to add a rectangle to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return Gd
     */
    public function drawRectangle($x, $y, $w, $h = null)
    {
        $x2 = $x + $w;
        $y2 = $y + ((null === $h) ? $w : $h);

        // Create an image resource.
        $this->createResource();

        // Set fill color and create rectangle.
        if ((null === $this->fillColor) && (null === $this->backgroundColor)) {
            $fill = $this->setColor(new Space\Rgb(255, 255, 255));
        } else if (null === $this->fillColor) {
            $fill = $this->setColor($this->backgroundColor);
        } else {
            $fill = $this->setColor($this->fillColor);
        }

        imagefilledrectangle($this->resource, $x, $y, $x2, $y2, $fill);

        // Create stroke, if applicable.
        if (null !== $this->strokeColor) {
            $stroke = $this->setColor($this->strokeColor);
            if (null === $this->strokeWidth) {
                $this->strokeWidth = 1;
            }
            imagesetthickness($this->resource, $this->strokeWidth);
            imagerectangle($this->resource, $x, $y, $x2, $y2, $stroke);
        }
        $this->output = $this->resource;

        return $this;
    }

    /**
     * Method to add a square to the image.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @return Gd
     */
    public function drawSquare($x, $y, $w)
    {
        $this->drawRectangle($x, $y, $w, $w);
        return $this;
    }

    /**
     * Method to add an ellipse to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return Gd
     */
    public function drawEllipse($x, $y, $w, $h = null)
    {
        $wid = $w * 2;
        $hgt = ((null === $h) ? $w : $h) * 2;

        // Create an image resource.
        $this->createResource();

        // Create stroke, if applicable.
        if (null !== $this->strokeColor) {
            $stroke = $this->setColor($this->strokeColor);
            if (null === $this->strokeWidth) {
                $this->strokeWidth = 1;
            }
            imagefilledellipse($this->resource, $x, $y, ($wid + $this->strokeWidth), ($hgt + $this->strokeWidth), $stroke);
        }

        // Set fill color and create ellipse.
        if ((null === $this->fillColor) && (null === $this->backgroundColor)) {
            $fill = $this->setColor(new Space\Rgb(255, 255, 255));
        } else if (null === $this->fillColor) {
            $fill = $this->setColor($this->backgroundColor);
        } else {
            $fill = $this->setColor($this->fillColor);
        }

        imagefilledellipse($this->resource, $x, $y, $wid, $hgt, $fill);

        $this->output = $this->resource;

        return $this;
    }

    /**
     * Method to add a circle to the image.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @return Gd
     */
    public function drawCircle($x, $y, $w)
    {
        $this->drawEllipse($x, $y, $w, $w);
        return $this;
    }

    /**
     * Method to add an arc to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $start
     * @param  int $end
     * @param  int $w
     * @param  int $h
     * @return Gd
     */
    public function drawArc($x, $y, $start, $end, $w, $h = null)
    {
        $wid = $w * 2;
        $hgt = ((null === $h) ? $w : $h) * 2;

        // Create an image resource.
        $this->createResource();

        // Set fill color and create rectangle.
        if ((null === $this->fillColor) && (null === $this->backgroundColor)) {
            $fill = $this->setColor(new Space\Rgb(255, 255, 255));
        } else if (null === $this->fillColor) {
            $fill = $this->setColor($this->backgroundColor);
        } else {
            $fill = $this->setColor($this->fillColor);
        }

        imagefilledarc($this->resource, $x, $y, $wid, $hgt, $start, $end, $fill, IMG_ARC_PIE);

        // Create stroke, if applicable.
        if (null !== $this->strokeColor) {
            $x1 = $w * cos($start / 180 * pi());
            $y1 = $h * sin($start / 180 * pi());
            $x2 = $w * cos($end / 180 * pi());
            $y2 = $h * sin($end / 180 * pi());

            $stroke = $this->setColor($this->strokeColor);

            if (null === $this->strokeWidth) {
                $this->strokeWidth = 1;
            }

            imagesetthickness($this->resource, $this->strokeWidth);
            imagearc($this->resource, $x, $y, $wid, $hgt, $start, $end, $stroke);
            imageline($this->resource, $x, $y, $x + $x1, $y + $y1, $stroke);
            imageline($this->resource, $x, $y, $x + $x2, $y + $y2, $stroke);
        }

        $this->output = $this->resource;

        return $this;
    }

    /**
     * Method to add a polygon to the image.
     *
     * @param  array $points
     * @return Gd
     */
    public function drawPolygon($points)
    {
        $realPoints = array();
        foreach ($points as $coord) {
            if (isset($coord['x']) && isset($coord['y'])) {
                $realPoints[] = $coord['x'];
                $realPoints[] = $coord['y'];
            }
        }

        // Create an image resource.
        $this->createResource();

        // Set fill color and create rectangle.
        if ((null === $this->fillColor) && (null === $this->backgroundColor)) {
            $fill = $this->setColor(new Space\Rgb(255, 255, 255));
        } else if (null === $this->fillColor) {
            $fill = $this->setColor($this->backgroundColor);
        } else {
            $fill = $this->setColor($this->fillColor);
        }

        imagefilledpolygon($this->resource, $realPoints, count($points), $fill);

        // Create stroke, if applicable.
        if (null !== $this->strokeColor) {
            $stroke = $this->setColor($this->strokeColor);
            if (null === $this->strokeWidth) {
                $this->strokeWidth = 1;
            }
            imagesetthickness($this->resource, $this->strokeWidth);
            imagepolygon($this->resource, $realPoints, count($points), $stroke);
        }

        $this->output = $this->resource;

        return $this;
    }

    /**
     * Method to adjust the brightness of the image.
     *
     * @param  int $b
     * @return Gd
     */
    public function brightness($b)
    {
        // Create an image resource.
        $this->createResource();
        imagefilter($this->resource, IMG_FILTER_BRIGHTNESS, $b);
        $this->output = $this->resource;

        return $this;
    }

    /**
     * Method to adjust the contrast of the image.
     *
     * @param  int $amount
     * @return Gd
     */
    public function contrast($amount)
    {
        // Create an image resource.
        $this->createResource();
        imagefilter($this->resource, IMG_FILTER_CONTRAST, (0 - $amount));
        $this->output = $this->resource;

        return $this;
    }

    /**
     * Method to desaturate the image.
     *
     * @return Gd
     */
    public function desaturate()
    {
        // Create an image resource.
        $this->createResource();
        imagefilter($this->resource, IMG_FILTER_GRAYSCALE);
        $this->output = $this->resource;

        return $this;
    }

    /**
     * Method to sharpen the image.
     *
     * @param  int $amount
     * @return Gd
     */
    public function sharpen($amount)
    {
        // Create an image resource.
        $this->createResource();
        imagefilter($this->resource, IMG_FILTER_SMOOTH, (0 - $amount));
        $this->output = $this->resource;

        return $this;
    }
    /**
     * Method to blur the image.
     *
     * @param  int $amount
     * @param  int $type
     * @return Gd
     */
    public function blur($amount, $type = Gd::GAUSSIAN_BLUR)
    {
        // Create an image resource.
        $this->createResource();
        $blurType = ($type == self::GAUSSIAN_BLUR) ? IMG_FILTER_GAUSSIAN_BLUR : IMG_FILTER_SELECTIVE_BLUR;

        for ($i = 1; $i <= $amount; $i++) {
            imagefilter($this->resource, $blurType);
        }

        $this->output = $this->resource;

        return $this;
    }

    /**
     * Method to add a border to the image.
     *
     * @param  int $w
     * @param  int $h
     * @param  int $type
     * @return Gd
     */
    public function border($w, $h = null, $type = Gd::INNER_BORDER)
    {
        $h = (null === $h) ? $w : $h;

        $this->fillColor = $this->strokeColor;
        $this->setOpacity(100);

        if ($type == self::INNER_BORDER) {
            $this->drawRectangle(0, 0, $this->width, $h);
            $this->drawRectangle(0, ($this->height - $h), $this->width, $this->height);
            $this->drawRectangle(0, 0, $w, $this->height);
            $this->drawRectangle(($this->width - $w), 0, $this->width, $this->height);
        } else {
            $newWidth = $this->width + ($w * 2);
            $newHeight = $this->height + ($h * 2);
            $this->createResource();
            $oldResource = $this->resource;
            $this->resource = imagecreatetruecolor($newWidth, $newHeight);
            $color = $this->setColor($this->fillColor);
            imagefill($this->resource, 0, 0, $color);
            imagealphablending($this->resource, true);
            imagecopy($this->resource, $oldResource, $w, $h, 0, 0, imagesx($oldResource), imagesy($oldResource));
            $this->output = $this->resource;
        }

        return $this;
    }

    /**
     * Overlay an image onto the current image.
     *
     * @param  string     $ovr
     * @param  int $x
     * @param  int $y
     * @throws Exception
     * @return Gd
     */
    public function overlay($ovr, $x = 0, $y = 0)
    {
        // Create image resource and turn on alpha blending
        $this->createResource();
        imagealphablending($this->resource, true);

        // Create an image resource from the overlay image.
        if (stripos($ovr, '.gif') !== false) {
            $overlay = imagecreatefromgif($ovr);
        } else if (stripos($ovr, '.png') !== false) {
            $overlay = imagecreatefrompng($ovr);
        } else if (stripos($ovr, '.jp') !== false) {
            $overlay = imagecreatefromjpeg($ovr);
        } else {
            throw new Exception('Error: The overlay image must be either a JPG, GIF or PNG.');
        }

        // Copy the overlay image on top of the main image resource.
        imagecopy($this->resource, $overlay, $x, $y, 0, 0, imagesx($overlay), imagesy($overlay));

        $this->output = $this->resource;

        return $this;
    }

    /**
     * Method to colorize the image with the color passed.
     *
     * @param  Space\ColorInterface $color
     * @return Gd
     */
    public function colorize(Space\ColorInterface $color)
    {
        // Create an image resource.
        $this->createResource();
        imagefilter($this->resource, IMG_FILTER_COLORIZE, $color->getRed(), $color->getGreen(), $color->getBlue());
        $this->output = $this->resource;

        return $this;
    }

    /**
     * Method to invert the image (create a negative.)
     *
     * @return Gd
     */
    public function invert()
    {
        // Create an image resource.
        $this->createResource();
        imagefilter($this->resource, IMG_FILTER_NEGATE);
        $this->output = $this->resource;

        return $this;
    }

    /**
     * Method to flip the image over the x-axis.
     *
     * @return Gd
     */
    public function flip()
    {
        // Create a new image output resource.
        $this->createResource();
        $this->output = imagecreatetruecolor($this->width, $this->height);

        // Calculate the new dimensions
        $curWidth = $this->width;
        $curHeight = $this->height;
        $srcX = 0;
        $srcY = $this->height - 1; // Compensate for a 1-pixel space on the flipped image
        $this->height = 0 - $this->height;

        // Copy newly sized image to the output resource.
        $this->copyImage($curWidth, $curHeight, $srcX , $srcY);
        $this->height = abs($this->height);

        return $this;
    }

    /**
     * Method to flip the image over the y-axis.
     *
     * @return Gd
     */
    public function flop()
    {
        // Create a new image output resource.
        $this->createResource();
        $this->output = imagecreatetruecolor($this->width, $this->height);

        // Calculate the new dimensions
        $curWidth = $this->width;
        $curHeight = $this->height;
        $srcX = $this->width - 1; // Compensate for a 1-pixel space on the flipped image
        $srcY = 0;
        $this->width = 0 - $this->width;

        // Copy newly sized image to the output resource.
        $this->copyImage($curWidth, $curHeight, $srcX , $srcY);
        $this->width = abs($this->width);

        return $this;
    }

    /**
     * Apply a mosiac pixelate effect to the image
     *
     * @param  int $px
     * @return Gd
     */
    public function pixelate($px)
    {
        // Create an image resource.
        $this->createResource();
        imagefilter($this->resource, IMG_FILTER_PIXELATE, $px, true);
        $this->output = $this->resource;

        return $this;
    }

    /**
     * Apply a pencil/sketch effect to the image
     *
     * @return Gd
     */
    public function pencil()
    {
        // Create an image resource.
        $this->createResource();
        imagefilter($this->resource, IMG_FILTER_MEAN_REMOVAL);
        $this->output = $this->resource;

        return $this;
    }

    /**
     * Return the number of colors in the palette of indexed images.
     * Returns 0 for true color images.
     *
     * @return int
     */
    public function colorTotal()
    {
        // Set the image resource and get the total number of colors.
        $this->createResource();
        $colors = imagecolorstotal($this->resource);

        // Destroy the image resource.
        $this->destroy();

        return $colors;
    }

    /**
     * Return all of the colors in the palette in an array format
     *
     * @param  int|string $format
     * @return array
     */
    public function getColors($format = Gd::HEX)
    {
        // Initialize the colors array and the image resource.
        $colors = array();
        $this->createResource();

        // Loop through each pixel of the image, recording the color result
        // in the color array.
        for ($h = 0; $h < $this->height; $h++) {
            for ($w = 0; $w < $this->width; $w++) {
                // Get the color index at the pixel location, translating
                // into human readable form.
                $color_index = imagecolorat($this->resource, $w, $h);
                $color_trans = imagecolorsforindex($this->resource, $color_index);

                // Convert to the proper HEX or RGB format.
                if ($format) {
                    $rgb = sprintf('%02s', dechex($color_trans['red'])) . sprintf('%02s', dechex($color_trans['green'])) . sprintf('%02s', dechex($color_trans['blue']));
                } else {
                    $rgb = $color_trans['red'] . "," . $color_trans['green'] . "," . $color_trans['blue'];
                }

                // If the color is not already in the array, add to it.
                if (!in_array($rgb, $colors)) {
                    $colors[] = $rgb;
                }
            }
        }

        // Destroy the image resource.
        $this->destroy();

        // Return the colors array.
        return $colors;
    }

    /**
     * Convert the image object to the new specified image type.
     *
     * @param  string $type
     * @throws Exception
     * @return Gd
     */
    public function convert($type)
    {
        $type = strtolower($type);

        // Check if the requested image type is supported.
        if (!array_key_exists($type, $this->allowed)) {
            throw new Exception('Error: That image type is not supported. Only GIF, JPG and PNG image types are supported.');
        // Check if the image is already the requested image type.
        } else if (strtolower($this->extension) == $type) {
            throw new Exception('Error: This image file is already a ' . strtoupper($type) . ' image file.');
        }

        // Else, save the image as the new type.
        // Open a new image, maintaining the GIF image's palette and
        // transparency where applicable.
        if ($this->mime == 'image/gif') {
            $this->createResource();
            imageinterlace($this->resource, 0);

            // Change the type of the image object to the new,
            // requested image type.
            $this->extension = $type;
            $this->mime      = $this->allowed[$this->extension];

            // Redefine the image object properties with the new values.
            $this->fullpath = $this->dir . $this->filename . '.' . $this->extension;
            $this->basename = basename($this->fullpath);
        // Else, open a new true color image.
        } else {
            if ($type == 'gif') {
                $this->createResource();

                // Change the type of the image object to the new,
                // requested image type.
                $this->extension = $type;
                $this->mime = $this->allowed[$this->extension];

                // Redefine the image object properties with the new values.
                $this->fullpath = $this->dir . $this->filename . '.' . $this->extension;
                $this->basename = basename($this->fullpath);
            } else {
                $new = imagecreatetruecolor($this->width, $this->height);

                // Create a new, blank image file and copy the image over.
                $this->createResource();

                // Change the type of the image object to the new,
                // requested image type.
                $this->extension = $type;
                $this->mime      = $this->allowed[$this->extension];

                // Redefine the image object properties with the new values.
                $this->fullpath = $this->dir . $this->filename . '.' . $this->extension;
                $this->basename = basename($this->fullpath);

                // Create and save the image in it's new, proper format.
                imagecopyresampled($new, $this->resource, 0, 0, 0, 0, $this->width, $this->height, $this->width, $this->height);
            }
        }

        return $this;
    }

    /**
     * Output the image object directly.
     *
     * @param  boolean $download
     * @return Gd
     */
    public function output($download = false)
    {
        // Determine if the force download argument has been passed.
        $attach = ($download) ? 'attachment; ' : null;
        $headers = array(
            'Content-type'        => $this->mime,
            'Content-disposition' => $attach . 'filename=' . $this->basename
        );

        if ($_SERVER['SERVER_PORT'] == 443) {
            $headers['Expires']       = 0;
            $headers['Cache-Control'] = 'private, must-revalidate';
            $headers['Pragma']        = 'cache';
        }

        if (null === $this->resource) {
            $this->createResource();
        }

        if (null === $this->output) {
            $this->output = $this->resource;
        }

        // Send the headers and output the image
        header('HTTP/1.1 200 OK');
        foreach ($headers as $name => $value) {
            header($name . ": " . $value);
        }

        $this->createImage($this->output, null, $this->quality);

        return $this;
    }

    /**
     * Save the image object to disk.
     *
     * @param  string  $to
     * @return Gd
     */
    public function save($to = null)
    {
        if (null === $this->resource) {
            $this->createResource();
        }

        if (null === $this->output) {
            $this->output = $this->resource;
        }

        $this->createImage($this->output, ((null === $to) ? $this->fullpath : $to), $this->quality);
        clearstatcache();

        $this->setImage((null === $to) ? $this->fullpath : $to);

        $imgSize = getimagesize($this->fullpath);

        // Set image object properties.
        $this->width    = $imgSize[0];
        $this->height   = $imgSize[1];
        $this->channels = (isset($imgSize['channels'])) ? $imgSize['channels'] : null;

        return $this;
    }

    /**
     * Destroy the image object and the related image file directly.
     *
     * @param  boolean $delete
     * @return void
     */
    public function destroy($delete = false)
    {
        // Destroy the image resource.
        if (null !== $this->resource) {
            if (!is_string($this->resource) && is_resource($this->resource)) {
                imagedestroy($this->resource);
            }
            $this->resource = null;
        }
        // Destroy the image output resource.
        if (null !== $this->output) {
            if (!is_string($this->output) && is_resource($this->output)) {
                imagedestroy($this->output);
            }
            $this->output = null;
        }

        // Clear PHP's file status cache.
        clearstatcache();

        // If the $delete flag is passed, delete the image file.
        if (($delete) && file_exists($this->fullpath)) {
            unlink($this->fullpath);
        }
    }

    /**
     * Get the array of supported formats with GD.
     *
     * @return array
     */
    public function getFormats()
    {
        return array_keys($this->allowed);
    }

    /**
     * Get the number of supported formats with GD.
     *
     * @return int
     */
    public function getNumberOfFormats()
    {
        return count($this->allowed);
    }

    /**
     * To string method to output the image
     *
     * @return string
     */
    public function __toString()
    {
        $this->output();
        return '';
    }

    /**
     * Get GD Info.
     *
     * @return void
     */
    protected function getInfo()
    {
        $gd = gd_info();
        $gdInfo = array(
            'version'             => $gd['GD Version'],
            'freeTypeSupport'     => $gd['FreeType Support'],
            'freeTypeLinkage'     => $gd['FreeType Linkage'],
            't1LibSupport'        => $gd['T1Lib Support'],
            'gifReadSupport'      => $gd['GIF Read Support'],
            'gifCreateSupport'    => $gd['GIF Create Support'],
            'jpegSupport'         => $gd['JPEG Support'],
            'pngSupport'          => $gd['PNG Support'],
            'wbmpSupport'         => $gd['WBMP Support'],
            'xpmSupport'          => $gd['XPM Support'],
            'xbmSupport'          => $gd['XBM Support'],
            'japaneseFontSupport' => $gd['JIS-mapped Japanese Font Support']
        );

        $this->info = new \ArrayObject($gdInfo, \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Set and return a color identifier.
     *
     * @param  Space\ColorInterface $color
     * @throws Exception
     * @return mixed
     */
    protected function setColor(Space\ColorInterface $color = null)
    {
        if (null === $this->resource) {
            throw new Exception('Error: The image resource has not been created.');
        }

        $opac = (null === $this->opacity) ? 0 : $this->opacity;
        if (null !== $color) {
            $color = imagecolorallocatealpha($this->resource, (int)$color->getRed(), (int)$color->getGreen(), (int)$color->getBlue(), $opac);
        } else {
            $color = imagecolorallocatealpha($this->resource, 0, 0, 0, $opac);
        }

        return $color;
    }

    /**
     * Create a new image resource based on the current image type
     * of the image object.
     *
     * @return void
     */
    protected function createResource()
    {
        if (null !== $this->output) {
            $this->resource = (is_string($this->output)) ? imagecreatefromstring($this->output) : $this->output;
        } else if (file_exists($this->fullpath)) {
            switch ($this->mime) {
                case 'image/gif':
                    $this->resource = imagecreatefromgif($this->fullpath);
                    break;
                case 'image/png':
                    $this->resource = imagecreatefrompng($this->fullpath);
                    break;
                case 'image/jpeg':
                    $this->resource = imagecreatefromjpeg($this->fullpath);
                    break;
            }
        }
    }

    /**
     * Create and save the new image file in the correct format.
     *
     * @param  string $new
     * @param  string $img
     * @param  int $q
     * @return void
     */
    protected function createImage($new, $img = null, $q = null)
    {
        if (is_string($new)) {
            $new = imagecreatefromstring($new);
        }

        switch ($this->mime) {
            case 'image/gif':
                imagegif($new, $img);
                break;
            case 'image/png':
                if (null !== $q) {
                    imagepng($new, $img, $q);
                } else {
                    imagepng($new, $img);
                }
                break;
            case 'image/jpeg':
                imagejpeg($new, $img, $q);
                break;
        }
    }

    /**
     * Copy the image resource to the image output resource with the set parameters.
     *
     * @param  int $w
     * @param  int $h
     * @param  int $x
     * @param  int $y
     * @return void
     */
    protected function copyImage($w, $h, $x = 0, $y = 0)
    {
        imagecopyresampled($this->output, $this->resource, 0, 0, $x, $y, $w, $h, $this->width, $this->height);
        $this->width  = imagesx($this->output);
        $this->height = imagesy($this->output);
    }

}
