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
namespace Pop\Font\TrueType\Table\Cmap;

/**
 * CMAP segment-to-delta table class
 *
 * @category   Pop
 * @package    Pop_Font
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class SegmentToDelta
{

    /**
     * Method to parse the Segment to Delta (Format 4) CMAP data
     *
     * @param  string $data
     * @return array
     */
    public static function parseData($data)
    {
        $ary = unpack(
            'nsegCountx2/' .
            'nsearchRange/' .
            'nentrySelector/' .
            'nrangeShift', substr($data, 0, 8)
        );

        $ary['segCount'] = $ary['segCountx2'] / 2;
        $ary['endCount'] = array();

        $bytePos = 8;
        for ($i = 0; $i < $ary['segCount']; $i++) {
            $ar = unpack('nendCount', substr($data, $bytePos, 2));
            $ary['endCount'][$i] = $ar['endCount'];
            $bytePos += 2;
        }

        $ar = unpack('nreservedPad', substr($data, $bytePos, 2));
        $bytePos += 2;

        $ary['reservedPad'] = $ar['reservedPad'];

        $ary['startCount'] = array();

        for ($i = 0; $i < $ary['segCount']; $i++) {
            $ar = unpack('nstartCount', substr($data, $bytePos, 2));
            $ary['startCount'][$i] = $ar['startCount'];
            $bytePos += 2;
        }

        $ary['idDelta'] = array();

        for ($i = 0; $i < $ary['segCount']; $i++) {
            $ar = unpack('nidDelta', substr($data, $bytePos, 2));
            $ary['idDelta'][$i] = self::shiftToSigned($ar['idDelta']);
            $bytePos += 2;
        }

        $ary['idRangeOffset'] = array();

        for ($i = 0; $i < $ary['segCount']; $i++) {
            $ar = unpack('nidRangeOffset', substr($data, $bytePos, 2));
            $ary['idRangeOffset'][$i] = $ar['idRangeOffset'] >> 1;
            $bytePos += 2;
        }

        $ary['glyphIndexArray'] = array();

        for (; $bytePos < strlen($data); $bytePos += 2) {
            $ar = unpack('nglyphIndex', substr($data, $bytePos, 2));
            $ary['glyphIndexArray'][] = $ar['glyphIndex'];
        }

        $ary['glyphNumbers'] = array();

        for ($segmentNum = 0; $segmentNum < $ary['segCount']; $segmentNum++) {
            if ($ary['idRangeOffset'][$segmentNum] == 0) {
                $delta = $ary['idDelta'][$segmentNum];

                for ($code = $ary['startCount'][$segmentNum];
                     $code <= $ary['endCount'][$segmentNum];
                     $code++) {
                    $ary['glyphNumbers'][$code] = ($code + $delta) % 65536;
                }
            } else {
                $code       = $ary['startCount'][$segmentNum];
                $glyphIndex = $ary['idRangeOffset'][$segmentNum] - ($ary['segCount'] - $segmentNum) - 1;

                while ($code <= $ary['endCount'][$segmentNum]) {
                    if (isset($ary['glyphIndexArray'][$glyphIndex])) {
                        $ary['glyphNumbers'][$code] = $ary['glyphIndexArray'][$glyphIndex];
                    }

                    $code++;
                    $glyphIndex++;
                }
            }
        }

        $ary['mapData'] = str_repeat("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00", 8192);
        // Fill the index
        foreach ($ary['glyphNumbers'] as $charCode => $glyph) {
            $ary['mapData'][$charCode * 2] = chr($glyph >> 8);
            $ary['mapData'][$charCode * 2 + 1] = chr($glyph & 0xFF);
        }

        return $ary;
    }

    /**
     * Method to shift an unpacked signed short from big endian to little endian
     *
     * @param  int|array $values
     * @return int|array
     */
    public static function shiftToSigned($values)
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

}