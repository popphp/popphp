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
namespace Pop\Image\Type;

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
class Svg implements TypeInterface
{

    /**
     * Image object
     * @var \Pop\Image\Svg
     */
    protected $image = null;

    /**
     * Type font size
     * @var int
     */
    protected $size = 12;

    /**
     * Type font
     * @var string
     */
    protected $font = 'Arial';

    /**
     * Fill color
     * @var array
     */
    protected $fillColor = [0, 0, 0];

    /**
     * Type X-position
     * @var int
     */
    protected $x = 0;

    /**
     * Type Y-position
     * @var int
     */
    protected $y = 0;

    /**
     * Type rotation in degrees
     * @var int
     */
    protected $rotation = 0;

    /**
     * Opacity
     * @var float
     */
    protected $opacity = 1.0;

    /**
     * Type bold flag
     * @var boolean
     */
    protected $bold = false;

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
     * @return float
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
     * Set the font size
     *
     * @param  int $size
     * @return Svg
     */
    public function size($size)
    {
        $this->size = (int)$size;
        return $this;
    }

    /**
     * Set the font
     *
     * @param  string $font
     * @return Svg
     */
    public function font($font)
    {
        $this->font = $font;
        return $this;
    }

    /**
     * Set the X-position
     *
     * @param  int $x
     * @return Svg
     */
    public function x($x)
    {
        $this->x = (int)$x;
        return $this;
    }

    /**
     * Set the Y-position
     *
     * @param  int $y
     * @return Svg
     */
    public function y($y)
    {
        $this->y = (int)$y;
        return $this;
    }

    /**
     * Set both the X- and Y-positions
     *
     * @param  int $x
     * @param  int $y
     * @return Svg
     */
    public function xy($x, $y)
    {
        $this->x($x);
        $this->y($y);
        return $this;
    }

    /**
     * Set the rotation of the text
     *
     * @param  int $degrees
     * @return Svg
     */
    public function rotate($degrees)
    {
        $this->rotation = (int)$degrees;
        return $this;
    }

    /**
     * Set if the text is bold
     *
     * @param  boolean $bold
     * @return Svg
     */
    public function bold($bold)
    {
        $this->bold = (bool)$bold;
        return $this;
    }

    /**
     * Set and apply the text on the image
     *
     * @param  string $string
     * @return Svg
     */
    public function text($string)
    {
        $text = $this->image->resource()->addChild('text', $string);
        $text->addAttribute('x', $this->x . $this->image->getUnits());
        $text->addAttribute('y', $this->y . $this->image->getUnits());
        $text->addAttribute('font-size', $this->size);
        $text->addAttribute('font-family', $this->font);

        if (null !== $this->fillColor) {
            $text->addAttribute('fill', 'rgb(' . $this->fillColor[0] . ',' . $this->fillColor[1] . ',' . $this->fillColor[2] . ')');
            if ($this->opacity < 1.0) {
                $text->addAttribute('fill-opacity', $this->opacity);
            }
        }

        if (null !== $this->rotation) {
            $text->addAttribute('transform', 'rotate(' . $this->rotation . ' ' . $this->x . ',' . $this->y .')');
        }
        if ($this->bold) {
            $text->addAttribute('font-weight', 'bold');
        }
        return $this;
    }

}
