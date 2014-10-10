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
class Imagick extends AbstractImage
{

    /**
     * Array of allowed file types.
     * @var array
     */
    protected $allowed = [
        'ai'    => 'application/postscript',
        'avi'   => 'video/x-msvideo',
        'bmp'   => 'image/x-ms-bmp',
        'eps'   => 'application/octet-stream',
        'gif'   => 'image/gif',
        'ico'   => 'image/ico',
        'jpe'   => 'image/jpeg',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'mov'   => 'video/quicktime',
        'mp4'   => 'video/mp4',
        'mpg'   => 'video/mpeg',
        'mpeg'  => 'video/mpeg',
        'pdf'   => 'application/pdf',
        'png'   => 'image/png',
        'ps'    => 'application/postscript',
        'psb'   => 'image/x-photoshop',
        'psd'   => 'image/x-photoshop',
        'svg'   => 'image/svg+xml',
        'tif'   => 'image/tiff',
        'tiff'  => 'image/tiff'
    ];

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
     * Constructor
     *
     * Instantiate an image file object based on either a pre-existing
     * image file on disk, or a new image file using the Imagick extension.
     *
     * @param  string $img
     * @param  int    $w
     * @param  int    $h
     * @throws Exception
     * @return Imagick
     */
    public function __construct($img = null, $w = null, $h = null)
    {
        // Check to see if Imagick is installed.
        if (!self::isInstalled()) {
            throw new Exception('Error: The Imagick extension must be installed to use the Imagick adapter.');
        }

        // Set the allowed formats
        $this->setFormats();

        parent::__construct($img, $w, $h);

        // If image exists
        if (file_exists($this->fullpath) && ($this->size > 0)) {
            $this->load($img);
        // Else, if image does not exists
        } else if ((null !== $this->width) && (null !== $this->height)) {
            $this->create($this->width, $this->height, $this->basename);
        }

        // Set a default quality
        $this->setQuality(80);

        // Get the extension info
        $imagickVersion = (new \Imagick())->getVersion();
        $versionString  = trim(substr($imagickVersion['versionString'], 0, stripos($imagickVersion['versionString'], 'http://')));
        $version        = substr($versionString, (strpos($versionString, ' ') + 1));
        $version        = substr($version, 0, strpos($version, '-'));

        $this->info = new \ArrayObject([
            'version'       => $version,
            'versionString' => $versionString
        ], \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Check if Imagick is installed.
     *
     * @return boolean
     */
    public static function isInstalled()
    {
        return self::isAvailable('imagick');
    }

    /**
     * Get the allowed image formats
     *
     * @return array
     */
    public static function getFormats()
    {
        return (new self())->getAllowedTypes();
    }

    /**
     * Create a new image resource
     *
     * @param  int    $width
     * @param  int    $height
     * @param  string $image
     * @return Imagick
     */
    public function create($width, $height, $image = null)
    {
        $this->width    = $width;
        $this->height   = $height;
        $this->resource = new \Imagick();
        $this->resource->newImage($width, $height, new \ImagickPixel('white'));

        if (null !== $image) {
            $this->setImage($image);
            $this->resource->setImageFormat($this->extension);
        }

        $this->output = $this->resource;

        return $this;
    }

    /**
     * Load an existing image as a resource
     *
     * @param  string $image
     * @throws Exception
     * @return Imagick
     */
    public function load($image)
    {
        if (!file_exists($image)) {
            throw new Exception('Error: That image file does not exist.');
        }

        $this->resource = new \Imagick($image);
        $this->width    = $this->resource->getImageWidth();
        $this->height   = $this->resource->getImageHeight();
        $this->setImage($image);

        return $this;
    }

    /**
     * Get the image adjust object
     *
     * @return Adjust\AdjustInterface
     */
    public function adjust()
    {
        if (null === $this->adjust) {
            $this->adjust = new Adjust\Imagick($this);
        }
        if (null === $this->adjust->getImage()) {
            $this->adjust->setImage($this);
        }

        return $this->adjust;
    }

    /**
     * Get the image draw object
     *
     * @return Draw\DrawInterface
     */
    public function draw()
    {
        if (null === $this->draw) {
            $this->draw = new Draw\Imagick($this);
        }
        if (null === $this->draw->getImage()) {
            $this->draw->setImage($this);
        }
        return $this->draw;
    }

    /**
     * Get the image effect object
     *
     * @return Effect\EffectInterface
     */
    public function effect()
    {
        if (null === $this->effect) {
            $this->effect = new Effect\Imagick($this);
        }
        if (null === $this->effect->getImage()) {
            $this->effect->setImage($this);
        }
        return $this->effect;
    }

    /**
     * Get the image filter object
     *
     * @return Filter\FilterInterface
     */
    public function filter()
    {
        if (null === $this->filter) {
            $this->filter = new Filter\Imagick($this);
        }
        if (null === $this->filter->getImage()) {
            $this->filter->setImage($this);
        }
        return $this->filter;
    }

    /**
     * Get the image layer object
     *
     * @return Layer\LayerInterface
     */
    public function layer()
    {
        if (null === $this->layer) {
            $this->layer = new Layer\Imagick($this);
        }
        if (null === $this->layer->getImage()) {
            $this->layer->setImage($this);
        }
        return $this->layer;
    }

    /**
     * Get the image type object
     *
     * @return Type\TypeInterface
     */
    public function type()
    {
        if (null === $this->type) {
            $this->type = new Type\Imagick($this);
        }
        if (null === $this->type->getImage()) {
            $this->type->setImage($this);
        }
        return $this->type;
    }



    /**
     * Set the image opacity.
     *
     * @param  int $opacity
     * @return Imagick
     */
    public function setOpacity($opacity)
    {
        $this->opacity = round((127 - (127 * ($opacity / 100))));
        return $this;
    }

    /**
     * Set the image quality.
     *
     * @param  int $quality
     * @return Imagick
     */
    public function setQuality($quality)
    {
        $this->quality = (int)$quality;
        return $this;
    }

    /**
     * Set the image compression
     *
     * @param  int $compression
     * @return Imagick
     */
    public function setCompression($compression)
    {
        $this->compression = (int)$compression;
        return $this;
    }

    /**
     * Set the image filter.
     *
     * @param  int $filter
     * @return Imagick
     */
    public function setImageFilter($filter)
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * Set the image blur.
     *
     * @param  int $blur
     * @return Imagick
     */
    public function setImageBlur($blur)
    {
        $this->blur = (int)$blur;
        return $this;
    }

    /**
     * Resize the image object to the width parameter passed.
     *
     * @param  int $w
     * @return Imagick
     */
    public function resizeToWidth($w)
    {
        $scale        = $w / $this->width;
        $this->width  = $w;
        $this->height = round($this->height * $scale);

        $this->resource->resizeImage($this->width, $this->height, $this->filter, $this->blur);
        return $this;
    }

    /**
     * Resize the image object to the height parameter passed.
     *
     * @param  int $h
     * @return Imagick
     */
    public function resizeToHeight($h)
    {
        $scale        = $h / $this->height;
        $this->height = $h;
        $this->width  = round($this->width * $scale);

        $this->resource->resizeImage($this->width, $this->height, $this->filter, $this->blur);
        return $this;
    }

    /**
     * Resize the image object, allowing for the largest dimension to be scaled
     * to the value of the $px argument.
     *
     * @param  int $px
     * @return Imagick
     */
    public function resize($px)
    {
        $scale        = ($this->width > $this->height) ? ($px / $this->width) : ($px / $this->height);
        $this->width  = round($this->width * $scale);
        $this->height = round($this->height * $scale);

        $this->resource->resizeImage($this->width, $this->height, $this->filter, $this->blur);

        return $this;
    }

    /**
     * Scale the image object, allowing for the dimensions to be scaled
     * proportionally to the value of the $scl argument.
     *
     * @param  float $scale
     * @return Imagick
     */
    public function scale($scale)
    {
        $this->width  = round($this->width * $scale);
        $this->height = round($this->height * $scale);

        $this->resource->resizeImage($this->width, $this->height, $this->filter, $this->blur);
        return $this;
    }

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
     * @return Imagick
     */
    public function crop($w, $h, $x = 0, $y = 0)
    {
        $this->width  = $w;
        $this->height = $h;
        $this->resource->cropImage($this->width, $this->height, $x, $y);
        return $this;
    }

    /**
     * Crop the image object to a square image whose dimensions are based on the
     * value of the $px argument. The optional $offset argument allows for the
     * adjustment of the crop to select a certain area of the image to be
     * cropped.
     *
     * @param  int $px
     * @param  int $offset
     * @return Imagick
     */
    public function cropThumb($px, $offset = null)
    {
        $xOffset = 0;
        $yOffset = 0;

        if (null !== $offset) {
            if ($this->width > $this->height) {
                $xOffset = $offset;
                $yOffset = 0;
            } else if ($this->width < $this->height) {
                $xOffset = 0;
                $yOffset = $offset;
            }
        }

        $scale = ($this->width > $this->height) ? ($px / $this->height) : ($px / $this->width);

        $wid = round($this->width * $scale);
        $hgt = round($this->height * $scale);

        // Create a new image output resource.
        if (null !== $offset) {
            $this->resource->resizeImage($wid, $hgt, $this->filter, $this->blur);
            $this->resource->cropImage($px, $px, $xOffset, $yOffset);
        } else {
            $this->resource->cropThumbnailImage($px, $px);
        }

        $this->width  = $px;
        $this->height = $px;
        return $this;
    }

    /**
     * Rotate the image object
     *
     * @param  int   $degrees
     * @param  array $bgColor
     * @throws Exception
     * @return Imagick
     */
    public function rotate($degrees, array $bgColor = [255, 255, 255])
    {
        if (count($bgColor) != 3) {
            throw new Exception('The background color array must contain 3 integers.');
        }
        $this->resource->rotateImage($this->getColor($bgColor), $degrees);
        $this->width  = $this->resource->getImageWidth();
        $this->height = $this->resource->getImageHeight();
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
     * Convert the image object to another format.
     *
     * @param  string $type
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

        $old             = $this->extension;
        $this->extension = $type;
        $this->mime      = $this->allowed[$this->extension];
        $this->fullpath  = $this->dir . DIRECTORY_SEPARATOR . $this->filename . '.' . $this->extension;
        $this->basename  = basename($this->fullpath);

        if (($old == 'psd') || ($old == 'tif') || ($old == 'tiff')) {
            $this->flatten();
        }

        $this->resource->setImageFormat($type);

        return $this;
    }

    /**
     * Save the image object to disk.
     *
     * @param  string $to
     * @return void
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

        // Set image object properties.
        $this->width  = $this->resource->getImageWidth();
        $this->height = $this->resource->getImageHeight();
    }

    /**
     * Output the image object directly.
     *
     * @param  boolean $download
     * @throws Exception
     * @return void
     */
    public function output($download = false)
    {
        // Determine if the force download argument has been passed.
        $attach = ($download) ? 'attachment; ' : null;
        $headers = [
            'Content-type'        => $this->mime,
            'Content-disposition' => $attach . 'filename=' . $this->basename
        ];

        if (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443)) {
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
        if (!headers_sent()) {
            header('HTTP/1.1 200 OK');
            foreach ($headers as $name => $value) {
                header($name . ": " . $value);
            }
        }

        echo $this->resource;
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
            $this->resource->clear();
            $this->resource->destroy();
        }

        $this->resource = null;
        $this->output   = null;

        clearstatcache();

        // If the $delete flag is passed, delete the image file.
        if (($delete) && file_exists($this->fullpath)) {
            unlink($this->fullpath);
        }
    }

    /**
     * Create and return a color.
     *
     * @param  array   $color
     * @param  int     $opacity
     * @throws Exception
     * @return mixed
     */
    public function getColor(array $color, $opacity = 100)
    {
        if (null === $this->resource) {
            throw new Exception('Error: The image resource has not been created.');
        }

        if (count($color) == 3) {
            $r = $color[0];
            $g = $color[1];
            $b = $color[2];
        } else {
            $r = 0;
            $g = 0;
            $b = 0;
        }

        return new \ImagickPixel('rgba(' . $r . ',' . $g . ',' . $b . ',' . (int)$opacity . ')');
    }

    /**
     * Output the image
     *
     * @return string
     */
    public function __toString()
    {
        $this->output();
        return '';
    }


    /**
     * Set the image formats based on what's supported by Imagick
     *
     * @return void
     */
    protected function setFormats()
    {
        $formats = (new \Imagick())->queryFormats();
        array_walk($formats, function(&$item) { $item = strtolower($item); });
        foreach ($formats as $format) {
            if (!array_key_exists($format, $this->allowed)) {
                $this->allowed[$format] = 'application/octet-stream';
            }
        }

        ksort($this->allowed);
    }

}
