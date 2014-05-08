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
 * MySQL Db adapter class
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Mysql extends AbstractAdapter
{

    /**
     * Constructor
     *
     * Instantiate the Mysql database connection object.
     *
     * @param  array $options
     * @throws \Pop\Db\Adapter\Exception
     * @return \Pop\Db\Adapter\Mysql
     */
    public function __construct(array $options)
    {
        if (!isset($options['database']) || !isset($options['host']) || !isset($options['username']) || !isset($options['password'])) {
            throw new Exception('Error: The proper database credentials were not passed.');
        }

        $this->connection = mysql_connect($options['host'], $options['username'], $options['password']);

        // Select the DB to use, or display the SQL error.
        if (!(mysql_select_db($options['database'], $this->connection))) {
            $this->showError();
        }
    }

    /**
     * Throw an exception upon a database error.
     *
     * @throws \Pop\Db\Adapter\Exception
     * @return void
     */
    public function showError()
    {
        throw new Exception('Error: ' . mysql_errno() . ' => ' . mysql_error() . '.');
    }

    /**
     * Execute the prepared SQL query.
     *
     * @param  string $sql
     * @return void
     */
    public function execute($sql)
    {
        $this->query($sql);
    }

    /**
     * Execute the SQL query and create a result resource, or display the SQL error.
     *
     * @param  string $sql
     * @return void
     */
    public function query($sql)
    {
        if (!($this->result = mysql_query($sql, $this->connection))) {
            $this->showError();
        }
    }

    /**
     * Return the results array from the results resource.
     *
     * @throws \Pop\Db\Adapter\Exception
     * @return array
     */
    public function fetch()
    {
        if (!isset($this->result)) {
            throw new Exception('Error: The database result resource is not currently set.');
        }

        return mysql_fetch_array($this->result, MYSQL_ASSOC);
    }

    /**
     * Return the escaped string value.
     *
     * @param  string $value
     * @return string
     */
    public function escape($value)
    {
        return mysql_real_escape_string($value);
    }

    /**
     * Return the auto-increment ID of the last query.
     *
     * @return int
     */
    public function lastId()
    {
        return mysql_insert_id();
    }

    /**
     * Return the number of rows in the result.
     *
     * @throws \Pop\Db\Adapter\Exception
     * @return int
     */
    public function numRows()
    {
        if (!isset($this->result)) {
            throw new Exception('Error: The database result resource is not currently set.');
        }

        return mysql_num_rows($this->result);
    }

    /**
     * Return the number of fields in the result.
     *
     * @throws \Pop\Db\Adapter\Exception
     * @return int
     */
    public function numFields()
    {
        if (!isset($this->result)) {
            throw new Exception('Error: The database result resource is not currently set.');
        }

        return mysql_num_fields($this->result);
    }

    /**
     * Return the database version.
     *
     * @return string
     */
    public function version()
    {
        return 'MySQL ' . mysql_get_server_info($this->connection);
    }

    /**
     * Close the DB connection.
     *
     * @return void
     */
    public function disconnect()
    {
        if ($this->isConnected()) {
            mysql_close($this->connection);
        }
    }

    /**
     * Get an array of the tables of the database.
     *
     * @return array
     */
    protected function loadTables()
    {
        $tables = array();

        $this->query('SHOW TABLES');
        while (($row = $this->fetch()) != false) {
            foreach($row as $value) {
                $tables[] = $value;
            }
        }

        return $tables;
    }

}
