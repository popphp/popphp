<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Font
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Font\TrueType\Table;

/**
 * HMTX table class
 *
 * @category   Pop
 * @package    Pop_Font
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Hmtx
{

    /**
     * Glyph widths
     * @var array
     */
    public $glyphWidths = array();

    /**
     * Constructor
     *
     * Instantiate a TTF 'hmtx' table object.
     *
     * @param  \Pop\Font\AbstractFont $font
     * @return \Pop\Font\TrueType\Table\Hmtx
     */
    public function __construct(\Pop\Font\AbstractFont $font)
    {
        $bytePos = $font->tableInfo['hmtx']->offset;

        for ($i = 0; $i < $font->numberOfHMetrics; $i++) {
            $ary = unpack('nglyphWidth/', $font->read($bytePos, 2));
            $this->glyphWidths[$i] = $font->shiftToSigned($ary['glyphWidth']);
            $bytePos += 4;
        }

        while (count($this->glyphWidths) < $font->numberOfGlyphs) {
            $this->glyphWidths[] = end($this->glyphWidths);
        }
    }

}
