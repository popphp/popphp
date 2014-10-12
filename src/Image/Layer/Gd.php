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
namespace Pop\Image\Layer;

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
class Gd extends AbstractLayer
{

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
     * Overlay an image onto the current image.
     *
     * @param  string $image
     * @param  int    $x
     * @param  int    $y
     * @throws Exception
     * @return Gd
     */
    public function overlay($image, $x = 0, $y = 0)
    {
        imagealphablending($this->image->resource(), true);

        // Create an image resource from the overlay image.
        if (stripos($image, '.gif') !== false) {
            $overlay = imagecreatefromgif($image);
        } else if (stripos($image, '.png') !== false) {
            $overlay = imagecreatefrompng($image);
        } else if (stripos($image, '.jp') !== false) {
            $overlay = imagecreatefromjpeg($image);
        } else {
            throw new Exception('Error: The overlay image must be either a JPG, GIF or PNG.');
        }

        if ($this->opacity > 0) {
            if ($this->opacity == 100) {
                imagecopy($this->image->resource(), $overlay, $x, $y, 0, 0, imagesx($overlay), imagesy($overlay));
            } else{
                imagecopymerge($this->image->resource(), $overlay, $x, $y, 0, 0, imagesx($overlay), imagesy($overlay), $this->opacity);
            }
        }

        return $this;
    }

}
