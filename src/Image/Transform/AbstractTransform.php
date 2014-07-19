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
namespace Pop\Image\Transform;

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
abstract class AbstractTransform implements TransformInterface
{

    /**
     * Image object
     * @var \Pop\Image\AbstractImage
     */
    protected $image = null;

    /**
     * Constructor
     *
     * Instantiate an image object
     *
     * @param  \Pop\Image\AbstractImage
     * @return AbstractTransform
     */
    public function __construct(\Pop\Image\AbstractImage $image)
    {
        $this->image = $image;
    }

}
