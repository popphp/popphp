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
namespace Pop\Pdf\Type\Font\TrueType\Table;

/**
 * CMAP table class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Cmap
{

    /**
     * Cmap header
     * @var \ArrayObject
     */
    public $header = null;

    /**
     * Cmap subtables
     * @var \ArrayObject
     */
    public $subTables = [];

    /**
     * Constructor
     *
     * Instantiate a TTF 'cmap' table object.
     *
     * @param  \Pop\Pdf\Type\Font\TrueType $font
     * @return Cmap
     */
    public function __construct(\Pop\Pdf\Type\Font\TrueType $font)
    {
        $bytePos = $font->tableInfo['cmap']->offset;

        // Get the CMAP header data.
        $cmapTableHeader = unpack(
            'ntableVersion/' .
            'nnumberOfTables', $font->read($bytePos, 4)
        );

        $this->header = new \ArrayObject($cmapTableHeader, \ArrayObject::ARRAY_AS_PROPS);
        $this->parseSubTables($font);
    }

    /**
     * Method to parse the CMAP sub-tables.
     *
     * @param  \Pop\Pdf\Type\Font\TrueType $font
     * @return void
     */
    protected function parseSubTables(\Pop\Pdf\Type\Font\TrueType $font)
    {
        $bytePos = $font->tableInfo['cmap']->offset + 4;

        // Get each of the sub-table's data.
        for ($i = 0; $i < $this->header->numberOfTables; $i++) {
            $ary = unpack(
                'nplatformId/' .
                'nencodingId/' .
                'Noffset', $font->read($bytePos, 8)
            );
            if (($ary['platformId'] == 0) && ($ary['encodingId'] == 0)) {
                $ary['encoding'] = 'Unicode 2.0';
            } else if (($ary['platformId'] == 0) && ($ary['encodingId'] == 3)) {
                $ary['encoding'] = 'Unicode';
            } else if (($ary['platformId'] == 3) && ($ary['encodingId'] == 1)) {
                $ary['encoding'] = 'Microsoft Unicode';
            } else if (($ary['platformId'] == 1) && ($ary['encodingId'] == 0)) {
                $ary['encoding'] = 'Mac Roman';
            } else {
                $ary['encoding'] = 'Unknown';
            }
            $this->subTables[] = new \ArrayObject($ary, \ArrayObject::ARRAY_AS_PROPS);
            $bytePos += 8;
        }

        // Parse each of the sub-table's data.
        foreach ($this->subTables as $key => $subTable) {
            $bytePos = $font->tableInfo['cmap']->offset + $subTable->offset;
            $ary = unpack(
                'nformat/' .
                'nlength/' .
                'nlanguage', $font->read($bytePos, 6)
            );
            $this->subTables[$key]->format = $ary['format'];
            $this->subTables[$key]->length = $ary['length'];
            $this->subTables[$key]->language = $ary['language'];
            $bytePos += 6;
            $this->subTables[$key]->data = $font->read($bytePos, $ary['length'] - 6);
            switch ($this->subTables[$key]->format) {
                case 0:
                    $this->subTables[$key]->parsed = Cmap\ByteEncoding::parseData($this->subTables[$key]->data);
                    break;
                case 4:
                    $this->subTables[$key]->parsed = Cmap\SegmentToDelta::parseData($this->subTables[$key]->data);
                    break;
                case 6:
                    $this->subTables[$key]->parsed = Cmap\TrimmedTable::parseData($this->subTables[$key]->data);
                    break;
            }
        }
    }

}
