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
 * Draw class for Imagick
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Imagick extends AbstractDraw
{

    /**
     * Opacity
     * @var float
     */
    protected $opacity = 1.0;

    /**
     * Set the opacity
     *
     * @param  float $opacity
     * @return Gmagick
     */
    public function setOpacity($opacity)
    {
        $this->opacity = $opacity;
        return $this;
    }

    /**
     * Draw a line on the image.
     *
     * @param  int $x1
     * @param  int $y1
     * @param  int $x2
     * @param  int $y2
     * @return Imagick
     */
    public function line($x1, $y1, $x2, $y2)
    {
        $draw = new \ImagickDraw();
        $draw->setStrokeColor($this->image->getColor($this->strokeColor, $this->opacity));
        $draw->setStrokeWidth((null === $this->strokeWidth) ? 1 : $this->strokeWidth);
        $draw->line($x1, $y1, $x2, $y2);
        $this->image->resource()->drawImage($draw);

        return $this;
    }

    /**
     * Draw a rectangle on the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return Imagick
     */
    public function rectangle($x, $y, $w, $h = null)
    {
        $x2 = $x + $w;
        $y2 = $y + ((null === $h) ? $w : $h);

        $draw = new \ImagickDraw();


        if (null !== $this->fillColor) {
            $draw->setFillColor($this->image->getColor($this->fillColor, $this->opacity));
        }

        if ($this->strokeWidth > 0) {
            $draw->setStrokeColor($this->image->getColor($this->strokeColor, $this->opacity));
            $draw->setStrokeWidth($this->strokeWidth);
        }

        $draw->rectangle($x, $y, $x2, $y2);
        $this->image->resource()->drawImage($draw);

        return $this;
    }

    /**
     * Draw a square on the image.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @return Imagick
     */
    public function square($x, $y, $w)
    {
        return $this->rectangle($x, $y, $w, $w);
    }

    /**
     * Draw a rounded rectangle on the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @param  int $rx
     * @param  int $ry
     * @return Imagick
     */
    public function roundRectangle($x, $y, $w, $h = null, $rx = 10, $ry = null)
    {
        $x2 = $x + $w;
        $y2 = $y + ((null === $h) ? $w : $h);
        if (null === $ry) {
            $ry = $rx;
        }

        $draw = new \ImagickDraw();

        if (null !== $this->fillColor) {
            $draw->setFillColor($this->image->getColor($this->fillColor, $this->opacity));
        }

        if ($this->strokeWidth > 0) {
            $draw->setStrokeColor($this->image->getColor($this->strokeColor, $this->opacity));
            $draw->setStrokeWidth($this->strokeWidth);
        }

        $draw->roundRectangle($x, $y, $x2, $y2, $rx, $ry);
        $this->image->resource()->drawImage($draw);

        return $this;
    }

    /**
     * Draw a rounded square on the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $rx
     * @param  int $ry
     * @return Imagick
     */
    public function roundSquare($x, $y, $w, $rx = 10, $ry = null)
    {
        return $this->roundRectangle($x, $y, $w, $w, $rx, $ry);
    }

    /**
     * Draw an ellipse on the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return Imagick
     */
    public function ellipse($x, $y, $w, $h = null)
    {
        $wid = $w;
        $hgt = (null === $h) ? $w : $h;

        $draw = new \ImagickDraw();
        if (null !== $this->fillColor) {
            $draw->setFillColor($this->image->getColor($this->fillColor, $this->opacity));
        }

        if ($this->strokeWidth > 0) {
            $draw->setStrokeColor($this->image->getColor($this->strokeColor, $this->opacity));
            $draw->setStrokeWidth($this->strokeWidth);
        }

        $draw->ellipse($x, $y, $wid, $hgt, 0, 360);
        $this->image->resource()->drawImage($draw);

        return $this;
    }

    /**
     * Method to add a circle to the image.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @return Imagick
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
     * @return Imagick
     */
    public function arc($x, $y, $start, $end, $w, $h = null)
    {
        if ($this->strokeWidth == 0) {
            $this->setStrokeWidth(1);
        }

        $wid = $w;
        $hgt = (null === $h) ? $w : $h;

        $draw = new \ImagickDraw();
        $draw->setFillOpacity(0);
        $draw->setStrokeColor($this->image->getColor($this->strokeColor, $this->opacity));
        $draw->setStrokeWidth($this->strokeWidth);

        $draw->ellipse($x, $y, $wid, $hgt, $start, $end);

        $this->image->resource()->drawImage($draw);

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
     * @return Imagick
     */
    public function chord($x, $y, $start, $end, $w, $h = null)
    {
        $wid = $w;
        $hgt = (null === $h) ? $w : $h;

        $draw = new \ImagickDraw();
        $draw->setFillColor($this->image->getColor($this->fillColor));

        $x1 = $w * cos($start / 180 * pi());
        $y1 = $h * sin($start / 180 * pi());
        $x2 = $w * cos($end / 180 * pi());
        $y2 = $h * sin($end / 180 * pi());

        $draw->ellipse($x, $y, $wid, $hgt, $start, $end);
        $this->image->resource()->drawImage($draw);

        if ($this->strokeWidth > 0) {
            $draw = new \ImagickDraw();

            $draw->setFillColor($this->image->getColor($this->fillColor));
            $draw->setStrokeColor($this->image->getColor($this->strokeColor));
            $draw->setStrokeWidth($this->strokeWidth);

            $draw->ellipse($x, $y, $wid, $hgt, $start, $end);
            $draw->line($x + $x1, $y + $y1, $x + $x2, $y + $y2);

            $this->image->resource()->drawImage($draw);
        }

        return $this;
    }

    /**
     * Draw a pie slice on the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $start
     * @param  int $end
     * @param  int $w
     * @param  int $h
     * @return Imagick
     */
    public function pie($x, $y, $start, $end, $w, $h = null)
    {

        $wid = $w;
        $hgt = (null === $h) ? $w : $h;

        $draw = new \ImagickDraw();
        $draw->setFillColor($this->image->getColor($this->fillColor));

        $x1 = $w * cos($start / 180 * pi());
        $y1 = $h * sin($start / 180 * pi());
        $x2 = $w * cos($end / 180 * pi());
        $y2 = $h * sin($end / 180 * pi());

        $points = [
            ['x' => $x, 'y' => $y],
            ['x' => $x + $x1, 'y' => $y + $y1],
            ['x' => $x + $x2, 'y' => $y + $y2]
        ];

        $draw->polygon($points);

        $draw->ellipse($x, $y, $wid, $hgt, $start, $end);
        $this->image->resource()->drawImage($draw);

        if ($this->strokeWidth > 0) {
            $draw = new \ImagickDraw();

            $draw->setFillColor($this->image->getColor($this->fillColor));
            $draw->setStrokeColor($this->image->getColor($this->strokeColor));
            $draw->setStrokeWidth($this->strokeWidth);

            $draw->ellipse($x, $y, $wid, $hgt, $start, $end);
            $draw->line($x, $y, $x + $x1, $y + $y1);
            $draw->line($x, $y, $x + $x2, $y + $y2);

            $this->image->resource()->drawImage($draw);
        }

        return $this;
    }

    /**
     * Draw a polygon on the image.
     *
     * @param  array $points
     * @return Imagick
     */
    public function polygon($points)
    {
        $draw = new \ImagickDraw();
        if (null !== $this->fillColor) {
            $draw->setFillColor($this->image->getColor($this->fillColor, $this->opacity));
        }

        if ($this->strokeWidth > 0) {
            $draw->setStrokeColor($this->image->getColor($this->strokeColor, $this->opacity));
            $draw->setStrokeWidth($this->strokeWidth);
        }

        $draw->polygon($points);
        $this->image->resource()->drawImage($draw);

        return $this;
    }

}
