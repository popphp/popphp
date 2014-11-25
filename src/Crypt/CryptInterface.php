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
 * Crypt interface
 *
 * @category   Pop
 * @package    Pop_Crypt
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
interface CryptInterface
{

    /**
     * Set the salt
     *
     * @param  string $salt
     * @return self
     */
    public function setSalt($salt = null);

    /**
     * Get the salt
     *
     * @return string
     */
    public function getSalt();

    /**
     * Create the hashed value
     *
     * @param  string $string
     * @return string
     */
    public function create($string);

    /**
     * Create the hashed value
     *
     * @param  string $string
     * @param  string $hash
     * @return boolean
     */
    public function verify($string, $hash);

}
