<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
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
class File implements AdapterInterface
{

    /**
     * Auth file
     * @var string
     */
    protected $filename = null;

    /**
     * Auth file type
     * @var string
     */
    protected $type = 'Digest';

    /**
     * Auth file encryption
     * @var string
     */
    protected $encryption = 'md5';

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
            if (isset($options['type'])) {
                $this->type = $options['type'];
            }
            if (isset($options['encryption'])) {
                $this->encryption = $options['encryption'];
            }
            if (isset($options['realm'])) {
                $this->realm = $options['realm'];
            }
            if (isset($options['delimiter'])) {
                $this->delimiter = $options['delimiter'];
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
     * Method to get the auth type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
     * Method to set the auth type
     *
     * @param string $type
     * @return \Pop\Auth\Adapter\File
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * @param  string $username
     * @param  string $password
     * @return int
     */
    public function authenticate($username, $password)
    {

    }

}
