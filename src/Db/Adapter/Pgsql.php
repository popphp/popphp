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
 * PostgreSQL Db adapter class
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Pgsql extends AbstractAdapter
{

    /**
     * Statement index
     * @var int
     */
    protected static $statementIndex = 0;

    /**
     * Prepared statement name
     * @var string
     */
    protected $statementName = null;

    /**
     * Prepared statement parameters
     * @var array
     */
    protected $parameters = null;

    /**
     * Prepared SQL string
     * @var string
     */
    protected $sql = null;

    /**
     * Constructor
     *
     * Instantiate the PostgreSQL database connection object.
     *
     * @param  array $options
     * @throws \Pop\Db\Adapter\Exception
     * @return \Pop\Db\Adapter\Pgsql
     */
    public function __construct(array $options)
    {
        // Default to localhost
        if (!isset($options['host'])) {
            $options['host'] = 'localhost';
        }

        if (!isset($options['database']) || !isset($options['host']) || !isset($options['username']) || !isset($options['password'])) {
            throw new Exception('Error: The proper database credentials were not passed.');
        }

        $this->connection = pg_connect("host=" . $options['host'] . " dbname=" . $options['database'] . " user=" . $options['username'] . " password=" . $options['password']);

        // Select the DB to use, or display the SQL error.
        if (!$this->connection) {
            throw new Exception('Error: There was an error connecting to the database.');
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
        throw new Exception(pg_last_error($this->connection) . '.');
    }

    /**
     * Prepare a SQL query.
     *
     * @param  string $sql
     * @return \Pop\Db\Adapter\Pgsql
     */
    public function prepare($sql)
    {
        $this->sql = $sql;
        $this->statementName = 'pop_db_adapter_pgsql_statement_' . ++static::$statementIndex;
        $this->statement = pg_prepare($this->connection, $this->statementName, $this->sql);
        return $this;
    }

    /**
     * Bind parameters to for a prepared SQL query.
     *
     * @param  string|array  $params
     * @return \Pop\Db\Adapter\Pgsql
     */
    public function bindParams($params)
    {
        if (!is_array($params)) {
            $this->parameters = [$params];
        } else {
            $this->parameters = [];
            foreach ($params as $param) {
                if (is_array($param)) {
                    foreach ($param as $par) {
                        $this->parameters[] = $par;
                    }
                } else {
                    $this->parameters[] = $param;
                }
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
        $rows = [];

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

        if ((null !== $this->parameters) && is_array($this->parameters))  {
            $this->result = pg_execute($this->connection, $this->statementName, $this->parameters);
            $this->parameters = null;
        } else {
            $this->query($this->sql);
        }
    }

    /**
     * Execute the SQL query and create a result resource, or display the SQL error.
     *
     * @param  string $sql
     * @return void
     */
    public function query($sql)
    {
        if (!($this->result = pg_query($this->connection, $sql))) {
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

        return pg_fetch_array($this->result, null, PGSQL_ASSOC);
    }

    /**
     * Return the escaped string value.
     *
     * @param  string $value
     * @return string
     */
    public function escape($value)
    {
        return pg_escape_string($value);
    }

    /**
     * Return the auto-increment ID of the last query.
     *
     * @return int
     */
    public function lastId()
    {
        $insert_query = pg_query("SELECT lastval();");
        $insert_row = pg_fetch_row($insert_query);

        return $insert_row[0];
    }

    /**
     * Return the number of rows in the result.
     *
     * @throws \Pop\Db\Adapter\Exception
     * @return int
     */
    public function numberOfRows()
    {
        if (!isset($this->result)) {
            throw new Exception('Error: The database result resource is not currently set.');
        }

        return pg_num_rows($this->result);
    }

    /**
     * Return the number of fields in the result.
     *
     * @throws \Pop\Db\Adapter\Exception
     * @return int
     */
    public function numberOfFields()
    {
        if (!isset($this->result)) {
            throw new Exception('Error: The database result resource is not currently set.');
        }

        return pg_num_fields($this->result);
    }

    /**
     * Return the database version.
     *
     * @return string
     */
    public function version()
    {
        $ver = pg_version($this->connection);
        return 'PostgreSQL ' . $ver['server'];
    }

    /**
     * Close the DB connection.
     *
     * @return void
     */
    public function disconnect()
    {
        if ($this->isConnected()) {
            pg_close($this->connection);
        }
    }

    /**
     * Get an array of the tables of the database.
     *
     * @return array
     */
    protected function loadTables()
    {
        $tables = [];

        $this->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
        while (($row = $this->fetch()) != false) {
            foreach($row as $value) {
                $tables[] = $value;
            }
        }

        return $tables;
    }

}
