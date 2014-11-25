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
 * SHA Crypt class
 *
 * @category   Pop
 * @package    Pop_Crypt
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Sha extends AbstractCrypt
{

    /**
     * Bits
     * @var int
     */
    protected $bits = 512;

    /**
     * Rounds
     * @var int
     */
    protected $rounds = 5000;

    /**
     * Constructor
     *
     * Instantiate the sha object.
     *
     * @param  int $bits
     * @param  int $rounds
     * @throws Exception
     * @return self
     */
    public function __construct($bits = 512, $rounds = 5000)
    {
        $this->setBits($bits);
        $this->setRounds($rounds);
    }

    /**
     * Method to set the cost
     *
     * @param  int $bits
     * @throws Exception
     * @return self
     */
    public function setBits($bits = 512)
    {
        $bits = (int)$bits;

        if (($bits != 256) && ($bits != 512)) {
            throw new Exception('Error: The bit setting must be 256 or 512');
        }
        if (($bits == 256) && (CRYPT_SHA256 == 0)) {
            throw new Exception('Error: SHA 256 hashing is not supported on this system.');
        }
        if (($bits == 512) && (CRYPT_SHA512 == 0)) {
            throw new Exception('Error: SHA 512 hashing is not supported on this system.');
        }

        $this->bits = $bits;
        return $this;
    }

    /**
     * Get the bits
     *
     * @return int
     */
    public function getBits()
    {
        return $this->bits;
    }

    /**
     * Set the rounds
     *
     * @param  int $rounds
     * @return self
     */
    public function setRounds($rounds = 5000)
    {
        $rounds = (int)$rounds;

        if ($rounds < 1000) {
            $rounds = 1000;
        } else if ($rounds > 999999999) {
            $rounds = 999999999;
        }

        $this->rounds = $rounds;
        return $this;
    }

    /**
     * Get the rounds
     *
     * @return int
     */
    public function getRounds()
    {
        return $this->rounds;
    }

    /**
     * Create the hashed value
     *
     * @param  string $string
     * @throws Exception
     * @return string
     */
    public function create($string)
    {
        $hash = null;
        $prefix = ($this->bits == 512) ? '$6$' : '$5$';
        $prefix .= 'rounds=' . $this->rounds . '$';

        $this->salt = (null === $this->salt) ?
            substr(str_replace('+', '.', base64_encode($this->generateRandomString(32))), 0, 16) :
            substr(str_replace('+', '.', base64_encode($this->salt)), 0, 16);

        $hash = crypt($string, $prefix . $this->salt);

        return $hash;
    }

    /**
     * Verify the hashed value
     *
     * @param  string $string
     * @param  string $hash
     * @throws Exception
     * @return boolean
     */
    public function verify($string, $hash)
    {
        $result = crypt($string, $hash);
        return ($result === $hash);
    }

}
