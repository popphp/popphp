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
namespace Pop\Pdf\Parser;

use Pop\Pdf\Object\Object;

/**
 * Font parser class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Font
{

    /**
     * Font object
     * @var mixed
     */
    protected $font = null;

    /**
     * Font reference index
     * @var int
     */
    protected $fontIndex = 0;

    /**
     * Font object index
     * @var int
     */
    protected $objectIndex = 0;

    /**
     * Font descriptor index
     * @var int
     */
    protected $fontDescIndex = 0;

    /**
     * Font file index
     * @var int
     */
    protected $fontFileIndex = 0;

    /**
     * Font objects
     * @var array
     */
    protected $objects = [];

    /**
     * Font compress flag
     * @var boolean
     */
    protected $compress = false;

    /**
     * Constructor
     *
     * Instantiate a font parser object to be used by Pop_Pdf.
     *
     * @param  string  $fle
     * @param  int     $fi
     * @param  int     $oi
     * @param  boolean $comp
     * @throws Exception
     * @return Font
     */
    public function __construct($fle, $fi, $oi, $comp = false)
    {
        $this->fontIndex = $fi;
        $this->objectIndex = $oi;
        $this->fontDescIndex = $oi + 1;
        $this->fontFileIndex = $oi + 2;
        $this->compress = $comp;

        $ext = strtolower(substr($fle, -4));
        switch ($ext) {
            case '.ttf':
                $this->font = new \Pop\Pdf\Font\TrueType($fle);
                break;
            case '.otf':
                $this->font = new \Pop\Pdf\Font\TrueType\OpenType($fle);
                break;
            case '.pfb':
                $this->font = new \Pop\Pdf\Font\Type1($fle);
                if (null === $this->font->afmPath) {
                    throw new Exception('The AFM font file was not found.');
                }
                break;
            default:
                throw new Exception('That font type is not supported.');
        }

        $this->createFontObjects();
    }

    /**
     * Method to get the font objects.
     *
     * @return array
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * Method to get the font reference.
     *
     * @return string
     */
    public function getFontRef()
    {
        return "/TT{$this->fontIndex} {$this->objectIndex} 0 R";
    }

    /**
     * Method to get the font name.
     *
     * @return string
     */
    public function getFontName()
    {
        $fontName = ($this->font instanceof \Pop\Pdf\Font\Type1) ? $this->font->info->postscriptName : $this->font->tables['name']->postscriptName;
        return $fontName;
    }

    /**
     * Method to get if the font is embeddable.
     *
     * @return boolean
     */
    public function isEmbeddable()
    {
        return $this->font->embeddable;
    }

    /**
     * Method to create the font objects.
     *
     * @return void
     */
    protected function createFontObjects()
    {
        if ($this->font instanceof \Pop\Pdf\Font\Type1) {
            $fontType     = 'Type1';
            $fontName     = $this->font->info->postscriptName;
            $fontFile     = 'FontFile';
            $glyphWidths  = ['encoding' => 'StandardEncoding', 'widths' => $this->font->glyphWidths];
            $unCompStream = $this->font->fontData;
            $length1      = $this->font->length1;
            $length2      = " /Length2 " . $this->font->length2 . " /Length3 0";
        } else {
            $fontType     = 'TrueType';
            $fontName     = $this->font->tables['name']->postscriptName;
            $fontFile     = 'FontFile2';
            $glyphWidths  = $this->getGlyphWidths($this->font->tables['cmap']);
            $unCompStream = $this->font->read();
            $length1      = strlen($unCompStream);
            $length2      = null;
        }

        $this->objects[$this->objectIndex] = new Object("{$this->objectIndex} 0 obj\n<<\n    /Type /Font\n    /Subtype /{$fontType}\n    /FontDescriptor {$this->fontDescIndex} 0 R\n    /Name /TT{$this->fontIndex}\n    /BaseFont /" . $fontName . "\n    /FirstChar 32\n    /LastChar 255\n    /Widths [" . implode(' ', $glyphWidths['widths']) . "]\n    /Encoding /" . $glyphWidths['encoding'] . "\n>>\nendobj\n\n");
        $bBox = '[' . $this->font->bBox->xMin . ' ' . $this->font->bBox->yMin . ' ' . $this->font->bBox->xMax . ' ' . $this->font->bBox->yMax . ']';

        $compStream = (function_exists('gzcompress')) ? gzcompress($unCompStream, 9) : null;
        if ($this->compress) {
            $fontFileObj = "{$this->fontFileIndex} 0 obj\n<</Length " . strlen($compStream) . " /Filter /FlateDecode /Length1 " . $length1 . $length2 . ">>\nstream\n" . $compStream . "\nendstream\nendobj\n\n";
        } else {
            $fontFileObj = "{$this->fontFileIndex} 0 obj\n<</Length " . strlen($unCompStream) . " /Length1 " . $length1 . $length2 . ">>\nstream\n" . $unCompStream . "\nendstream\nendobj\n\n";
        }

        $this->objects[$this->fontDescIndex] = new Object("{$this->fontDescIndex} 0 obj\n<<\n    /Type /FontDescriptor\n    /FontName /" . $fontName . "\n    /{$fontFile} {$this->fontFileIndex} 0 R\n    /MissingWidth {$this->font->missingWidth}\n    /StemV {$this->font->stemV}\n    /Flags " . $this->font->calcFlags() . "\n    /FontBBox {$bBox}\n    /Descent {$this->font->descent}\n    /Ascent {$this->font->ascent}\n    /CapHeight {$this->font->capHeight}\n    /ItalicAngle {$this->font->italicAngle}\n>>\nendobj\n\n");
        $this->objects[$this->fontFileIndex] = new Object($fontFileObj);
    }

    /**
     * Method to to get the glyph widths
     *
     * @param  \Pop\Pdf\Font\TrueType\Table\Cmap $cmap
     * @return array
     */
    protected function getGlyphWidths(\Pop\Pdf\Font\TrueType\Table\Cmap $cmap)
    {
        $gw = ['encoding' => null, 'widths' => []];
        $uniTable = null;
        $msTable = null;
        $macTable = null;

        foreach ($cmap->subTables as $index => $table) {
            if ($table->encoding == 'Microsoft Unicode') {
                $msTable = $index;
            }
            if ($table->encoding == 'Unicode') {
                $uniTable = $index;
            }
            if (($table->encoding == 'Mac Roman') && ($table->format == 0)) {
                $macTable = $index;
            }
        }

        if (null !== $msTable) {
            $gw['encoding'] = 'WinAnsiEncoding';
            foreach ($cmap->subTables[$msTable]->parsed['glyphNumbers'] as $key => $value) {
                $gw['widths'][$key] = $this->font->glyphWidths[$value];
            }
        } else if (null !== $uniTable) {
            $gw['encoding'] = 'WinAnsiEncoding';
            foreach ($cmap->subTables[$uniTable]->parsed['glyphNumbers'] as $key => $value) {
                $gw['widths'][$key] = $this->font->glyphWidths[$value];
            }
        } else if (null !== $macTable) {
            $gw['encoding'] = 'MacRomanEncoding';
            foreach ($cmap->subTables[$macTable]->parsed as $key => $value) {
                if (($this->font->glyphWidths[$value->ascii] != 0) && ($this->font->glyphWidths[$value->ascii] != $this->font->missingWidth)) {
                    $gw['widths'][$key] = $this->font->glyphWidths[$value->ascii];
                }
            }
        }

        return $gw;
    }

    /**
     * Method to calculate byte length.
     *
     * @param  string $str
     * @return int
     */
    protected function calcByteLength($str)
    {
        $bytes = str_replace("\n", "", $str);
        return strlen($bytes);
    }

}