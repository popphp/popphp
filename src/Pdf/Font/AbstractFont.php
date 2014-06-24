<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
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
 * Font abstract class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractFont
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
    public $glyphWidths = [];

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
    protected $allowed = [
        'afm' => 'application/x-font-afm',
        'otf' => 'application/x-font-otf',
        'pfb' => 'application/x-font-pfb',
        'pfm' => 'application/x-font-pfm',
        'ttf' => 'application/x-font-ttf'
    ];

    /**
     * Full path of font file, i.e. '/path/to/fontfile.ext'
     * @var string
     */
    protected $fullpath = null;

    /**
     * Full, absolute directory of the font file, i.e. '/some/dir/'
     * @var string
     */
    protected $dir = null;

    /**
     * Full basename of font file, i.e. 'fontfile.ext'
     * @var string
     */
    protected $basename = null;

    /**
     * Full filename of font file, i.e. 'fontfile'
     * @var string
     */
    protected $filename = null;

    /**
     * Font file extension, i.e. 'ext'
     * @var string
     */
    protected $extension = null;

    /**
     * Font file size in bytes
     * @var int
     */
    protected $size = 0;

    /**
     * Font file mime type
     * @var string
     */
    protected $mime = 'text/plain';

    /**
     * Constructor
     *
     * Instantiate a font file object based on a pre-existing font file on disk.
     *
     * @param  string $font
     * @throws Exception
     * @return \Pop\Pdf\Font\AbstractFont
     */
    public function __construct($font)
    {
        if (!file_exists($font)) {
            throw new Exception('The font file does not exist.');
        }

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

        $this->fullpath  = $font;
        $parts           = pathinfo($font);
        $this->size      = filesize($font);
        $this->dir       = realpath($parts['dirname']);
        $this->basename  = $parts['basename'];
        $this->filename  = $parts['filename'];
        $this->extension = (isset($parts['extension']) && ($parts['extension'] != '')) ? $parts['extension'] : null;

        if (null === $this->extension) {
            throw new Exception('Error: That font file does not have an extension.');
        }

        if ((null !== $this->extension) && !isset($this->allowed[$this->extension])) {
            throw new Exception('Error: That font file type is not allowed.');
        }

        $this->mime = $this->allowed[$this->extension];
    }

    /**
     * Read data from the font file.
     *
     * @param  int $offset
     * @param  int $length
     * @return string
     */
    public function read($offset = null, $length = null)
    {
        if (null !== $offset) {
            $data = (null !== $length) ?
                file_get_contents($this->fullpath, null, null, $offset, $length) :
                file_get_contents($this->fullpath, null, null, $offset);
        } else {
            $data = file_get_contents($this->fullpath);
        }

        return $data;
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
