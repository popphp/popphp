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
 * Imagick image class
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Imagick extends AbstractImage
{

    /**
     * Constant for motion blur
     * @var int
     */
    const MOTION_BLUR = 5;

    /**
     * Constant for radial blur
     * @var int
     */
    const RADIAL_BLUR = 6;

    /**
     * Array of allowed file types.
     * @var array
     */
    protected $allowed = array(
        'afm'   => 'application/x-font-afm',
        'ai'    => 'application/postscript',
        'avi'   => 'video/x-msvideo',
        'bmp'   => 'image/x-ms-bmp',
        'eps'   => 'application/octet-stream',
        'gif'   => 'image/gif',
        'html'  => 'text/html',
        'htm'   => 'text/html',
        'ico'   => 'image/ico',
        'jpe'   => 'image/jpeg',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'mov'   => 'video/quicktime',
        'mp4'   => 'video/mp4',
        'mpg'   => 'video/mpeg',
        'mpeg'  => 'video/mpeg',
        'otf'   => 'application/x-font-otf',
        'pdf'   => 'application/pdf',
        'pfb'   => 'application/x-font-pfb',
        'pfm'   => 'application/x-font-pfm',
        'png'   => 'image/png',
        'ps'    => 'application/postscript',
        'psb'   => 'image/x-photoshop',
        'psd'   => 'image/x-photoshop',
        'shtml' => 'text/html',
        'shtm'  => 'text/html',
        'svg'   => 'image/svg+xml',
        'tif'   => 'image/tiff',
        'tiff'  => 'image/tiff',
        'tsv'   => 'text/tsv',
        'ttf'   => 'application/x-font-ttf',
        'txt'   => 'text/plain',
        'xhtml' => 'application/xhtml+xml',
        'xml'   => 'application/xml'
    );

    /**
     * Image color opacity
     * @var float
     */
    protected $opacity = 1.0;

    /**
     * Image compression
     * @var int|string
     */
    protected $compression = null;

    /**
     * Image filter
     * @var int
     */
    protected $filter = \Imagick::FILTER_LANCZOS;

    /**
     * Image blur
     * @var int
     */
    protected $blur = 1;

    /**
     * Image overlay
     * @var int
     */
    protected $overlay = \Imagick::COMPOSITE_ATOP;

    /**
     * Constructor
     *
     * Instantiate an Imagick file object based on either a pre-existing image
     * file on disk, or a new image file.
     *
     * As of July 28th, 2011, stable testing was successful with the
     * following versions of the required software:
     *
     * ImageMagick 6.5.*
     * Ghostscript 8.70 or 8.71
     * Imagick PHP Extension 3.0.1
     *
     * Any variation in the versions of the required software may contribute to
     * the Pop\Image\Imagick component not functioning properly.
     *
     * @param  string $img
     * @param  int    $w
     * @param  int    $h
     * @param  array  $types
     * @throws Exception
     * @return Imagick
     */
    public function __construct($img, $w = null, $h = null, $types = null)
    {
        // If image passed is a paged image, like a PDF
        if (!file_exists($img) && (strpos($img, '[') !== false)) {
            $imgFile = trim(substr($img, 0, strpos($img, '[')));
            $imgFile .= substr($img, (strpos($img, ']') + 1));

            $page        = substr($img, strpos($img, '['));
            $page        = substr($page, 0, (strpos($page, ']') + 1));
            $img         = $imgFile;
            $imagickFile = (file_exists($imgFile)) ? realpath($imgFile) . $page : $img;
        // Else, continue
        } else {
            $imgFile     = $img;
            $imagickFile = realpath($img);
        }

        parent::__construct($img, $w, $h, $types);

        // Check to see if Imagick is installed.
        if (!self::isInstalled()) {
            throw new Exception('Error: The Imagick library extension must be installed to use the Imagick adapter.');
        }

        // If image exists, get image info and store in an array.
        if (file_exists($this->fullpath) && ($this->size > 0)) {
            $this->resource = new \Imagick($imagickFile);
            $this->setImageInfo();
            $this->setQuality(100);
        // If image does not exists, check to make sure the width and height
        // properties of the new image have been passed.
        } else {
            $this->resource = new \Imagick();

            if ((null === $w) || (null === $h)) {
                throw new Exception('Error: You must define a width and height for a new image object.');
            }

            // Set image object properties.
            $this->width = $w;
            $this->height = $h;
            $this->channels = null;

            // Create a new image and allocate the background color.
            $this->resource->newImage($w, $h, $this->setColor([255, 255, 255]), $this->extension);

            // Set the quality and create a new, blank image file.
            $this->setQuality(100);
        }

        $this->getInfo();
    }

    /**
     * Check if Imagick is installed.
     *
     * @return boolean
     */
    public static function isInstalled()
    {
        return class_exists('Imagick', false);
    }

    /**
     * Get the image resource to directly interact with it
     *
     * @return \Imagick
     */
    public function resource()
    {
        return $this->resource;
    }

    /**
     * Set the background color.
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return Imagick
     */
    public function setBackgroundColor($r = 0, $g = 0, $b = 0)
    {
        parent::setBackgroundColor($r, $g, $b);
        $this->resource->newImage($this->width, $this->height, $this->setColor($this->backgroundColor), $this->extension);
        return $this;
    }

    /**
     * Set the image quality.
     *
     * @param  int $q
     * @return Imagick
     */
    public function setQuality($q = null)
    {
        $this->quality = (null !== $q) ? (int)$q : null;
        return $this;
    }

    /**
     * Set the image compression quality with an
     * Imagick compression constant
     *
     * @param  int $comp
     * @return Imagick
     */
    public function setCompression($comp = null)
    {
        $this->compression = (null !== $comp) ? (int)$comp : null;
        return $this;
    }

    /**
     * Set the opacity.
     *
     * @param  float $opac
     * @return Imagick
     */
    public function setOpacity($opac)
    {
        $this->opacity = $opac;
        return $this;
    }

    /**
     * Set the image filter.
     *
     * @param  int|string $filter
     * @return Imagick
     */
    public function setFilter($filter = null)
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * Set the image blur.
     *
     * @param  int|string $blur
     * @return Imagick
     */
    public function setBlur($blur = null)
    {
        $this->blur = $blur;
        return $this;
    }

    /**
     * Set the image overlay.
     *
     * @param  int|string $ovr
     * @return Imagick
     */
    public function setOverlay($ovr = null)
    {
        $this->overlay = $ovr;
        return $this;
    }

    /**
     * Resize the image object to the width parameter passed.
     *
     * @param  int|string $wid
     * @return Imagick
     */
    public function resizeToWidth($wid)
    {
        $this->setImageInfo();

        $scale = $wid / $this->width;
        $hgt = round($this->height * $scale);

        // Create a new image output resource.
        $this->resource->resizeImage($wid, $hgt, $this->filter, $this->blur);

        $this->setImageInfo();

        return $this;
    }

    /**
     * Resize the image object to the height parameter passed.
     *
     * @param  int|string $hgt
     * @return Imagick
     */
    public function resizeToHeight($hgt)
    {
        $this->setImageInfo();

        $scale = $hgt / $this->height;
        $wid = round($this->width * $scale);

        // Create a new image output resource.
        $this->resource->resizeImage($wid, $hgt, $this->filter, $this->blur);

        $this->setImageInfo();

        return $this;
    }

    /**
     * Resize the image object to the largest dimension
     *
     * @param  int|string $px
     * @return Imagick
     */
    public function resize($px)
    {
        // Determine whether or not the image is landscape or portrait and set
        // the scale, new width and new height accordingly, with the largest
        // dimension being scaled to the value of the $px argument.
        $this->setImageInfo();
        $scale = ($this->width > $this->height) ? ($px / $this->width) : ($px / $this->height);

        $wid = round($this->width * $scale);
        $hgt = round($this->height * $scale);

        // Create a new image output resource.
        $this->resource->resizeImage($wid, $hgt, $this->filter, $this->blur);

        $this->setImageInfo();

        return $this;
    }

    /**
     * Scale the image object
     *
     * @param  float|string $scl
     * @return Imagick
     */
    public function scale($scl)
    {
        // Determine the new width and height of the image based on the
        // value of the $scl argument.
        $this->setImageInfo();
        $wid = round($this->width * $scl);
        $hgt = round($this->height * $scl);

        // Create a new image output resource.
        $this->resource->resizeImage($wid, $hgt, $this->filter, $this->blur);

        $this->setImageInfo();

        return $this;
    }

    /**
     * Crop the image object to a image
     *
     * @param  int|string $wid
     * @param  int|string $hgt
     * @param  int|string $x
     * @param  int|string $y
     * @return mixed
     */
    public function crop($wid, $hgt, $x = 0, $y = 0)
    {
        // Create a new image output resource.
        $this->resource->cropImage($wid, $hgt, $x, $y);
        $this->setImageInfo();

        return $this;
    }

    /**
     * Crop the image object to a square image
     *
     * @param  int|string $px
     * @param  int|string $x
     * @param  int|string $y
     * @return Imagick
     */
    public function cropThumb($px, $x = 0, $y = 0)
    {
        // Determine whether or not the image is landscape or portrait and set
        // the scale, new width and new height accordingly, with the smallest
        // dimension being scaled to the value of the $px argument to allow
        // for a complete crop.
        $this->setImageInfo();
        $scale = ($this->width > $this->height) ? ($px / $this->height) : ($px / $this->width);

        $wid = round($this->width * $scale);
        $hgt = round($this->height * $scale);

        // Create a new image output resource.
        $this->resource->resizeImage($wid, $hgt, $this->filter, $this->blur);
        $this->resource->cropImage($px, $px, $x, $y);

        $this->setImageInfo();

        return $this;
    }

    /**
     * Rotate the image object, using simple degrees, i.e. -90,
     * to rotate the image.
     *
     * @param  int|string $deg
     * @return Imagick
     */
    public function rotate($deg)
    {
        // Create a new image resource and rotate it.
        $color = $this->setColor($this->backgroundColor);
        $this->resource->rotateImage($color, $deg);

        $this->setImageInfo();

        return $this;
    }

    /**
     * Method to get the fonts recognized by Imagick
     *
     * @return array
     */
    public function getFonts()
    {
        return $this->resource->queryFonts();
    }

    /**
     * Create text within the an image object
     *
     * @param  string     $str
     * @param  int|string $size
     * @param  int|string $x
     * @param  int|string $y
     * @param  string     $font
     * @param  int|string $rotate
     * @param  boolean    $stroke
     * @throws Exception
     * @return Imagick
     */
    public function text($str, $size, $x, $y, $font = null, $rotate = null, $stroke = false)
    {
        $draw = new \ImagickDraw();

        // Set the font if passed
        if (null !== $font) {
            if (!$draw->setFont($font)) {
                throw new Exception('Error: That font is not recognized by the Imagick extension.');
            }
        // Else, attempt to set a basic, default system font
        } else {
            $fonts = $this->resource->queryFonts();
            if (in_array('Arial', $fonts)) {
                $font = 'Arial';
            } else if (in_array('Helvetica', $fonts)) {
                $font = 'Helvetica';
            } else if (in_array('Tahoma', $fonts)) {
                $font = 'Tahoma';
            } else if (in_array('Verdana', $fonts)) {
                $font = 'Verdana';
            } else if (in_array('System', $fonts)) {
                $font = 'System';
            } else if (in_array('Fixed', $fonts)) {
                $font = 'Fixed';
            } else if (in_array('system', $fonts)) {
                $font = 'system';
            } else if (in_array('fixed', $fonts)) {
                $font = 'fixed';
            } else if (isset($fonts[0])) {
                $font = $fonts[0];
            } else {
                throw new Exception('Error: No default font could be found by the Imagick extension.');
            }
        }

        $draw->setFont($font);
        $draw->setFontSize($size);
        $draw->setFillColor($this->setColor($this->fillColor));

        if (null !== $rotate) {
            $draw->rotate($rotate);
        }

        if ($stroke) {
            $draw->setStrokeColor($this->setColor($this->strokeColor));
            $draw->setStrokeWidth((null === $this->strokeWidth) ? 1 : $this->strokeWidth);
        }

        $draw->annotation($x, $y, $str);
        $this->resource->drawImage($draw);

        return $this;
    }

    /**
     * Method to add a line to the image.
     *
     * @param  int $x1
     * @param  int $y1
     * @param  int $x2
     * @param  int $y2
     * @return Imagick
     */
    public function drawLine($x1, $y1, $x2, $y2)
    {
        $draw = new \ImagickDraw();
        $draw->setStrokeColor($this->setColor($this->strokeColor));
        $draw->setStrokeWidth((null === $this->strokeWidth) ? 1 : $this->strokeWidth);
        $draw->line($x1, $y1, $x2, $y2);
        $this->resource->drawImage($draw);

        return $this;
    }

    /**
     * Method to add a rectangle to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return Imagick
     */
    public function drawRectangle($x, $y, $w, $h = null)
    {
        $x2 = $x + $w;
        $y2 = $y + ((null === $h) ? $w : $h);

        $draw = new \ImagickDraw();
        $draw->setFillColor($this->setColor($this->fillColor));

        if (null !== $this->strokeWidth) {
            $draw->setStrokeColor($this->setColor($this->strokeColor));
            $draw->setStrokeWidth((null === $this->strokeWidth) ? 1 : $this->strokeWidth);
        }

        $draw->rectangle($x, $y, $x2, $y2);
        $this->resource->drawImage($draw);

        return $this;
    }

    /**
     * Method to add a square to the image.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @return Imagick
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
     * @return Imagick
     */
    public function drawEllipse($x, $y, $w, $h = null)
    {
        $wid = $w;
        $hgt = (null === $h) ? $w : $h;

        $draw = new \ImagickDraw();
        $draw->setFillColor($this->setColor($this->fillColor));

        if (null !== $this->strokeWidth) {
            $draw->setStrokeColor($this->setColor($this->strokeColor));
            $draw->setStrokeWidth((null === $this->strokeWidth) ? 1 : $this->strokeWidth);
        }

        $draw->ellipse($x, $y, $wid, $hgt, 0, 360);
        $this->resource->drawImage($draw);

        return $this;
    }

    /**
     * Method to add a circle to the image.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @return Imagick
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
     * @return Imagick
     */
    public function drawArc($x, $y, $start, $end, $w, $h = null)
    {
        $wid = $w;
        $hgt = (null === $h) ? $w : $h;

        $draw = new \ImagickDraw();
        $draw->setFillColor($this->setColor($this->fillColor));

        $x1 = $w * cos($start / 180 * pi());
        $y1 = $h * sin($start / 180 * pi());
        $x2 = $w * cos($end / 180 * pi());
        $y2 = $h * sin($end / 180 * pi());

        $points = array(
                      array('x' => $x, 'y' => $y),
                      array('x' => $x + $x1, 'y' => $y + $y1),
                      array('x' => $x + $x2, 'y' => $y + $y2)
                  );

        $draw->polygon($points);

        $draw->ellipse($x, $y, $wid, $hgt, $start, $end);
        $this->resource->drawImage($draw);

        if (null !== $this->strokeWidth) {
            $draw = new \ImagickDraw();

            $draw->setFillColor($this->setColor($this->fillColor));
            $draw->setStrokeColor($this->setColor($this->strokeColor));
            $draw->setStrokeWidth((null === $this->strokeWidth) ? 1 : $this->strokeWidth);

            $draw->ellipse($x, $y, $wid, $hgt, $start, $end);
            $draw->line($x, $y, $x + $x1, $y + $y1);
            $draw->line($x, $y, $x + $x2, $y + $y2);

            $this->resource->drawImage($draw);
        }

        return $this;
    }

    /**
     * Method to add a polygon to the image.
     *
     * @param  array $points
     * @return Imagick
     */
    public function drawPolygon($points)
    {
        $draw = new \ImagickDraw();
        $draw->setFillColor($this->setColor($this->fillColor));

        if (null !== $this->strokeWidth) {
            $draw->setStrokeColor($this->setColor($this->strokeColor));
            $draw->setStrokeWidth((null === $this->strokeWidth) ? 1 : $this->strokeWidth);
        }

        $draw->polygon($points);
        $this->resource->drawImage($draw);

        return $this;
    }

    /**
     * Method to adjust the hue of the image.
     *
     * @param  int $h
     * @return Imagick
     */
    public function hue($h)
    {
        $this->resource->modulateImage(100, 100, $h);
        return $this;
    }

    /**
     * Method to adjust the saturation of the image.
     *
     * @param  int $s
     * @return Imagick
     */
    public function saturation($s)
    {
        $this->resource->modulateImage(100, $s, 100);
        return $this;
    }

    /**
     * Method to adjust the brightness of the image.
     *
     * @param  int $b
     * @return Imagick
     */
    public function brightness($b)
    {
        $this->resource->modulateImage($b, 100, 100);
        return $this;
    }

    /**
     * Method to adjust the HSB of the image altogether.
     *
     * @param  int $h
     * @param  int $s
     * @param  int $b
     * @return Imagick
     */
    public function hsb($h, $s, $b)
    {
        $this->resource->modulateImage($h, $s, $b);
        return $this;
    }

    /**
     * Method to adjust the levels of the image using a 0 - 255 range.
     *
     * @param  int   $black
     * @param  float $gamma
     * @param  int   $white
     * @return Imagick
     */
    public function level($black, $gamma, $white)
    {
        $quantumRange = $this->resource->getQuantumRange();

        if ($black < 0) {
            $black = 0;
        }
        if ($white > 255) {
            $white = 255;
        }

        $blackPoint = ($black / 255) * $quantumRange['quantumRangeLong'];
        $whitePoint = ($white / 255) * $quantumRange['quantumRangeLong'];

        $this->resource->levelImage($blackPoint, $gamma, $whitePoint);

        return $this;
    }

    /**
     * Method to adjust the contrast of the image.
     *
     * @param  int $amount
     * @return Imagick
     */
    public function contrast($amount)
    {
        if ($amount > 0) {
            for ($i = 1; $i <= $amount; $i++) {
                $this->resource->contrastImage(1);
            }
        } else if ($amount < 0) {
            for ($i = -1; $i >= $amount; $i--) {
                $this->resource->contrastImage(0);
            }
        }

        return $this;
    }

    /**
     * Method to desaturate of the image.
     *
     * @return Imagick
     */
    public function desaturate()
    {
        $this->resource->modulateImage(100, 0, 100);
        return $this;
    }

    /**
     * Method to sharpen the image.
     *
     * @param  int $radius
     * @param  int $sigma
     * @return Imagick
     */
    public function sharpen($radius = 0, $sigma = 0)
    {
        $this->resource->sharpenImage($radius, $sigma);
        return $this;
    }

    /**
     * Method to blur the image.
     *
     * @param  int $radius
     * @param  int $sigma
     * @param  int $angle
     * @param  int $type
     * @return Imagick
     */
    public function blur($radius = 0, $sigma = 0, $angle = 0, $type = Imagick::BLUR)
    {
        switch ($type) {
            case self::BLUR:
                $this->resource->blurImage($radius, $sigma);
                break;
            case self::GAUSSIAN_BLUR:
                $this->resource->gaussianBlurImage($radius, $sigma);
                break;
            case self::MOTION_BLUR:
                $this->resource->motionBlurImage($radius, $sigma, $angle);
                break;
            case self::RADIAL_BLUR:
                $this->resource->radialBlurImage($angle);
                break;
        }

        return $this;
    }

    /**
     * Method to add a border to the image.
     *
     * @param  int $w
     * @param  int $h
     * @param  int $type
     * @return Imagick
     */
    public function border($w, $h = null, $type = Imagick::INNER_BORDER)
    {
        $h = (null === $h) ? $w : $h;

        if ($type == self::INNER_BORDER) {
            $this->setStrokeWidth(($h * 2));
            $this->drawLine(0, 0, $this->width, 0);
            $this->drawLine(0, $this->height, $this->width, $this->height);
            $this->setStrokeWidth(($w * 2));
            $this->drawLine(0, 0, 0, $this->height);
            $this->drawLine($this->width, 0, $this->width, $this->height);
        } else {
            $this->resource->borderImage($this->setColor($this->strokeColor), $w, $h);
        }

        return $this;
    }

    /**
     * Overlay an image onto the current image.
     *
     * @param  string     $ovr
     * @param  int|string $x
     * @param  int|string $y
     * @return Imagick
     */
    public function overlay($ovr, $x = 0, $y = 0)
    {
        $overlayImage = new \Imagick($ovr);
        if ($this->opacity < 1) {
            $overlayImage->setImageOpacity($this->opacity);
        }

        $this->resource->compositeImage($overlayImage, $this->overlay, $x, $y);
        return $this;
    }

    /**
     * Method to colorize the image with the color passed.
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return Imagick
     */
    public function colorize($r = 0, $g = 0, $b = 0)
    {
        $this->resource->colorizeImage('rgb(' . (int)$r . ',' . (int)$g . ',' . (int)$b . ',' . ')', $this->opacity);
        return $this;
    }

    /**
     * Method to invert the image (create a negative.)
     *
     * @return Imagick
     */
    public function invert()
    {
        $this->resource->negateImage(false);
        return $this;
    }

    /**
     * Method to flip the image over the x-axis.
     *
     * @return Imagick
     */
    public function flip()
    {
        $this->resource->flipImage();
        return $this;
    }

    /**
     * Method to flip the image over the y-axis.
     *
     * @return Imagick
     */
    public function flop()
    {
        $this->resource->flopImage();
        return $this;
    }

    /**
     * Flatten the image layers
     *
     * @return Imagick
     */
    public function flatten()
    {
        $this->resource = $this->resource->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        return $this;
    }

    /**
     * Apply an oil paint effect to the image using the pixel radius threshold
     *
     * @param  int $radius
     * @return Imagick
     */
    public function paint($radius)
    {
        $this->resource->oilPaintImage($radius);
        return $this;
    }

    /**
     * Apply a posterize effect to the image
     *
     * @param  int     $levels
     * @param  boolean $dither
     * @return Imagick
     */
    public function posterize($levels, $dither = false)
    {
        $this->resource->posterizeImage($levels, $dither);
        return $this;
    }

    /**
     * Apply a noise effect to the image
     *
     * @param  int $type
     * @return Imagick
     */
    public function noise($type = \Imagick::NOISE_MULTIPLICATIVEGAUSSIAN)
    {
        $this->resource->addNoiseImage($type);
        return $this;
    }

    /**
     * Apply a diffusion effect to the image
     *
     * @param  int $radius
     * @return Imagick
     */
    public function diffuse($radius)
    {
        $this->resource->spreadImage($radius);
        return $this;
    }

    /**
     * Apply a skew effect to the image
     *
     * @param  array $color
     * @param  int   $x
     * @param  int   $y
     * @return Imagick
     */
    public function skew(array $color, $x, $y)
    {
        if (count($color) == 3) {
            $r = (int)$color[0];
            $g = (int)$color[1];
            $b = (int)$color[2];
        } else {
            $r = 0;
            $g = 0;
            $b = 0;
        }

        $this->resource->shearImage('rgb(' . $r . ',' . $g . ',' . $b . ')', $x, $y);
        return $this;
    }

    /**
     * Apply a swirl effect to the image
     *
     * @param  int $degrees
     * @return Imagick
     */
    public function swirl($degrees)
    {
        $this->resource->swirlImage($degrees);
        return $this;
    }

    /**
     * Apply a wave effect to the image
     *
     * @param  int $amp
     * @param  int $length
     * @return Imagick
     */
    public function wave($amp, $length)
    {
        $this->resource->waveImage($amp, $length);
        return $this;
    }

    /**
     * Apply a mosiac pixelate effect to the image
     *
     * @param  int $w
     * @param  int $h
     * @return Imagick
     */
    public function pixelate($w, $h = null)
    {
        $x = $this->width / $w;
        $y = $this->height / ((null === $h) ? $w : $h);

        $this->resource->scaleImage($x, $y);
        $this->resource->scaleImage($this->width, $this->height);

        return $this;
    }

    /**
     * Apply a pencil/sketch effect to the image
     *
     * @param  int $radius
     * @param  int $sigma
     * @param  int $angle
     * @return Imagick
     */
    public function pencil($radius, $sigma, $angle)
    {
        $this->resource->sketchImage($radius, $sigma, $angle);
        return $this;
    }

    /**
     * Return the number of colors in the palette of indexed images.
     *
     * @return int
     */
    public function colorTotal()
    {
        return $this->resource->getImageColors();
    }

    /**
     * Return all of the colors in the palette in an array format
     *
     * @param int|string $format
     * @return array
     */
    public function getColors($format = Imagick::HEX)
    {
        // Initialize the colors array and the image resource.
        $colors = array();

        // Loop through each pixel of the image, recording the color result
        // in the color array.
        for ($h = 0; $h < $this->height; $h++) {
            for ($w = 0; $w < $this->width; $w++) {
                $point = $this->resource->getImagePixelColor($w, $h);
                $color = $point->getColor();

                // Convert to the proper HEX or RGB format.
                if ($format) {
                    $rgb = sprintf('%02s', dechex($color['r'])) . sprintf('%02s', dechex($color['g'])) . sprintf('%02s', dechex($color['b']));
                } else {
                    $rgb = $color['r'] . "," . $color['g'] . "," . $color['b'];
                }
                // If the color is not already in the array, add to it.
                if (!in_array($rgb, $colors)) {
                    $colors[] = $rgb;
                }
            }
        }

        // Return the colors array.
        return $colors;
    }

    /**
     * Convert the image object to the new specified image type.
     *
     * @param  string     $type
     * @throws Exception
     * @return Imagick
     */
    public function convert($type)
    {
        $type = strtolower($type);

        // Check if the requested image type is supported.
        if (!array_key_exists($type, $this->allowed)) {
            throw new Exception('Error: That image type is not supported.');
        // Check if the image is already the requested image type.
        } else if (strtolower($this->extension) == $type) {
            throw new Exception('Error: This image file is already a ' . strtoupper($type) . ' image file.');
        }

        // Else, save the image as the new type.
        $old = $this->extension;
        $this->extension = $type;
        $this->mime = $this->allowed[strtolower($this->extension)];
        $this->fullpath = $this->dir . $this->filename . '.' . $this->extension;
        $this->basename = basename($this->fullpath);

        if (($old == 'psd') || ($old == 'tif') || ($old == 'tiff')) {
            $this->flatten();
        }
        $this->resource->setImageFormat($type);

        return $this;
    }

    /**
     * Output the image object directly.
     *
     * @param  boolean $download
     * @return Imagick
     */
    public function output($download = false)
    {
        // Determine if the force download argument has been passed.
        $attach = ($download) ? 'attachment; ' : null;
        $headers = array(
            'Content-type' => $this->mime,
            'Content-disposition' => $attach . 'filename=' . $this->basename
        );

        if ($_SERVER['SERVER_PORT'] == 443) {
            $headers['Expires']       = 0;
            $headers['Cache-Control'] = 'private, must-revalidate';
            $headers['Pragma']        = 'cache';
        }

        if (null !== $this->compression) {
            $this->resource->setImageCompression($this->compression);
        }
        if (null !== $this->quality) {
            $this->resource->setImageCompressionQuality($this->quality);
        }

        // Send the headers and output the image
        header('HTTP/1.1 200 OK');
        foreach ($headers as $name => $value) {
            header($name . ": " . $value);
        }

        echo $this->resource;

        return $this;
    }

    /**
     * Save the image object to disk.
     *
     * @param  string  $to
     * @return Imagick
     */
    public function save($to = null)
    {
        if (null !== $this->compression) {
            $this->resource->setImageCompression($this->compression);
        }
        if (null !== $this->quality) {
            $this->resource->setImageCompressionQuality($this->quality);
        }
        $img = (null !== $to) ? $to : $this->fullpath;
        $this->resource->writeImage($img);

        clearstatcache();

        $this->setImage($img);
        $this->setImageInfo();

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
        $this->resource->clear();
        $this->resource->destroy();

        // Clear PHP's file status cache.
        clearstatcache();

        // If the $delete flag is passed, delete the image file.
        if (($delete) && file_exists($this->fullpath)) {
            unlink($this->fullpath);
        }
    }

    /**
     * Set the current object formats to include the supported formats of Imagick.
     *
     * @return Imagick
     */
    public function setFormats()
    {
        $formats = $this->getFormats();

        foreach ($formats as $format) {
            $frmt = strtolower($format);
            if (!array_key_exists($frmt, $this->allowed)) {
                $this->allowed[$frmt] = 'image/' . $frmt;
            }
        }

        ksort($this->allowed);

        return $this;
    }

    /**
     * Get the array of supported formats with Imagick.
     *
     * @return array
     */
    public function getFormats()
    {
        $formats = $this->resource->queryFormats();
        array_walk($formats, function(&$item) { $item = strtolower($item); });
        return $formats;
    }

    /**
     * Get the number of supported formats with Imagick.
     *
     * @return int
     */
    public function getNumberOfFormats()
    {
        return count($this->resource->queryFormats());
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
     * Destructor to destroy the image resource
     *
     * @return void
     */
    public function __destruct()
    {
        $this->destroy();
    }

    /**
     * Get Imagick Info.
     *
     * @return void
     */
    protected function getInfo()
    {
        $imagickVersion = $this->resource->getVersion();
        $versionString = trim(substr($imagickVersion['versionString'], 0, stripos($imagickVersion['versionString'], 'http://')));
        $version = substr($versionString, (strpos($versionString, ' ') + 1));
        $version = substr($version, 0, strpos($version, '-'));

        $imInfo = array(
            'version'       => $version,
            'versionString' => $versionString
        );

        $this->info = new \ArrayObject($imInfo, \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Set the image info
     *
     * @return void
     */
    protected function setImageInfo()
    {
        // Set image object properties.
        $this->width   = $this->resource->getImageWidth();
        $this->height  = $this->resource->getImageHeight();
        $this->depth   = $this->resource->getImageDepth();
        $this->quality = null;

        $this->alpha = ($this->resource->getImageAlphaChannel() == 1) ? true : false;
        $colorSpace = $this->resource->getImageColorspace();
        $type = $this->resource->getImageType();

        switch ($colorSpace) {
            case \Imagick::COLORSPACE_UNDEFINED:
                $this->channels = 0;
                $this->mode = '';
                break;
            case \Imagick::COLORSPACE_RGB:
                if ($type == \Imagick::IMGTYPE_PALETTE) {
                    $this->channels = 3;
                    $this->mode = 'Indexed';
                } else if ($type == \Imagick::IMGTYPE_PALETTEMATTE) {
                    $this->channels = 3;
                    $this->mode = 'Indexed';
                } else if ($type == \Imagick::IMGTYPE_GRAYSCALE) {
                    $this->channels = 1;
                    $this->mode = 'Gray';
                } else if ($type == \Imagick::IMGTYPE_GRAYSCALEMATTE) {
                    $this->channels = 1;
                    $this->mode = 'Gray';
                } else {
                    $this->channels = 3;
                    $this->mode = 'RGB';
                }
                break;
            case \Imagick::COLORSPACE_GRAY:
                $this->channels = 1;
                $this->mode = (($type == \Imagick::IMGTYPE_PALETTE) || ($type == \Imagick::IMGTYPE_PALETTEMATTE)) ? 'Indexed' : 'Gray';
                break;
            case \Imagick::COLORSPACE_TRANSPARENT:
                $this->channels = 1;
                $this->mode = 'Transparent';
                break;
            case \Imagick::COLORSPACE_OHTA:
                $this->channels = 3;
                $this->mode = 'OHTA';
                break;
            case \Imagick::COLORSPACE_LAB:
                $this->channels = 3;
                $this->mode = 'LAB';
                break;
            case \Imagick::COLORSPACE_XYZ:
                $this->channels = 3;
                $this->mode = 'XYZ';
                break;
            case \Imagick::COLORSPACE_YCBCR:
                $this->channels = 3;
                $this->mode = 'YCbCr';
                break;
            case \Imagick::COLORSPACE_YCC:
                $this->channels = 3;
                $this->mode = 'YCC';
                break;
            case \Imagick::COLORSPACE_YIQ:
                $this->channels = 3;
                $this->mode = 'YIQ';
                break;
            case \Imagick::COLORSPACE_YPBPR:
                $this->channels = 3;
                $this->mode = 'YPbPr';
                break;
            case \Imagick::COLORSPACE_YUV:
                $this->channels = 3;
                $this->mode = 'YUV';
                break;
            case \Imagick::COLORSPACE_CMYK:
                $this->channels = 4;
                $this->mode = 'CMYK';
                break;
            case \Imagick::COLORSPACE_SRGB:
                $this->channels = 3;
                $this->mode = 'sRGB';
                break;
            case \Imagick::COLORSPACE_HSB:
                $this->channels = 3;
                $this->mode = 'HSB';
                break;
            case \Imagick::COLORSPACE_HSL:
                $this->channels = 3;
                $this->mode = 'HSL';
                break;
            case \Imagick::COLORSPACE_HWB:
                $this->channels = 3;
                $this->mode = 'HWB';
                break;
            case \Imagick::COLORSPACE_REC601LUMA:
                $this->channels = 3;
                $this->mode = 'Rec601';
                break;
            case \Imagick::COLORSPACE_REC709LUMA:
                $this->channels = 3;
                $this->mode = 'Rec709';
                break;
            case \Imagick::COLORSPACE_LOG:
                $this->channels = 3;
                $this->mode = 'LOG';
                break;
            case \Imagick::COLORSPACE_CMY:
                $this->channels = 3;
                $this->mode = 'CMY';
                break;
        }
    }

    /**
     * Set and return a color identifier.
     *
     * @param  array $color
     * @return \ImagickPixel
     */
    protected function setColor(array $color)
    {
        if (count($color) == 3) {
            $r = (int)$color[0];
            $g = (int)$color[1];
            $b = (int)$color[2];
        } else {
            $r = 0;
            $g = 0;
            $b = 0;
        }

        return new \ImagickPixel('rgb(' . $r . ',' . $g . ',' . $b . ')');
    }

}
