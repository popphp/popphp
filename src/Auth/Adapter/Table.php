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
 * Table auth adapter class
 *
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Table extends AbstractAdapter
{

    /**
     * DB table name / class name
     * @var string
     */
    protected $tableName = null;

    /**
     * Username field
     * @var string
     */
    protected $usernameField = null;

    /**
     * Password field
     * @var string
     */
    protected $passwordField = null;

    /**
     * Access field
     * @var string
     */
    protected $accessField = null;

    /**
     * Constructor
     *
     * Instantiate the DbTable object
     *
     * @param string $tableName
     * @param string $usernameField
     * @param string $passwordField
     * @param string $accessField
     * @return \Pop\Auth\Adapter\Table
     */
    public function __construct($tableName, $usernameField = 'username', $passwordField = 'password', $accessField = null)
    {
        $this->tableName = $tableName;
        $this->usernameField = $usernameField;
        $this->passwordField = $passwordField;
        $this->accessField = $accessField;
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
        $access = null;

        $table = $this->tableName;
        $usernameField = $this->usernameField;
        $passwordField = $this->passwordField;
        $accessField = $this->accessField;

        $user = $table::findBy(array($this->usernameField => $username));

        if (!isset($user->$usernameField)) {
            return Auth::USER_NOT_FOUND;
        }

        if (!$this->verifyPassword($user->$passwordField, $password, $encryption, $options)) {
            return Auth::PASSWORD_INCORRECT;
        }

        if ((null !== $accessField) && ((strtolower($user->$accessField) == 'blocked') ||
            (null === $user->$accessField) ||
            (is_numeric($user->$accessField) && ($user->$accessField == 0)))) {
            return Auth::USER_IS_BLOCKED;
        } else {
            $this->user = $user->getValues();
            return Auth::USER_IS_VALID;
        }
    }

}
