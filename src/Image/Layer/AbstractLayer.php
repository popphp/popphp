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
abstract class AbstractLayer implements LayerInterface
{

    /**
     * Image object
     * @var \Pop\Image\AbstractImage
     */
    protected $image = null;

    /**
     * Opacity
     * @var mixed
     */
    protected $opacity = 100;

    /**
     * Constructor
     *
     * Instantiate an image object
     *
     * @param  \Pop\Image\AbstractImage
     * @return AbstractLayer
     */
    public function __construct(\Pop\Image\AbstractImage $image = null)
    {
        if (null !== $image) {
            $this->setImage($image);
        }
    }

    /**
     * Get the image object
     *
     * @return \Pop\Image\AbstractImage
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Get the opacity
     *
     * @return mixed
     */
    public function getOpacity()
    {
        return $this->opacity;
    }

    /**
     * Set the image object
     *
     * @param  \Pop\Image\AbstractImage
     * @return AbstractLayer
     */
    public function setImage(\Pop\Image\AbstractImage $image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * Set the opacity
     *
     * @param  mixed $opacity
     * @return AbstractLayer
     */
    abstract public function setOpacity($opacity);

}
