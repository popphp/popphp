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
namespace Pop\Pdf\Font\TrueType\Table\Cmap;

/**
 * CMAP byte-encoding class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class ByteEncoding
{

    /**
     * Method to parse the Byte Encoding (Format 0) CMAP data
     *
     * @param  string $data
     * @return array
     */
    public static function parseData($data)
    {
        $ary = array();

        for ($i = 0; $i < strlen($data); $i++) {
            $ary[$i] = new \ArrayObject(array(
                'hex'   => bin2hex($data[$i]),
                'ascii' => ord($data[$i]),
                'char'  => chr(ord($data[$i]))
            ), \ArrayObject::ARRAY_AS_PROPS);
        }

        return $ary;
    }

}