<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Filter
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Filter;

/**
 * Filter string class
 *
 * @category   Pop
 * @package    Pop_Filter
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class String
{

    /**
     * Constant for alpha-numeric + special characters
     * @var int
     */
    const ALL = 1;

    /**
     * Constant for alpha-numeric
     * @var int
     */
    const ALPHANUM = 2;

    /**
     * Constant for alpha
     * @var int
     */
    const ALPHA = 3;

    /**
     * Constant for mixed case
     * @var int
     */
    const MIXED = 4;

    /**
     * Constant for lower case only
     * @var int
     */
    const LOWER = 5;

    /**
     * Constant for upper case only
     * @var int
     */
    const UPPER = 6;

    /**
     * Method to generate a random alphanumeric string of a predefined length.
     *
     * @param  int  $length
     * @param  int  $type
     * @param  int  $case
     * @return string
     */
    public static function random($length, $type = String::ALL, $case = String::MIXED)
    {
        $str = null;

        $chars = array(
            0 => str_split('abcdefghjkmnpqrstuvwxyz'),
            1 => str_split('ABCDEFGHJKLMNPQRSTUVWXYZ'),
            2 => str_split('23456789'),
            3 => str_split('!#$%&()*+-,.:;=?@[]^_{|}')
        );

        $indices = array(0, 1, 2, 3);

        switch ($type) {
            case self::ALPHANUM:
                $indices = array(0, 1, 2);
                break;
            case self::ALPHA:
                $indices = array(0, 1);
                break;
        }

        switch ($case) {
            case self::LOWER:
                unset($indices[1]);
                break;
            case self::UPPER:
                unset($indices[0]);
                break;
        }

        $indices = array_values($indices);

        for ($i = 0; $i < $length; $i++) {
            $index = $indices[rand(0, (count($indices) - 1))];
            $subIndex = rand(0, (count($chars[$index]) - 1));
            $str .= $chars[$index][$subIndex];
        }

        return $str;
    }

    /**
     * Method to return a substring of the string between two delimiters.
     *
     * @param  string $string
     * @param  int    $start
     * @param  int    $end
     * @return string
     */
    public static function between($string, $start, $end)
    {
        $startPos = (strpos($string, $start) !== false)
            ? (strpos($string, $start) + strlen($start)) : 0;

        $string = substr($string, $startPos);
        $string = (strpos($string, $end) !== false)
            ? substr($string, 0, (strpos($string, $end))) : $string;

        return $string;
    }

    /**
     * Method to simulate escaping a string for DB entry, much like
     * mysql_real_escape_string(), but without requiring a DB connection.
     *
     * The parameter $all is boolean flag that, when set to true, causes the
     * '%' and '_' characters to be escaped as well.
     *
     * @param  string $string
     * @param  boolean $all
     * @return string
     */
    public static function escape($string, $all = false)
    {
        $search = array('\\', "\n", "\r", "\x00", "\x1a", '\'', '"');
        $replace = array('\\\\', "\\n", "\\r", "\\x00", "\\x1a", '\\\'', '\\"');

        $str = str_replace($search, $replace, $string);

        if ($all) {
            $str = str_replace('%', '\%', $str);
            $str = str_replace('_', '\_', $str);
        }

        return $str;
    }

    /**
     * Method to clean the string of any of the standard MS Word based
     * characters and return the newly edited string
     *
     * @param  string $string
     * @param  boolean $html
     * @return string
     */
    public static function clean($string, $html = false)
    {
        if ($html) {
            $apos = "&#39;";
            $quot = "&#34;";
        } else {
            $apos = "'";
            $quot = '"';
        }

        $string = str_replace(chr(146), $apos, $string);
        $string = str_replace(chr(147), $quot, $string);
        $string = str_replace(chr(148), $quot, $string);
        $string = str_replace(chr(150), "&#150;", $string);
        $string = str_replace(chr(133), "...", $string);

        return $string;
    }

    /**
     * Method to convert newlines from DOS to UNIX
     *
     * @param  string $string
     * @return string
     */
    public static function dosToUnix($string)
    {
        return str_replace(chr(13) . chr(10), chr(10), $string);
    }

    /**
     * Method to convert newlines from UNIX to DOS
     *
     * @param  string $string
     * @return string
     */
    public static function unixToDos($string)
    {
        return str_replace(chr(10), chr(13) . chr(10), $string);
    }

    /**
     * Method to convert the string into an SEO-friendly slug.
     *
     * @param  string $string
     * @param  string $sep
     * @return string
     */
    public static function slug($string, $sep = null)
    {
        if (strlen($string) > 0) {
            if (null !== $sep) {
                $strAry = explode($sep, $string);
                $tmpStrAry = array();

                foreach ($strAry as $value) {
                    $str = strtolower($value);
                    $str = str_replace('&', 'and', $str);
                    $str = preg_replace('/([^a-zA-Z0-9 \-\/])/', '', $str);
                    $str = str_replace('/', '-', $str);
                    $str = str_replace(' ', '-', $str);
                    $str = preg_replace('/-*-/', '-', $str);
                    $tmpStrAry[] = $str;
                }

                $string = implode('/', $tmpStrAry);
                $string = str_replace('/-', '/', $string);
                $string = str_replace('-/', '/', $string);
            } else {
                $string = strtolower($string);
                $string = str_replace('&', 'and', $string);
                $string = preg_replace('/([^a-zA-Z0-9 \-\/])/', '', $string);
                $string = str_replace('/', '-', $string);
                $string = str_replace(' ', '-', $string);
                $string = preg_replace('/-*-/', '-', $string);
            }
        } else {
            $string = '';
        }

        return $string;
    }

    /**
     * Method to convert any links in the string to clickable HTML links.
     *
     * @param  string $string
     * @param  boolean $target
     * @return string
     */
    public static function links($string, $target = false)
    {
        $target = ($target == true) ? 'target="_blank" ' : '';

        $string = preg_replace('/[ftp|http|https]+:\/\/[^\s]*/', '<a href="$0">$0</a>', $string);
        $string = preg_replace('/\s[\w]+[a-zA-Z0-9\.\-\_]+(\.[a-zA-Z]{2,4})/', ' <a href="http://$0">$0</a>', $string);
        $string = preg_replace('/[a-zA-Z0-9\.\-\_+%]+@[a-zA-Z0-9\-\_\.]+\.[a-zA-Z]{2,4}/', '<a href="mailto:$0">$0</a>', $string);
        $string = str_replace(
            array(
                'href="http:// ',
                'href="https:// ',
                '"> ',
                '<a '
            ),
            array(
                'href="http://',
                'href="https://',
                '">',
                '<a ' . $target
            ),
            $string
        );

        return $string;
    }

    /**
     * Method to convert the string from camelCase to dash format
     *
     * @param  string $string
     * @return string
     */
    public static function camelCaseToDash($string)
    {
       return self::convertCamelCase($string, '-');
    }

    /**
     * Method to convert the string from camelCase to separator format
     *
     * @param  string $string
     * @param  string $sep
     * @return string
     */
    public static function camelCaseToSeparator($string, $sep = DIRECTORY_SEPARATOR)
    {
        return self::convertCamelCase($string, $sep);
    }

    /**
     * Method to convert the string from camelCase to under_score format
     *
     * @param  string $string
     * @return string
     */
    public static function camelCaseToUnderscore($string)
    {
        return self::convertCamelCase($string, '_');
    }

    /**
     * Method to convert the string from dash to camelCase format
     *
     * @param  string $string
     * @return string
     */
    public static function dashToCamelcase($string)
    {
        $strAry = explode('-', $string);
        $camelCase = null;
        $i = 0;

        foreach ($strAry as $word) {
            if ($i == 0) {
                $camelCase .= $word;
            } else {
                $camelCase .= ucfirst($word);
            }
            $i++;
        }

        return $camelCase;
    }

    /**
     * Method to convert the string from dash to separator format
     *
     * @param  string $string
     * @param  string $sep
     * @return string
     */
    public static function dashToSeparator($string, $sep = DIRECTORY_SEPARATOR)
    {
        return str_replace('-', $sep, $string);
    }

    /**
     * Method to convert the string from dash to under_score format
     *
     * @param  string $string
     * @return string
     */
    public static function dashToUnderscore($string)
    {
        return str_replace('-', '_', $string);
    }

    /**
     * Method to convert the string from under_score to camelCase format
     *
     * @param  string $string
     * @return string
     */
    public static function underscoreToCamelcase($string)
    {
        $strAry = explode('_', $string);
        $camelCase = null;
        $i = 0;

        foreach ($strAry as $word) {
            if ($i == 0) {
                $camelCase .= $word;
            } else {
                $camelCase .= ucfirst($word);
            }
            $i++;
        }

        return $camelCase;
    }

    /**
     * Method to convert the string from under_score to dash format
     *
     * @param  string $string
     * @return string
     */
    public static function underscoreToDash($string)
    {
        return str_replace('_', '-', $string);
    }

    /**
     * Method to convert the string from under_score to separator format
     *
     * @param  string $string
     * @param  string $sep
     * @return string
     */
    public static function underscoreToSeparator($string, $sep = DIRECTORY_SEPARATOR)
    {
        return str_replace('_', $sep, $string);
    }

    /**
     * Method to convert a camelCase string using the $sep value passed
     *
     * @param string $string
     * @param string $sep
     * @return string
     */
    protected static function convertCamelCase($string, $sep)
    {
        $strAry = str_split($string);
        $convert = null;
        $i = 0;

        foreach ($strAry as $chr) {
            if ($i == 0) {
                $convert .= strtolower($chr);
            } else {
                $convert .= (ctype_upper($chr)) ? ($sep . strtolower($chr)) : $chr;
            }
            $i++;
        }

        return $convert;
    }

}
