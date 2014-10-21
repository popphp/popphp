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
 * HHEA table class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Hhea extends AbstractTable
{

    /**
     * Ascent
     * @var int
     */
    public $ascent = 0;

    /**
     * Descent
     * @var int
     */
    public $descent = 0;

    /**
     * Number of horizontal metrics
     * @var int
     */
    public $numberOfHMetrics = 0;

    /**
     * Constructor
     *
     * Instantiate a TTF 'hhea' table object.
     *
     * @param  \Pop\Pdf\Type\Font\TrueType $font
     * @return Hhea
     */
    public function __construct(\Pop\Pdf\Type\Font\TrueType $font)
    {
        $bytePos = $font->tableInfo['hhea']->offset + 4;

        $ary = unpack(
            'nascent/' .
            'ndescent', $font->read($bytePos, 4)
        );

        $ary = $font->shiftToSigned($ary);
        $this->ascent  = $font->toEmSpace($ary['ascent']);
        $this->descent = $font->toEmSpace($ary['descent']);

        $bytePos = $font->tableInfo['hhea']->offset + 34;
        $ary = unpack('nnumberOfHMetrics/', $font->read($bytePos, 2));
        $this->numberOfHMetrics = $ary['numberOfHMetrics'];
    }

}
