<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Pdf\Type;

/**
 * Pdf type class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Type extends \Pop\Pdf\AbstractEffect
{

    /**
     * PDF text parameters.
     * @var array
     */
    protected $textParams = [
        'c'    => 0,
        'w'    => 0,
        'h'    => 100,
        'v'    => 100,
        'rot'  => 0,
        'rend' => 0
    ];

    /**
     * Standard PDF fonts with their approximate character width and height factors.
     * @var array
     */
    protected $standardFonts = [
        'Arial'                    => ['width_factor' => 0.5, 'height_factor' => 1],
        'Arial,Italic'             => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'Arial,Bold'               => ['width_factor' => 0.55, 'height_factor' => 1.12],
        'Arial,BoldItalic'         => ['width_factor' => 0.55, 'height_factor' => 1.12],
        'Courier'                  => ['width_factor' => 0.65, 'height_factor' => 1],
        'CourierNew'               => ['width_factor' => 0.65, 'height_factor' => 1],
        'Courier-Oblique'          => ['width_factor' => 0.65, 'height_factor' => 1],
        'CourierNew,Italic'        => ['width_factor' => 0.65, 'height_factor' => 1],
        'Courier-Bold'             => ['width_factor' => 0.65, 'height_factor' => 1],
        'CourierNew,Bold'          => ['width_factor' => 0.65, 'height_factor' => 1],
        'Courier-BoldOblique'      => ['width_factor' => 0.65, 'height_factor' => 1],
        'CourierNew,BoldItalic'    => ['width_factor' => 0.65, 'height_factor' => 1],
        'Helvetica'                => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'Helvetica-Oblique'        => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'Helvetica-Bold'           => ['width_factor' => 0.55, 'height_factor' => 1.12],
        'Helvetica-BoldOblique'    => ['width_factor' => 0.55, 'height_factor' => 1.12],
        'Symbol'                   => ['width_factor' => 0.85, 'height_factor' => 1.12],
        'Times-Roman'              => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'Times-Bold'               => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'Times-Italic'             => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'Times-BoldItalic'         => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'TimesNewRoman'            => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'TimesNewRoman,Italic'     => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'TimesNewRoman,Bold'       => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'TimesNewRoman,BoldItalic' => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'ZapfDingbats'             => ['width_factor' => 0.75, 'height_factor' => 1.12]
    ];

    /**
     * Current fonts added to the PDF
     * @var array
     */
    protected $fonts = [];

    /**
     * Last font name
     * @var string
     */
    protected $lastFontName = null;

    /**
     * Type font size
     * @var int
     */
    protected $size = 12;

    /**
     * Type X-position
     * @var int
     */
    protected $x = 0;

    /**
     * Type Y-position
     * @var int
     */
    protected $y = 0;


    /**
     * Set the font size
     *
     * @param  int $size
     * @return Type
     */
    public function size($size)
    {
        $this->size = (int)$size;
        return $this;
    }

    /**
     * Set the X-position
     *
     * @param  int $x
     * @return Type
     */
    public function x($x)
    {
        $this->x = (int)$x;
        return $this;
    }

    /**
     * Set the Y-position
     *
     * @param  int $y
     * @return Type
     */
    public function y($y)
    {
        $this->y = (int)$y;
        return $this;
    }

    /**
     * Set both the X- and Y-positions
     *
     * @param  int $x
     * @param  int $y
     * @return Type
     */
    public function xy($x, $y)
    {
        $this->x($x);
        $this->y($y);
        return $this;
    }

    /**
     * Method to set the rotation of the text
     *
     * @param  int $rotation
     * @return Type
     */
    public function setRotation($rotation)
    {
        $this->textParams['rot']  = $rotation;
        return $this;
    }

    /**
     * Method to add a standard font to the PDF.
     *
     * @param  string  $font
     * @throws Exception
     * @return Type
     */
    public function addFont($font)
    {
        // Check to make sure the font is a standard PDF font.
        if (!array_key_exists($font, $this->standardFonts)) {
            throw new Exception('Error: That font is not contained within the standard PDF fonts.');
        }
        // Set the font index.
        $fontIndex = (count($this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->fonts) == 0) ? 1 :
            count($this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->fonts) + 1;

        // Set the font name and the next object index.
        $f = 'MF' . $fontIndex;
        $i = $this->lastIndex($this->objects) + 1;

        // Add the font to the current page's fonts and add the font to _objects array.
        $this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->fonts[$font] = "/{$f} {$i} 0 R";
        $this->objects[$i] = new Object\Object("{$i} 0 obj\n<<\n    /Type /Font\n    /Subtype /Type1\n    /Name /{$f}\n    /BaseFont /{$font}\n    /Encoding /WinAnsiEncoding\n>>\nendobj\n\n");

        $this->lastFontName = $font;

        if (!in_array($this->lastFontName, $this->fonts)) {
            $this->fonts[] = $this->lastFontName;
        }

        return $this;
    }


    /**
     * Method to embed a font file to the PDF.
     *
     * @param  string  $font
     * @param  boolean $embedOverride
     * @throws Exception
     * @return Type
     */
    public function embedFont($font, $embedOverride = false)
    {
        // Embed the font file.
        if (!file_exists($font)) {
            throw new Exception('Error: The font file does not exist.');
        }

        $fontIndex = (count($this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->fonts) == 0) ? 1 :
            count($this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->fonts) + 1;
        $objectIndex = $this->lastIndex($this->objects) + 1;

        $fontParser = new Parser\Font($font, $fontIndex, $objectIndex, $this->compress);

        if (!$fontParser->isEmbeddable() && !$embedOverride) {
            throw new Exception('Error: The font license does not allow for it to be embedded.');
        }

        $this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->fonts[$fontParser->getFontName()] = $fontParser->getFontRef();
        $fontObjects = $fontParser->getObjects();

        foreach ($fontObjects as $key => $value) {
            $this->objects[$key] = $value;
        }

        $this->lastFontName = $fontParser->getFontName();

        if (!in_array($this->lastFontName, $this->fonts)) {
            $this->fonts[] = $this->lastFontName;
        }

        return $this;
    }

    /**
     * Method to add text to the PDF.
     *
     * @param  string $str
     * @param  string $font
     * @throws Exception
     * @return Type
     */
    public function text($str, $font = null)
    {
        // Check to see if the font already exists on another page.
        $fontExists = false;

        if (null === $font) {
            $font = $this->getLastFontName();
        }

        if (function_exists('mb_strlen')) {
            if (mb_strlen($str, 'UTF-8') < strlen($str)) {
                $str = utf8_decode($str);
            }
        }

        foreach ($this->pages as $value) {
            if (array_key_exists($font, $this->objects[$value]->fonts)) {
                $this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->fonts[$font] = $this->objects[$value]->fonts[$font];
                $fontObj = substr($this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->fonts[$font], 1, (strpos(' ', $this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->fonts[$font]) + 3));
                $fontExists = true;
            }
        }

        // If the font does not already exist, add it.
        if (!$fontExists) {
            if (isset($this->pages[$this->currentPage]) &&
                isset($this->objects[$this->pages[$this->currentPage]]) &&
                isset($this->objects[$this->objects[$this->pages[$this->currentPage]]->index]) &&
                (array_key_exists($font, $this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->fonts))) {
                $fontObj = substr($this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->fonts[$font], 1, (strpos(' ', $this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->fonts[$font]) + 3));
            } else {
                throw new Exception('Error: The font \'' . $font . '\' has not been added to the PDF.');
            }
        }

        // Add the text to the current page's content stream.
        $coIndex = $this->getContentObject();
        $this->objects[$coIndex]->setStream("\nBT\n    /{$fontObj} {$this->size} Tf\n    " . $this->calcTextMatrix() . " {$this->x} {$this->y} Tm\n    " . $this->textParams['c'] . " Tc " . $this->textParams['w'] . " Tw " . $this->textParams['rend'] . " Tr\n    ({$str})Tj\nET\n");

        return $this;
    }

    /**
     * Method to get the width and height of a string in a certain font. It returns
     * an array with the approximate width, height and offset baseline values.
     *
     * @param  string $str
     * @param  string $font
     * @param  int    $sz
     * @throws Exception
     * @return array
     */
    public function getStringSize($str, $font, $sz)
    {
        if (!array_key_exists($font, $this->standardFonts)) {
            throw new Exception('Error: That font is not contained within the standard PDF fonts.');
        }

        // Calculate the approximate width, height and offset baseline values of the string at the certain font.
        $size = [];

        $size['width']    = round(($sz * $this->standardFonts[$font]['width_factor']) * strlen($str));
        $size['height']   = round($sz * $this->standardFonts[$font]['height_factor']);
        $size['baseline'] = round($sz / 3);

        return $size;
    }

    /**
     * Method to return the name of the last font added.
     *
     * @return string
     */
    public function getFonts()
    {
        return $this->fonts;
    }

    /**
     * Method to return the name of the last font added.
     *
     * @return string
     */
    public function getLastFontName()
    {
        return $this->lastFontName;
    }

    /**
     * Method to set the text parameters for rendering text content.
     *
     * @param  int $c    (character spacing)
     * @param  int $w    (word spacing)
     * @param  int $h    (horz stretch)
     * @param  int $v    (vert stretch)
     * @param  int $rot  (rotation)
     * @param  int $rend (render flag, 0 - 7)
     * @throws Exception
     * @return Type
     */
    public function setTextParams($c = 0, $w = 0, $h = 100, $v = 100, $rot = 0, $rend = 0)
    {
        // Check the rotation parameter.
        if (abs($rot) > 90) {
            throw new Exception('Error: The rotation parameter must be between -90 and 90 degrees.');
        }

        // Check the render parameter.
        if ((!is_int($rend)) || (($rend > 7) || ($rend < 0))) {
            throw new Exception('Error: The render parameter must be an integer between 0 and 7.');
        }

        // Set the text parameters.
        $this->textParams['c']    = $c;
        $this->textParams['w']    = $w;
        $this->textParams['h']    = $h;
        $this->textParams['v']    = $v;
        $this->textParams['rot']  = $rot;
        $this->textParams['rend'] = $rend;

        return $this;
    }

}