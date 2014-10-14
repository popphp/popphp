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
 * SVG image class
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Svg
{

    /**
     * SVG image resource
     * @var \SimpleXMLElement
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
     * SVG image file extension, i.e. 'ext'
     * @var string
     */
    protected $extension = null;

    /**
     * SVG image file size in bytes
     * @var int
     */
    protected $size = 0;

    /**
     * SVG image file mime type
     * @var string
     */
    protected $mime = 'image/svg+xml';

    /**
     * SVG image file output buffer
     * @var mixed
     */
    protected $output = null;

    /**
     * SVG image width
     * @var int
     */
    protected $width = null;

    /**
     * SVG image height
     * @var int
     */
    protected $height = null;

    /**
     * Array of allowed image types.
     * @var array
     */
    protected $allowed = [
        'svg' => 'image/svg+xml'
    ];

    /**
     * SVG image fill color
     * @var mixed
     */
    protected $fillColor = null;

    /**
     * SVG image background color
     * @var mixed
     */
    protected $backgroundColor = null;

    /**
     * SVG image stroke color
     * @var mixed
     */
    protected $strokeColor = null;

    /**
     * SVG image stroke width
     * @var array
     */
    protected $strokeWidth = null;

    /**
     * Stroke dash length
     * @var int
     */
    protected $strokeDashLength = null;

    /**
     * Stroke dash gap
     * @var int
     */
    protected $strokeDashGap = null;

    /**
     * SVG image available gradients
     * @var array
     */
    protected $gradients = [];

    /**
     * Current gradient to use.
     * @var int
     */
    protected $curGradient = null;

    /**
     * SVG image color opacity
     * @var float
     */
    protected $opacity = 1.0;

    /**
     * SVG image units
     * @var string
     */
    protected $units = null;

    /**
     * Array of allowed units.
     * @var array
     */
    protected $allowedUnits = ['em', 'ex', 'px', 'pt', 'pc', 'cm', 'mm', 'in', '%'];

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
     * Instantiate an SVG image file object based on either a pre-existing
     * image file on disk, or a new SVG image file.
     *
     * @param  string $img
     * @param  int    $w
     * @param  int    $h
     * @throws Exception
     * @return Svg
     */
    public function __construct($img = null, $w = null, $h = null)
    {
        // If the arguments passed are $img, $w, $h
        if ((null !== $img) && (stripos($img, '.svg') !== false)) {
            $this->setImage($img);
            if ((null !== $w) && (null !== $h)) {
                $this->parseDimensions($w, $h);
            }
            // Else, if the arguments passed are $w, $h, $img
        } else if ((null !== $h) && (stripos($h, '.svg') !== false)) {
            $imgName = (null !== $h) ? $h : 'pop-svg-image-' . time() . '.svg';
            $this->parseDimensions($img, $w);
            $this->setImage($imgName);
        }

        // If image exists
        if (file_exists($this->fullpath) && ($this->size > 0)) {
            $this->load($this->fullpath);
        // Else, if image does not exists
        } else if ((null !== $this->width) && (null !== $this->height)) {
            $this->create($this->width, $this->height, $this->basename);
        }
    }

    /**
     * Load an existing SVG image resource
     *
     * @param  string $image
     * @return Svg
     */
    public function load($image)
    {
        $this->resource = new \SimpleXMLElement($image, null, true);
        $this->parseDimensions($this->resource->attributes()->width, $this->resource->attributes()->height);
        return $this;
    }

    /**
     * Create a new SVG image resource
     *
     * @param  int    $width
     * @param  int    $height
     * @param  string $image
     * @return Svg
     */
    public function create($width, $height, $image = null)
    {
        $this->parseDimensions($width, $height);
        $svg = '<?xml version="1.0" standalone="no"?>' . PHP_EOL . '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" ' .
            '"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">' . PHP_EOL . '<svg width="' . $width . '" height="' . $height .
            '" version="1.1" xmlns="http://www.w3.org/2000/svg">' . PHP_EOL . '    <desc>' . PHP_EOL .
            '        SVG Image generated by Pop PHP Framework' . PHP_EOL . '    </desc>' . PHP_EOL . '</svg>' . PHP_EOL;

        $this->resource = new \SimpleXMLElement($svg);

        if (null !== $image) {
            $this->setImage($image);
        }

        return $this;
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
     * Get the SVG image units.
     *
     * @return string
     */
    public function getUnits()
    {
        return $this->units;
    }

    /**
     * Add a gradient
     *
     * @param  array  $color1
     * @param  array  $color2
     * @param  float  $opacity
     * @return Svg
     */
    public function addRadialGradient(array $color1, array $color2, $opacity = 1.0)
    {
        $this->curGradient = count($this->gradients);
        $this->gradients[] = $this->curGradient;
        $defs = $this->resource->addChild('defs');

        $grad = $defs->addChild('radialGradient');
        $grad->addAttribute('id', 'grad' . $this->curGradient);
        $grad->addAttribute('cx', '50%');
        $grad->addAttribute('cy', '50%');
        $grad->addAttribute('r', '50%');
        $grad->addAttribute('fx', '50%');
        $grad->addAttribute('fy', '50%');

        $stop1 = $grad->addChild('stop');
        $stop1->addAttribute('offset', '0%');
        $stop1->addAttribute('style', 'stop-color: rgb(' . $color1[0] . ',' . $color1[1] . ',' . $color1[2] . '); stop-opacity: ' . $opacity . ';');

        $stop2 = $grad->addChild('stop');
        $stop2->addAttribute('offset', '100%');
        $stop2->addAttribute('style', 'stop-color: rgb(' . $color2[0] . ',' . $color2[1] . ',' . $color2[2] . '); stop-opacity: ' . $opacity . ';');

        return $this;
    }

    /**
     * Add a gradient
     *
     * @param  array   $color1
     * @param  array   $color2
     * @param  float   $opacity
     * @param  boolean $vertical
     * @return Svg
     */
    public function addLinearGradient(array $color1, array $color2, $opacity = 1.0, $vertical = true)
    {
        $this->curGradient = count($this->gradients);
        $this->gradients[] = $this->curGradient;
        $defs = $this->resource->addChild('defs');

        if ($vertical) {
            $grad = $defs->addChild('linearGradient');
            $grad->addAttribute('id', 'grad' . $this->curGradient);
            $grad->addAttribute('x1', '0%');
            $grad->addAttribute('y1', '0%');
            $grad->addAttribute('x2', '0%');
            $grad->addAttribute('y2', '100%');
        } else {
            $grad = $defs->addChild('linearGradient');
            $grad->addAttribute('id', 'grad' . $this->curGradient);
            $grad->addAttribute('x1', '0%');
            $grad->addAttribute('y1', '0%');
            $grad->addAttribute('x2', '100%');
            $grad->addAttribute('y2', '0%');
        }

        $stop1 = $grad->addChild('stop');
        $stop1->addAttribute('offset', '0%');
        $stop1->addAttribute('style', 'stop-color: rgb(' . $color1[0] . ',' . $color1[1] . ',' . $color1[2] . '); stop-opacity: ' . $opacity . ';');

        $stop2 = $grad->addChild('stop');
        $stop2->addAttribute('offset', '100%');
        $stop2->addAttribute('style', 'stop-color: rgb(' . $color2[0] . ',' . $color2[1] . ',' . $color2[2] . '); stop-opacity: ' . $opacity . ';');

        return $this;
    }

    /**
     * Get the gradients
     *
     * @return array
     */
    public function getGradients()
    {
        return $this->gradients;
    }

    /**
     * Get the number of gradients
     *
     * @return int
     */
    public function getNumberOfGradients()
    {
        return count($this->gradients);
    }

    /**
     * Get the current gradient index
     *
     * @return mixed
     */
    public function getCurGradient()
    {
        return $this->curGradient;
    }

    /**
     * Get the current gradient index
     *
     * @param  mixed $grad
     * @return Svg
     */
    public function setCurGradient($grad)
    {
        if (in_array($grad, $this->gradients) || (null === $grad)) {
            $this->curGradient = $grad;
        }
        return $this;
    }

    /**
     * Get the image draw object
     *
     * @return Draw\DrawInterface
     */
    public function draw()
    {
        if (null === $this->draw) {
            $this->draw = new Draw\Svg($this);
        }
        if (null === $this->draw->getImage()) {
            $this->draw->setImage($this);
        }
        return $this->draw;
    }

    /**
     * Get the image effect object
     *
     * @return Effect\EffectInterface
     */
    public function effect()
    {
        if (null === $this->effect) {
            $this->effect = new Effect\Svg($this);
        }
        if (null === $this->effect->getImage()) {
            $this->effect->setImage($this);
        }
        return $this->effect;
    }

    /**
     * Get the image type object
     *
     * @return Type\TypeInterface
     */
    public function type()
    {
        if (null === $this->type) {
            $this->type = new Type\Svg($this);
        }
        if (null === $this->type->getImage()) {
            $this->type->setImage($this);
        }
        return $this->type;
    }

    /**
     * Save the image object to disk.
     *
     * @param  string $to
     * @return void
     */
    public function save($to = null)
    {
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;
        $dom->loadXML($this->resource->asXML());

        $this->output = $dom->saveXML();

        file_put_contents(((null === $to) ? $this->fullpath : $to), $this->output);
    }

    /**
     * Output the image object directly.
     *
     * @param  boolean $download
     * @param  boolean $sendHeaders
     * @throws Exception
     * @return void
     */
    public function output($download = false, $sendHeaders = true)
    {
        // Determine if the force download argument has been passed.
        $attach = ($download) ? 'attachment; ' : null;
        $headers = [
            'Content-type'        => $this->mime,
            'Content-disposition' => $attach . 'filename=' . $this->basename
        ];

        if (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443)) {
            $headers['Expires']       = 0;
            $headers['Cache-Control'] = 'private, must-revalidate';
            $headers['Pragma']        = 'cache';
        }

        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;
        $dom->loadXML($this->resource->asXML());

        $this->output = $dom->saveXML();

        if (null === $this->output) {
            throw new Exception('Error: The image resource has not been properly created.');
        }

        // Send the headers and output the image
        if (!headers_sent() && ($sendHeaders)) {
            header('HTTP/1.1 200 OK');
            foreach ($headers as $name => $value) {
                header($name . ": " . $value);
            }
        }

        echo $this->output;
    }

    /**
     * Destroy the image object and the related image file directly.
     *
     * @param  boolean $delete
     * @return void
     */
    public function destroy($delete = false)
    {
        $this->resource = null;
        $this->output   = null;

        clearstatcache();

        // If the $delete flag is passed, delete the image file.
        if (($delete) && file_exists($this->fullpath)) {
            unlink($this->fullpath);
        }
    }

    /**
     * To string method to output the image
     *
     * @return string
     */
    public function __toString()
    {
        $this->output();
        return '';
    }

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

    /**
     * Parse and set the image dimensions and units
     *
     * @param  mixed $w
     * @param  mixed $h
     * @return void
     */
    protected function parseDimensions($w, $h)
    {
        if (is_numeric($w) && is_numeric($h)) {
            $this->width  = $w;
            $this->height = $h;
        } else {
            foreach ($this->allowedUnits as $unit) {
                if ((stripos($w, $unit) !== false) && (stripos($w, $unit) !== false)) {
                    $this->units  = $unit;
                    $this->width  = substr($w, 0, strpos($w, $unit));
                    $this->height = substr($h, 0, strpos($h, $unit));
                }
            }
        }
    }

}
