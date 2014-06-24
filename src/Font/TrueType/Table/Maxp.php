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
 * MAXP table class
 *
 * @category   Pop
 * @package    Pop_Font
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Maxp
{

    /**
     * Number of glyphs
     * @var int
     */
    public $numberOfGlyphs = 0;

    /**
     * Constructor
     *
     * Instantiate a TTF 'maxp' table object.
     *
     * @param  \Pop\Font\TrueType $font
     * @return \Pop\Font\TrueType\Table\Maxp
     */
    public function __construct(\Pop\Font\TrueType $font)
    {
        $bytePos = $font->tableInfo['maxp']->offset + 4;
        $ary     = unpack('nnumberOfGlyphs/', $font->read($bytePos, 2));
        $this->numberOfGlyphs = $ary['numberOfGlyphs'];
    }

}
