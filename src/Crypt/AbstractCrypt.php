<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Crypt
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Crypt;

/**
 * Crypt interface
 *
 * @category   Pop
 * @package    Pop_Crypt
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractCrypt implements CryptInterface
{

    /**
     * Method to generate a random alphanumeric string of a predefined length.
     *
     * @param  int  $length
     * @return string
     */
    protected function generateRandomString($length)
    {
        $str   = null;
        $chars = str_split('abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789');

        for ($i = 0; $i < $length; $i++) {
            $index = mt_rand(0, (count($chars) - 1));
            $str .= $chars[$index];
        }

        return $str;
    }
}
