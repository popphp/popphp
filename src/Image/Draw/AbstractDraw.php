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
abstract class AbstractDraw implements DrawInterface
{

    /**
     * Image object
     * @var \Pop\Image\AbstractImage
     */
    protected $image = null;

    /**
     * Fill color
     * @var array
     */
    protected $fillColor = [0, 0, 0];

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
     * Constructor
     *
     * Instantiate an image object
     *
     * @param  \Pop\Image\AbstractImage
     * @return AbstractDraw
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
     * Get fill color
     *
     * @return array
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
     * @param  \Pop\Image\AbstractImage
     * @return AbstractDraw
     */
    public function setImage(\Pop\Image\AbstractImage $image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * Set fill color
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return AbstractDraw
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
     * @return AbstractDraw
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
     * @return AbstractDraw
     */
    public function setStrokeWidth($w)
    {
        $this->strokeWidth = (int)$w;
        return $this;
    }

}
