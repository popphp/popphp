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
namespace Pop\Image\Draw;

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
class Gd extends AbstractDraw
{

    /**
     * Draw a line on the image.
     *
     * @param  int $x1
     * @param  int $y1
     * @param  int $x2
     * @param  int $y2
     * @return Gd
     */
    public function line($x1, $y1, $x2, $y2)
    {

        $strokeColor = ($this->image->getMime() == 'image/gif') ? $this->image->getColor($this->strokeColor, false) :
            $this->image->getColor($this->strokeColor);

        // Draw the line.
        imagesetthickness($this->image->resource(), (($this->strokeWidth == 0) ? 1 : $this->strokeWidth));
        imageline($this->image->resource(), $x1, $y1, $x2, $y2, $strokeColor);

        return $this;
    }

    /**
     * Draw a rectangle on the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return Gd
     */
    public function rectangle($x, $y, $w, $h = null)
    {
        $x2 = $x + $w;
        $y2 = $y + ((null === $h) ? $w : $h);

        $fillColor = ($this->image->getMime() == 'image/gif') ? $this->image->getColor($this->fillColor, false) :
            $this->image->getColor($this->fillColor);


        imagefilledrectangle($this->image->resource(), $x, $y, $x2, $y2, $fillColor);

        if ($this->strokeWidth > 0) {
            $strokeColor = ($this->image->getMime() == 'image/gif') ? $this->image->getColor($this->strokeColor, false) :
                $this->image->getColor($this->strokeColor);
            imagesetthickness($this->image->resource(), $this->strokeWidth);
            imagerectangle($this->image->resource(), $x, $y, $x2, $y2, $strokeColor);
        }

        return $this;
    }

    /**
     * Draw a square on the image.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @return Gd
     */
    public function square($x, $y, $w)
    {
        return $this->rectangle($x, $y, $w, $w);
    }

    /**
     * Draw an ellipse on the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return Gd
     */
    public function ellipse($x, $y, $w, $h = null)
    {
        return $this;
    }

    /**
     * Method to add a circle to the image.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @return Gd
     */
    public function circle($x, $y, $w)
    {
        return $this->ellipse($x, $y, $w, $w);
    }

    /**
     * Draw an arc on the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $start
     * @param  int $end
     * @param  int $w
     * @param  int $h
     * @return Gd
     */
    public function arc($x, $y, $start, $end, $w, $h = null)
    {
        return $this;
    }

    /**
     * Draw a chord on the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $start
     * @param  int $end
     * @param  int $w
     * @param  int $h
     * @return Gd
     */
    public function chord($x, $y, $start, $end, $w, $h = null)
    {
        return $this;
    }

    /**
     * Draw a polygon on the image.
     *
     * @param  array $points
     * @return Gd
     */
    public function polygon($points)
    {
        return $this;
    }

}
