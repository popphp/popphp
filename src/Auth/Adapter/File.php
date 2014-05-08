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

use Pop\Auth\Auth;

/**
 * File auth adapter class
 *
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class File extends AbstractAdapter
{

    /**
     * Field delimiter
     * @var string
     */
    protected $delimiter = null;

    /**
     * Users
     * @var array
     */
    protected $users = array();

    /**
     * Constructor
     *
     * Instantiate the AuthFile object
     *
     * @param string $filename
     * @param string $delimiter
     * @throws Exception
     * @return \Pop\Auth\Adapter\File
     */
    public function __construct($filename, $delimiter = '|')
    {
        if (!file_exists($filename)) {
            throw new Exception('The access file does not exist.');
        }

        $this->delimiter = $delimiter;
        $this->parse($filename);
    }

    /**
     * Method to authenticate the user
     *
     * @param  string $username
     * @param  string $password
     * @param  int    $encryption
     * @param  array  $options
     * @return int
     */
    public function authenticate($username, $password, $encryption, $options)
    {
        if (!array_key_exists($username, $this->users)) {
            return Auth::USER_NOT_FOUND;
        }

        if (!$this->verifyPassword($this->users[$username]['password'], $password, $encryption, $options)) {
            return Auth::PASSWORD_INCORRECT;
        }

        if ((strtolower($this->users[$username]['access']) == 'blocked') ||
            (null === $this->users[$username]['access']) ||
            (is_numeric($this->users[$username]['access']) && ($this->users[$username]['access'] == 0))) {
            return Auth::USER_IS_BLOCKED;
        } else {
            $this->user = $this->users[$username];
            return Auth::USER_IS_VALID;
        }
    }

    /**
     * Method to parse the source file.
     *
     * @param  string $filename
     * @return void
     */
    protected function parse($filename)
    {
        $entries = explode("\n", trim(file_get_contents($filename)));

        foreach ($entries as $entry) {
            $ent = trim($entry);
            $entAry = explode($this->delimiter , $ent);
            if (isset($entAry[0]) && isset($entAry[1])) {
                $this->users[$entAry[0]] = array(
                    'username' => $entAry[0],
                    'password' => $entAry[1],
                    'access'   => (isset($entAry[2]) ? $entAry[2] : null)
                );
            }
        }
    }
}
