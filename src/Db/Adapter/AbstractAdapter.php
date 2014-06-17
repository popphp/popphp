<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Db\Adapter;

/**
 * Db abstract adapter class
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractAdapter implements AdapterInterface
{

    /**
     * Database results
     * @var resource
     */
    protected $result;

    /**
     * Default database connection
     * @var resource
     */
    protected $connection;

    /**
     * Prepared statement
     * @var mixed
     */
    protected $statement = null;

    /**
     * Database tables
     * @var array
     */
    protected $tables = [];

    /**
     * Constructor
     *
     * Instantiate the database adapter object.
     *
     * @param  array $options
     * @return \Pop\Db\Adapter\AbstractAdapter
     */
    abstract public function __construct(array $options);

    /**
     * Throw an exception upon a database error.
     *
     * @throws Exception
     * @return void
     */
    abstract public function showError();

    /**
     * Prepare a SQL query.
     *
     * @param  string $sql
     * @return AbstractAdapter
     */
    abstract public function prepare($sql);

    /**
     * Bind parameters to a prepared SQL query.
     *
     * @param  array $params
     * @return AbstractAdapter
     */
    abstract public function bindParams($params);

    /**
     * Execute the prepared SQL query.
     *
     * @throws \Pop\Db\Adapter\Exception
     * @return void
     */
    abstract public function execute();

    /**
     * Execute the SQL query and create a result resource, or display the SQL error.
     *
     * @param  string $sql
     * @return void
     */
    abstract public function query($sql);

    /**
     * Return the results array from the results resource.
     *
     * @throws Exception
     * @return array
     */
    abstract public function fetch();

    /**
     * Return the escaped string value.
     *
     * @param  string $value
     * @return string
     */
    abstract public function escape($value);

    /**
     * Return the auto-increment ID of the last query.
     *
     * @return int
     */
    abstract public function lastId();

    /**
     * Return the number of rows in the result.
     *
     * @throws Exception
     * @return int
     */
    abstract public function numberOfRows();

    /**
     * Return the number of fields in the result.
     *
     * @throws Exception
     * @return int
     */
    abstract public function numberOfFields();

    /**
     * Determine whether or not an result resource exists
     *
     * @return boolean
     */
    public function hasResult()
    {
        return is_resource($this->result);
    }

    /**
     * Get the result resource
     *
     * @return resource
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Determine whether or not connected
     *
     * @return boolean
     */
    public function isConnected()
    {
        return is_resource($this->connection);
    }

    /**
     * Get the connection resource
     *
     * @return resource
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Disconnect from the database
     *
     * @return void
     */
    abstract public function disconnect();

    /**
     * Get an array of the tables of the database.
     *
     * @return array
     */
    public function getTables()
    {
        if (count($this->tables) == 0) {
            $this->tables = $this->loadTables();
        }

        return $this->tables;
    }

    /**
     * Return the database version.
     *
     * @return string
     */
    abstract public function version();

    /**
     * Return if the adapter is a PDO adapter
     *
     * @return boolean
     */
    public function isPdo() {
        return (stripos(get_class($this), 'pdo') !== false);
    }

    /**
     * Load the tables of the database into an array.
     *
     * @return array
     */
    abstract protected function loadTables();

}
