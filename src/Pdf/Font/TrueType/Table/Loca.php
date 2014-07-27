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
namespace Pop\Pdf\Font\TrueType\Table;

/**
 * LOCA table class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Loca
{

    /**
     * Location offsets
     * @var array
     */
    public $offsets = [];

    /**
     * Constructor
     *
     * Instantiate a TTF 'loca' table object.
     *
     * @param  \Pop\Pdf\Font\TrueType $font
     * @return Loca
     */
    public function __construct(\Pop\Pdf\Font\TrueType $font)
    {
        $bytePos    = $font->tableInfo['loca']->offset;
        $format     = ($font->header->indexToLocFormat == 1) ? 'N' : 'n';
        $byteLength = ($font->header->indexToLocFormat == 1) ? 4 : 2;
        $multiplier = ($font->header->indexToLocFormat == 1) ? 1 : 2;

        for ($i = 0; $i < ($font->numberOfGlyphs + 1); $i++) {
            $ary = unpack($format . 'offset', $font->read($bytePos, $byteLength));
            $this->offsets[$i] = $ary['offset'] * $multiplier;
            $bytePos += $byteLength;
        }
    }

}
