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
namespace Pop\Image;

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
interface ImageInterface
{

    /**
     * Get the image resource
     *
     * @return resource
     */
    public function resource();

    /**
     * Get the image full path
     *
     * @return string
     */
    public function getFullpath();

    /**
     * Get the image directory
     *
     * @return string
     */
    public function getDir();

    /**
     * Get the image basename
     *
     * @return string
     */
    public function getBasename();

    /**
     * Get the image filename
     *
     * @return string
     */
    public function getFilename();

    /**
     * Get the image extension
     *
     * @return string
     */
    public function getExtension();

    /**
     * Get the image size
     *
     * @return int
     */
    public function getSize();

    /**
     * Get the image mime type
     *
     * @return string
     */
    public function getMime();

    /**
     * Get the image width.
     *
     * @return int
     */
    public function getWidth();

    /**
     * Get the image height.
     *
     * @return int
     */
    public function getHeight();

    /**
     * Resize the image object to the width parameter passed.
     *
     * @param  int $w
     * @return ImageInterface
     */
    public function resizeToWidth($w);

    /**
     * Resize the image object to the height parameter passed.
     *
     * @param  int $h
     * @return ImageInterface
     */
    public function resizeToHeight($h);

    /**
     * Resize the image object, allowing for the largest dimension to be scaled
     * to the value of the $px argument.
     *
     * @param  int $px
     * @return ImageInterface
     */
    public function resize($px);

    /**
     * Scale the image object, allowing for the dimensions to be scaled
     * proportionally to the value of the $scl argument.
     *
     * @param  float $scale
     * @return ImageInterface
     */
    public function scale($scale);

    /**
     * Crop the image object to a image whose dimensions are based on the
     * value of the $wid and $hgt argument. The optional $x and $y arguments
     * allow for the adjustment of the crop to select a certain area of the
     * image to be cropped.
     *
     * @param  int $w
     * @param  int $h
     * @param  int $x
     * @param  int $y
     * @return ImageInterface
     */
    public function crop($w, $h, $x = 0, $y = 0);

    /**
     * Crop the image object to a square image whose dimensions are based on the
     * value of the $px argument. The optional $x and $y arguments allow for the
     * adjustment of the crop to select a certain area of the image to be
     * cropped.
     *
     * @param  int $px
     * @param  int $x
     * @param  int $y
     * @return ImageInterface
     */
    public function cropThumb($px, $x = 0, $y = 0);
    
    /**
     * Save the image object to disk.
     *
     * @param  string $to
     * @return void
     */
    public function save($to = null);

    /**
     * Output the image object directly.
     *
     * @param  boolean $download
     * @return void
     */
    public function output($download = false);

    /**
     * Destroy the image object and the related image file directly.
     *
     * @param  boolean $file
     * @return void
     */
    public function destroy($file = false);

}
