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
 * Effect class for Gd
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Gd extends AbstractEffect
{

    /**
     * Draw a border around the image.
     *
     * @param  array $color
     * @param  int $w
     * @param  int $h
     * @throws Exception
     * @return Gd
     */
    public function border(array $color, $w, $h = null)
    {
        if (count($color) != 3) {
            throw new Exception('The color parameter must be an array of 3 integers.');
        }

        $h = (null === $h) ? $w : $h;
        $width  = $this->image->getWidth();
        $height = $this->image->getHeight();

        $this->image->draw()->setFillColor((int)$color[0], (int)$color[1], (int)$color[2]);
        $this->image->draw()->rectangle(0, 0, $width, $h);
        $this->image->draw()->rectangle(0, ($height - $h), $width, $height);
        $this->image->draw()->rectangle(0, 0, $w, $height);
        $this->image->draw()->rectangle(($width - $w), 0, $width, $height);

        return $this;
    }

    /**
     * Flood the image with a color fill.
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return Gd
     */
    public function fill($r, $g, $b)
    {
        if ($this->image->getMime() == 'image/gif') {
            imagefill($this->image->resource(), 0, 0, $this->image->getColor([$r, $g, $b], false));
        } else {
            imagefill($this->image->resource(), 0, 0, $this->image->getColor([$r, $g, $b]));
        }
        return $this;
    }

    /**
     * Flood the image with a vertical color gradient.
     *
     * @param  array   $color1
     * @param  array   $color2
     * @return Gd
     */
    public function radialGradient(array $color1, array $color2)
    {
        if ($this->image->getHeight() > $this->image->getWidth()) {
            $tween = $this->image->getHeight();
            $tween = round($tween * ($this->image->getHeight() / $this->image->getWidth()));
        } else if ($this->image->getWidth() > $this->image->getHeight()) {
            $tween = $this->image->getWidth();
            $tween = round($tween * ($this->image->getWidth() / $this->image->getHeight()));
        } else {
            $tween = $this->image->getWidth();
            $tween = round($tween * 1.5);
        }
        $blend = $this->getBlend($color1, $color2, $tween);

        $x = round($this->image->getWidth() / 2);
        $y = round($this->image->getHeight() / 2);
        $w = $tween;
        $h = $tween;

        foreach ($blend['r'] as $i => $v) {
            $r = $v;
            $g = $blend['g'][$i];
            $b = $blend['b'][$i];
            $color = ($this->image->getMime() == 'image/gif') ? $this->image->getColor([$r, $g, $b], false) :
                $this->image->getColor([$r, $g, $b]);

            imagefilledellipse($this->image->resource(), $x, $y, $w, $h, $color);
            $w--;
            $h--;
        }

        return $this;
    }

    /**
     * Flood the image with a vertical color gradient.
     *
     * @param  array   $color1
     * @param  array   $color2
     * @return Gd
     */
    public function verticalGradient(array $color1, array $color2)
    {
        return $this->linearGradient($color1, $color2, true);
    }

    /**
     * Flood the image with a vertical color gradient.
     *
     * @param  array   $color1
     * @param  array   $color2
     * @return Gd
     */
    public function horizontalGradient(array $color1, array $color2)
    {
        return $this->linearGradient($color1, $color2, false);
    }

    /**
     * Flood the image with a color gradient.
     *
     * @param  array   $color1
     * @param  array   $color2
     * @param  boolean $vertical
     * @throws Exception
     * @return Gd
     */
    public function linearGradient(array $color1, array $color2, $vertical = true)
    {
        $tween = ($vertical) ? $this->image->getHeight() : $this->image->getWidth();
        $blend = $this->getBlend($color1, $color2, $tween);

        foreach ($blend['r'] as $i => $v) {
            $r = $v;
            $g = $blend['g'][$i];
            $b = $blend['b'][$i];
            $color = ($this->image->getMime() == 'image/gif') ? $this->image->getColor([$r, $g, $b], false) :
                $this->image->getColor([$r, $g, $b]);
            if ($vertical) {
                imageline($this->image->resource(), 0, $i, $this->image->getWidth(), $i, $color);
            } else {
                imageline($this->image->resource(), $i, 0, $i, $this->image->getHeight(), $color);
            }
        }

        return $this;
    }

}
