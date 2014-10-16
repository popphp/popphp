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
namespace Pop\Image\Layer;

/**
 * Layer class for Gmagick
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Gmagick extends AbstractLayer
{

    /**
     * Opacity
     * @var mixed
     */
    protected $opacity = 1.0;

    /**
     * Overlay style
     * @var int
     */
    protected $overlay = \Gmagick::COMPOSITE_ATOP;

    /**
     * Get the overlay
     *
     * @return int
     */
    public function getOverlay()
    {
        return $this->overlay;
    }

    /**
     * Get the overlay
     *
     * @param  int $overlay
     * @return Gmagick
     */
    public function setOverlay($overlay)
    {
        $this->overlay = $overlay;
        return $this;
    }

    /**
     * Set the opacity
     *
     * @param  float $opacity
     * @return Gmagick
     */
    public function setOpacity($opacity)
    {
        $this->opacity = $opacity;
        return $this;
    }

    /**
     * Overlay an image onto the current image.
     *
     * @param  string $image
     * @param  int    $x
     * @param  int    $y
     * @return Gmagick
     */
    public function overlay($image, $x = 0, $y = 0)
    {
        $overlayImage = new \Gmagick($image);
        if ($this->opacity < 1) {
            $overlayImage->setImageOpacity($this->opacity);
        }

        $this->image->resource()->compositeImage($overlayImage, $this->overlay, $x, $y);
        return $this;
    }

    /**
     * Flatten the image layers
     *
     * @return Gmagick
     */
    public function flatten()
    {
        if (method_exists($this->image->resource(), 'flattenImages')) {
            $this->image->resource()->flattenImages();
        }
        return $this;
    }

}
