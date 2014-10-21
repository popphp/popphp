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
namespace Pop\Image\Factory;

/**
 * Factory interface
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
interface ImageFactoryInterface
{

    /**
     * Load an existing image as a resource and return the image object
     *
     * @param  string $image
     * @return mixed
     */
    public function load($image);

    /**
     * Create a new image resource and return the image object
     *
     * @param  int    $width
     * @param  int    $height
     * @param  string $image
     * @return mixed
     */
    public function create($width, $height, $image = null);

}
