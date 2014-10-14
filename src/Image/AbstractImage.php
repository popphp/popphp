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
 * Abstract image class
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractImage implements ImageInterface
{

    /**
     * Image resource
     * @var mixed
     */
    protected $resource = null;

    /**
     * Full path of image file, i.e. '/path/to/image.ext'
     * @var string
     */
    protected $fullpath = null;

    /**
     * Full, absolute directory of the image file, i.e. '/some/dir/'
     * @var string
     */
    protected $dir = null;

    /**
     * Full basename of image file, i.e. 'image.ext'
     * @var string
     */
    protected $basename = null;

    /**
     * Full filename of image file, i.e. 'image'
     * @var string
     */
    protected $filename = null;

    /**
     * Image file extension, i.e. 'ext'
     * @var string
     */
    protected $extension = null;

    /**
     * Image file size in bytes
     * @var int
     */
    protected $size = 0;

    /**
     * Image file mime type
     * @var string
     */
    protected $mime = null;

    /**
     * Image file output buffer
     * @var mixed
     */
    protected $output = null;

    /**
     * Image width
     * @var int
     */
    protected $width = null;

    /**
     * Image height
     * @var int
     */
    protected $height = null;

    /**
     * Array of allowed image types.
     * @var array
     */
    protected $allowed = [];

    /**
     * Image draw object
     * @var Draw\DrawInterface
     */
    protected $draw = null;

    /**
     * Image effect object
     * @var Effect\EffectInterface
     */
    protected $effect = null;

    /**
     * Image type object
     * @var Type\TypeInterface
     */
    protected $type = null;

    /**
     * Constructor
     *
     * Instantiate an image object based on either a pre-existing image
     * file on disk, or a new image file.
     *
     * @param  string $img
     * @param  int    $w
     * @param  int    $h
     * @return AbstractImage
     */
    public function __construct($img = null, $w = null, $h = null)
    {
        // If the arguments passed are $img, $w, $h
        if ((null !== $img) && !is_numeric($img) && is_string($img)) {
            $this->setImage($img);
            if ((null !== $w) && (null !== $h) && is_numeric($w) && is_numeric($h)) {
                $this->width  = $w;
                $this->height = $h;
            }
        // Else, if the arguments passed are $w, $h, $img
        } else if ((null !== $img) && (null !== $w) && is_numeric($img) && is_numeric($w)) {
            $this->width  = $img;
            $this->height = $w;
            $imgName      = ((null !== $h) && !is_numeric($h) && is_string($h)) ? $h : 'pop-image-' . time() . '.jpg';
            $this->setImage($imgName);
        }
    }

    /**
     * Get the image resource
     *
     * @return resource
     */
    public function resource()
    {
        return $this->resource;
    }

    /**
     * Get the image full path
     *
     * @return string
     */
    public function getFullpath()
    {
        return $this->fullpath;
    }

    /**
     * Get the image directory
     *
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * Get the image basename
     *
     * @return string
     */
    public function getBasename()
    {
        return $this->basename;
    }

    /**
     * Get the image filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get the image extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Get the image size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Get the image mime type
     *
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * Get the image width.
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Get the image height.
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set the image draw object
     *
     * @param  Draw\DrawInterface $draw
     * @return AbstractImage
     */
    public function setDraw(Draw\DrawInterface $draw)
    {
        $this->draw = $draw;
        return $this;
    }

    /**
     * Set the image effect object
     *
     * @param  Effect\EffectInterface $effect
     * @return AbstractImage
     */
    public function setEffect(Effect\EffectInterface $effect)
    {
        $this->effect = $effect;
        return $this;
    }

    /**
     * Set the image type object
     *
     * @param  Type\TypeInterface $type
     * @return AbstractImage
     */
    public function setType(Type\TypeInterface $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get the image draw object
     *
     * @return Draw\DrawInterface
     */
    abstract public function draw();

    /**
     * Get the image effect object
     *
     * @return Effect\EffectInterface
     */
    abstract public function effect();

    /**
     * Get the image type object
     *
     * @return Type\TypeInterface
     */
    abstract public function type();

    /**
     * Create a new image resource
     *
     * @param  int    $width
     * @param  int    $height
     * @param  string $image
     * @return AbstractImage
     */
    abstract public function create($width, $height, $image = null);

    /**
     * Load an existing image as a resource
     *
     * @param  string $image
     * @throws Exception
     * @return AbstractImage
     */
    abstract public function load($image);

    /**
     * Save the image object to disk.
     *
     * @param  string $to
     * @return void
     */
    abstract public function save($to = null);

    /**
     * Output the image object directly.
     *
     * @param  boolean $download
     * @return void
     */
    abstract public function output($download = false);

    /**
     * Destroy the image object and the related image file directly.
     *
     * @param  boolean $delete
     * @return void
     */
    abstract public function destroy($delete = false);

    /**
     * Set the image properties
     *
     * @param  string $img
     * @throws Exception
     * @return void
     */
    protected function setImage($img)
    {
        $this->fullpath  = $img;
        $parts           = pathinfo($img);
        $this->size      = (file_exists($img) ? filesize($img) : 0);
        $this->dir       = realpath($parts['dirname']);
        $this->basename  = $parts['basename'];
        $this->filename  = $parts['filename'];
        $this->extension = (isset($parts['extension']) && ($parts['extension'] != '')) ? $parts['extension'] : null;

        if ((null === $this->extension) || (!isset($this->allowed[strtolower($this->extension)]))) {
            throw new Exception('Error: That image file does not have the correct extension.');
        } else {
            $this->mime = $this->allowed[strtolower($this->extension)];
        }
    }

}
