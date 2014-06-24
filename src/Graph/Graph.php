<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Graph
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Graph;

use Pop\Color\Space;
use Pop\Pdf\Pdf;

/**
 * Graph class
 *
 * @category   Pop
 * @package    Pop_Graph
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Graph
{

    /**
     * Constant to force using the GD component
     * if the graph is a bitmap image
     * @var int
     */
    const FORCE_GD = true;

    /**
     * Graph canvas width
     * @var int
     */
    protected $width = 0;

    /**
     * Graph canvas height
     * @var int
     */
    protected $height = 0;

    /**
     * Graph canvas padding
     * @var int
     */
    protected $padding = 50;

    /**
     * Graph graphic adapter interface
     * @var \Pop\Graph\Adapter\AbstractAdapter
     */
    protected $adapter = null;

    /**
     * Available fonts
     * @var array
     */
    protected $fonts = [];

    /**
     * Current font to use
     * @var string
     */
    protected $font = null;

    /**
     * Font size
     * @var int
     */
    protected $fontSize = 12;

    /**
     * Font color
     * @var mixed
     */
    protected $fontColor = null;

    /**
     * Reverse font color
     * @var mixed
     */
    protected $reverseFontColor = null;

    /**
     * Fill color
     * @var mixed
     */
    protected $fillColor = null;

    /**
     * Stroke color
     * @var mixed
     */
    protected $strokeColor = null;

    /**
     * Stroke width
     * @var int
     */
    protected $strokeWidth = 1;

    /**
     * Axis color
     * @var mixed
     */
    protected $axisColor = null;

    /**
     * Axis width
     * @var int
     */
    protected $axisWidth = 2;

    /**
     * Bar width
     * @var int
     */
    protected $barWidth = 50;

    /**
     * Show data text flag
     * @var boolean
     */
    protected $showText = false;

    /**
     * Show X-axis increment lines flag
     * @var boolean
     */
    protected $showX = false;

    /**
     * Show X-axis color
     * @var mixed
     */
    protected $showXColor = null;

    /**
     * Show Y-axis increment lines flag
     * @var boolean
     */
    protected $showY = false;

    /**
     * Show X-axis color
     * @var mixed
     */
    protected $showYColor = null;

    /**
     * Constructor
     *
     * Instantiate the graph object.
     *
     * @param  array   $options
     * @param  boolean $forceGd
     * @throws Exception
     * @return Graph
     */
    public function __construct($options, $forceGd = false)
    {
        if (!isset($options['filename'])) {
            throw new Exception('Error: You must pass a filename in the $options parameter.');
        }

        if (isset($options['width']) && isset($options['height']) && is_numeric($options['width']) && is_numeric($options['height'])) {
            $this->width = $options['width'];
            $this->height = $options['height'];
        } else  {
            throw new Exception('Error: You must either pass a valid width and height or a valid image in the $options parameter.');
        }

        if (isset($options['background']) && is_array($options['background']) && (count($options['background']) == 3)) {
            $background = new Space\Rgb($options['background'][0], $options['background'][1], $options['background'][2]);
        } else {
            $background = null;
        }

        $this->fontColor  = new Space\Rgb(0, 0, 0);
        $this->axisColor  = new Space\Rgb(0, 0, 0);
        $this->showXColor = new Space\Rgb(200, 200, 200);
        $this->showYColor = new Space\Rgb(200, 200, 200);

        if (stripos($options['filename'], '.pdf') !== false) {
            $this->adapter = new Pdf($options['filename'], null, $this->width, $this->height);
        } else {
            if (stripos($options['filename'], '.svg') !== false) {
                $class = '\Pop\Image\Svg';
            } else if (($forceGd) || (!\Pop\Image\Imagick::isInstalled())) {
                $class = '\Pop\Image\Gd';
            } else {
                $class = '\Pop\Image\Imagick';
            }
            $this->adapter = new $class($options['filename'], $this->width, $this->height, $background);
        }
    }

    /**
     * Get the graph graphic adapter
     *
     * @return \Pop\Graph\Adapter\AbstractAdapter
     */
    public function adapter()
    {
        return $this->adapter;
    }

    /**
     * Set the axis options
     *
     * @param  Space\ColorInterface $color
     * @param  int                  $width
     * @return Graph
     */
    public function setAxisOptions(Space\ColorInterface $color = null, $width = 2)
    {
        $this->axisColor = (null === $color) ? new Space\Rgb(0, 0, 0) : $color;
        $this->axisWidth = (int)$width;

        return $this;
    }

    /**
     * Add a font to available fonts
     *
     * @param  string $font
     * @return Graph
     */
    public function addFont($font)
    {
        if ($this->adapter instanceof Pdf) {
            $this->adapter->addFont($font);
            $this->font = $this->adapter->getLastFontName();
            $this->fonts[$this->font] = $this->font;
        } else {
            $this->font = $font;
            if (strpos($this->font, DIRECTORY_SEPARATOR) !== false) {
                $this->font = substr($this->font, (strrpos($this->font, DIRECTORY_SEPARATOR) + 1));
            }
            if (strpos($this->font, '.') !== false) {
                $this->font = substr($this->font, 0, strrpos($this->font, '.'));
            }

            $this->fonts[$this->font] = $font;
        }

        return $this;
    }

    /**
     * Set the font to use from the available fonts
     *
     * @param  string $font
     * @throws Exception
     * @return Graph
     */
    public function setFont($font = null)
    {
        if ((null !== $font) && !array_key_exists($font, $this->fonts)) {
            throw new Exception('That font is not available.');
        }

        $this->font = $font;

        return $this;
    }

    /**
     * Set the font size
     *
     * @param  int $size
     * @return Graph
     */
    public function setFontSize($size)
    {
        $this->fontSize = (int)$size;
        return $this;
    }

    /**
     * Set the font color
     *
     * @param  Space\ColorInterface $color
     * @return Graph
     */
    public function setFontColor(Space\ColorInterface $color)
    {
        $this->fontColor = $color;
        return $this;
    }

    /**
     * Set the reverse font color
     *
     * @param  Space\ColorInterface $color
     * @return Graph
     */
    public function setReverseFontColor(Space\ColorInterface $color)
    {
        $this->reverseFontColor = $color;
        return $this;
    }

    /**
     * Set the fill color
     *
     * @param  Space\ColorInterface $color
     * @return Graph
     */
    public function setFillColor(Space\ColorInterface $color)
    {
        $this->fillColor = $color;
        return $this;
    }

    /**
     * Set the stroke color
     *
     * @param  Space\ColorInterface $color
     * @return Graph
     */
    public function setStrokeColor(Space\ColorInterface $color)
    {
        $this->strokeColor = $color;
        return $this;
    }

    /**
     * Set the stroke width
     *
     * @param  int $width
     * @return Graph
     */
    public function setStrokeWidth($width = 1)
    {
        $this->strokeWidth = $width;
        return $this;
    }

    /**
     * Set the graph canvas padding
     *
     * @param  int $pad
     * @return Graph
     */
    public function setPadding($pad)
    {
        $this->padding = (int)$pad;
        return $this;
    }

    /**
     * Set the bar width
     *
     * @param  int $width
     * @return Graph
     */
    public function setBarWidth($width)
    {
        $this->barWidth = (int)$width;
        return $this;
    }

    /**
     * Set the 'show data text' flag
     *
     * @param  boolean $showText
     * @return Graph
     */
    public function showText($showText)
    {
        $this->showText = (boolean)$showText;
        return $this;
    }

    /**
     * Set the 'show X-axis increment lines' flag
     *
     * @param  boolean                         $showX
     * @param  Space\ColorInterface $color
     * @return Graph
     */
    public function showX($showX, Space\ColorInterface $color = null)
    {
        $this->showX = (boolean)$showX;
        $this->showXColor = (null === $color) ? new Space\Rgb(200, 200, 200) : $color;
        return $this;
    }

    /**
     * Set the 'show Y-axis increment lines' flag
     *
     * @param  boolean                         $showY
     * @param  Space\ColorInterface $color
     * @return Graph
     */
    public function showY($showY, Space\ColorInterface $color = null)
    {
        $this->showY = (boolean)$showY;
        $this->showYColor = (null === $color) ? new Space\Rgb(200, 200, 200) : $color;
        return $this;
    }

    /**
     * Get the width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Get the height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Get the show X flag
     *
     * @return boolean
     */
    public function getShowX()
    {
        return $this->showX;
    }

    /**
     * Get the show Y flag
     *
     * @return boolean
     */
    public function getShowY()
    {
        return $this->showY;
    }

    /**
     * Get the show X color
     *
     * @return \Pop\Color\Space\ColorInterface
     */
    public function getXColor()
    {
        return $this->showXColor;
    }

    /**
     * Get the show Y color
     *
     * @return \Pop\Color\Space\ColorInterface
     */
    public function getYColor()
    {
        return $this->showYColor;
    }

    /**
     * Get the 'show data text' flag
     *
     * @return boolean
     */
    public function getShowText()
    {
        return $this->showText;
    }

    /**
     * Get the axis color
     *
     * @return \Pop\Color\Space\ColorInterface
     */
    public function getAxisColor()
    {
        return $this->axisColor;
    }

    /**
     * Get the axis width
     *
     * @return int
     */
    public function getAxisWidth()
    {
        return $this->axisWidth;
    }

    /**
     * Get the font
     *
     * @return string
     */
    public function getFont()
    {
        return $this->font;
    }

    /**
     * Get the fonts
     *
     * @param  string $font
     * @return string
     */
    public function getFonts($font = null)
    {
        if (null != $font) {
            return (isset($this->fonts[$font])) ? $this->fonts[$font] : null;
        } else {
            return $this->fonts;
        }
    }

    /**
     * Get the font size
     *
     * @return int
     */
    public function getFontSize()
    {
        return $this->fontSize;
    }

    /**
     * Get the font color
     *
     * @return mixed
     */
    public function getFontColor()
    {
        return $this->fontColor;
    }

    /**
     * Get the reverse font color
     *
     * @return mixed
     */
    public function getReverseFontColor()
    {
        return $this->reverseFontColor;
    }

    /**
     * Get the fill color
     *
     * @return mixed
     */
    public function getFillColor()
    {
        return $this->fillColor;
    }

    /**
     * Get the stroke color
     *
     * @return mixed
     */
    public function getStrokeColor()
    {
        return $this->strokeColor;
    }

    /**
     * Get the stroke width
     *
     * @return int
     */
    public function getStrokeWidth()
    {
        return $this->strokeWidth;
    }

    /**
     * Get the graph canvas padding
     *
     * @return int
     */
    public function getPadding()
    {
        return $this->padding;
    }

    /**
     * Get the bar width
     *
     * @return int
     */
    public function getBarWidth()
    {
        return $this->barWidth;
    }

    /**
     * Create a line graph
     *
     * @param  array $dataPoints
     * @param  array $xAxis
     * @param  array $yAxis
     * @return Graph
     */
    public function createLineGraph(array $dataPoints, array $xAxis, array $yAxis)
    {
        $line = new Adapter\Line($this);
        $line->create($dataPoints, $xAxis, $yAxis);

        return $this;
    }

    /**
     * Create a vertical bar graph
     *
     * @param  array $dataPoints
     * @param  array $xAxis
     * @param  array $yAxis
     * @return Graph
     */
    public function createVBarGraph(array $dataPoints, array $xAxis, array $yAxis)
    {
        $vbar = new Adapter\VBar($this);
        $vbar->create($dataPoints, $xAxis, $yAxis);

        return $this;
    }

    /**
     * Create a horizontal bar graph
     *
     * @param  array $dataPoints
     * @param  array $xAxis
     * @param  array $yAxis
     * @return Graph
     */
    public function createHBarGraph(array $dataPoints, array $xAxis, array $yAxis)
    {
        $hbar = new Adapter\HBar($this);
        $hbar->create($dataPoints, $xAxis, $yAxis);

        return $this;
    }

    /**
     * Create a horizontal bar graph
     *
     * @param  array $pie
     * @param  array $percents
     * @param  int   $explode
     * @return Graph
     */
    public function createPieChart(array $pie, array $percents, $explode = 0)
    {
        $piechart = new Adapter\Pie($this);
        $piechart->create($pie, $percents, $explode);

        return $this;
    }

    /**
     * Output the graph
     *
     * @param  boolean $download
     * @return void
     */
    public function output($download = false)
    {
        $this->adapter->output($download);
    }

    /**
     * Save the graph image to disk
     *
     * @param  string $to
     * @param  boolean $append
     * @return Graph
     */
    public function save($to = null, $append = false)
    {
        $this->adapter->save($to, $append);
        return $this;
    }

}
