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
interface CryptInterface
{

    /**
     * Method to set the salt
     *
     * @param  string $salt
     * @return self
     */
    public function setSalt($salt = null);

    /**
     * Method to get the salt
     *
     * @return string
     */
    public function getSalt();

    /**
     * Method to create the hashed value
     *
     * @param  string $string
     * @return string
     */
    public function create($string);

    /**
     * Method to create the hashed value
     *
     * @param  string $string
     * @param  string $hash
     * @return boolean
     */
    public function verify($string, $hash);

}
