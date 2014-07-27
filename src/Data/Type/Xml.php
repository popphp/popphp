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
 * XML data type class
 *
 * @category   Pop
 * @package    Pop_Data
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Xml implements TypeInterface
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
     * @param  mixed   $data
     * @param  string  $table
     * @param  boolean $pma
     * @return string
     */
    public static function encode($data, $table = null, $pma = false)
    {
        $xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?" . ">\n<data>\n";

        if ($pma) {
            foreach($data as $key => $ary) {
                $table = (null === $table) ? substr($key, 0, strrpos($key, '_')) : $table;
                $xml .= "    <table name=\"" . $table . "\">\n";
                foreach ($ary as $k => $v) {
                    $xml .= "        <column name=\"" . $k . "\">" . $v . "</column>\n";
                }
                $xml .= "    </table>\n";
            }
        } else {
            foreach($data as $key => $ary) {
                $table = (null === $table) ? substr($key, 0, strrpos($key, '_')) : $table;
                if (empty($table)) {
                    $table = 'row';
                }
                $xml .= "    <" . $table . ">\n";
                foreach ($ary as $k => $v) {
                    $xml .= "        <" . $k . ">" . $v . "</" . $k . ">\n";

                }
                $xml .= "    </" . $table . ">\n";
            }
        }

        $xml .= "</data>\n";

        return $xml;
    }

}
