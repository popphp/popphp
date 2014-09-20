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
 * Image class
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Gmagick extends AbstractEffect
{

    /**
     * Draw a border around the image.
     *
     * @param  int    $w
     * @param  int    $h
     * @return Gmagick
     */
    public function border($w, $h = null)
    {
        return $this;
    }

    /**
     * Flood the image with a color fill.
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return Gmagick
     */
    public function fill($r, $g, $b)
    {
        return $this;
    }

    /**
     * Flood the image with a vertical color gradient.
     *
     * @param  array   $color1
     * @param  array   $color2
     * @return Gmagick
     */
    public function radialGradient(array $color1, array $color2)
    {
        return $this;
    }

    /**
     * Flood the image with a vertical color gradient.
     *
     * @param  array   $color1
     * @param  array   $color2
     * @return Gmagick
     */
    public function verticalGradient(array $color1, array $color2)
    {
        return $this;
    }

    /**
     * Flood the image with a vertical color gradient.
     *
     * @param  array   $color1
     * @param  array   $color2
     * @return Gmagick
     */
    public function horizontalGradient(array $color1, array $color2)
    {
        return $this;
    }

    /**
     * Flood the image with a color gradient.
     *
     * @param  array   $color1
     * @param  array   $color2
     * @param  boolean $vertical
     * @throws Exception
     * @return Gmagick
     */
    public function linearGradient(array $color1, array $color2, $vertical = true)
    {
        return $this;
    }

}
