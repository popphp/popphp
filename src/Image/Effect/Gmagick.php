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
 * Effect class for Gmagick
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
     * @param  array $color
     * @param  int   $w
     * @param  int   $h
     * @throws Exception
     * @return Gmagick
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
     * @return Gmagick
     */
    public function fill($r, $g, $b)
    {
        $draw = new \GmagickDraw();
        $draw->setFillColor($this->image->getColor([(int)$r, (int)$g, (int)$b]));
        $draw->rectangle(0, 0, $this->image->getWidth(), $this->image->getHeight());
        $this->image->resource()->drawImage($draw);
        return $this;
    }

}
