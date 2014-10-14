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
class Svg extends AbstractType
{

    /**
     * Type font
     * @var string
     */
    protected $font = 'Arial';

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
