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
 * Image class
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Imagick extends AbstractLayer
{

    /**
     * Opacity
     * @var float
     */
    protected $opacity = 1.0;

    /**
     * Overlay style
     * @var int
     */
    protected $overlay = \Imagick::COMPOSITE_ATOP;

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
     * @return Imagick
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
     * @return Imagick
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
     * @return Imagick
     */
    public function overlay($image, $x = 0, $y = 0)
    {
        $overlayImage = new \Imagick($image);
        if ($this->opacity < 1) {
            $overlayImage->setImageOpacity($this->opacity);
        }

        $this->image->resource()->compositeImage($overlayImage, $this->overlay, $x, $y);
        return $this;
    }

    /**
     * Flatten the image layers
     *
     * @param  int $method
     * @return Imagick
     */
    public function flatten($method = \Imagick::LAYERMETHOD_FLATTEN)
    {
        $this->image->resource()->mergeImageLayers($method);
        return $this;
    }

}
