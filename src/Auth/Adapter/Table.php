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
class Table extends AbstractAdapter
{

    /**
     * DB table name / class name
     * @var string
     */
    protected $table = null;

    /**
     * Username field
     * @var string
     */
    protected $usernameField = 'username';

    /**
     * Password field
     * @var string
     */
    protected $passwordField = 'password';

    /**
     * Constructor
     *
     * Instantiate the Table auth adapter object
     *
     * @param string $table
     * @param array  $options
     * @return \Pop\Auth\Adapter\Table
     */
    public function __construct($table, array $options = null)
    {
        $this->setTable($table);
        if (null !== $options) {
            if (isset($options['usernameField'])) {
                $this->setUsernameField($options['usernameField']);
            }
            if (isset($options['passwordField'])) {
                $this->setPasswordField($options['passwordField']);
            }
        }
    }

    /**
     * Method to get the table name
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
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
     * @param string $table
     * @return \Pop\Auth\Adapter\Table
     */
    public function setTable($table)
    {
        $this->table = $table;
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
     * @return int
     */
    public function authenticate()
    {
        $table = $this->table;

        $user = $table::findBy([
            $this->usernameField => $this->username,
            $this->passwordField => $this->password
        ]);

        return (int)(isset($user->{$usernameField}));
    }

}
