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
 * Mcrypt class
 *
 * @category   Pop
 * @package    Pop_Crypt
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Mcrypt extends AbstractCrypt
{

    /**
     * Cipher
     * @var int
     */
    protected $cipher = null;

    /**
     * Mode
     * @var int
     */
    protected $mode = null;

    /**
     * Source
     * @var int
     */
    protected $source = null;

    /**
     * IV
     * @var string
     */
    protected $iv = null;

    /**
     * IV size
     * @var int
     */
    protected $ivSize = 0;

    /**
     * Constructor
     *
     * Instantiate the mcrypt object.
     *
     * @param  int $cipher
     * @param  int $mode
     * @param  int $source
     * @throws Exception
     * @return self
     */
    public function __construct($cipher = null, $mode = null, $source = null)
    {
        if (!function_exists('mcrypt_encrypt')) {
            throw new Exception('Error: The mcrypt extension is not installed.');
        }
        $this->setCipher($cipher);
        $this->setMode($mode);
        $this->setSource($source);
    }

    /**
     * Set the cipher
     *
     * @param  int $cipher
     * @return self
     */
    public function setCipher($cipher = null)
    {
        $this->cipher = (null !== $cipher) ? $cipher : MCRYPT_RIJNDAEL_256;
        return $this;
    }

    /**
     * Get the cipher
     *
     * @return int
     */
    public function getCipher()
    {
        return $this->cipher;
    }

    /**
     * Set the mode
     *
     * @param  int $mode
     * @return self
     */
    public function setMode($mode = null)
    {
        $this->mode = (null !== $mode) ? $mode : MCRYPT_MODE_CBC;
        return $this;
    }

    /**
     * Get the mode
     *
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Set the source
     *
     * @param  int $source
     * @return self
     */
    public function setSource($source = null)
    {
        $this->source = (null !== $source) ? $source : MCRYPT_RAND;
        return $this;
    }

    /**
     * Get the source
     *
     * @return int
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Get the iv
     *
     * @return string
     */
    public function getIv()
    {
        return $this->iv;
    }

    /**
     * Get the iv size
     *
     * @return int
     */
    public function getIvSize()
    {
        return $this->ivSize;
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

        $this->ivSize = mcrypt_get_iv_size($this->cipher, $this->mode);

        $this->salt = (null === $this->salt) ?
            substr(str_replace('+', '.', base64_encode($this->generateRandomString(32))), 0, $this->ivSize) :
            substr(str_replace('+', '.', base64_encode($this->salt)), 0, $this->ivSize);

        $this->iv = mcrypt_create_iv($this->ivSize, $this->source);

        $hash = mcrypt_encrypt($this->cipher, $this->salt, $string, $this->mode, $this->iv);
        $hash = base64_encode($this->iv . $this->salt . '$' . $hash);

        return $hash;
    }

    /**
     * Decrypt the hashed value
     *
     * @param  string $hash
     * @return string
     */
    public function decrypt($hash)
    {
        if ($this->ivSize == 0) {
            $this->ivSize = mcrypt_get_iv_size($this->cipher, $this->mode);
        }

        $decrypted = base64_decode($hash);

        $this->iv = substr($decrypted, 0, $this->ivSize);
        if (null === $this->salt) {
            $this->salt = substr($decrypted, $this->ivSize);
            $this->salt = substr($this->salt, 0, strpos($this->salt, '$'));
        }
        $decrypted = substr($decrypted, ($this->ivSize + strlen($this->salt) + 1));
        return trim(mcrypt_decrypt($this->cipher, $this->salt, $decrypted, $this->mode, $this->iv));
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
        if ($this->ivSize == 0) {
            $this->ivSize = mcrypt_get_iv_size($this->cipher, $this->mode);
        }

        $decrypted = base64_decode($hash);

        $this->iv = substr($decrypted, 0, $this->ivSize);
        if (null === $this->salt) {
            $this->salt = substr($decrypted, $this->ivSize);
            $this->salt = substr($this->salt, 0, strpos($this->salt, '$'));
        }
        $decrypted = substr($decrypted, ($this->ivSize + strlen($this->salt) + 1));
        $decrypted = trim(mcrypt_decrypt($this->cipher, $this->salt, $decrypted, $this->mode, $this->iv));

        return ($string === $decrypted);
    }

}
