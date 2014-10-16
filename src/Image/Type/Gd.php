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
 * Type class for Gd
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Gd extends AbstractType
{

    /**
     * Opacity
     * @var int
     */
    protected $opacity = 0;

    /**
     * Set the opacity
     *
     * @param  int $opacity
     * @return Gd
     */
    public function setOpacity($opacity)
    {
        $this->opacity = (int)round((127 - (127 * ($opacity / 100))));
        return $this;
    }

    /**
     * Set and apply the text on the image
     *
     * @param  string $string
     * @return Gd
     */
    public function text($string)
    {
        $fillColor = ($this->image->getMime() == 'image/gif') ? $this->image->getColor($this->fillColor, false) :
            $this->image->getColor($this->fillColor, true, $this->opacity);

        if ((null !== $this->font) && function_exists('imagettftext')) {
            if (null !== $this->strokeColor) {
                $strokeColor = ($this->image->getMime() == 'image/gif') ? $this->image->getColor($this->strokeColor, false) :
                    $this->image->getColor($this->strokeColor, true, $this->opacity);
                imagettftext($this->image->resource(), $this->size, $this->rotation, $this->x, ($this->y - 1), $strokeColor, $this->font, $string);
                imagettftext($this->image->resource(), $this->size, $this->rotation, $this->x, ($this->y + 1), $strokeColor, $this->font, $string);
                imagettftext($this->image->resource(), $this->size, $this->rotation, ($this->x - 1), $this->y, $strokeColor, $this->font, $string);
                imagettftext($this->image->resource(), $this->size, $this->rotation, ($this->x + 1), $this->y, $strokeColor, $this->font, $string);
            }
            imagettftext($this->image->resource(), $this->size, $this->rotation, $this->x, $this->y, $fillColor, $this->font, $string);
        } else {
            // Cap the system font size between 1 and 5
            if ($this->size > 5) {
                $this->size = 5;
            } else if ($this->size < 1) {
                $this->size = 1;
            }
            imagestring($this->image->resource(), $this->size, $this->x, $this->y,  $string, $fillColor);
        }

        return $this;
    }

}
