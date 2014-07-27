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
 * Html data type class
 *
 * @category   Pop
 * @package    Pop_Data
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Html implements TypeInterface
{

    /**
     * Decode the data into PHP.
     *
     * @param  string  $data
     * @param  boolean $preserve
     * @return mixed
     */
    public static function decode($data, $preserve = false)
    {
        $nodes = [];

        if ($preserve) {
            $matches = [];
            preg_match_all('/<!\[cdata\[(.*?)\]\]>/is', $data, $matches);

            foreach ($matches[0] as $match) {
                $strip = str_replace(
                    ['<![CDATA[', ']]>', '<', '>'],
                    ['', '', '&lt;', '&gt;'],
                    $match
                );
                $data = str_replace($match, $strip, $data);
            }

            $nodes = json_decode(json_encode((array) simplexml_load_string($data)), true);
        } else {
            $xml = new \SimpleXMLElement($data);
            $i = 1;

            foreach ($xml as $key => $node) {
                $objs = [];
                foreach ($node as $k => $v) {
                    $j = 1;
                    if (array_key_exists((string)$k, $objs)) {
                        while (array_key_exists($k . '_' . $j, $objs)) {
                            $j++;
                        }
                        $newKey = (string)$k . '_' . $j;
                    } else {
                        $newKey = (string)$k;
                    }
                    $objs[$newKey] = trim((string)$v);
                }
                $nodes[$key . '_' . $i] = $objs;
                $i++;
            }
        }

        return $nodes;
    }

    /**
     * Encode the data into its native format.
     *
     * @param  mixed  $data
     * @param  array  $options
     * @return string
     */
    public static function encode($data, array $options = null)
    {
        $output  = '';
        $indent  = (isset($options['indent']))  ? $options['indent'] : '    ';
        $date    = (isset($options['date']))    ? $options['date'] : 'M j, Y';
        $exclude = (isset($options['exclude'])) ? $options['exclude'] : [];
        $process = null;
        $submit  = null;

        if (!is_array($exclude)) {
            $exclude = [$exclude];
        }

        $output .= $indent . '    <table';
        if (isset($options['table']) && is_array($options['table'])) {
            foreach ($options['table'] as $attrib => $value) {
                if ($attrib != 'headers') {
                    $output .= ' ' . $attrib . '="' . $value . '"';
                }
            }
        }
        $output .= '>' . PHP_EOL;

        // Initialize and clean the header fields.
        foreach ($data as $ary) {
            $tempAry = array_keys((array)$ary);
        }

        $headerAry = [];
        $headerKeysAry = [];
        foreach ($tempAry as $value) {
            if (!in_array($value, $exclude)) {
                $headerKeysAry[] = $value;
                if (isset($options['table']) && isset($options['table']['headers']) && is_array($options['table']['headers']) && array_key_exists($value, $options['table']['headers'])) {
                    $headerAry[] = $options['table']['headers'][$value];
                } else {
                    $headerAry[] = ucwords(str_replace('_', ' ' , (string)$value));
                }
            }
        }

        // Set header output.
        $output .= $indent . '        <tr><th>' . implode('</th><th>', $headerAry) . '</th></tr>' . PHP_EOL;
        $pos = strrpos($output, '<th') + 3;
        $output = substr($output, 0, $pos) . substr($output, $pos);

        $rowValuesAry = [];
        // Initialize and clean the field values.
        $i = 1;
        foreach ($data as $value) {
            $rowAry = [];
            foreach ($value as $key => $val) {
                if (!in_array($key, $exclude)) {
                    if ((strtotime((string)$val) !== false) && ((stripos($key, 'date') !== false) || (stripos($key, 'time') !== false))) {
                        $v = (date($date, strtotime($val)) != '12/31/1969') ? date($date, strtotime((string)$val)) : '';
                    } else {
                        $v = (string)$val;
                    }
                    if (isset($options[$key])) {
                        $tmpl = $options[$key];
                        foreach ($value as $ky => $vl) {
                            if ((strtotime((string)$vl) !== false) && ((stripos($key, 'date') !== false) || (stripos($key, 'time') !== false))) {
                                $vl = (date($date, strtotime($vl)) != '12/31/1969') ? date($date, strtotime((string)$vl)) : '';
                            } else {
                                $vl = (string)$vl;
                            }
                            $tmpl = str_replace('[{' . $ky . '}]', $vl, $tmpl);
                        }
                        $v = $tmpl;
                    }
                    $rowAry[] = $v;
                }
            }
            $i++;

            foreach ($rowAry as $k => $r) {
                $rowAry[$headerKeysAry[$k]] = $r;
                unset($rowAry[$k]);
            }

            $rowValuesAry[] = $rowAry;

            // Set field output.
            $output .= $indent . '        <tr><td>' . implode('</td><td>', $rowAry) . '</td></tr>' . PHP_EOL;
            $pos    = strrpos($output, '<td') + 3;
            $output = substr($output, 0, $pos) . substr($output, $pos);
        }

        $output .= $indent . '    </table>' . PHP_EOL;

        return $output;
    }

}
