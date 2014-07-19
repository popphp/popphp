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
namespace Pop\Image\Filter;

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
class Filter
{

    /**
     * Image adapter
     * @var \Pop\Image\Adapter\AbstractAdapter
     */
    protected $adapter = null;

    /**
     * Constructor
     *
     * Instantiate an image object
     *
     * @param  \Pop\Image\Adapter\AbstractAdapter
     * @return Filter
     */
    public function __construct(\Pop\Image\Adapter\AbstractAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Blur the image
     *
     * @param  int $amount
     * @param  int $type
     * @return Filter
     */
    public function blur($amount, $type = IMG_FILTER_GAUSSIAN_BLUR)
    {
        for ($i = 1; $i <= $amount; $i++) {
            imagefilter($this->adapter->resource(), $type);
        }

        return $this;
    }

}
