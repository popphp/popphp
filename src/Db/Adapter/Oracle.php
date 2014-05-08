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
 * Oracle Db adapter class
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Oracle extends AbstractAdapter
{

    /**
     * Prepared statement
     * @var Resource
     */
    protected $statement = null;

    /**
     * Constructor
     *
     * Instantiate the Oracle database connection object.
     *
     * @param  array $options
     * @throws \Pop\Db\Adapter\Exception
     * @return \Pop\Db\Adapter\Oracle
     */
    public function __construct(array $options)
    {
        if (!isset($options['database']) || !isset($options['host']) || !isset($options['username']) || !isset($options['password'])) {
            throw new Exception('Error: The proper database credentials were not passed.');
        }

        $this->connection = oci_connect($options['username'], $options['password'], $options['host'] . '/' . $options['database']);

        if ($this->connection == false) {
            throw new Exception('Error: Could not connect to database. ' . oci_error());
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
        throw new Exception('Error: ' . oci_error($this->connection));
    }

    /**
     * Prepare a SQL query.
     *
     * @param  string $sql
     * @return \Pop\Db\Adapter\Oracle
     */
    public function prepare($sql)
    {
        $this->statement = oci_parse($this->connection, $sql);
        return $this;
    }

    /**
     * Bind parameters to for a prepared SQL query.
     *
     * @param  array  $params
     * @return \Pop\Db\Adapter\Oracle
     */
    public function bindParams($params)
    {
        foreach ($params as $dbColumnName => $dbColumnValue) {
            if (is_array($dbColumnValue)) {
                $i = 1;
                foreach ($dbColumnValue as $dbColumnVal) {
                    ${$dbColumnName . $i} = $dbColumnVal;
                    oci_bind_by_name($this->statement, ':' . $dbColumnName . $i, ${$dbColumnName . $i});
                    $i++;
                }
            } else {
                ${$dbColumnName} = $dbColumnValue;
                oci_bind_by_name($this->statement, ':' . $dbColumnName, ${$dbColumnName});
            }
        }

        return $this;
    }

    /**
     * Fetch and return the values.
     *
     * @return array
     */
    public function fetchResult()
    {
        $rows = array();

        while (($row = $this->fetch()) != false) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Execute the prepared SQL query.
     *
     * @throws \Pop\Db\Adapter\Exception
     * @return void
     */
    public function execute()
    {
        if (null === $this->statement) {
            throw new Exception('Error: The database statement resource is not currently set.');
        }

        oci_execute($this->statement);
    }

    /**
     * Execute the SQL query and create a result resource, or display the SQL error.
     *
     * @param  string $sql
     * @return void
     */
    public function query($sql)
    {
        $this->statement = oci_parse($this->connection, $sql);
        if (!($this->result = oci_execute($this->statement))) {
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
        if (!isset($this->statement)) {
            throw new Exception('Error: The database result resource is not currently set.');
        }

        return oci_fetch_array($this->statement, OCI_RETURN_NULLS+OCI_ASSOC);
    }

    /**
     * Return the escaped string value.
     *
     * @param  string $value
     * @return string
     */
    public function escape($value)
    {
        $search = array('\\', "\n", "\r", "\x00", "\x1a", '\'', '"');
        $replace = array('\\\\', "\\n", "\\r", "\\x00", "\\x1a", '\\\'', '\\"');

        return str_replace($search, $replace, $value);
    }

    /**
     * Return the auto-increment ID of the last query.
     *
     * @return int
     */
    public function lastId()
    {
        return null;
    }

    /**
     * Return the number of rows in the result.
     *
     * @throws \Pop\Db\Adapter\Exception
     * @return int
     */
    public function numRows()
    {
        if (isset($this->statement)) {
            return oci_num_rows($this->statement);
        } else {
            throw new Exception('Error: The database result resource is not currently set.');
        }
    }

    /**
     * Return the number of fields in the result.
     *
     * @throws \Pop\Db\Adapter\Exception
     * @return int
     */
    public function numFields()
    {
        if (isset($this->statement)) {
            return oci_num_fields($this->statement);
        } else {
            throw new Exception('Error: The database result resource is not currently set.');
        }
    }

    /**
     * Return the database version.
     *
     * @return string
     */
    public function version()
    {
        return oci_server_version($this->connection);
    }

    /**
     * Close the DB connection.
     *
     * @return void
     */
    public function disconnect()
    {
        if ($this->isConnected()) {
            oci_close($this->connection);
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

        $this->query("SELECT TABLE_NAME FROM USER_TABLES");
        while (($row = $this->fetch()) != false) {
            foreach($row as $value) {
                $tables[] = $value;
            }
        }

        return $tables;
    }

}
