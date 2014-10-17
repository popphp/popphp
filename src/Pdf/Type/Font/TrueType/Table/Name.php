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
 * NAME table class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Name
{

    /**
     * Font info
     * @var array
     */
    protected $fontInfo = [];

    /**
     * TrueType font info names
     * @var array
     */
    protected $names = [
        0  => 'copyright',
        1  => 'fontFamily',
        2  => 'fontSubFamily',
        3  => 'uniqueId',
        4  => 'fullName',
        5  => 'version',
        6  => 'postscriptName',
        7  => 'trademark',
        8  => 'manufacturer',
        9  => 'designer',
        10 => 'description',
        11 => 'vendorUrl',
        12 => 'designerUrl',
        13 => 'license',
        14 => 'licenseUrl',
        16 => 'preferredFamily',
        17 => 'preferredSubFamily',
        18 => 'compatibleFull',
        19 => 'sampleText'
    ];

    /**
     * Constructor
     *
     * Instantiate a TTF 'name' table object.
     *
     * @param  \Pop\Pdf\Type\Font\TrueType $font
     * @return Name
     */
    public function __construct(\Pop\Pdf\Type\Font\TrueType $font)
    {
        $font->tableInfo['name']->header = new \ArrayObject(
            unpack(
                'nformatSelector/' .
                'nnameRecordsCount/' .
                'nstorageOffset', $font->read($font->tableInfo['name']->offset, 6)
            ), \ArrayObject::ARRAY_AS_PROPS
        );

        $bytePos = $font->tableInfo['name']->offset + 6;

        for ($j = 0; $j < $font->tableInfo['name']->header->nameRecordsCount; $j++) {
            $ttfRecord = unpack(
                'nplatformId/' .
                'nencodingId/' .
                'nlanguageId/' .
                'nnameId/' .
                'nlength/' .
                'noffset', $font->read($bytePos, 12)
            );

            $ttfRecordOffset = $bytePos + 12;
            $nextBytePos = $font->tableInfo['name']->offset + $font->tableInfo['name']->header->storageOffset + $ttfRecord['offset'];

            $ttfValue = $font->read($nextBytePos, $ttfRecord['length']);

            if ($ttfRecord['platformId'] != 1) {
                $ttfValue = @iconv('UTF-16be', 'UTF-8//TRANSLIT', $ttfValue);
            }
            if (($ttfValue != '') && isset($ttfRecord['nameId']) && isset($this->names[$ttfRecord['nameId']])) {
                $this->fontInfo[$this->names[$ttfRecord['nameId']]] = $ttfValue;
            }

            $bytePos = $ttfRecordOffset;
        }

    }

    /**
     * Set method to set the property to the value of fontInfo[$name].
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->fontInfo[$name] = $value;
    }

    /**
     * Get method to return the value of fontInfo[$name].
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return (array_key_exists($name, $this->fontInfo)) ? $this->fontInfo[$name] : null;
    }

    /**
     * Return the isset value of fontInfo[$name].
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->fontInfo[$name]);
    }

    /**
     * Unset fontInfo[$name].
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        $this->fontInfo[$name] = null;
    }

}
