<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Cache
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Cache\Adapter;

/**
 * SQLite cache adapter class
 *
 * @category   Pop
 * @package    Pop_Cache
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Sqlite implements AdapterInterface
{

    /**
     * Cache db file
     * @var string
     */
    protected $db = null;

    /**
     * Cache db table
     * @var string
     */
    protected $table = null;

    /**
     * Sqlite DB object (either a PDO or Sqlite3 object)
     * @var mixed
     */
    protected $sqlite = null;

    /**
     * Sqlite DB statement object (either a PDOStatement or SQLite3Stmt object)
     * @var mixed
     */
    protected $statement = null;

    /**
     * Database results
     * @var resource
     */
    protected $result;

    /**
     * PDO flag
     * @var boolean
     */
    protected $isPdo = false;

    /**
     * Constructor
     *
     * Instantiate the cache db object
     *
     * @param  string  $db
     * @param  string  $table
     * @param  boolean $pdo
     * @throws Exception
     * @return Sqlite
     */
    public function __construct($db, $table = 'pop_cache', $pdo = false)
    {
        $this->db    = $db;
        $this->table = addslashes($table);
        $dir         = dirname($this->db);

        // If the database file doesn't exist, create it.
        if (!file_exists($this->db)) {
            if (is_writable($dir)) {
                touch($db);
            } else {
                throw new Exception('Error: That cache db file and/or directory is not writable.');
            }
        }

        // Make it writable.
        chmod($this->db, 0777);

        // Check the permissions, access the database and check for the cache table.
        if (!is_writable($dir) || !is_writable($this->db)) {
            throw new Exception('Error: That cache db file and/or directory is not writable.');
        }

        if (!class_exists('Sqlite3', false) && !class_exists('Pdo', false)) {
            throw new Exception('Error: Neither SQLite3 or PDO are available.');
        }

        $pdoDrivers = (class_exists('Pdo', false)) ? \PDO::getAvailableDrivers() : [];
        if (!class_exists('Sqlite3', false) && !in_array('sqlite', $pdoDrivers)) {
            throw new Exception('Error: SQLite is not available.');
        } else if (($pdo) && !in_array('sqlite', $pdoDrivers)) {
            $pdo = false;
        } else if ((!$pdo) && !class_exists('Sqlite3', false)) {
            $pdo = true;
        }

        if ($pdo) {
            $this->sqlite = new \PDO('sqlite:' . $this->db);
            $this->isPdo  = true;
        } else {
            $this->sqlite = new \SQLite3($this->db);
        }

        $this->checkTable();
    }

    /**
     * Method to get the current cache db file.
     *
     * @return string
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Method to get the current cache db table.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Method to Set the cache db table.
     *
     * @param  string $table
     * @return Sqlite
     */
    public function setTable($table = 'pop_cache')
    {
        return $this->table = addslashes($table);
    }

    /**
     * Method to save a value to cache.
     *
     * @param  string $id
     * @param  mixed  $value
     * @param  string $time
     * @return void
     */
    public function save($id, $value, $time)
    {
        $timestamp = ($time != 0) ? time() + (int)$time : 0;

        // If the value doesn't exist, save the new value.
        if (!$this->load($id, $time)) {
            $sql = "INSERT INTO " . $this->table .
                " (id, value, time) VALUES (:id, :value, :time)";
            $params = [
                'id'    => sha1($id),
                'value' => serialize($value),
                'time'  => $timestamp
            ];
        // Else, update it.
        } else {
            $sql = "UPDATE " . $this->table .
                " SET value = :value, time = :time WHERE id = :id";
            $params = [
                'value' => serialize($value),
                'time'  => $timestamp,
                'id'    => sha1($id)
            ];
        }

        // Save value
        $this->prepare($sql)
             ->bindParams($params)
             ->execute();
    }

    /**
     * Method to load a value from cache.
     *
     * @param  string $id
     * @param  string $time
     * @return mixed
     */
    public function load($id, $time)
    {
        $value = false;

        // Determine if the value already exists.
        $rows = [];

        $this->prepare("SELECT * FROM " . $this->table . " WHERE id = :id")
             ->bindParams(['id' => sha1($id)])
             ->execute();

        if ($this->isPdo) {
            while (($row = $this->result->fetchAll(\PDO::FETCH_ASSOC)) != false) {
                $rows[] = $row;
            }
        } else {
            while (($row = $this->result->fetchArray(SQLITE3_ASSOC)) != false) {
                $rows[] = $row;
            }
        }

        // If the value is found, check expiration and return.
        if (count($rows) > 0) {
            $data      = $rows[0]['value'];
            $timestamp = $rows[0]['time'];
            if (($timestamp == 0) || ((time() - $timestamp) <= $time)) {
                $value = unserialize($data);
            }
        }

        return $value;
    }

    /**
     * Method to delete a value in cache.
     *
     * @param  string $id
     * @return void
     */
    public function remove($id)
    {
        $this->prepare("DELETE FROM " . $this->table . " WHERE id = :id")
             ->bindParams(['id' => sha1($id)])
             ->execute();
    }

    /**
     * Method to clear all stored values from cache.
     *
     * @return void
     */
    public function clear()
    {
        $this->query("DELETE FROM " . $this->table);
    }

    /**
     * Method to delete the entire database file
     *
     * @return void
     */
    public function delete()
    {
        if (file_exists($this->db)) {
            unlink($this->db);
        }
    }

    /**
     * Prepare a SQL query.
     *
     * @param  string $sql
     * @return Sqlite
     */
    protected function prepare($sql)
    {
        $this->statement = $this->sqlite->prepare($sql);
        return $this;
    }

    /**
     * Bind parameters to for a prepared SQL query.
     *
     * @param  array  $params
     * @return Sqlite
     */
    protected function bindParams($params)
    {
        foreach ($params as $dbColumnName => $dbColumnValue) {
            ${$dbColumnName} = $dbColumnValue;
            $this->statement->bindParam(':' . $dbColumnName, ${$dbColumnName});
        }

        return $this;
    }

    /**
     * Execute the prepared SQL query.
     *
     * @throws Exception
     * @return void
     */
    protected function execute()
    {
        if (null === $this->statement) {
            throw new Exception('Error: The database statement resource is not currently set.');
        }

        $this->result = $this->statement->execute();
    }

    /**
     * Execute the SQL query.
     *
     * @param  string $sql
     * @throws Exception
     * @return void
     */
    public function query($sql)
    {
        if ($this->isPdo) {
            $sth = $this->sqlite->prepare($sql);

            if (!($sth->execute())) {
                throw new Exception($sth->errorCode() . ': ' .  $sth->errorInfo());
            } else {
                $this->result = $sth;
            }
        } else {
            if (!($this->result = $this->sqlite->query($sql))) {
                throw new Exception('Error: ' . $this->sqlite->lastErrorCode() . ': ' . $this->sqlite->lastErrorMsg() . '.');
            }
        }
    }

    /**
     * Method to check if cache table exists
     *
     * @return void
     */
    protected function checkTable()
    {
        $tables = [];
        $sql    = "SELECT name FROM sqlite_master WHERE type IN ('table', 'view') AND name NOT LIKE 'sqlite_%' UNION ALL SELECT name FROM sqlite_temp_master WHERE type IN ('table', 'view') ORDER BY 1";

        if ($this->isPdo) {
            $sth = $this->sqlite->prepare($sql);
            $sth->execute();
            $result = $sth;
            while (($row = $result->fetch(\PDO::FETCH_ASSOC)) != false) {
                $tables[] = $row['name'];
            }
        } else {
            $result = $this->sqlite->query($sql);
            while (($row = $result->fetchArray(SQLITE3_ASSOC)) != false) {
                $tables[] = $row['name'];
            }
        }

        // If the cache table doesn't exist, create it.
        if (!in_array($this->table, $tables)) {
            $sql = 'CREATE TABLE IF NOT EXISTS "' .
                $this->table . '" ("id" VARCHAR PRIMARY KEY NOT NULL UNIQUE, "value" BLOB, "time" INTEGER)';

            if ($this->isPdo) {
                $sth = $this->sqlite->prepare($sql);
                $sth->execute();
            } else {
                $this->sqlite->query($sql);
            }
        }
    }

}
