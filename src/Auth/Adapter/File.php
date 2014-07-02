<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth\Adapter;

/**
 * File auth adapter class
 *
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class File extends AbstractAdapter
{

    /**
     * Constant for MD5 encryption
     * @var string
     */
    const ENCRYPT_MD5 = 'ENCRYPT_MD5';

    /**
     * Constant for MD5 encryption
     * @var string
     */
    const ENCRYPT_SHA1 = 'ENCRYPT_SHA1';

    /**
     * Constant for no encryption
     * @var string
     */
    const ENCRYPT_NONE = 'ENCRYPT_NONE';

    /**
     * Auth file
     * @var string
     */
    protected $filename = null;

    /**
     * Auth file encryption
     * @var string
     */
    protected $encryption = 'ENCRYPT_MD5';

    /**
     * Auth realm
     * @var string
     */
    protected $realm = null;

    /**
     * Auth file delimiter
     * @var string
     */
    protected $delimiter = ':';

    /**
     * Constructor
     *
     * Instantiate the File auth adapter object
     *
     * @param string $filename
     * @param array  $options
     * @return \Pop\Auth\Adapter\File
     */
    public function __construct($filename, array $options = null)
    {
        $this->setFilename($filename);

        if (null !== $options) {
            if (isset($options['encryption'])) {
                $this->setEncryption($options['encryption']);
            }
            if (isset($options['realm'])) {
                $this->setRealm($options['realm']);
            }
            if (isset($options['delimiter'])) {
                $this->setDelimiter($options['delimiter']);
            }
        }
    }

    /**
     * Method to get the auth filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Method to get the auth encryption
     *
     * @return string
     */
    public function getEncryption()
    {
        return $this->encryption;
    }

    /**
     * Method to get the auth realm
     *
     * @return string
     */
    public function getRealm()
    {
        return $this->realm;
    }

    /**
     * Method to get the auth file delimiter
     *
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * Method to set the auth filename
     *
     * @param string $filename
     * @throws Exception
     * @return \Pop\Auth\Adapter\File
     */
    public function setFilename($filename)
    {
        if (!file_exists($filename)) {
            throw new Exception('The access file does not exist.');
        }

        $this->filename = $filename;
        return $this;
    }

    /**
     * Method to set the auth encryption
     *
     * @param string $encryption
     * @return \Pop\Auth\Adapter\File
     */
    public function setEncryption($encryption)
    {
        $this->encryption = $encryption;
        return $this;
    }

    /**
     * Method to set the auth realm
     *
     * @param string $realm
     * @return \Pop\Auth\Adapter\File
     */
    public function setRealm($realm)
    {
        $this->realm = $realm;
        return $this;
    }

    /**
     * Method to set the auth file delimiter
     *
     * @param string $delimiter
     * @return \Pop\Auth\Adapter\File
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    /**
     * Method to authenticate the user
     *
     * @return int
     */
    public function authenticate()
    {
        $string = $this->username . $this->delimiter;
        $hash   = $this->username . $this->delimiter;

        if (null !== $this->realm) {
            $string .= $this->realm . $this->delimiter;
            $hash   .= $this->realm . $this->delimiter;
        }

        $hash .= $this->password;

        switch ($this->encryption) {
            case self::ENCRYPT_MD5:
                $hash = md5($hash);
                break;

            case self::ENCRYPT_SHA1:
                $hash = sha1($hash);
                break;
        }

        $string .= $hash;
        $lines = file($this->filename);

        $result = 0;
        foreach ($lines as $line) {
            if (trim($line) == $string) {
                $result = 1;
            }
        }

        return $result;
    }

}
