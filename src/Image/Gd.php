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
class Gd extends AbstractImage
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
            $this->create($this->width, $this->height);
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
     * Get the image transform object
     *
     * @return Transform\TransformInterface
     */
    public function transform()
    {
        if (null === $this->transform) {
            $this->transform = new Transform\Gd($this);
        }
        if (null === $this->transform->getImage()) {
            $this->transform->setImage($this);
        }
        return $this->transform;
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
     * Set the image opacity.
     *
     * @param  int $opacity
     * @return Gd
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

        $this->compression = $this->quality;

        return $this;
    }

    /**
     * Set the image compression (for Gd, an alias to setQuality())
     *
     * @param  int $compression
     * @return Gd
     */
    public function setCompression($compression)
    {
        $this->setQuality($compression);
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
     * value of the $px argument. The optional $x and $y arguments allow for the
     * adjustment of the crop to select a certain area of the image to be
     * cropped.
     *
     * @param  int $px
     * @param  int $x
     * @param  int $y
     * @return Gd
     */
    public function cropThumb($px, $x = 0, $y = 0)
    {
        $scale        = ($this->width > $this->height) ? ($px / $this->height) : ($px / $this->width);
        $w            = round($this->width * $scale);
        $h            = round($this->height * $scale);
        $this->output = imagecreatetruecolor($px, $px);

        $this->copyImage($w, $h, $x, $y);
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
        $this->width    = $imgSize[0];
        $this->height   = $imgSize[1];
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

        if (null === $this->output) {
            throw new Exception('Error: The image resource has not been properly created.');
        }

        // Send the headers and output the image
        header('HTTP/1.1 200 OK');
        foreach ($headers as $name => $value) {
            header($name . ": " . $value);
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
            $this->resource = null;
        }

        // Destroy the image output resource.
        if (null !== $this->output) {
            if (!is_string($this->output) && is_resource($this->output)) {
                imagedestroy($this->output);
            }
            $this->output = null;
        }

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
     * @throws Exception
     * @return mixed
     */
    public function getColor(array $color, $alpha = true)
    {
        if (null === $this->resource) {
            throw new Exception('Error: The image resource has not been created.');
        }

        $opacity = (null === $this->opacity) ? 0 : $this->opacity;

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
            imagecolorallocatealpha($this->resource, (int)$r, (int)$g, (int)$b, $opacity) :
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
    protected function copyImage($w, $h, $x = 0, $y = 0)
    {
        imagecopyresampled($this->output, $this->resource, 0, 0, $x, $y, $w, $h, $this->width, $this->height);
        $this->width    = imagesx($this->output);
        $this->height   = imagesy($this->output);
        $this->resource = $this->output;
    }

}
