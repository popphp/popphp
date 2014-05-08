<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Cache
 * @author     Nick Sagona, III <info@popphp.org>
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
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
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
     * PDO object
     * @var \Pdo
     */
    protected $pdo = null;

    /**
     * SQLite3 object
     * @var \Sqlite3
     */
    protected $sqlite = null;

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
     * @return \Pop\Cache\Adapter\Sqlite
     */
    public function __construct($db, $table = 'pop_cache', $pdo = false)
    {
        $this->db    = $db;
        $this->table = $table;
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

        $pdoDrivers = (class_exists('Pdo')) ? \PDO::getAvailableDrivers() : [];
        if (!class_exists('Sqlite3') && !in_array('sqlite', $pdoDrivers)) {
            throw new Exception('Error: SQLite is not available.');
        } else if (($pdo) && !in_array('sqlite', $pdoDrivers)) {
            $pdo = false;
        } else if ((!$pdo) && !class_exists('Sqlite3')) {
            $pdo = true;
        }

        if ($pdo) {
            $this->pdo   = new \PDO('sqlite:' . $this->db);
            $this->isPdo = true;
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
     * @return \Pop\Cache\Adapter\Sqlite
     */
    public function setTable($table = 'pop_cache')
    {
        return $this->table = $table;
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

        // Determine if the value already exists.
        $rows = [];
        $sql  = "SELECT * FROM " . addslashes($this->table) . " WHERE id = '" . sha1(addslashes($id)) . "'";

        if ($this->isPdo) {
            $sth = $this->pdo->prepare($sql);
            $sth->execute();
            $result = $sth;
            while (($row = $result->fetchAll(\PDO::FETCH_ASSOC)) != false) {
                $rows[] = $row;
            }
        } else {
            $result = $this->sqlite->query($sql);
            while (($row = $result->fetchArray(SQLITE3_ASSOC)) != false) {
                $rows[] = $row;
            }
        }

        // If the value exists, update it.
        if (count($rows) > 0) {
            $sql = "UPDATE " . addslashes($this->table) .
                " SET value = '" . addslashes(serialize($value)) . "', time = '" . $timestamp .
                "' WHERE id = '" . sha1(addslashes($id)) . "'";
        // Else, save the new value.
        } else {
            $sql = "INSERT INTO " . addslashes($this->table) .
                " (id, value, time) VALUES ('" . sha1(addslashes($id)) . "', '" . addslashes(serialize($value)) . "', '" . $timestamp . "')";
        }

        if ($this->isPdo) {
            $sth = $this->pdo->prepare($sql);
            $sth->execute();
        } else {
            $this->sqlite->query($sql);
        }
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
        $sql  = "SELECT * FROM " . addslashes($this->table) . " WHERE id = '" . sha1(addslashes($id)) . "'";

        if ($this->isPdo) {
            $sth = $this->pdo->prepare($sql);
            $sth->execute();
            $result = $sth;
            while (($row = $result->fetchAll(\PDO::FETCH_ASSOC)) != false) {
                $rows[] = $row;
            }
        } else {
            $result = $this->sqlite->query($sql);
            while (($row = $result->fetchArray(SQLITE3_ASSOC)) != false) {
                $rows[] = $row;
            }
        }

        // If the value is found, check expiration and return.
        if (count($rows) > 0) {
            $data      = stripslashes($rows[0]['value']);
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
        $sql = "DELETE FROM " . addslashes($this->table) . " WHERE id = '" . sha1(addslashes($id)) . "'";
        if ($this->isPdo) {
            $sth = $this->pdo->prepare($sql);
            $sth->execute();
        } else {
            $this->sqlite->query($sql);
        }
    }

    /**
     * Method to clear all stored values from cache.
     *
     * @return void
     */
    public function clear()
    {
        $sql = "DELETE FROM " . addslashes($this->table);
        if ($this->isPdo) {
            $sth = $this->pdo->prepare($sql);
            $sth->execute();
        } else {
            $this->sqlite->query($sql);
        }
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
     * Method to check if cache table exists
     *
     * @return void
     */
    protected function checkTable()
    {
        $tables = [];
        $sql    = "SELECT name FROM sqlite_master WHERE type IN ('table', 'view') AND name NOT LIKE 'sqlite_%' UNION ALL SELECT name FROM sqlite_temp_master WHERE type IN ('table', 'view') ORDER BY 1";

        if ($this->isPdo) {
            $sth = $this->pdo->prepare($sql);
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
                addslashes($this->table) . '" ("id" VARCHAR PRIMARY KEY NOT NULL UNIQUE, "value" BLOB, "time" INTEGER)';

            if ($this->isPdo) {
                 $sth = $this->pdo->prepare($sql);
                $sth->execute();
            } else {
                $this->sqlite->query($sql);
            }
        }
    }

}
