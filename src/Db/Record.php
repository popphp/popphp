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
namespace Pop\Db;

/**
 * Record class
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Record
{

    /**
     * Constant to set the type to INSERT on save
     * @var int
     */
    const INSERT = 0;

    /**
     * Constant to set the type to UPDATE on save
     * @var int
     */
    const UPDATE = 1;

    /**
     * Database connection(s)
     * @var array
     */
    public static $db = array('default' => null);

    /**
     * Rows of multiple return results from a database query
     * in an ArrayObject format.
     * @var array
     */
    public $rows = array();

    /**
     * Record interface
     * @var \Pop\Db\Record\AbstractRecord
     */
    protected $interface = null;

    /**
     * Table prefix
     * @var string
     */
    protected $prefix = null;

    /**
     * Table name of the database table
     * @var string
     */
    protected $tableName = null;

    /**
     * Primary ID column name of the database table
     * @var string|array
     */
    protected $primaryId = 'id';

    /**
     * Property that determines whether or not the primary ID is auto-increment or not
     * @var boolean
     */
    protected $auto = true;

    /**
     * Column names of the database table
     * @var array
     */
    protected $columns = array();

    /**
     * Flag on whether or not to use prepared statements
     * @var boolean
     */
    protected $usePrepared = true;

    /**
     * Constructor
     *
     * Instantiate the database record object.
     *
     * @param  array $columns
     * @param  \Pop\Db\Db    $db
     * @return \Pop\Db\Record
     */
    public function __construct(array $columns = null, \Pop\Db\Db $db = null)
    {
        $class = get_class($this);

        if (null !== $db) {
            $class::setDb($db);
        }

        // If the $columns argument is set, set the columns properties.
        if (null !== $columns) {
            $this->columns = $columns;
        }

        if (null === $this->tableName) {
            if (strpos($class, '_') !== false) {
                $cls = substr($class, (strrpos($class, '_') + 1));
            } else if (strpos($class, '\\') !== false) {
                $cls = substr($class, (strrpos($class, '\\') + 1));
            } else {
                $cls = $class;
            }
            $this->tableName = $this->prefix . $this->camelCaseToUnderscore($cls);
        } else {
            $this->tableName = $this->prefix . $this->tableName;
        }

        $options = array(
            'tableName' => $this->tableName,
            'primaryId' => $this->primaryId,
            'auto'      => $this->auto
        );

        $type = self::getDb()->getAdapterType();

        if (($type == 'Mysql') || (!$this->usePrepared)) {
            $this->interface = new Record\Escaped(self::getDb(), $options);
        } else {
            $this->interface = new Record\Prepared(self::getDb(), $options);
        }
    }

    /**
     * Set DB connection
     *
     * @param  \Pop\Db\Db $db
     * @param  boolean    $isDefault
     * @return void
     */
    public static function setDb(\Pop\Db\Db $db, $isDefault = false)
    {
        $class = get_called_class();

        static::$db[$class] = $db;
        if (($isDefault) || ($class === __CLASS__)) {
            static::$db['default'] = $db;
        }
    }

    /**
     * Get DB connection
     *
     * @throws Exception
     * @return \Pop\Db\Db
     */
    public static function getDb()
    {
        $class = get_called_class();

        if (isset(static::$db[$class])) {
            return static::$db[$class];
        } else if (isset(static::$db['default'])) {
            return static::$db['default'];
        } else {
            throw new Exception('No database adapter was found.');
        }
    }

    /**
     * Find a database row by the primary ID passed through the method argument.
     *
     * @param  mixed $id
     * @param  int   $limit
     * @param  int    $offset
     * @return \Pop\Db\Record
     */
    public static function findById($id, $limit = null, $offset = null)
    {
        $record = new static();
        $record->interface->findById($id, $limit, $offset);
        $record->setResults($record->interface->getResult());

        return $record;
    }

    /**
     * Find a database row by the column passed through the method argument.
     *
     * @param  array  $columns
     * @param  string $order
     * @param  int    $limit
     * @param  int    $offset
     * @return \Pop\Db\Record
     */
    public static function findBy(array $columns, $order = null, $limit = null, $offset = null)
    {
        $record = new static();
        $record->interface->findBy($columns, $order, $limit, $offset);
        $record->setResults($record->interface->getResult());

        return $record;
    }


    /**
     * Find all of the database rows by the column passed through the method argument.
     *
     * @param  string $order
     * @param  array  $columns
     * @param  int    $limit
     * @param  int    $offset
     * @return \Pop\Db\Record
     */
    public static function findAll($order = null, array $columns = null, $limit = null, $offset = null)
    {
        $record = new static();
        $record->interface->findAll($order, $columns, $limit, $offset);
        $record->setResults($record->interface->getResult());

        return $record;
    }

    /**
     * Execute a custom prepared SQL query.
     *
     * @param  string $sql
     * @param  array  $params
     * @return \Pop\Db\Record
     */
    public static function execute($sql, $params = null)
    {
        $record = new static();
        $record->interface->execute($sql, $params);
        $record->setResults($record->interface->getResult());

        return $record;
    }

    /**
     * Execute a custom SQL query.
     *
     * @param  string $sql
     * @return \Pop\Db\Record
     */
    public static function query($sql)
    {
        $record = new static();
        $record->interface->query($sql);
        $record->setResults($record->interface->getResult());

        return $record;
    }

    /**
     * Get total count of records
     *
     * @param  array $columns
     * @return mixed
     */
    public static function getCount(array $columns = null)
    {
        $record = new static();
        $record->interface->getCount($columns);
        $record->setResults($record->interface->getResult());

        return $record->total_count;
    }

    /**
     * Get the SQL object.
     *
     * @return \Pop\Db\Sql
     */
    public static function getSql()
    {
        $record = new static();
        return $record->interface->sql();
    }

    /**
     * Get table info anf return as an array.
     *
     * @return array
     */
    public static function getTableInfo()
    {
        $record = new static();
        $tableName = $record->getFullTableName();
        $info = array(
            'tableName' => $tableName,
            'primaryId' => $record->getId(),
            'columns'   => array()
        );

        $sql = null;
        $field = 'column_name';
        $type = 'data_type';
        $nullField = 'is_nullable';

        // SQLite
        if ($record->interface->sql()->getDbType() == \Pop\Db\Sql::SQLITE) {
            $sql = 'PRAGMA table_info(\'' . $tableName . '\')';
            $field = 'name';
            $type = 'type';
            $nullField = 'notnull';
        // PostgreSQL
        } else if ($record->interface->sql()->getDbType() == \Pop\Db\Sql::PGSQL) {
            $sql = 'SELECT * FROM information_schema.COLUMNS WHERE table_name = \'' . $tableName . '\' ORDER BY ordinal_position ASC';
        // SQL Server
        } else if ($record->interface->sql()->getDbType() == \Pop\Db\Sql::SQLSRV) {
            $sql = 'SELECT c.name \'column_name\', t.Name \'data_type\', c.is_nullable, c.column_id FROM sys.columns c INNER JOIN sys.types t ON c.system_type_id = t.system_type_id WHERE object_id = OBJECT_ID(\'' . $tableName . '\') ORDER BY c.column_id ASC';
        // Oracle
        } else if ($record->interface->sql()->getDbType() == \Pop\Db\Sql::ORACLE) {
            $sql = 'SELECT column_name, data_type, nullable FROM all_tab_cols where table_name = \'' . $tableName . '\'';
            $field = 'COLUMN_NAME';
            $type = 'DATA_TYPE';
            $nullField = 'NULLABLE';
        // MySQL
        } else {
            $sql = 'SHOW COLUMNS FROM `' . $tableName . '`';
            $field = 'Field';
            $type = 'Type';
            $nullField  = 'Null';
        }

        $record->interface->sql()->adapter()->query($sql);

        while (($row = $record->interface->sql()->adapter()->fetch()) != false) {
            if ($record->interface->sql()->getDbType() == \Pop\Db\Sql::SQLITE) {
                $nullResult = ($row[$nullField]) ? false : true;
            } else if ($record->interface->sql()->getDbType() == \Pop\Db\Sql::MYSQL) {
                $nullResult = (strtoupper($row[$nullField]) != 'NO') ? true : false;
            } else if ($record->interface->sql()->getDbType() == \Pop\Db\Sql::ORACLE) {
                $nullResult = (strtoupper($row[$nullField]) != 'Y') ? true : false;
            } else {
                $nullResult = $row[$nullField];
            }

            $info['columns'][$row[$field]] = array(
                'type'    => $row[$type],
                'null'    => $nullResult
            );
        }

        return $info;
    }

    /**
     * Get if the record interface is prepared or not
     *
     * @return boolean
     */
    public function isPrepared()
    {
        return $this->usePrepared;
    }

    /**
     * Get if the table is an autoincrement table
     *
     * @return boolean
     */
    public function isAuto()
    {
        return $this->auto;
    }

    /**
     * Get the table primary ID
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->primaryId;
    }

    /**
     * Get the table prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Get the abbreviated table name, without the prefix
     *
     * @return string
     */
    public function getTableName()
    {
        if (null !== $this->prefix) {
            return str_replace($this->prefix, '', $this->tableName);
        } else {
            return $this->tableName;
        }
    }

    /**
     * Get the full table name, with the prefix
     *
     * @return string
     */
    public function getFullTableName()
    {
        return $this->tableName;
    }

    /**
     * Method to return the current number of records.
     *
     * @return int
     */
    public function count()
    {
        return count($this->rows);
    }

    /**
     * Set all the table column values at once.
     *
     * @param  array $columns
     * @throws Exception
     * @return \Pop\Db\Record
     */
    public function setValues($columns = null)
    {
        // If null, clear the columns.
        if (null === $columns) {
            $this->columns = array();
            $this->rows = array();
        // Else, if an array, set the columns.
        } else if (is_array($columns)) {
            $this->columns = $columns;
            $this->rows[0] = new \ArrayObject($columns, \ArrayObject::ARRAY_AS_PROPS);
        // Else, throw an exception.
        } else {
            throw new Exception('The parameter passed must be either an array or null.');
        }

        return $this;
    }

    /**
     * Get all the table column values at once as an associative array.
     *
     * @return array
     */
    public function getValues()
    {
        return (array)$this->columns;
    }

    /**
     * Update (save) the existing database record.
     *
     * @return void
     */
    public function update()
    {
        $this->save(self::UPDATE);
    }

    /**
     * Save the database record.
     *
     * @param  int $type
     * @return void
     */
    public function save($type = Record::INSERT)
    {
        $this->interface->save($this->columns, $type);
        $this->setResults($this->interface->getResult());
    }

    /**
     * Delete the database record.
     *
     * @param  array $columns
     * @return void
     */
    public function delete(array $columns = null)
    {
        $this->interface->delete($this->columns, $columns);
        $this->setResults($this->interface->getResult());
    }

    /**
     * Return the escaped string value.
     *
     * @param  string $value
     * @return string
     */
    public function escape($value)
    {
        return $this->interface->sql()->adapter()->escape($value);
    }

    /**
     * Return the auto-increment ID of the last query.
     *
     * @return int
     */
    public function lastId()
    {
        return $this->interface->sql()->adapter()->lastId();
    }

    /**
     * Return the number of rows in the result.
     *
     * @return int
     */
    public function numRows()
    {
        return $this->interface->sql()->adapter()->numRows();
    }

    /**
     * Return the number of fields in the result.
     *
     * @return int
     */
    public function numFields()
    {
        return $this->interface->sql()->adapter()->numFields();
    }

    /**
     * Set the query results.
     *
     * @param  array $result
     * @return void
     */
    protected function setResults($result)
    {
        $this->rows = $result['rows'];
        $this->columns = $result['columns'];
    }

    /**
     * Method to convert a camelCase string to an under_score string
     *
     * @param string $string
     * @return string
     */
    protected function camelCaseToUnderscore($string)
    {
        $strAry = str_split($string);
        $convert = null;
        $i = 0;

        foreach ($strAry as $chr) {
            if ($i == 0) {
                $convert .= strtolower($chr);
            } else {
                $convert .= (ctype_upper($chr)) ? ('_' . strtolower($chr)) : $chr;
            }
            $i++;
        }

        return $convert;
    }

    /**
     * Set method to set the property to the value of columns[$name].
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->columns[$name] = $value;
    }

    /**
     * Get method to return the value of columns[$name].
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return (isset($this->columns[$name])) ? $this->columns[$name] : null;
    }

    /**
     * Return the isset value of columns[$name].
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->columns[$name]);
    }

    /**
     * Unset columns[$name].
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        $this->columns[$name] = null;
    }

}
