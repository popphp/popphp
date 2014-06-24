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
 * POST table class
 *
 * @category   Pop
 * @package    Pop_Font
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Post
{

    /**
     * Italic angle
     * @var float
     */
    public $italicAngle = 0;


    /**
     * Fixed
     * @var int
     */
    public $fixed = 0;

    /**
     * Constructor
     *
     * Instantiate a TTF 'post' table object.
     *
     * @param  \Pop\Font\TrueType $font
     * @return \Pop\Font\TrueType\Table\Post
     */
    public function __construct(\Pop\Font\TrueType $font)
    {
        $bytePos = $font->tableInfo['post']->offset + 4;

        $italicBytes       = $font->read($bytePos, 4);
        $this->italicAngle = $font->readFixed(16, 16, $italicBytes);

        $bytePos += 8;

        $ary = unpack('nfixed/', $font->read($bytePos, 2));
        $ary = $font->shiftToSigned($ary);
        $this->fixed = $ary['fixed'];
    }

}
