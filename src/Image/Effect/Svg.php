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
class Svg extends AbstractEffect
{

    /**
     * Draw a border around the image.
     *
     * @param  array $color
     * @param  int   $w
     * @param  int   $dashLen
     * @param  int   $dashGap
     * @throws Exception
     * @return Svg
     */
    public function border(array $color, $w, $dashLen = null, $dashGap = null)
    {
        if (count($color) != 3) {
            throw new Exception('The color parameter must be an array of 3 integers.');
        }

        $rect = $this->image->resource()->addChild('rect');
        $rect->addAttribute('x', '0' . $this->image->getUnits());
        $rect->addAttribute('y', '0' . $this->image->getUnits());
        $rect->addAttribute('width', $this->image->getWidth() . $this->image->getUnits());
        $rect->addAttribute('height', $this->image->getHeight() . $this->image->getUnits());


        $rect->addAttribute('stroke', 'rgb(' . $color[0] . ',' . $color[1] . ',' . $color[2] . ')');
        $rect->addAttribute('stroke-width', ($w * 2) . $this->image->getUnits());
        if ((null !== $dashLen) && (null !== $dashGap)) {
            $rect->addAttribute('stroke-dasharray', $dashLen . $this->image->getUnits() . ',' . $dashGap . $this->image->getUnits());
        }

        $rect->addAttribute('fill', 'none');

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
        $rect = $this->image->resource()->addChild('rect');
        $rect->addAttribute('x', '0' . $this->image->getUnits());
        $rect->addAttribute('y', '0' . $this->image->getUnits());
        $rect->addAttribute('width', $this->image->getWidth());
        $rect->addAttribute('height', $this->image->getHeight());

        $rect->addAttribute('fill', 'rgb(' . $r . ',' . $g . ',' . $b . ')');
        return $this;
    }

    /**
     * Flood the image with a radial color gradient.
     *
     * @param  array $color1
     * @param  array $color2
     * @param  float $opacity
     * @throws Exception
     * @return Svg
     */
    public function radialGradient(array $color1, array $color2, $opacity = 1.0)
    {
        if ((count($color1) != 3) || (count($color2) != 3)) {
            throw new Exception('The color parameters must be arrays of 3 integers.');
        }

        $this->image->addRadialGradient($color1, $color2, $opacity);

        $rect = $this->image->resource()->addChild('rect');
        $rect->addAttribute('x', '0' . $this->image->getUnits());
        $rect->addAttribute('y', '0' . $this->image->getUnits());
        $rect->addAttribute('width', $this->image->getWidth());
        $rect->addAttribute('height', $this->image->getHeight());

        $rect->addAttribute('fill', 'url(#grad' . $this->image->getCurGradient() . ')');

        return $this;
    }

    /**
     * Flood the image with a vertical color gradient.
     *
     * @param  array $color1
     * @param  array $color2
     * @param  float $opacity
     * @return Gd
     */
    public function verticalGradient(array $color1, array $color2, $opacity = 1.0)
    {
        return $this->linearGradient($color1, $color2, $opacity, true);
    }

    /**
     * Flood the image with a vertical color gradient.
     *
     * @param  array $color1
     * @param  array $color2
     * @param  float $opacity
     * @return Gd
     */
    public function horizontalGradient(array $color1, array $color2, $opacity = 1.0)
    {
        return $this->linearGradient($color1, $color2, $opacity, false);
    }

    /**
     * Flood the image with a color gradient.
     *
     * @param  array   $color1
     * @param  array   $color2
     * @param  float   $opacity
     * @param  boolean $vertical
     * @throws Exception
     * @return Gd
     */
    public function linearGradient(array $color1, array $color2, $opacity = 1.0, $vertical = true)
    {
        if ((count($color1) != 3) || (count($color2) != 3)) {
            throw new Exception('The color parameters must be arrays of 3 integers.');
        }

        if ($vertical) {
            $this->image->addLinearGradient($color1, $color2, $opacity, true);
        } else {
            $this->image->addLinearGradient($color1, $color2, $opacity, false);
        }

        $rect = $this->image->resource()->addChild('rect');
        $rect->addAttribute('x', '0' . $this->image->getUnits());
        $rect->addAttribute('y', '0' . $this->image->getUnits());
        $rect->addAttribute('width', $this->image->getWidth());
        $rect->addAttribute('height', $this->image->getHeight());

        $rect->addAttribute('fill', 'url(#grad' . $this->image->getCurGradient() . ')');

        return $this;
    }

}
