<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Db\Adapter;

/**
 * Db adapter interface
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
interface AdapterInterface
{

    /**
     * Throw an exception upon a database error.
     *
     * @throws Exception
     * @return void
     */
    public function showError();

    /**
     * Execute the SQL query and create a result resource, or display the SQL error.
     *
     * @param  string $sql
     * @return void
     */
    public function query($sql);

    /**
     * Return the results array from the results resource.
     *
     * @throws Exception
     * @return array
     */
    public function fetch();

    /**
     * Return the escaped string value.
     *
     * @param  string $value
     * @return string
     */
    public function escape($value);

    /**
     * Return the auto-increment ID of the last query.
     *
     * @return int
     */
    public function lastId();

    /**
     * Return the number of rows in the result.
     *
     * @throws Exception
     * @return int
     */
    public function numberOfRows();

    /**
     * Return the number of fields in the result.
     *
     * @throws Exception
     * @return int
     */
    public function numberOfFields();

    /**
     * Determine whether or not an result resource exists
     *
     * @return boolean
     */
    public function hasResult();

    /**
     * Get the result resource
     *
     * @return resource
     */
    public function getResult();

    /**
     * Determine whether or not connected
     *
     * @return boolean
     */
    public function isConnected();

    /**
     * Get the connection resource
     *
     * @return resource
     */
    public function getConnection();

    /**
     * Disconnect from the database
     *
     * @return void
     */
    public function disconnect();

    /**
     * Get an array of the tables of the database.
     *
     * @return array
     */
    public function getTables();

    /**
     * Return the database version.
     *
     * @return string
     */
    public function version();

    /**
     * Return if the adapter is a PDO adapter
     *
     * @return boolean
     */
    public function isPdo();

}
