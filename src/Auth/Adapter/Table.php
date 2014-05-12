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
 * Table auth adapter class
 *
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Table implements AdapterInterface
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
     * Constructor
     *
     * Instantiate the Table auth adapter object
     *
     * @param string $tableName
     * @param string $usernameField
     * @param string $passwordField
     * @return \Pop\Auth\Adapter\Table
     */
    public function __construct($tableName, $usernameField = 'username', $passwordField = 'password')
    {
        $this->setTableName($tableName);
        $this->setUsernameField($usernameField);
        $this->setPasswordField($passwordField);
    }

    /**
     * Method to get the table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Method to get the username field
     *
     * @return string
     */
    public function getUsernameField()
    {
        return $this->usernameField;
    }

    /**
     * Method to get the password field
     *
     * @return string
     */
    public function getPasswordField()
    {
        return $this->passwordField;
    }

    /**
     * Method to set the table name
     *
     * @param string $tableName
     * @return \Pop\Auth\Adapter\Table
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * Method to set the username field
     *
     * @param string $usernameField
     * @return \Pop\Auth\Adapter\Table
     */
    public function setUsernameField($usernameField)
    {
        $this->usernameField = $usernameField;
        return $this;
    }

    /**
     * Method to set the password field
     *
     * @param string $passwordField
     * @return \Pop\Auth\Adapter\Table
     */
    public function setPasswordField($passwordField)
    {
        $this->passwordField = $passwordField;
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
        $table = $this->tableName;

        $user = $table::findBy([
            $this->usernameField => $username,
            $this->passwordField => $password
        ]);

        return (int)(isset($user->$usernameField));
    }

}
