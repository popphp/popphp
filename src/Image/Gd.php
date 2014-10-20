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
 * GD image adapter class
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Gd extends AbstractRasterImage
{

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
     * Constructor
     *
     * Instantiate an image file object based on either a pre-existing
     * image file on disk, or a new image file using the GD extension.
     *
     * @param  string $img
     * @param  int    $w
     * @param  int    $h
     * @throws Exception
     * @return Gd
     */
    public function __construct($img = null, $w = null, $h = null)
    {
        // Check to see if GD is installed.
        if (!self::isInstalled()) {
            throw new Exception('Error: The GD library extension must be installed to use the Gd adapter.');
        }

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
        $gd = gd_info();
        $this->info = new \ArrayObject([
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
        ], \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Check if GD is installed.
     *
     * @return boolean
     */
    public static function isInstalled()
    {
        return self::isAvailable('gd');
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
     * @return Gd
     */
    public function create($width, $height, $image = null)
    {
        $this->width    = $width;
        $this->height   = $height;
        $this->resource = ($this->mime == 'image/gif') ? imagecreate($width, $height) : imagecreatetruecolor($width, $height);
        $this->output   = $this->resource;

        if (null !== $image) {
            $this->setImage($image);
        }

        return $this;
    }

    /**
     * Load an existing image as a resource
     *
     * @param  string $image
     * @throws Exception
     * @return Gd
     */
    public function load($image)
    {
        if (!file_exists($image)) {
            throw new Exception('Error: That image file does not exist.');
        }
        $imgSize = getimagesize($image);

        // Set image object properties.
        $this->width  = $imgSize[0];
        $this->height = $imgSize[1];

        $this->setImage($image);
        $this->setQuality(80);
        $this->createResource();

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
            $this->adjust = new Adjust\Gd($this);
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
            $this->draw = new Draw\Gd($this);
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
            $this->effect = new Effect\Gd($this);
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
            $this->filter = new Filter\Gd($this);
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
            $this->layer = new Layer\Gd($this);
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
            $this->type = new Type\Gd($this);
        }
        if (null === $this->type->getImage()) {
            $this->type->setImage($this);
        }
        return $this->type;
    }

    /**
     * Set the image quality.
     *
     * @param  int $quality
     * @return Gd
     */
    public function setQuality($quality)
    {
        switch ($this->mime) {
            case 'image/png':
                $this->quality = ($quality < 10) ? 9 : 10 - round(($quality / 10), PHP_ROUND_HALF_DOWN);
                break;
            case 'image/jpeg':
                $this->quality = round($quality);
                break;
            default:
                $this->quality = 100;
        }

        return $this;
    }

    /**
     * Resize the image object to the width parameter passed.
     *
     * @param  int $w
     * @return Gd
     */
    public function resizeToWidth($w)
    {
        $scale        = $w / $this->width;
        $h            = round($this->height * $scale);
        $this->output = imagecreatetruecolor($w, $h);

        $this->copyImage($w, $h);
        return $this;
    }

    /**
     * Resize the image object to the height parameter passed.
     *
     * @param  int $h
     * @return Gd
     */
    public function resizeToHeight($h)
    {
        $scale        = $h / $this->height;
        $w            = round($this->width * $scale);
        $this->output = imagecreatetruecolor($w, $h);

        $this->copyImage($w, $h);
        return $this;
    }

    /**
     * Resize the image object, allowing for the largest dimension to be scaled
     * to the value of the $px argument.
     *
     * @param  int $px
     * @return Gd
     */
    public function resize($px)
    {
        $scale        = ($this->width > $this->height) ? ($px / $this->width) : ($px / $this->height);
        $w            = round($this->width * $scale);
        $h            = round($this->height * $scale);
        $this->output = imagecreatetruecolor($w, $h);

        $this->copyImage($w, $h);
        return $this;
    }

    /**
     * Scale the image object, allowing for the dimensions to be scaled
     * proportionally to the value of the $scl argument.
     *
     * @param  float $scale
     * @return Gd
     */
    public function scale($scale)
    {
        $w = round($this->width * $scale);
        $h = round($this->height * $scale);
        $this->output = imagecreatetruecolor($w, $h);

        $this->copyImage($w, $h);
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
     * @return Gd
     */
    public function crop($w, $h, $x = 0, $y = 0)
    {
        $this->output = imagecreatetruecolor($w, $h);
        $this->copyImage($this->width, $this->height, $x, $y);
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
     * @return Gd
     */
    public function cropThumb($px, $offset = null)
    {
        $xOffset = 0;
        $yOffset = 0;

        $scale        = ($this->width > $this->height) ? ($px / $this->height) : ($px / $this->width);
        $w            = round($this->width * $scale);
        $h            = round($this->height * $scale);
        $this->output = imagecreatetruecolor($px, $px);

        if (null !== $offset) {
            if ($this->width > $this->height) {
                $xOffset = $offset;
                $yOffset = 0;
            } else if ($this->width < $this->height) {
                $xOffset = 0;
                $yOffset = $offset;
            }
        } else {
            if ($this->width > $this->height) {
                $xOffset = round(($this->width - $this->height) / 2);
                $yOffset = 0;
            } else if ($this->width < $this->height) {
                $xOffset = 0;
                $yOffset = round(($this->height - $this->width) / 2);
            }
        }

        $this->copyImage($w, $h, $xOffset, $yOffset);
        return $this;
    }

    /**
     * Rotate the image object
     *
     * @param  int   $degrees
     * @param  array $bgColor
     * @throws Exception
     * @return Gd
     */
    public function rotate($degrees, array $bgColor = [255, 255, 255])
    {
        if (count($bgColor) != 3) {
            throw new Exception('The background color array must contain 3 integers.');
        }

        $this->resource = imagerotate($this->resource, $degrees, $this->getColor($bgColor));
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
        $curWidth  = $this->width;
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
        $curWidth  = $this->width;
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
     * Convert the image object to another format.
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
            throw new Exception('Error: That image type is not supported.');
            // Check if the image is already the requested image type.
        } else if (strtolower($this->extension) == $type) {
            throw new Exception('Error: This image file is already a ' . strtoupper($type) . ' image file.');
        }

        // Open a new image, maintaining the GIF image's palette and
        // transparency where applicable.
        if ($this->mime == 'image/gif') {
            $this->createResource();
            imageinterlace($this->resource, 0);

            // Change the type of the image object to the new,
            // requested image type.
            $this->extension = $type;
            $this->mime = $this->allowed[$this->extension];

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
                $this->mime      = $this->allowed[$this->extension];

                // Redefine the image object properties with the new values.
                $this->fullpath = $this->dir . DIRECTORY_SEPARATOR . $this->filename . '.' . $this->extension;
                $this->basename = basename($this->fullpath);
            } else {
                $new = imagecreatetruecolor($this->width, $this->height);

                // Create a new, blank image file and copy the image over.
                $this->createResource();

                // Change the type of the image object to the new,
                // requested image type.
                $this->extension = $type;
                $this->mime = $this->allowed[$this->extension];

                // Redefine the image object properties with the new values.
                $this->fullpath = $this->dir . DIRECTORY_SEPARATOR . $this->filename . '.' . $this->extension;
                $this->basename = basename($this->fullpath);

                // Create and save the image in it's new, proper format.
                imagecopyresampled($new, $this->resource, 0, 0, 0, 0, $this->width, $this->height, $this->width, $this->height);
                $this->setQuality(80);
            }
        }

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
        $this->width  = $imgSize[0];
        $this->height = $imgSize[1];
    }

    /**
     * Output the image object directly.
     *
     * @param  boolean $download
     * @param  boolean $sendHeaders
     * @throws Exception
     * @return void
     */
    public function output($download = false, $sendHeaders = true)
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

        if (null === $this->resource) {
            $this->createResource();
        }

        if (null === $this->output) {
            $this->output = $this->resource;
        }

        if (null === $this->output) {
            throw new Exception('Error: The image resource has not been properly created.');
        }

        // Send the headers and output the image
        if (!headers_sent() && ($sendHeaders)) {
            header('HTTP/1.1 200 OK');
            foreach ($headers as $name => $value) {
                header($name . ": " . $value);
            }
        }

        $this->createImage($this->output, null, $this->quality);
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
        }

        // Destroy the image output resource.
        if (null !== $this->output) {
            if (!is_string($this->output) && is_resource($this->output)) {
                imagedestroy($this->output);
            }
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
     * @param  boolean $alpha
     * @param  int     $opacity
     * @throws Exception
     * @return mixed
     */
    public function getColor(array $color, $alpha = true, $opacity = 0)
    {
        if (null === $this->resource) {
            throw new Exception('Error: The image resource has not been created.');
        }

        if (count($color) == 3) {
            $r = (int)$color[0];
            $g = (int)$color[1];
            $b = (int)$color[2];
        } else {
            $r = 0;
            $g = 0;
            $b = 0;
        }

        return ($alpha) ?
            imagecolorallocatealpha($this->resource, (int)$r, (int)$g, (int)$b, (int)$opacity) :
            imagecolorallocate($this->resource, (int)$r, (int)$g, (int)$b);
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
    public function copyImage($w, $h, $x = 0, $y = 0)
    {
        if ((null === $this->output) && (null !== $this->resource)) {
            $this->output = $this->resource;
        }

        imagecopyresampled($this->output, $this->resource, 0, 0, $x, $y, $w, $h, $this->width, $this->height);
        $this->width    = imagesx($this->output);
        $this->height   = imagesy($this->output);
        $this->resource = $this->output;
    }

}
