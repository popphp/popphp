<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Crypt
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Crypt;

/**
 * MD5 Crypt class
 *
 * @category   Pop
 * @package    Pop_Crypt
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Md5 extends AbstractCrypt
{

    /**
     * Constructor
     *
     * Instantiate the md5 object.
     *
     * @throws Exception
     * @return self
     */
    public function __construct()
    {
        if (CRYPT_MD5 == 0) {
            throw new Exception('Error: MD5 hashing is not supported on this system.');
        }
    }

    /**
     * Create the hashed value
     *
     * @param  string $string
     * @return string
     */
    public function create($string)
    {
        $hash = null;

        $this->salt = (null === $this->salt) ?
            substr(str_replace('+', '.', base64_encode($this->generateRandomString(32))), 0, 9) :
            substr(str_replace('+', '.', base64_encode($this->salt)), 0, 9);

        $hash = crypt($string, '$1$' . $this->salt);

        return $hash;
    }

    /**
     * Verify the hashed value
     *
     * @param  string $string
     * @param  string $hash
     * @return boolean
     */
    public function verify($string, $hash)
    {
        $result = crypt($string, $hash);
        return ($result === $hash);
    }

}
