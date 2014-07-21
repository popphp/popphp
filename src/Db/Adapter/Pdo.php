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
 * PDO Db adapter class
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Pdo extends AbstractAdapter
{

    /**
     * Database
     * @var string
     */
    protected $database = null;

    /**
     * PDO DSN
     * @var string
     */
    protected $dsn = null;

    /**
     * PDO DB Type
     * @var string
     */
    protected $dbtype = null;

    /**
     * Statement placeholder
     * @var string
     */
    protected $placeholder = null;

    /**
     * Constructor
     *
     * Instantiate the PDO database connection object.
     *
     * @param  array $options
     * @throws Exception
     * @return Pdo
     */
    public function __construct(array $options)
    {
        // Default to localhost
        if (!isset($options['host'])) {
            $options['host'] = 'localhost';
        }

        if (!isset($options['type']) || !isset($options['database'])) {
            throw new Exception('Error: The proper database credentials were not passed.');
        }

        try {
            $this->database = $options['database'];
            $this->dbtype = strtolower($options['type']);
            if ($this->dbtype == 'sqlite') {
                $this->dsn = $this->dbtype . ':' . $options['database'];
                $this->connection = new \PDO($this->dsn);
            } else {
                if (!isset($options['host']) || !isset($options['username']) || !isset($options['password'])) {
                    throw new Exception('Error: The proper database credentials were not passed.');
                }

                if ($this->dbtype == 'sqlsrv') {
                    $this->dsn = $this->dbtype . ':Server=' . $options['host'] . ';Database=' . $options['database'];
                } else {
                    $this->dsn = $this->dbtype . ':host=' . $options['host'] . ';dbname=' . $options['database'];
                }

                $this->connection = new \PDO($this->dsn, $options['username'], $options['password']);
            }
        } catch (\PDOException $e) {
            throw new Exception('Error: Could not connect to database. ' . $e->getMessage());
        }
    }

    /**
     * Check if PDO is installed.
     *
     * @param  string $type
     * @return boolean
     */
    public static function isInstalled($type = null)
    {
        if (null === $type) {
            return (class_exists('Pdo', false));
        } else {
            return self::isAvailable('pdo_' . $type);
        }
    }

    /**
     * Get PDO DSN
     *
     * @return string
     */
    public function getDsn()
    {
        return $this->dsn;
    }

    /**
     * Get PDO DB Type
     *
     * @return string
     */
    public function getDbtype()
    {
        return $this->dbtype;
    }

    /**
     * Throw an exception upon a database error.
     *
     * @param  string $code
     * @param  array  $info
     * @throws Exception
     * @return void
     */
    public function showError($code = null, $info = null)
    {
        $errorMessage = null;

        if ((null === $code) && (null === $info)) {
            $errorCode = $this->connection->errorCode();
            $errorInfo = $this->connection->errorInfo();
        } else {
            $errorCode = $code;
            $errorInfo = $info;
        }

        if (is_array($errorInfo)) {
            $errorMessage = null;
            if (isset($errorInfo[1])) {
                $errorMessage .= $errorInfo[1];
            }
            if (isset($errorInfo[2])) {
                $errorMessage .= ' : ' . $errorInfo[2];
            }
        } else {
            $errorMessage = $errorInfo;
        }

        throw new Exception('Error: ' . $errorCode . ' => ' . $errorMessage  . '.');
    }

    /**
     * Prepare a SQL query.
     *
     * @param  string $sql
     * @param  array  $attribs
     * @return Pdo
     */
    public function prepare($sql, $attribs = null)
    {
        if (strpos($sql, '?') !== false) {
            $this->placeholder = '?';
        } else if (strpos($sql, ':') !== false) {
            $this->placeholder = ':';
        }

        if ((null !== $attribs) && is_array($attribs)) {
            $this->statement = $this->connection->prepare($sql, $attribs);
        } else {
            $this->statement = $this->connection->prepare($sql);
        }

        return $this;
    }

    /**
     * Bind parameters to for a prepared SQL query.
     *
     * @param  array  $params
     * @return Pdo
     */
    public function bindParams($params)
    {
        if ($this->placeholder == '?') {
            $i = 1;
            foreach ($params as $dbColumnName => $dbColumnValue) {
                if (is_array($dbColumnValue)) {
                    foreach ($dbColumnValue as $dbColumnVal) {
                        ${$dbColumnName} = $dbColumnVal;
                        $this->statement->bindParam($i, ${$dbColumnName});
                        $i++;
                    }
                } else {
                    ${$dbColumnName} = $dbColumnValue;
                    $this->statement->bindParam($i, ${$dbColumnName});
                    $i++;
                }
            }
        } else if ($this->placeholder == ':') {
            foreach ($params as $dbColumnName => $dbColumnValue) {
                if (is_array($dbColumnValue)) {
                    $i = 1;
                    foreach ($dbColumnValue as $dbColumnVal) {
                        ${$dbColumnName . $i} = $dbColumnVal;
                        $this->statement->bindParam(':' . $dbColumnName . $i, ${$dbColumnName . $i});
                        $i++;
                    }
                } else {
                    ${$dbColumnName} = $dbColumnValue;
                    $this->statement->bindParam(':' . $dbColumnName, ${$dbColumnName});
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
        return $this->statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Execute the prepared SQL query.
     *
     * @throws Exception
     * @return void
     */
    public function execute()
    {
        if (null === $this->statement) {
            throw new Exception('Error: The database statement resource is not currently set.');
        }

        $this->result = $this->statement->execute();
    }

    /**
     * Execute the SQL query and create a result resource, or display the SQL error.
     *
     * @param  string $sql
     * @return void
     */
    public function query($sql)
    {
        $sth = $this->connection->prepare($sql);

        if (!($sth->execute())) {
            $this->showError($sth->errorCode(), $sth->errorInfo());
        } else {
            $this->result = $sth;
        }
    }

    /**
     * Return the results array from the results resource.
     *
     * @throws Exception
     * @return array
     */
    public function fetch()
    {
        if (!isset($this->result)) {
            throw new Exception('Error: The database result resource is not currently set.');
        }

        return $this->result->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Return the escaped string value.
     *
     * @param  string $value
     * @return string
     */
    public function escape($value)
    {
        return substr($this->connection->quote($value), 1, -1);
    }

    /**
     * Return the auto-increment ID of the last query.
     *
     * @return int
     */
    public function lastId()
    {
        $id = null;

        // If the DB type is PostgreSQL
        if ($this->dbtype == 'pgsql') {
            $this->query("SELECT lastval();");
            if (isset($this->result)) {
                $insert_row = $this->result->fetch();
                $id = $insert_row[0];
            }
        // Else, if th eDB type is SQLSrv
        } else if ($this->dbtype == 'sqlsrv') {
            $this->query('SELECT SCOPE_IDENTITY() as Current_Identity');
            $row = $this->fetch();
            $id = (isset($row['Current_Identity'])) ? $row['Current_Identity'] : null;
        // Else, just
        } else {
            $id = $this->connection->lastInsertId();
        }

        return $id;
    }

    /**
     * Return the number of rows in the result.
     *
     * @throws Exception
     * @return int
     */
    public function numberOfRows()
    {
        if (!isset($this->result)) {
            throw new Exception('Error: The database result resource is not currently set.');
        }

        return $this->result->rowCount();
    }

    /**
     * Return the number of fields in the result.
     *
     * @throws Exception
     * @return int
     */
    public function numberOfFields()
    {
        if (!isset($this->result)) {
            throw new Exception('Error: The database result resource is not currently set.');
        }

        return $this->result->columnCount();
    }

    /**
     * Return the database version.
     *
     * @return string
     */
    public function version()
    {
        return 'PDO ' . substr($this->dsn, 0, strpos($this->dsn, ':')) . ' ' . $this->connection->getAttribute(\PDO::ATTR_SERVER_VERSION);
    }

    /**
     * Close the DB connection.
     *
     * @return void
     */
    public function disconnect()
    {
        if ($this->isConnected()) {
            $this->connection = null;
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

        if (stripos($this->dsn, 'sqlite') !== false) {
            $sql = "SELECT name FROM sqlite_master WHERE type IN ('table', 'view') AND name NOT LIKE 'sqlite_%' UNION ALL SELECT name FROM sqlite_temp_master WHERE type IN ('table', 'view') ORDER BY 1";

            $this->query($sql);
            while (($row = $this->fetch()) != false) {
                $tables[] = $row['name'];
            }
        } else {
            if (stripos($this->dsn, 'pgsql') !== false) {
                $sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'";
            } else if (stripos($this->dsn, 'sqlsrv') !== false) {
                $sql = "SELECT name FROM " . $this->database . ".sysobjects WHERE xtype = 'U'";
            } else {
                $sql = 'SHOW TABLES';
            }
            $this->query($sql);
            while (($row = $this->fetch()) != false) {
                foreach($row as $value) {
                    $tables[] = $value;
                }
            }
        }

        return $tables;
    }

}
