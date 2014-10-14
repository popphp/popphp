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
class Svg implements DrawInterface
{

    /**
     * Image object
     * @var \Pop\Image\AbstractImage
     */
    protected $image = null;

    /**
     * Opacity
     * @var float
     */
    protected $opacity = 1.0;

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
     * @return \Pop\Image\AbstractImage
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
     * @param  float $opacity
     * @return Svg
     */
    public function setOpacity($opacity)
    {
        $this->opacity = $opacity;
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
     * Method to draw a rectangle to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return Svg
     */
    public function rectangle($x, $y, $w, $h = null)
    {
        $rect = $this->image->resource()->addChild('rect');
        $rect->addAttribute('x', $x . $this->image->getUnits());
        $rect->addAttribute('y', $y . $this->image->getUnits());
        $rect->addAttribute('width', $w . $this->image->getUnits());
        $rect->addAttribute('height', ((null === $h) ? $w : $h) . $this->image->getUnits());

        $rect = $this->setStyles($rect);

        return $this;
    }

    /**
     * Method to set the styles.
     *
     * @param  \SimpleXMLElement $obj
     * @return \SimpleXMLElement
     */
    protected function setStyles($obj)
    {
        //if (null !== $this->curClippingPath) {
        //    $obj->addAttribute('style', 'clip-path: url(#clip' . $this->curClippingPath .');');
        //}

        //if (null !== $this->curGradient) {
        //    $obj->addAttribute('fill', 'url(#grad' . $this->curGradient . ')');
        //} else if (null !== $this->fillColor) {
        if (null !== $this->fillColor) {
            $obj->addAttribute('fill', 'rgb(' . $this->fillColor[0] . ',' . $this->fillColor[1] . ',' . $this->fillColor[2] . ')');
            if ($this->opacity < 1.0) {
                $obj->addAttribute('fill-opacity', $this->opacity);
            }
        }
        if ($this->strokeWidth > 0) {
            $obj->addAttribute('stroke', 'rgb(' . $this->strokeColor[0] . ',' . $this->strokeColor[1] . ',' . $this->strokeColor[2] . ')');
            $obj->addAttribute('stroke-width', $this->strokeWidth . $this->image->getUnits());
            if ((null !== $this->strokeDashLength) && (null !== $this->strokeDashGap)) {
                $obj->addAttribute('stroke-dasharray', $this->strokeDashLength . $this->image->getUnits() . ',' . $this->strokeDashGap . $this->image->getUnits());
            }
        }

        return $obj;
    }

}
