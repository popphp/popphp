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
class Svg implements EffectInterface
{

    /**
     * Image object
     * @var \Pop\Image\Svg
     */
    protected $image = null;

    /**
     * SVG image available gradients
     * @var array
     */
    protected $gradients = [];

    /**
     * Current gradient to use.
     * @var int
     */
    protected $curGradient = null;

    /**
     * Opacity
     * @var int
     */
    protected $opacity = 0;

    /**
     * Fill color
     * @var array
     */
    protected $fillColor = null;

    /**
     * Stroke color
     * @var array
     */
    protected $strokeColor = [0, 0, 0];

    /**
     * Stroke width
     * @var int
     */
    protected $strokeWidth = 0;

    /**
     * Stroke dash length
     * @var int
     */
    protected $strokeDashLength = null;

    /**
     * Stroke dash gap
     * @var int
     */
    protected $strokeDashGap = null;

    /**
     * Constructor
     *
     * Instantiate an image object
     *
     * @param  \Pop\Image\Svg
     * @return Svg
     */
    public function __construct(\Pop\Image\Svg $image = null)
    {
        if (null !== $image) {
            $this->setImage($image);
        }
    }

    /**
     * Get the image object
     *
     * @return \Pop\Image\Svg
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Get the opacity
     *
     * @return int
     */
    public function getOpacity()
    {
        return $this->opacity;
    }

    /**
     * Get fill color
     *
     * @return mixed
     */
    public function getFillColor()
    {
        return $this->fillColor;
    }

    /**
     * Get stroke color
     *
     * @return array
     */
    public function getStrokeColor()
    {
        return $this->strokeColor;
    }

    /**
     * Get stroke width
     *
     * @return int
     */
    public function getStrokeWidth()
    {
        return $this->strokeWidth;
    }

    /**
     * Set the image object
     *
     * @param  \Pop\Image\Svg
     * @return Svg
     */
    public function setImage(\Pop\Image\Svg $image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * Set the opacity
     *
     * @param  int $opacity
     * @return Svg
     */
    public function setOpacity($opacity)
    {
        $this->opacity = (int)round((127 - (127 * ($opacity / 100))));
        return $this;
    }

    /**
     * Set fill color
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return Svg
     */
    public function setFillColor($r, $g, $b)
    {
        $this->fillColor = [(int)$r, (int)$g, (int)$b];
        return $this;
    }

    /**
     * Set stroke color
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return Svg
     */
    public function setStrokeColor($r, $g, $b)
    {
        $this->strokeColor = [(int)$r, (int)$g, (int)$b];
        return $this;
    }

    /**
     * Get stroke width
     *
     * @param int $w
     * @param int $dashLength
     * @param int $dashGap
     * @return Svg
     */
    public function setStrokeWidth($w, $dashLength = null, $dashGap = null)
    {
        $this->strokeWidth      = (int)$w;
        $this->strokeDashLength = $dashLength;
        $this->strokeDashGap    = $dashGap;
        return $this;
    }

    /**
     * Draw a border around the image.
     *
     * @param  array $color
     * @param  int $w
     * @throws Exception
     * @return Svg
     */
    public function border(array $color, $w)
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
        if ((null !== $this->strokeDashLength) && (null !== $this->strokeDashGap)) {
            $rect->addAttribute('stroke-dasharray', $this->strokeDashLength . $this->image->getUnits() . ',' . $this->strokeDashGap . $this->image->getUnits());
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
     * @param  int   $opacity
     * @throws Exception
     * @return Svg
     */
    public function radialGradient(array $color1, array $color2, $opacity = 1)
    {
        if ((count($color1) != 3) || (count($color2) != 3)) {
            throw new Exception('The color parameters must be arrays of 3 integers.');
        }

        $this->curGradient = count($this->gradients);
        $defs = $this->image->resource()->addChild('defs');

        $grad = $defs->addChild('radialGradient');
        $grad->addAttribute('id', 'grad' . $this->curGradient);
        $grad->addAttribute('cx', '50%');
        $grad->addAttribute('cy', '50%');
        $grad->addAttribute('r', '50%');
        $grad->addAttribute('fx', '50%');
        $grad->addAttribute('fy', '50%');

        $stop1 = $grad->addChild('stop');
        $stop1->addAttribute('offset', '0%');
        $stop1->addAttribute('style', 'stop-color: ' . 'rgb(' . $color1[0] . ',' . $color1[1] . ',' . $color1[2] . ')' . '; stop-opacity: ' . $opacity . ';');

        $stop2 = $grad->addChild('stop');
        $stop2->addAttribute('offset', '100%');
        $stop2->addAttribute('style', 'stop-color: ' . 'rgb(' . $color2[0] . ',' . $color2[1] . ',' . $color2[2] . ')' . '; stop-opacity: ' . $opacity . ';');

        $rect = $this->image->resource()->addChild('rect');
        $rect->addAttribute('x', '0' . $this->image->getUnits());
        $rect->addAttribute('y', '0' . $this->image->getUnits());
        $rect->addAttribute('width', $this->image->getWidth());
        $rect->addAttribute('height', $this->image->getHeight());

        $rect->addAttribute('fill', 'url(#grad' . $this->curGradient . ')');

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
        if ((count($color1) != 3) || (count($color2) != 3)) {
            throw new Exception('The color parameters must be arrays of 3 integers.');
        }
        return $this;
    }

}
