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
 * Image interface
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
     * Get the image full path
     *
     * @return string
     */
    public function getFullPath();

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
     * Set the fill color.
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return ImageInterface
     */
    public function setFillColor($r = 0, $g = 0, $b = 0);

    /**
     * Set the background color.
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return ImageInterface
     */
    public function setBackgroundColor($r = 0, $g = 0, $b = 0);

    /**
     * Set the stroke color.
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return ImageInterface
     */
    public function setStrokeColor($r = 0, $g = 0, $b = 0);

    /**
     * Set the stroke width.
     *
     * @param  mixed $wid
     * @return ImageInterface
     */
    public function setStrokeWidth($wid = null);

    /**
     * Set the opacity.
     *
     * @param  mixed $opac
     * @return ImageInterface
     */
    public function setOpacity($opac);


    /**
     * Method to add a line to the image.
     *
     * @param  int $x1
     * @param  int $y1
     * @param  int $x2
     * @param  int $y2
     * @return ImageInterface
     */
    public function drawLine($x1, $y1, $x2, $y2);

    /**
     * Method to add a rectangle to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return ImageInterface
     */
    public function drawRectangle($x, $y, $w, $h = null);

    /**
     * Method to add a square to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @return ImageInterface
     */
    public function drawSquare($x, $y, $w);

    /**
     * Method to add an ellipse to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return ImageInterface
     */
    public function drawEllipse($x, $y, $w, $h = null);

    /**
     * Method to add a circle to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @return ImageInterface
     */
    public function drawCircle($x, $y, $w);

    /**
     * Method to add an arc to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $start
     * @param  int $end
     * @param  int $w
     * @param  int $h
     * @return ImageInterface
     */
    public function drawArc($x, $y, $start, $end, $w, $h = null);

    /**
     * Method to add a polygon to the image.
     *
     * @param  array $points
     * @return ImageInterface
     */
    public function drawPolygon($points);

    /**
     * Method to add a border to the image.
     *
     * @param  int $w
     * @return ImageInterface
     */
    public function border($w);

    /**
     * Create text within the an image object.
     *
     * @param  string $str
     * @param  int    $size
     * @param  array  $options
     * @return ImageInterface
     */
    public function text($str, $size, array $options = []);

    /**
     * Destroy the image object and the related image file directly.
     *
     * @param  boolean $file
     * @return void
     */
    public function destroy($file = false);

    /**
     * Output the image object directly.
     *
     * @param  boolean $download
     * @return ImageInterface
     */
    public function output($download = false);

}