<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <info@popphp.org>
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
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractType implements TypeInterface
{

    /**
     * Image object
     * @var \Pop\Image\AbstractImage
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
    protected $font = null;

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
     * Flag to stroke the type
     * @var boolean
     */
    protected $stroke = false;

    /**
     * Constructor
     *
     * Instantiate an image object
     *
     * @param  \Pop\Image\AbstractImage
     * @return AbstractType
     */
    public function __construct(\Pop\Image\AbstractImage $image = null)
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
     * Set the image object
     *
     * @param  \Pop\Image\AbstractImage
     * @return AbstractType
     */
    public function setImage(\Pop\Image\AbstractImage $image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * Set the font size
     *
     * @param  int $size
     * @return AbstractType
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
     * @return AbstractType
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
     * @return AbstractType
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
     * @return AbstractType
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
     * @return AbstractType
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
     * @return AbstractType
     */
    public function rotate($degrees)
    {
        $this->rotation = (int)$degrees;
        return $this;
    }

    /**
     * Set the stroke of the text
     *
     * @param  boolean $stroke
     * @return AbstractType
     */
    public function stroke($stroke)
    {
        $this->stroke = (bool)$stroke;
        return $this;
    }

}
