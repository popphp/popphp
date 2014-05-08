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
namespace Pop\Font;

/**
 * Font abstract class
 *
 * @category   Pop
 * @package    Pop_Font
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
abstract class AbstractFont extends \Pop\File\File
{

    /**
     * Font info
     * @var mixed
     */
    public $info = null;

    /**
     * Font bounding box info
     * @var \ArrayObject
     */
    public $bBox = null;

    /**
     * Font ascent value
     * @var int
     */
    public $ascent = 0;

    /**
     * Font descent value
     * @var int
     */
    public $descent = 0;

    /**
     * Font number of glyphs value
     * @var int
     */
    public $numberOfGlyphs = 0;

    /**
     * Font glyph widths
     * @var array
     */
    public $glyphWidths = array();

    /**
     * Missing glyph width
     * @var int
     */
    public $missingWidth = 0;

    /**
     * Font number of horizontal metrics value
     * @var int
     */
    public $numberOfHMetrics = 0;

    /**
     * Font italic angle value
     * @var float
     */
    public $italicAngle = 0;

    /**
     * Font cap height value
     * @var int
     */
    public $capHeight = 0;

    /**
     * Font StemH value
     * @var int
     */
    public $stemH = 0;

    /**
     * Font StemV value
     * @var int
     */
    public $stemV = 0;

    /**
     * Font units per EM value
     * @var int
     */
    public $unitsPerEm = 1000;

    /**
     * Font flags
     * @var \ArrayObject
     */
    public $flags = null;

    /**
     * Font embeddable flag
     * @var boolean
     */
    public $embeddable = true;

    /**
     * Array of allowed file types.
     * @var array
     */
    protected $allowed = array(
        'afm' => 'application/x-font-afm',
        'otf' => 'application/x-font-otf',
        'pfb' => 'application/x-font-pfb',
        'pfm' => 'application/x-font-pfm',
        'ttf' => 'application/x-font-ttf'
    );

    /**
     * Constructor
     *
     * Instantiate a font file object based on a pre-existing font file on disk.
     *
     * @param  string $font
     * @throws Exception
     * @return \Pop\Font\AbstractFont
     */
    public function __construct($font)
    {
        if (!file_exists($font)) {
            throw new Exception('The font file does not exist.');
        }

        $this->flags = new \ArrayObject(array(
            'isFixedPitch'  => false,
            'isSerif'       => false,
            'isSymbolic'    => false,
            'isScript'      => false,
            'isNonSymbolic' => false,
            'isItalic'      => false,
            'isAllCap'      => false,
            'isSmallCap'    => false,
            'isForceBold'   => false
        ), \ArrayObject::ARRAY_AS_PROPS);

        parent::__construct($font);
    }

    /**
     * Static method to read and return a fixed-point number
     *
     * @param  int    $mantissaBits
     * @param  int    $fractionBits
     * @param  string $bytes
     * @return int
     */
    public function readFixed($mantissaBits, $fractionBits, $bytes)
    {
        $bitsToRead = $mantissaBits + $fractionBits;
        $number = $this->readInt(($bitsToRead >> 3), $bytes) / (1 << $fractionBits);
        return $number;
    }

    /**
     * Static method to read and return a signed integer
     *
     * @param  int    $size
     * @param  string $bytes
     * @return int
     */
    public function readInt($size, $bytes)
    {
        $number = ord($bytes[0]);

        if (($number & 0x80) == 0x80) {
            $number = (~ $number) & 0xff;
            for ($i = 1; $i < $size; $i++) {
                $number = ($number << 8) | ((~ ord($bytes[$i])) & 0xff);
            }
            $number = ~$number;
        } else {
            for ($i = 1; $i < $size; $i++) {
                $number = ($number << 8) | ord($bytes[$i]);
            }
        }

        return $number;
    }

    /**
     * Method to shift an unpacked signed short from little endian to big endian
     *
     * @param  int|array $values
     * @return int|array
     */
    public function shiftToSigned($values)
    {
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                if ($value >= pow(2, 15)) {
                    $values[$key] -= pow(2, 16);
                }
            }
        } else {
            if ($values >= pow(2, 15)) {
                $values -= pow(2, 16);
            }
        }

        return $values;
    }

    /**
     * Method to convert a value to the representative value in EM.
     *
     * @param int $value
     * @return int
     */
    public function toEmSpace($value)
    {
        return ($this->unitsPerEm == 1000) ? $value : ceil(($value / $this->unitsPerEm) * 1000);
    }

    /**
     * Method to calculate the font flags
     *
     * @return int
     */
    public function calcFlags()
    {
        $flags = 0;

        if ($this->flags->isFixedPitch) {
            $flags += 1 << 0;
        }

        $flags += 1 << 5;

        if ($this->flags->isItalic) {
            $flags += 1 << 6;
        }

        return $flags;
    }

}
