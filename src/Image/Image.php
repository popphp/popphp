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
class Image implements Adapter\AdapterInterface
{

    /**
     * Image adapter
     * @var Adapter\AbstractAdapter
     */
    protected $adapter = null;

    /**
     * Image filter object
     * @var Filter\Filter
     */
    protected $filter = null;

    /**
     * Constructor
     *
     * Instantiate an image object
     *
     * @param  Adapter\AbstractAdapter
     * @return Image
     */
    public function __construct(Adapter\AbstractAdapter $adapter)
    {
        $this->adapter = $adapter;
        $this->filter = new Filter\Filter($adapter);
    }

    /**
     * Get the image adapter
     *
     * @return Adapter\AbstractAdapter
     */
    public function adapter()
    {
        return $this->adapter;
    }

    /**
     * Get the image filter object
     *
     * @return Filter\Filter
     */
    public function filter()
    {
        return $this->filter;
    }

    /**
     * Get the image resource
     *
     * @return resource
     */
    public function resource()
    {
        return $this->adapter->resource();
    }

    /**
     * Get the image full path
     *
     * @return string
     */
    public function getFullpath()
    {
        return $this->adapter->getFullpath();
    }

    /**
     * Get the image directory
     *
     * @return string
     */
    public function getDir()
    {
        return $this->adapter->getDir();
    }

    /**
     * Get the image basename
     *
     * @return string
     */
    public function getBasename()
    {
        return $this->adapter->getBasename();
    }

    /**
     * Get the image filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->adapter->getFilename();
    }

    /**
     * Get the image extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->adapter->getExtension();
    }

    /**
     * Get the image size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->adapter->getSize();
    }

    /**
     * Get the image mime type
     *
     * @return string
     */
    public function getMime()
    {
        return $this->adapter->getMime();
    }

    /**
     * Get the image width.
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->adapter->getWidth();
    }

    /**
     * Get the image height.
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->adapter->getHeight();
    }

    /**
     * Resize the image object to the width parameter passed.
     *
     * @param  int $w
     * @return Image
     */
    public function resizeToWidth($w)
    {
        $this->adapter->resizeToWidth($w);
        return $this;
    }

    /**
     * Resize the image object to the height parameter passed.
     *
     * @param  int $h
     * @return Image
     */
    public function resizeToHeight($h)
    {
        $this->adapter->resizeToHeight($h);
        return $this;
    }

    /**
     * Resize the image object, allowing for the largest dimension to be scaled
     * to the value of the $px argument.
     *
     * @param  int $px
     * @return Image
     */
    public function resize($px)
    {
        $this->adapter->resize($px);
        return $this;
    }

    /**
     * Scale the image object.
     *
     * @param  float $scale
     * @return Image
     */
    public function scale($scale)
    {
        $this->adapter->scale($scale);
        return $this;
    }

    /**
     * Crop the image object to a image whose dimensions are based on the
     * value of the $wid and $hgt argument.
     *
     * @param  int $w
     * @param  int $h
     * @param  int $x
     * @param  int $y
     * @return Image
     */
    public function crop($w, $h, $x = 0, $y = 0)
    {
        $this->adapter->crop($w, $h, $x, $y);
        return $this;
    }

    /**
     * Crop the image object to a square image.
     *
     * @param  int $px
     * @param  int $x
     * @param  int $y
     * @return Image
     */
    public function cropThumb($px, $x = 0, $y = 0)
    {
        $this->adapter->cropThumb($px, $x, $y);
        return $this;
    }

    /**
     * Save the image object to disk.
     *
     * @param  string $to
     * @return void
     */
    public function save($to = null)
    {
        $this->adapter->save($to);
    }

    /**
     * Output the image object directly.
     *
     * @param  boolean $download
     * @return void
     */
    public function output($download = false)
    {
        $this->adapter->output($download);
    }

    /**
     * Destroy the image object and the related image file directly.
     *
     * @param  boolean $file
     * @return void
     */
    public function destroy($file = false)
    {
        $this->adapter->destroy($file);
    }

}
