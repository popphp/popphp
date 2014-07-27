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
 * YAML data type class
 *
 * @category   Pop
 * @package    Pop_Data
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Yaml implements TypeInterface
{

    /**
     * Decode the data into PHP.
     *
     * @param  string $data
     * @return mixed
     */
    public static function decode($data)
    {
        $eol     = (strpos($data, "-\r\n") !== false) ? "-\r\n" : "-\n";
        $yaml    = substr($data, (strpos($data, $eol) + strlen($eol)));
        $yamlAry = explode($eol, $yaml);

        $nodes = [];
        $i = 1;

        foreach ($yamlAry as $value) {
            $objs = explode("\n", trim($value));
            $ob = [];
            foreach ($objs as $v) {
                $vAry = explode(':', $v);
                $val  = trim($vAry[1]);
                $val  = substr($val, 1, -1);
                $ob[trim($vAry[0])] = stripslashes($val);
            }
            $nodes['row_' . $i] = $ob;
            $i++;
        }

        return $nodes;
    }

    /**
     * Encode the data into its native format.
     *
     * @param  mixed  $data
     * @return string
     */
    public static function encode($data)
    {
        $yaml = "%YAML 1.1\n---\n";

        foreach($data as $key => $ary) {
            foreach ($ary as $k => $v) {
                $yaml .= " " . $k . ": \"" . addslashes($v) . "\"\n";
            }
            $yaml .= "-\n";
        }

        return $yaml;
    }

}
