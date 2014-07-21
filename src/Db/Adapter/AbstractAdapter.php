<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
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
     * Get the available database adapters
     *
     * @return array
     */
    public static function getAvailableAdapters()
    {
        $pdoDrivers = (class_exists('Pdo', false)) ? \PDO::getAvailableDrivers() : [];

        return [
            'mysqli' => (class_exists('mysqli', false)),
            'oracle' => (function_exists('oci_connect')),
            'pdo'    => [
                'mysql'  => (in_array('mysql', $pdoDrivers)),
                'pgsql'  => (in_array('pgsql', $pdoDrivers)),
                'sqlite' => (in_array('sqlite', $pdoDrivers)),
                'sqlsrv' => (in_array('sqlsrv', $pdoDrivers))
            ],
            'pgsql'  => (function_exists('pg_connect')),
            'sqlite' => (class_exists('Sqlite3', false)),
            'sqlsrv' => (function_exists('sqlsrv_connect'))
        ];
    }

    /**
     * Get the available image library adapters
     *
     * @param  string $adapter
     * @return boolean
     */
    public static function isAvailable($adapter)
    {
        $adapter = strtolower($adapter);
        $result  = false;
        $type    = null;

        $pdoDrivers = (class_exists('Pdo', false)) ? \PDO::getAvailableDrivers() : [];
        if (strpos($adapter, 'pdo_') !== false) {
            $type    = substr($adapter, 4);
            $adapter = 'pdo';
        }

        switch ($adapter) {
            case 'mysql':
            case 'mysqli':
                $result = (class_exists('mysqli', false));
                break;
            case 'oci':
            case 'oracle':
                $result = (function_exists('oci_connect'));
                break;
            case 'pdo':
                $result = (in_array($type, $pdoDrivers));
                break;
            case 'pgsql':
                $result = (function_exists('pg_connect'));
                break;
            case 'sqlite':
                $result = (class_exists('Sqlite3', false));
                break;
            case 'sqlsrv':
                $result = (function_exists('sqlsrv_connect'));
                break;
        }

        return $result;
    }

    /**
     * Constructor
     *
     * Instantiate the database adapter object.
     *
     * @param  array $options
     * @return AbstractAdapter
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
     * @throws Exception
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
