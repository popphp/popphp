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
 * HEAD table class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Head
{

    /**
     * Header info
     * @var array
     */
    protected $headerInfo = [];

    /**
     * Constructor
     *
     * Instantiate a TTF 'head' table object.
     *
     * @param  \Pop\Pdf\Font\TrueType $font
     * @return Head
     */
    public function __construct(\Pop\Pdf\Font\TrueType $font)
    {
        $bytePos = $font->tableInfo['head']->offset;

        $tableVersionNumberBytes = $font->read($bytePos, 4);
        $tableVersionNumber      = $font->readFixed(16, 16, $tableVersionNumberBytes);

        $bytePos += 4;

        $fontRevisionBytes = $font->read($bytePos, 4);
        $fontRevision      = $font->readFixed(16, 16, $fontRevisionBytes);

        $versionArray = [
            'tableVersionNumber' => $tableVersionNumber,
            'fontRevision'       => $fontRevision
        ];

        $bytePos += 4;

        $headerArray = unpack(
            'NcheckSumAdjustment/' .
            'NmagicNumber/' .
            'nflags/' .
            'nunitsPerEm', $font->read($bytePos, 12)
        );

        $bytePos += 28;
        $bBox = unpack(
            'nxMin/' .
            'nyMin/' .
            'nxMax/' .
            'nyMax', $font->read($bytePos, 8)
        );
        $bBox = $font->shiftToSigned($bBox);

        $bytePos += 14;
        $indexToLocFormat = unpack('nindexToLocFormat', $font->read($bytePos, 2));
        $headerArray['indexToLocFormat'] = $font->shiftToSigned($indexToLocFormat['indexToLocFormat']);

        $this->headerInfo = array_merge($versionArray, $headerArray, $bBox);
    }

    /**
     * Set method to set the property to the value of headerInfo[$name].
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->headerInfo[$name] = $value;
    }

    /**
     * Get method to return the value of headerInfo[$name].
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return (array_key_exists($name, $this->headerInfo)) ? $this->headerInfo[$name] : null;
    }

    /**
     * Return the isset value of headerInfo[$name].
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->headerInfo[$name]);
    }

    /**
     * Unset headerInfo[$name].
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        $this->headerInfo[$name] = null;
    }

}
