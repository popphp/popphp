<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Pdf\Font;

/**
 * TrueType font class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class TrueType extends AbstractFont
{

    /**
     * TrueType font header
     * @var mixed
     */
    public $header = null;

    /**
     * TrueType font file header
     * @var \ArrayObject
     */
    public $ttfHeader = null;

    /**
     * TrueType font file table
     * @var \ArrayObject
     */
    public $ttfTable = null;

    /**
     * TrueType font tables
     * @var array
     */
    public $tables = [];

    /**
     * TrueType font table info
     * @var array
     */
    public $tableInfo = [];

    /**
     * Constructor
     *
     * Instantiate a TrueType font file object based on a pre-existing font file on disk.
     *
     * @param  string $font
     * @return \Pop\Pdf\Font\TrueType
     */
    public function __construct($font)
    {
        parent::__construct($font);

        $this->parseTtfTable();
        $this->parseName();
        $this->parseCommonTables();
        $this->parseRequiredTables();
    }

    /**
     * Method to parse the TTF header and table of the TrueType font file.
     *
     * @return void
     */
    protected function parseTtfTable()
    {
        $ttfHeader = unpack(
            'nmajorVersion/' .
            'nminorVersion/' .
            'nnumberOfTables/' .
            'nsearchRange/' .
            'nentrySelector/' .
            'nrangeShift', $this->read(0, 12)
        );

        $tableName = $this->read(12, 4);

        $ttfTable = unpack(
            'Nchecksum/' .
            'Noffset/' .
            'Nlength', $this->read(16, 12)
        );

        $ttfTable['name'] = $tableName;

        $this->ttfHeader = new \ArrayObject($ttfHeader, \ArrayObject::ARRAY_AS_PROPS);
        $this->ttfTable = new \ArrayObject($ttfTable, \ArrayObject::ARRAY_AS_PROPS);

        $nameByteOffset = 28;
        $tableByteOffset = 32;

        for ($i = 0; $i < $this->ttfHeader->numberOfTables; $i++) {
            $ttfTableName = $this->read($nameByteOffset, 4);
            $ttfTable = unpack(
                'Nchecksum/' .
                'Noffset/' .
                'Nlength', $this->read($tableByteOffset, 12)
            );

            $this->tableInfo[trim($ttfTableName)] = new \ArrayObject($ttfTable, \ArrayObject::ARRAY_AS_PROPS);

            $nameByteOffset = $tableByteOffset + 12;
            $tableByteOffset = $nameByteOffset + 4;
        }
    }

    /**
     * Method to parse the TTF info of the TrueType font file from the name table.
     *
     * @return void
     */
    protected function parseName()
    {
        if (isset($this->tableInfo['name'])) {
            $this->tables['name'] = new TrueType\Table\Name($this);
            $this->info = $this->tables['name'];
            if ((stripos($this->tables['name']->fontFamily, 'bold') !== false) ||
                (stripos($this->tables['name']->fullName, 'bold') !== false) ||
                (stripos($this->tables['name']->postscriptName, 'bold') !== false)) {
                $this->stemV = 120;
            } else {
                $this->stemV = 70;
            }
        }
    }

    /**
     * Method to parse the common tables of the TrueType font file.
     *
     * @return void
     */
    protected function parseCommonTables()
    {
        // head
        if (isset($this->tableInfo['head'])) {
            $this->tables['head'] = new TrueType\Table\Head($this);

            $this->unitsPerEm = $this->tables['head']->unitsPerEm;

            $this->tables['head']->xMin = $this->toEmSpace($this->tables['head']->xMin);
            $this->tables['head']->yMin = $this->toEmSpace($this->tables['head']->yMin);
            $this->tables['head']->xMax = $this->toEmSpace($this->tables['head']->xMax);
            $this->tables['head']->yMax = $this->toEmSpace($this->tables['head']->yMax);

            $this->bBox = new \ArrayObject([
                'xMin' => $this->tables['head']->xMin,
                'yMin' => $this->tables['head']->yMin,
                'xMax' => $this->tables['head']->xMax,
                'yMax' => $this->tables['head']->yMax
            ], \ArrayObject::ARRAY_AS_PROPS);

            $this->header = $this->tables['head'];
        }

        // hhea
        if (isset($this->tableInfo['hhea'])) {
            $this->tables['hhea'] = new TrueType\Table\Hhea($this);
            $this->ascent = $this->tables['hhea']->ascent;
            $this->descent = $this->tables['hhea']->descent;
            $this->capHeight = $this->ascent + $this->descent;
            $this->numberOfHMetrics = $this->tables['hhea']->numberOfHMetrics;
        }

        // maxp
        if (isset($this->tableInfo['maxp'])) {
            $this->tables['maxp'] = new TrueType\Table\Maxp($this);
            $this->numberOfGlyphs = $this->tables['maxp']->numberOfGlyphs;
        }

        // post
        if (isset($this->tableInfo['post'])) {
            $this->tables['post'] = new TrueType\Table\Post($this);

            if ($this->tables['post']->italicAngle != 0) {
                $this->flags->isItalic = true;
                $this->italicAngle = $this->tables['post']->italicAngle;
            }

            if ($this->tables['post']->fixed != 0) {
                $this->flags->isFixedPitch = true;
            }
        }

        // hmtx
        if (isset($this->tableInfo['hmtx'])) {
            $this->tables['hmtx'] = new TrueType\Table\Hmtx($this);
            $this->glyphWidths = $this->tables['hmtx']->glyphWidths;
            if (isset($this->glyphWidths[0])) {
                $this->missingWidth = round((1000 / $this->unitsPerEm) * $this->glyphWidths[0]);
            }
            foreach ($this->glyphWidths as $key => $value) {
                $this->glyphWidths[$key] = round((1000 / $this->unitsPerEm) * $value);
            }
        }

        // cmap
        if (isset($this->tableInfo['cmap'])) {
            $this->tables['cmap'] = new TrueType\Table\Cmap($this);
        }
    }

    /**
     * Method to parse the required tables of the TrueType font file.
     *
     * @return void
     */
    protected function parseRequiredTables()
    {
        // loca
        if (isset($this->tableInfo['loca'])) {
            $this->tables['loca'] = new TrueType\Table\Loca($this);
        }

        // glyf
        if (isset($this->tableInfo['glyf'])) {
            $this->tables['glyf'] = new TrueType\Table\Glyf($this);
        }

        // OS/2 (Optional in a TTF font file)
        if (isset($this->tableInfo['OS/2'])) {
            $this->tables['OS/2'] = new TrueType\Table\Os2($this);
            $this->flags->isSerif = $this->tables['OS/2']->flags->isSerif;
            $this->flags->isScript = $this->tables['OS/2']->flags->isScript;
            $this->flags->isSymbolic = $this->tables['OS/2']->flags->isSymbolic;
            $this->flags->isNonSymbolic = $this->tables['OS/2']->flags->isNonSymbolic;
            $this->embeddable = $this->tables['OS/2']->embeddable;
        }
    }

}
