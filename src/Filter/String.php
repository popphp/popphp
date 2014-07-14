<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
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
 * @version    2.0.0a
 */
class String
{

    /**
     * Method to generate a random string of a predefined length.
     *
     * @param  int   $length
     * @param  array $options
     * @return string
     */
    public static function random($length, array $options = [])
    {
        $type = null; // 'alpha', 'alphanum'
        $case = null; // 'lower', 'upper

        if (isset($options['type'])) {
            $type = strtolower($options['type']);
        }

        if (isset($options['case'])) {
            $case = strtolower($options['case']);
        }

        $chars = [
            0 => str_split('abcdefghjkmnpqrstuvwxyz'),
            1 => str_split('ABCDEFGHJKLMNPQRSTUVWXYZ'),
            2 => str_split('23456789'),
            3 => str_split('!#$%&()*+-,.:;=?@[]^_{|}')
        ];

        switch ($type) {
            case 'alpha':
                $indices = [0, 1];
                break;
            case 'alphanum':
                $indices = [0, 1, 2];
                break;
            default:
                $indices = [0, 1, 2, 3];
        }

        switch ($case) {
            case 'lower':
                unset($indices[1]);
                break;
            case 'upper':
                unset($indices[0]);
                break;
        }

        $indices = array_values($indices);
        $str     = '';

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
                $tmpStrAry = [];

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
            [
                'href="http:// ',
                'href="https:// ',
                '"> ',
                '<a '
            ],
            [
                'href="http://',
                'href="https://',
                '">',
                '<a ' . $target
            ],
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
