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
 * Filter class for Imagick
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Imagick extends AbstractFilter
{

    /**
     * Blur the image.
     *
     * @param  mixed $radius
     * @param  mixed $sigma
     * @param  int   $channel
     * @return Imagick
     */
    public function blur($radius = 0, $sigma = 0, $channel = \Imagick::CHANNEL_ALL)
    {
        $this->image->resource()->blurImage($radius, $sigma, $channel);
        return $this;
    }

    /**
     * Blur the image.
     *
     * @param  mixed $radius
     * @param  mixed $sigma
     * @param  int   $channel
     * @return Imagick
     */
    public function adaptiveBlur($radius = 0, $sigma = 0, $channel = \Imagick::CHANNEL_DEFAULT)
    {
        $this->image->resource()->adaptiveBlurImage($radius, $sigma, $channel);
        return $this;
    }

    /**
     * Blur the image.
     *
     * @param  mixed $radius
     * @param  mixed $sigma
     * @param  int   $channel
     * @return Imagick
     */
    public function gaussianBlur($radius = 0, $sigma = 0, $channel = \Imagick::CHANNEL_ALL)
    {
        $this->image->resource()->gaussianBlurImage($radius, $sigma, $channel);
        return $this;
    }

    /**
     * Blur the image.
     *
     * @param  mixed $radius
     * @param  mixed $sigma
     * @param  int   $angle
     * @param  int   $channel
     * @return Imagick
     */
    public function motionBlur($radius = 0, $sigma = 0, $angle = 0, $channel = \Imagick::CHANNEL_DEFAULT)
    {
        $this->image->resource()->motionBlurImage($radius, $sigma, $angle, $channel);
        return $this;
    }

    /**
     * Blur the image.
     *
     * @param  int $angle
     * @param  int $channel
     * @return Imagick
     */
    public function radialBlur($angle = 0, $channel = \Imagick::CHANNEL_ALL)
    {
        $this->image->resource()->radialBlurImage($angle, $channel);
        return $this;
    }

    /**
     * Sharpen the image
     *
     * @param  mixed $radius
     * @param  mixed $sigma
     * @param  int   $channel
     * @return Imagick
     */
    public function sharpen($radius = 0, $sigma = 0, $channel = \Imagick::CHANNEL_ALL)
    {
        $this->image->resource()->sharpenImage($radius, $sigma, $channel);
        return $this;
    }

    /**
     * Create a negative of the image
     *
     * @return Imagick
     */
    public function negate()
    {
        $this->image->resource()->negateImage(false);
        return $this;
    }

    /**
     * Apply an oil paint effect to the image using the pixel radius threshold
     *
     * @param  int $radius
     * @return Imagick
     */
    public function paint($radius)
    {
        $this->image->resource()->oilPaintImage($radius);
        return $this;
    }

    /**
     * Apply a posterize effect to the image
     *
     * @param  int     $levels
     * @param  boolean $dither
     * @return Imagick
     */
    public function posterize($levels, $dither = false)
    {
        $this->image->resource()->posterizeImage($levels, $dither);
        return $this;
    }

    /**
     * Apply a noise effect to the image
     *
     * @param  int $type
     * @param  int $channel
     * @return Imagick
     */
    public function noise($type = \Imagick::NOISE_MULTIPLICATIVEGAUSSIAN, $channel = \Imagick::CHANNEL_DEFAULT)
    {
        $this->image->resource()->addNoiseImage($type, $channel);
        return $this;
    }

    /**
     * Apply a diffusion effect to the image
     *
     * @param  int $radius
     * @return Imagick
     */
    public function diffuse($radius)
    {
        $this->image->resource()->spreadImage($radius);
        return $this;
    }

    /**
     * Apply a skew effect to the image
     *
     * @param  int    $x
     * @param  int    $y
     * @param  string $color
     * @return Imagick
     */
    public function skew($x, $y, $color = 'rgb(255, 255, 255)')
    {
        $this->image->resource()->shearImage($color, $x, $y);
        return $this;
    }

    /**
     * Apply a swirl effect to the image
     *
     * @param  int $degrees
     * @return Imagick
     */
    public function swirl($degrees)
    {
        $this->image->resource()->swirlImage($degrees);
        return $this;
    }

    /**
     * Apply a wave effect to the image
     *
     * @param  mixed $amp
     * @param  mixed $length
     * @return Imagick
     */
    public function wave($amp, $length)
    {
        $this->image->resource()->waveImage($amp, $length);
        return $this;
    }

    /**
     * Apply a mosaic pixelate effect to the image
     *
     * @param  int $w
     * @param  int $h
     * @return Imagick
     */
    public function pixelate($w, $h = null)
    {
        $x = $this->image->getWidth() / $w;
        $y = $this->image->getHeight() / ((null === $h) ? $w : $h);

        $this->image->resource()->scaleImage($x, $y);
        $this->image->resource()->scaleImage($this->image->getWidth(), $this->image->getHeight());

        return $this;
    }

    /**
     * Apply a pencil/sketch effect to the image
     *
     * @param  mixed $radius
     * @param  mixed $sigma
     * @param  mixed $angle
     * @return Imagick
     */
    public function pencil($radius, $sigma, $angle)
    {
        $this->image->resource()->sketchImage($radius, $sigma, $angle);
        return $this;
    }

}
