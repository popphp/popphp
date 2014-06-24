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
 * OS/2 table class
 *
 * @category   Pop
 * @package    Pop_Font
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Os2
{

    /**
     * Font cap height value
     * @var int
     */
    public $capHeight = 0;

    /**
     * Font embeddable flag
     * @var boolean
     */
    public $embeddable = true;

    /**
     * Font flags
     * @var \ArrayObject
     */
    public $flags = null;

    /**
     * Constructor
     *
     * Instantiate a OTF 'OS/2' table object.
     *
     * @param  \Pop\Font\TrueType $font
     * @return \Pop\Font\TrueType\Table\Os2
     */
    public function __construct(\Pop\Font\TrueType $font)
    {
        $this->flags = new \ArrayObject([
            'isFixedPitch'  => false,
            'isSerif'       => false,
            'isSymbolic'    => false,
            'isScript'      => false,
            'isNonSymbolic' => false,
            'isItalic'      => false,
            'isAllCap'      => false,
            'isSmallCap'    => false,
            'isForceBold'   => false
        ], \ArrayObject::ARRAY_AS_PROPS);

        $bytePos = $font->tableInfo['OS/2']->offset + 8;
        $ary     = unpack("nfsType", $font->read($bytePos, 2));
        $this->embeddable = (($ary['fsType'] != 2) && (($ary['fsType'] & 0x200) == 0));

        $bytePos = $font->tableInfo['OS/2']->offset + 30;
        $ary     = unpack("nfamily_class", $font->read($bytePos, 2));
        $familyClass = ($font->shiftToSigned($ary['family_class']) >> 8);

        if ((($familyClass >= 1) && ($familyClass <= 5)) || ($familyClass == 7)) {
            $this->flags->isSerif = true;
        } else if ($familyClass == 8) {
            $this->flags->isSerif = false;
        }
        if ($familyClass == 10) {
            $this->flags->isScript = true;
        }
        if ($familyClass == 12) {
            $this->flags->isSymbolic = true;
            $this->flags->isNonSymbolic = false;
        } else {
            $this->flags->isSymbolic = false;
            $this->flags->isNonSymbolic = true;
        }

        // Unicode bit-sniffing may not be necessary.
        $bytePos += 3;
        $ary = unpack(
            'NunicodeRange1/' .
            'NunicodeRange2/' .
            'NunicodeRange3/' .
            'NunicodeRange4', $font->read($bytePos, 16)
        );

        if (($ary['unicodeRange1'] == 1) && ($ary['unicodeRange2'] == 0) && ($ary['unicodeRange3'] == 0) && ($ary['unicodeRange4'] == 0)) {
            $this->flags->isSymbolic = false;
            $this->flags->isNonSymbolic = true;
        }

        $bytePos = $font->tableInfo['OS/2']->offset + 76;
        $ary = unpack("ncap/", $font->read($bytePos, 2));
        $this->capHeight = $font->toEmSpace($font->shiftToSigned($ary['cap']));
    }

}
