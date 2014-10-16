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
namespace Pop\Image\Effect;

/**
 * Effect class for Imagick
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Imagick extends AbstractEffect
{

    /**
     * Draw a border around the image.
     *
     * @param  array $color
     * @param  int   $w
     * @param  int   $h
     * @throws Exception
     * @return Imagick
     */
    public function border(array $color, $w = 1, $h = null)
    {
        if (count($color) != 3) {
            throw new Exception('The color parameter must be an array of 3 integers.');
        }

        $h = (null === $h) ? $w : $h;
        $this->image->resource()->borderImage($this->image->getColor($color), $w, $h);
        return $this;
    }

    /**
     * Flood the image with a color fill.
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return Imagick
     */
    public function fill($r, $g, $b)
    {
        $draw = new \ImagickDraw();
        $draw->setFillColor($this->image->getColor([(int)$r, (int)$g, (int)$b]));
        $draw->rectangle(0, 0, $this->image->getWidth(), $this->image->getHeight());
        $this->image->resource()->drawImage($draw);
        return $this;
    }

    /**
     * Flood the image with a vertical color gradient.
     *
     * @param  array   $color1
     * @param  array   $color2
     * @return Imagick
     */
    public function radialGradient(array $color1, array $color2)
    {
        $im = new \Imagick();
        $width  = round($this->image->getWidth() * 1.25);
        $height = round($this->image->getHeight() * 1.25);
        $im->newPseudoImage($width, $height, 'radial-gradient:#' . $this->getHex($color1) . '-#' . $this->getHex($color2));
        $this->image->resource()->compositeImage(
            $im, \Imagick::COMPOSITE_ATOP,
            0 - round(($width - $this->image->getWidth()) / 2),
            0 - round(($height - $this->image->getHeight()) / 2)
        );
        return $this;
    }

    /**
     * Flood the image with a vertical color gradient.
     *
     * @param  array   $color1
     * @param  array   $color2
     * @return Imagick
     */
    public function verticalGradient(array $color1, array $color2)
    {
        $im = new \Imagick();
        $im->newPseudoImage($this->image->getWidth(), $this->image->getHeight(), 'gradient:#' . $this->getHex($color1) . '-#' . $this->getHex($color2));
        $this->image->resource()->compositeImage($im, \Imagick::COMPOSITE_ATOP, 0, 0);
        return $this;
    }

    /**
     * Flood the image with a vertical color gradient.
     *
     * @param  array   $color1
     * @param  array   $color2
     * @return Imagick
     */
    public function horizontalGradient(array $color1, array $color2)
    {
        $im = new \Imagick();
        $im->newPseudoImage($this->image->getHeight(), $this->image->getWidth(), 'gradient:#' . $this->getHex($color1) . '-#' . $this->getHex($color2));
        $im->rotateImage('rgb(255, 255, 255)', -90);
        $this->image->resource()->compositeImage($im, \Imagick::COMPOSITE_ATOP, 0, 0);
        return $this;
    }

    /**
     * Flood the image with a color gradient.
     *
     * @param  array   $color1
     * @param  array   $color2
     * @param  boolean $vertical
     * @throws Exception
     * @return Imagick
     */
    public function linearGradient(array $color1, array $color2, $vertical = true)
    {
        if ($vertical) {
            $this->verticalGradient($color1, $color2);
        } else {
            $this->horizontalGradient($color1, $color2);
        }

        return $this;
    }

}
