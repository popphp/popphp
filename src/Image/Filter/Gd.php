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
 * Filter class for Gd
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Gd extends AbstractFilter
{

    /**
     * Blur the image
     *
     * @param  int $amount
     * @param  int $type
     * @return Gd
     */
    public function blur($amount, $type = IMG_FILTER_GAUSSIAN_BLUR)
    {
        for ($i = 1; $i <= $amount; $i++) {
            imagefilter($this->image->resource(), $type);
        }

        return $this;
    }

    /**
     * Sharpen the image.
     *
     * @param  int $amount
     * @return Gd
     */
    public function sharpen($amount)
    {
        imagefilter($this->image->resource(), IMG_FILTER_SMOOTH, (0 - $amount));
        return $this;
    }

    /**
     * Create a negative of the image
     *
     * @return Gd
     */
    public function negate()
    {
        imagefilter($this->image->resource(), IMG_FILTER_NEGATE);
        return $this;
    }

    /**
     * Colorize the image
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return Gd
     */
    public function colorize($r, $g, $b)
    {
        imagefilter($this->image->resource(), IMG_FILTER_COLORIZE, (int)$r, (int)$g, (int)$b);
        return $this;
    }

    /**
     * Pixelate the image
     *
     * @param  int $px
     * @return Gd
     */
    public function pixelate($px)
    {
        imagefilter($this->image->resource(), IMG_FILTER_PIXELATE, $px, true);
        return $this;
    }

    /**
     * Apply a pencil/sketch effect to the image
     *
     * @return Gd
     */
    public function pencil()
    {
        imagefilter($this->image->resource(), IMG_FILTER_MEAN_REMOVAL);
        return $this;
    }

}
