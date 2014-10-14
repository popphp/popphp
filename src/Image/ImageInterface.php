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
namespace Pop\Image;

/**
 * Image interface
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
interface ImageInterface
{

    /**
     * Create a new image resource
     *
     * @param  int    $width
     * @param  int    $height
     * @param  string $image
     * @return ImageInterface
     */
    public function create($width, $height, $image = null);

    /**
     * Load an existing image as a resource
     *
     * @param  string $image
     * @throws Exception
     * @return ImageInterface
     */
    public function load($image);

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
     * @param  boolean $delete
     * @return void
     */
    public function destroy($delete = false);

}
