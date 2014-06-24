<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
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

use Pop\Color\Space;

/**
 * SVG image class
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Svg
{

    /**
     * Linear horizontal gradient type.
     * @var int
     */
    const HORIZONTAL = 1;

    /**
     * Linear vertical gradient type.
     * @var int
     */
    const VERTICAL = 2;

    /**
     * Radial gradient type.
     * @var int
     */
    const RADIAL = 3;

    /**
     * SVG image resource
     * @var \SimpleXMLElement
     */
    protected $resource = null;

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
     * SVG image available clipping paths
     * @var array
     */
    protected $clippingPaths = [];

    /**
     * Current clipping path to use.
     * @var int
     */
    protected $curClippingPath = null;

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
     * Array of allowed file types.
     * @var array
     */
    protected $allowed = ['svg' => 'image/svg+xml'];

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
    protected $extension = 'pdf';

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
     * @var string
     */
    protected $output = null;

    /**
     * Constructor
     *
     * Instantiate an SVG image object based on either a pre-existing SVG image
     * file on disk, or a new SVG image file.
     *
     * @param  string               $svg
     * @param  int                  $w
     * @param  int                  $h
     * @param  Space\ColorInterface $color
     * @throws Exception
     * @return Svg
     */
    public function __construct($svg, $w = null, $h = null, Space\ColorInterface $color = null)
    {
        $this->fullpath  = $svg;
        $parts           = pathinfo($svg);
        $this->size      = (file_exists($svg) ? filesize($svg) : 0);
        $this->dir       = realpath($parts['dirname']);
        $this->basename  = $parts['basename'];
        $this->filename  = $parts['filename'];
        $this->extension = (isset($parts['extension']) && ($parts['extension'] != '')) ? $parts['extension'] : null;

        if ((null === $this->extension) || ($this->extension != 'svg')) {
            throw new Exception('Error: That SVG file does not have the correct extension.');
        } else {
            $this->mime = $this->allowed[$this->extension];
        }

        // If SVG image exists, get image info and store in an array.
        if (file_exists($this->fullpath) && ($this->size > 0)) {
            $this->resource = new \SimpleXMLElement($svg, null, true);
            $w = $this->resource->attributes()->width;
            $h = $this->resource->attributes()->height;
        // If SVG image does not exists, check to make sure the width and height
        // properties of the new SVG image have been passed.
        } else {
            if ((null === $w) || (null === $h)) {
                throw new Exception('Error: You must define a width and height for a new image object.');
            }

            $this->backgroundColor = (null !== $color) ? $color : new Space\Rgb(255, 255, 255);
            $newSvg = "<?xml version=\"1.0\" standalone=\"no\"?>\n<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n<svg width=\"{$w}\" height=\"{$h}\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\">\n    <desc>\n        SVG Image generated by Pop PHP Library\n    </desc>\n</svg>\n";
            $this->resource = new \SimpleXMLElement($newSvg);

            if (null !== $color) {
                $rect = $this->resource->addChild('rect');
                $rect->addAttribute('x', '0' . $this->units);
                $rect->addAttribute('y', '0' . $this->units);
                $rect->addAttribute('width', $w);
                $rect->addAttribute('height', $h);
                $rect->addAttribute('fill', $color->get(3, true));
            }
        }

        if (!is_numeric(substr($w, -1)) && !is_numeric(substr($w, -2, 1))) {
            $unit = substr($w, -2);
            if (in_array($unit, $this->allowedUnits)) {
                $this->units = $unit;
            }
            $this->width  = (float)substr($w, 0, -2);
            $this->height = (float)substr($h, 0, -2);
        } else if (!is_numeric(substr($w, 0, -1)) && (substr($w, 0, -1) == '%')) {
            $this->units  = '%';
            $this->width  = (float)substr($w, 0, -1);
            $this->height = (float)substr($h, 0, -1);
        } else {
            $this->width  = (float)$w;
            $this->height = (float)$h;
        }
    }

    /**
     * Get the SVG image width.
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Get the SVG image height.
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
     * Set the fill color.
     *
     * @param  Space\ColorInterface $color
     * @return Svg
     */
    public function setFillColor(Space\ColorInterface $color = null)
    {
        $this->curGradient = null;
        $this->fillColor = $color;
        return $this;
    }

    /**
     * Set the background color.
     *
     * @param  Space\ColorInterface $color
     * @return Svg
     */
    public function setBackgroundColor(Space\ColorInterface $color = null)
    {
        $this->backgroundColor = $color;
        return $this;
    }

    /**
     * Set the stroke color.
     *
     * @param  Space\ColorInterface $color
     * @return Svg
     */
    public function setStrokeColor(Space\ColorInterface $color = null)
    {
        $this->strokeColor = $color;
        return $this;
    }

    /**
     * Set the stroke width.
     *
     * @param  int $wid
     * @param  int $dash_len
     * @param  int $dash_gap
     * @return Svg
     */
    public function setStrokeWidth($wid = null, $dash_len = null, $dash_gap = null)
    {
        if ((null === $wid) || ($wid == false) || ($wid == 0)) {
            $this->strokeWidth      = null;
            $this->strokeDashLength = null;
            $this->strokeDashGap    = null;
        } else {
            $this->strokeWidth      = $wid;
            $this->strokeDashLength = $dash_len;
            $this->strokeDashGap    = $dash_gap;
        }

        return $this;
    }

    /**
     * Set the opacity.
     *
     * @param  float $opac
     * @return Svg
     */
    public function setOpacity($opac)
    {
        $this->opacity = $opac;
        return $this;
    }

    /**
     * Add a gradient.
     *
     * @param  Space\ColorInterface $color1
     * @param  Space\ColorInterface $color2
     * @param  int                  $type
     * @return Svg
     */
    public function addGradient(Space\ColorInterface $color1, Space\ColorInterface $color2, $type = Svg::HORIZONTAL)
    {
        $this->curGradient = count($this->gradients);
        $defs = $this->resource->addChild('defs');

        switch ($type) {
            case self::HORIZONTAL:
                $grad = $defs->addChild('linearGradient');
                $grad->addAttribute('id', 'grad' . $this->curGradient);
                $grad->addAttribute('x1', '0%');
                $grad->addAttribute('y1', '0%');
                $grad->addAttribute('x2', '100%');
                $grad->addAttribute('y2', '0%');
                break;
            case self::VERTICAL:
                $grad = $defs->addChild('linearGradient');
                $grad->addAttribute('id', 'grad' . $this->curGradient);
                $grad->addAttribute('x1', '0%');
                $grad->addAttribute('y1', '0%');
                $grad->addAttribute('x2', '0%');
                $grad->addAttribute('y2', '100%');
                break;
            case self::RADIAL:
                $grad = $defs->addChild('radialGradient');
                $grad->addAttribute('id', 'grad' . $this->curGradient);
                $grad->addAttribute('cx', '50%');
                $grad->addAttribute('cy', '50%');
                $grad->addAttribute('r', '50%');
                $grad->addAttribute('fx', '50%');
                $grad->addAttribute('fy', '50%');
                break;
        }

        $stop1 = $grad->addChild('stop');
        $stop1->addAttribute('offset', '0%');
        $stop1->addAttribute('style', 'stop-color: ' . $color1->get(3, true) . '; stop-opacity: 1;');

        $stop2 = $grad->addChild('stop');
        $stop2->addAttribute('offset', '100%');
        $stop2->addAttribute('style', 'stop-color: ' . $color2->get(3, true) . '; stop-opacity: 1;');

        return $this;
    }

    /**
     * Set the gradient to use.
     *
     * @param  int $index
     * @return Svg
     */
    public function setGradient($index = null)
    {
        if ((null !== $index) && array_key_exists($index, $this->gradients)) {
            $this->curGradient = $index;
        } else {
            $this->curGradient = null;
        }

        return $this;
    }

    /**
     * Add a clipping rectangle.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return Svg
     */
    public function drawClippingRectangle($x, $y, $w, $h = null)
    {
        $this->curClippingPath = count($this->clippingPaths);
        $defs = $this->resource->addChild('defs');

        $clip = $defs->addChild('clipPath');
        $clip->addAttribute('id', 'clip' . $this->curClippingPath);

        $rect = $clip->addChild('rect');
        $rect->addAttribute('x', $x . $this->units);
        $rect->addAttribute('y', $y . $this->units);
        $rect->addAttribute('width', $w . $this->units);
        $rect->addAttribute('height', ((null === $h) ? $w : $h) . $this->units);

        return $this;
    }

    /**
     * Add a clipping square.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @return Svg
     */
    public function drawClippingSquare($x, $y, $w)
    {
        $this->drawClippingRectangle($x, $y, $w);
        return $this;
    }

    /**
     * Add a clipping ellipse.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return Svg
     */
    public function drawClippingEllipse($x, $y, $w, $h = null)
    {
        $this->curClippingPath = count($this->clippingPaths);
        $defs = $this->resource->addChild('defs');

        $clip = $defs->addChild('clipPath');
        $clip->addAttribute('id', 'clip' . $this->curClippingPath);

        $ellipse = $clip->addChild('ellipse');
        $ellipse->addAttribute('cx', $x . $this->units);
        $ellipse->addAttribute('cy', $y . $this->units);
        $ellipse->addAttribute('rx', $w . $this->units);
        $ellipse->addAttribute('ry', ((null === $h) ? $w : $h) . $this->units);

        return $this;
    }

    /**
     * Add a clipping circle.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @return Svg
     */
    public function drawClippingCircle($x, $y, $w)
    {
        $this->curClippingPath = count($this->clippingPaths);
        $defs = $this->resource->addChild('defs');

        $clip = $defs->addChild('clipPath');
        $clip->addAttribute('id', 'clip' . $this->curClippingPath);

        $circle = $clip->addChild('circle');
        $circle->addAttribute('cx', $x . $this->units);
        $circle->addAttribute('cy', $y . $this->units);
        $circle->addAttribute('r', $w . $this->units);

        return $this;
    }

    /**
     * Add a clipping polygon.
     *
     * @param  array $points
     * @return Svg
     */
    public function drawClippingPolygon($points)
    {
        $this->curClippingPath = count($this->clippingPaths);
        $defs = $this->resource->addChild('defs');

        $clip = $defs->addChild('clipPath');
        $clip->addAttribute('id', 'clip' . $this->curClippingPath);

        $formattedPoints = [];
        foreach ($points as $point) {
            $formattedPoints[] = $point['x'] . ',' . $point['y'];
        }

        $poly = $clip->addChild('polygon');
        $poly->addAttribute('points', implode(' ', $formattedPoints));

        return $this;
    }

    /**
     * Set the clipping path to use.
     *
     * @param  int $index
     * @return Svg
     */
    public function setClippingPath($index = null)
    {
        if ((null !== $index) && array_key_exists($index, $this->clippingPaths)) {
            $this->curClippingPath = $index;
        } else {
            $this->curClippingPath = null;
        }

        return $this;
    }

    /**
     * Create text within the an SVG image object.
     *
     * @param  string     $str
     * @param  int|string $size
     * @param  int|string $x
     * @param  int|string $y
     * @param  string     $font
     * @param  int|string $rotate
     * @param  boolean      $bold
     * @return Svg
     */
    public function text($str, $size, $x, $y, $font = 'Arial', $rotate = null, $bold = false)
    {
        $text = $this->resource->addChild('text', $str);
        $text->addAttribute('x', $x . $this->units);
        $text->addAttribute('y', $y . $this->units);
        $text->addAttribute('font-size', $size);
        $text->addAttribute('font-family', $font);

        if (null !== $this->fillColor) {
            $text->addAttribute('fill', $this->fillColor->get(3, true));
            if ($this->opacity < 1.0) {
                $text->addAttribute('fill-opacity', $this->opacity);
            }
        }

        if (null !== $rotate) {
            $text->addAttribute('transform', 'rotate(' . $rotate . ' ' . $x . ',' . $y .')');
        }
        if ($bold) {
            $text->addAttribute('font-weight', 'bold');
        }

        return $this;
    }

    /**
     * Method to add a line to the image.
     *
     * @param  int $x1
     * @param  int $y1
     * @param  int $x2
     * @param  int $y2
     * @return Svg
     */
    public function drawLine($x1, $y1, $x2, $y2)
    {
        $line = $this->resource->addChild('line');
        $line->addAttribute('x1', $x1 . $this->units);
        $line->addAttribute('y1', $y1 . $this->units);
        $line->addAttribute('x2', $x2 . $this->units);
        $line->addAttribute('y2', $y2 . $this->units);

        $line = $this->setStyles($line);

        return $this;
    }

    /**
     * Method to add a rectangle to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return Svg
     */
    public function drawRectangle($x, $y, $w, $h = null)
    {
        $rect = $this->resource->addChild('rect');
        $rect->addAttribute('x', $x . $this->units);
        $rect->addAttribute('y', $y . $this->units);
        $rect->addAttribute('width', $w . $this->units);
        $rect->addAttribute('height', ((null === $h) ? $w : $h) . $this->units);

        $rect = $this->setStyles($rect);

        return $this;
    }

    /**
     * Method to add a square to the image.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @return Svg
     */
    public function drawSquare($x, $y, $w)
    {
        $this->drawRectangle($x, $y, $w, $w);
        return $this;
    }

    /**
     * Method to add an ellipse to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return Svg
     */
    public function drawEllipse($x, $y, $w, $h = null)
    {
        $ellipse = $this->resource->addChild('ellipse');
        $ellipse->addAttribute('cx', $x . $this->units);
        $ellipse->addAttribute('cy', $y . $this->units);
        $ellipse->addAttribute('rx', $w . $this->units);
        $ellipse->addAttribute('ry', ((null === $h) ? $w : $h) . $this->units);

        $ellipse = $this->setStyles($ellipse);

        return $this;
    }

    /**
     * Method to add a circle to the image.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @return Svg
     */
    public function drawCircle($x, $y, $w)
    {
        $circle = $this->resource->addChild('circle');
        $circle->addAttribute('cx', $x . $this->units);
        $circle->addAttribute('cy', $y . $this->units);
        $circle->addAttribute('r', $w . $this->units);

        $circle = $this->setStyles($circle);

        return $this;
    }

    /**
     * Method to add an arc to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $start
     * @param  int $end
     * @param  int $w
     * @param  int $h
     * @return Svg
     */
    public function drawArc($x, $y, $start, $end, $w, $h = null)
    {
        if (null === $h) {
            $h = $w;
        }

        $sX = round($w * cos($start / 180 * pi()));
        $sY = round($h * sin($start / 180 * pi()));
        $eX = round($w * cos($end / 180 * pi()));
        $eY = round($h * sin($end / 180 * pi()));

        $centerPoint = ['x' => $x, 'y' => $y];
        $startPoint = ['x' => $x + $sX, 'y' => $y + $sY];
        $endPoint = ['x' => $x + $eX, 'y' => $y + $eY];

        $startQuad = $this->getQuadrant($startPoint, $centerPoint);
        $endQuad = $this->getQuadrant($endPoint, $centerPoint);

        $corner1 = ['x' => $this->width, 'y' => $this->height];
        $corner2 = ['x' => 0, 'y' => $this->height];
        $corner3 = ['x' => 0, 'y' => 0];
        $corner4 = ['x' => $this->width, 'y' => 0];

        $polyPoints = [$centerPoint, $startPoint];

        switch ($startQuad) {
            case 1:
                if ($endQuad == 1) {
                    $polyPoints[] = $corner1;
                    $polyPoints[] = ['x' => $endPoint['x'], 'y' => $this->height];
                } else if ($endQuad == 2) {
                    $polyPoints[] = $corner1;
                    $polyPoints[] = $corner2;
                } else if ($endQuad == 3) {
                    $polyPoints[] = $corner1;
                    $polyPoints[] = $corner2;
                    $polyPoints[] = $corner3;
                } else if ($endQuad == 4) {
                    $polyPoints[] = $corner1;
                    $polyPoints[] = $corner2;
                    $polyPoints[] = $corner3;
                    $polyPoints[] = $corner4;
                }
                break;
            case 2:
                if ($endQuad == 1) {
                    $polyPoints[] = $corner2;
                    $polyPoints[] = $corner3;
                    $polyPoints[] = $corner4;
                    $polyPoints[] = $corner1;
                } else if ($endQuad == 2) {
                    $polyPoints[] = $corner2;
                    $polyPoints[] = ['x' => 0, 'y' => $endPoint['y']];
                } else if ($endQuad == 3) {
                    $polyPoints[] = $corner2;
                    $polyPoints[] = $corner3;
                } else if ($endQuad == 4) {
                    $polyPoints[] = $corner2;
                    $polyPoints[] = $corner3;
                    $polyPoints[] = $corner4;
                }
                break;
            case 3:
                if ($endQuad == 1) {
                    $polyPoints[] = $corner3;
                    $polyPoints[] = $corner4;
                    $polyPoints[] = $corner1;
                } else if ($endQuad == 2) {
                    $polyPoints[] = $corner3;
                    $polyPoints[] = $corner4;
                    $polyPoints[] = $corner1;
                    $polyPoints[] = $corner2;
                } else if ($endQuad == 3) {
                    $polyPoints[] = $corner3;
                    $polyPoints[] = ['x' => $endPoint['x'], 'y' => 0];
                } else if ($endQuad == 4) {
                    $polyPoints[] = $corner3;
                    $polyPoints[] = $corner4;
                }
                break;
            case 4:
                if ($endQuad == 1) {
                    $polyPoints[] = $corner4;
                    $polyPoints[] = $corner1;
                } else if ($endQuad == 2) {
                    $polyPoints[] = $corner4;
                    $polyPoints[] = $corner1;
                    $polyPoints[] = $corner2;
                } else if ($endQuad == 3) {
                    $polyPoints[] = $corner4;
                    $polyPoints[] = $corner1;
                    $polyPoints[] = $corner2;
                    $polyPoints[] = $corner3;
                } else if ($endQuad == 4) {
                    $polyPoints[] = $corner4;
                    $polyPoints[] = ['x' => $this->width, 'y' => $endPoint['y']];
                }
                break;
        }

        $polyPoints[] = $endPoint;

        $stamp = rand();

        $defs = $this->resource->addChild('defs');

        $clip = $defs->addChild('clipPath');
        $clip->addAttribute('id', 'polyClip' . $stamp);

        $formattedPoints = [];
        foreach ($polyPoints as $point) {
            $formattedPoints[] = $point['x'] . ',' . $point['y'];
        }
        $poly = $clip->addChild('polygon');
        $poly->addAttribute('points', implode(' ', $formattedPoints));

        $ellipse = $this->resource->addChild('ellipse');
        $ellipse->addAttribute('style', 'clip-path: url(#polyClip' . $stamp .');');
        $ellipse->addAttribute('cx', $x . $this->units);
        $ellipse->addAttribute('cy', $y . $this->units);
        $ellipse->addAttribute('rx', $w . $this->units);
        $ellipse->addAttribute('ry', ((null === $h) ? $w : $h) . $this->units);

        $ellipse = $this->setStyles($ellipse);

        return $this;
    }

    /**
     * Method to add a polygon to the image.
     *
     * @param  array $points
     * @return Svg
     */
    public function drawPolygon($points)
    {
        $formattedPoints = [];
        foreach ($points as $point) {
            $formattedPoints[] = $point['x'] . ',' . $point['y'];
        }
        $poly = $this->resource->addChild('polygon');
        $poly->addAttribute('points', implode(' ', $formattedPoints));

        $poly = $this->setStyles($poly);

        return $this;
    }

    /**
     * Method to add a border to the image.
     *
     * @param  int $w
     * @return Svg
     */
    public function border($w)
    {
        $rect = $this->resource->addChild('rect');
        $rect->addAttribute('x', '0px');
        $rect->addAttribute('y', '0px');
        $rect->addAttribute('width', $this->width . $this->units);
        $rect->addAttribute('height', $this->height . $this->units);

        $color = (null !== $this->strokeColor) ? $this->strokeColor : new Space\Rgb(0, 0, 0);

        $rect->addAttribute('stroke', $color->get(3, true));
        $rect->addAttribute('stroke-width', ($w * 2) . $this->units);
        if ((null !== $this->strokeDashLength) && (null !== $this->strokeDashGap)) {
            $rect->addAttribute('stroke-dasharray', $this->strokeDashLength . $this->units . ',' . $this->strokeDashGap . $this->units);
        }

        $rect->addAttribute('fill', 'none');

        return $this;
    }

    /**
     * Method to output the SVG image.
     *
     * @param  boolean $download
     * @return void
     */
    public function output($download = false)
    {
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->resource->asXML());

        $this->output = $dom->saveXML();

        // Determine if the force download argument has been passed.
        $attach = ($download) ? 'attachment; ' : null;
        $headers = array(
            'Content-type' => $this->mime,
            'Content-disposition' => $attach . 'filename=' . $this->basename
        );

        if ($_SERVER['SERVER_PORT'] == 443) {
            $headers['Expires']       = 0;
            $headers['Cache-Control'] = 'private, must-revalidate';
            $headers['Pragma']        = 'cache';
        }

        // Send the headers and output the image
        header('HTTP/1.1 200 OK');
        foreach ($headers as $name => $value) {
            header($name . ": " . $value);
        }

        echo $this->output;
    }

    /**
     * Save the SVG image object to disk.
     *
     * @param  string  $to
     * @return Imagick
     */
    public function save($to = null)
    {
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->resource->asXML());

        $this->output = $dom->saveXML();

        $svg = (null !== $to) ? $to : $this->fullpath;
        file_put_contents($svg, $this->output);

        return $this;
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
     * Method to set the styles.
     *
     * @param  \SimpleXMLElement $obj
     * @return \SimpleXMLElement
     */
    protected function setStyles($obj)
    {
        if (null !== $this->curClippingPath) {
            $obj->addAttribute('style', 'clip-path: url(#clip' . $this->curClippingPath .');');
        }

        if (null !== $this->curGradient) {
            $obj->addAttribute('fill', 'url(#grad' . $this->curGradient . ')');
        } else if (null !== $this->fillColor) {
            $obj->addAttribute('fill', $this->fillColor->get(3, true));
            if ($this->opacity < 1.0) {
                $obj->addAttribute('fill-opacity', $this->opacity);
            }
        }
        if (null !== $this->strokeColor) {
            $obj->addAttribute('stroke', $this->strokeColor->get(3, true));
            $obj->addAttribute('stroke-width', ((null !== $this->strokeWidth) ? $this->strokeWidth : 1) . $this->units);
            if ((null !== $this->strokeDashLength) && (null !== $this->strokeDashGap)) {
                $obj->addAttribute('stroke-dasharray', $this->strokeDashLength . $this->units . ',' . $this->strokeDashGap . $this->units);
            }
        }

        return $obj;
    }

    /**
     * Method to calculate which quadrant a point is in.
     *
     * @param  array $point
     * @param  array $center
     * @return int
     */
    protected function getQuadrant($point, $center)
    {
        $quad = 0;

        if ($point['x'] >= $center['x']) {
            $quad = ($point['y'] <= $center['y']) ? 4 : 1;
        } else {
            $quad = ($point['y'] <= $center['y']) ? 3 : 2;
        }

        return $quad;
    }

}
