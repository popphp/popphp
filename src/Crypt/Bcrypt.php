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
 * Bcrypt class
 *
 * @category   Pop
 * @package    Pop_Crypt
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Bcrypt extends AbstractCrypt
{

    /**
     * Cost
     * @var string
     */
    protected $cost = '08';

    /**
     * Prefix
     * @var string
     */
    protected $prefix = '$2y$';

    /**
     * Constructor
     *
     * Instantiate the bcrypt object.
     *
     * @param  string $cost
     * @param  string $prefix
     * @throws Exception
     * @return self
     */
    public function __construct($cost = '08', $prefix = '$2y$')
    {
        if (CRYPT_BLOWFISH == 0) {
            throw new Exception('Error: Blowfish hashing is not supported on this system.');
        }
        $this->setCost($cost);
        $this->setPrefix($prefix);
    }

    /**
     * Set the cost
     *
     * @param  string $cost
     * @return self
     */
    public function setCost($cost = '08')
    {
        if ((int)$cost < 4) {
            $cost = '04';
        }
        if ((int)$cost > 31) {
            $cost = '31';
        }

        $this->cost = $cost;
        return $this;
    }

    /**
     * Get the cost
     *
     * @return string
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set the prefix
     *
     * @param  string $prefix
     * @return self
     */
    public function setPrefix($prefix = '$2y$')
    {
        if (($prefix != '$2a$') && ($prefix != '$2x$') && ($prefix != '$2y$')) {
            $prefix = '$2y$';
        }

        if (version_compare(PHP_VERSION, '5.3.7') < 0) {
            $prefix = '$2a$';
        }
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Get the prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
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

        $this->salt = (null === $this->salt) ?
            substr(str_replace('+', '.', base64_encode($this->generateRandomString(32))), 0, 22) :
            substr(str_replace('+', '.', base64_encode($this->salt)), 0, 22);

        $hash = crypt($string, $this->prefix . $this->cost . '$' . $this->salt);

        if (strlen($hash) < 13) {
            throw new Exception('Error: There was an error with the bcrypt generation.');
        }

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

        if (strlen($result) < 13) {
            throw new Exception('Error: There was an error with the bcrypt verification.');
        }

        return ($result === $hash);
    }

}
