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
namespace Pop\Image\Adjust;

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
class Imagick extends AbstractAdjust
{

    /**
     * Method to adjust the hue of the image.
     *
     * @param  int $amount
     * @return Imagick
     */
    public function hue($amount)
    {
        $this->image->resource()->modulateImage(100, 100, $amount);
        return $this;
    }

    /**
     * Method to adjust the saturation of the image.
     *
     * @param  int $amount
     * @return Imagick
     */
    public function saturation($amount)
    {
        $this->image->resource()->modulateImage(100, $amount, 100);
        return $this;
    }

    /**
     * Adjust the image brightness
     *
     * @param  int $amount
     * @return Imagick
     */
    public function brightness($amount)
    {
        $this->image->resource()->modulateImage($amount, 100, 100);
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
        $this->image->resource()->modulateImage($h, $s, $b);
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
        $quantumRange = $this->image->resource()->getQuantumRange();

        if ($black < 0) {
            $black = 0;
        }
        if ($white > 255) {
            $white = 255;
        }

        $blackPoint = ($black / 255) * $quantumRange['quantumRangeLong'];
        $whitePoint = ($white / 255) * $quantumRange['quantumRangeLong'];

        $this->image->resource()->levelImage($blackPoint, $gamma, $whitePoint);

        return $this;
    }

    /**
     * Adjust the image contrast
     *
     * @param  int $amount
     * @return Imagick
     */
    public function contrast($amount)
    {
        if ($amount > 0) {
            for ($i = 1; $i <= $amount; $i++) {
                $this->image->resource()->contrastImage(1);
            }
        } else if ($amount < 0) {
            for ($i = -1; $i >= $amount; $i--) {
                $this->image->resource()->contrastImage(0);
            }
        }

        return $this;
    }

    /**
     * Adjust the image desaturate
     *
     * @return Imagick
     */
    public function desaturate()
    {
        $this->image->resource()->modulateImage(100, 0, 100);
        return $this;
    }

}
