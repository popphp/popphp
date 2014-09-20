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
namespace Pop\Image\Filter;

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
class Gmagick extends AbstractFilter
{

    /**
     * Blur the image
     *
     * @param  int $amount
     * @return Gmagick
     */
    public function blur($amount)
    {
        return $this;
    }

    /**
     * Sharpen the image
     *
     * @param  int $amount
     * @return Gmagick
     */
    public function sharpen($amount)
    {
        return $this;
    }

    /**
     * Create a negative of the image
     *
     * @return Gmagick
     */
    public function negate()
    {
        return $this;
    }

    /**
     * Pixelate the image
     *
     * @param  int $px
     * @return Gmagick
     */
    public function pixelate($px)
    {
        return $this;
    }

    /**
     * Apply a pencil/sketch effect to the image
     *
     * @return Gmagick
     */
    public function pencil()
    {
        return $this;
    }

}
