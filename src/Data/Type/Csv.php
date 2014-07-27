<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Data
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Data\Type;

/**
 * CSV data type class
 *
 * @category   Pop
 * @package    Pop_Data
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Csv implements TypeInterface
{

    /**
     * Decode the data into PHP.
     *
     * @param  string $data
     * @param  string $delim
     * @param  string $esc
     * @return mixed
     */
    public static function decode($data, $delim = ',', $esc = '"')
    {
        // Read the file data, separating by new lines.
        $lines = explode("\n", $data);

        $linesOfData    = [];
        $newLinesOfData = [];

        // Loop through the line data, parsing any quoted or escaped data.
        foreach ($lines as $data) {
            if ($data != '') {
                if (strpos($data, $esc) !== false) {
                    $matches = [];
                    preg_match_all('/"([^"]*)"/', $data, $matches);
                    if (isset($matches[0])) {
                        foreach ($matches[0] as $value) {
                            $escapedData = str_replace('"', '', $value);
                            $escapedData = str_replace($delim, '[{c}]', $escapedData);
                            $data = str_replace($value, $escapedData, $data);
                        }
                    }
                }

                // Finalize the data and store in the array.
                $data = str_replace($delim, '[{d}]', $data);
                $data = str_replace('[{c}]', $delim, $data);
                $linesOfData[] = explode('[{d}]', $data);
            }
        }

        // Create a corresponding associative array by converting the array keys to the header names.
        for ($i = 1; $i < count($linesOfData); $i++) {
            $newLinesOfData['row_' . $i] = [];

            foreach ($linesOfData[$i] as $key => $value) {
                $newKey = trim($linesOfData[0][$key]);
                $newLinesOfData['row_' . $i][$newKey] = trim($value);
            }
        }

        // Return the newly formed array data.
        return $newLinesOfData;
    }

    /**
     * Encode the data into its native format.
     *
     * @param  mixed  $data
     * @param  mixed  $omit
     * @param  string $delim
     * @param  string $esc
     * @param  string $dt
     * @return string
     */
    public static function encode($data, $omit = null, $delim = ',', $esc = '"', $dt = null)
    {
        $output    = '';
        $tempAry   = [];
        $headerAry = [];

        if (null === $omit) {
            $omit = [];
        } else if (!is_array($omit)) {
            $omit = [$omit];
        }

        // Initialize and clean the header fields.
        foreach ($data as $ary) {
            $tempAry = array_keys((array)$ary);
        }

        foreach ($tempAry as $key => $value) {
            if (!in_array($value, $omit)) {
                $v = (string)$value;
                if (strpos($v, $esc) !== false) {
                    $v = str_replace($esc, $esc . $esc, $v);
                }
                if (strpos($v, $delim) !== false) {
                    $v = $esc . $v . $esc;
                }
                $headerAry[] = (string)$v;
            }
        }

        // Set header output.
        $output .= implode($delim, $headerAry) . "\n";

        // Initialize and clean the field values.
        foreach ($data as $value) {
            $rowAry = [];
            foreach ($value as $key => $val) {
                if (!in_array($key, $omit)) {
                    if (null !== $dt) {
                        if ((strtotime($val) !== false) && ((stripos($key, 'date') !== false || (stripos($key, 'time') !== false)))) {
                            $v = (date($dt, strtotime($val)) != '12/31/1969') ? date($dt, strtotime((string)$val)) : '';
                        } else {
                            $v = (string)$val;
                        }
                    } else {
                        $v = (string)$val;
                    }
                    if (strpos($v, $esc) !== false) {
                        $v = str_replace($esc, $esc . $esc, $v);
                    }
                    if (strpos($v, $delim) !== false) {
                        $v = $esc . (string)$v . $esc;
                    }
                    $rowAry[] = $v;
                }
            }

            // Set field output.
            $output .= implode($delim, $rowAry) . "\n";
        }

        return $output;
    }

}
