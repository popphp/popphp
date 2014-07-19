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
namespace Pop\Image\Adapter;

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
class Gd extends AbstractAdapter
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
     * @param  array  $types
     * @throws Exception
     * @return Gd
     */
    public function __construct($img, $w = null, $h = null, $types = null)
    {
        parent::__construct($img, $w, $h, $types);

        // Check to see if GD is installed.
        if (!self::isInstalled()) {
            throw new Exception('Error: The GD library extension must be installed to use the Gd adapter.');
        }

        // If image exists
        if (file_exists($this->fullpath) && ($this->size > 0)) {
            $imgSize = getimagesize($img);

            // Set image object properties.
            $this->width  = $imgSize[0];
            $this->height = $imgSize[1];

            $this->createResource();
        // Else, if image does not exists
        } else {
            if ((null === $w) || (null === $h)) {
                throw new Exception('Error: That image file does not exist yet, so you must define a width and height for a new image object.');
            }

            // Set image object properties.
            $this->width    = $w;
            $this->height   = $h;
            $this->resource = ($this->mime == 'image/gif') ? imagecreate($w, $h) : imagecreatetruecolor($w, $h);
            $this->output   = $this->resource;
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
     * Resize the image object to the width parameter passed.
     *
     * @param  int $w
     * @return Gd
     */
    public function resizeToWidth($w)
    {
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

        $this->createImage($this->output, ((null === $to) ? $this->fullpath : $to), 100);
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

        // Send the headers and output the image
        header('HTTP/1.1 200 OK');
        foreach ($headers as $name => $value) {
            header($name . ": " . $value);
        }

        $this->createImage($this->output, null, 100);
    }

    /**
     * Destroy the image object and the related image file directly.
     *
     * @param  boolean $file
     * @return void
     */
    public function destroy($file = false)
    {

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
