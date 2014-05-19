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
 * @version    2.0.0a
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
    public static $db = ['default' => null];

    /**
     * Sql object
     * @var \Pop\Db\Sql
     */
    protected $sql = null;

    /**
     * Rows of multiple return results from a database query
     * in an ArrayObject format.
     * @var array
     */
    public $rows = [];

    /**
     * Column names of the database table
     * @var array
     */
    protected $columns = [];

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
     * Original query finder, if primary ID is not set.
     * @var array
     */
    protected $finder = [];

    /**
     * Property that determines whether or not the primary ID is auto-increment or not
     * @var boolean
     */
    protected $auto = true;

    /**
     * Prepared statement parameter placeholder
     * @var string
     */
    protected $placeholder = '?';

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

        $this->sql = new Sql($class::getDb(), $this->tableName);

        if (($this->sql->getDbType() == \Pop\Db\Sql::SQLITE) ||
            (stripos($this->sql->getDb()->getAdapterType(), 'pdo') !== false)) {
            $this->placeholder = ':';
        } else if ($this->sql->getDbType() == \Pop\Db\Sql::PGSQL) {
            $this->placeholder = '$';
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
     * @throws Exception
     * @return \Pop\Db\Record
     */
    public static function findById($id, $limit = null, $offset = null)
    {
        $record    = new static();
        $primaryId = $record->getId();

        if (null === $primaryId) {
            throw new Exception('This primary ID of this table either is not set or does not exist.');
        }

        // Build the SQL.
        $sql = $record->getSql();
        $sql->select();

        if (is_array($primaryId)) {
            if (!is_array($id) || (count($id) != count($primaryId))) {
                throw new Exception('The array of ID values does not match the number of IDs.');
            }
            foreach ($id as $key => $value) {
                if (null === $value) {
                    $sql->select()->where()->isNull($primaryId[$key]);
                } else {
                    $sql->select()->where()->equalTo($primaryId[$key], $record->getPlaceholder($primaryId[$key], ($key + 1)));
                }
            }
        } else {
            $sql->select()->where()->equalTo($primaryId, $record->getPlaceholder($primaryId));
        }

        // Set the limit, if passed
        if (null !== $limit) {
            $sql->select()->limit($sql->adapter()->escape($limit));
        }

        // Set the offset, if passed
        if (null !== $offset) {
            $sql->select()->offset($sql->adapter()->escape($offset));
        }

        // Prepare the statement
        $sql->adapter()->prepare($sql->render(true));

        if (is_array($primaryId)) {
            $params = [];
            foreach ($id as $key => $value) {
                if (null !== $value) {
                    $params[$primaryId[$key]] = $value;
                }
            }
        } else {
            $params = [$primaryId => $id];
        }

        // Bind the parameters, execute the statement and set the return results.
        $sql->adapter()->bindParams((array)$params);
        $sql->adapter()->execute();
        $record->setResults();

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
        $record->setFinder(array_merge($record->getFinder(), $columns));

        // Build the SQL.
        $sql = $record->getSql();
        $sql->select();

        $i = 1;
        foreach ($columns as $key => $value) {
            if (strpos($value, '%') !== false) {
                $sql->select()->where()->like($key, $record->getPlaceholder($key, $i));
                $i++;
            } else if (null === $value) {
                $sql->select()->where()->isNull($key);
            } else {
                $sql->select()->where()->equalTo($key, $record->getPlaceholder($key, $i));
                $i++;
            }
        }

        // Set the limit, if passed
        if (null !== $limit) {
            $sql->select()->limit($sql->adapter()->escape($limit));
        }

        // Set the offset, if passed
        if (null !== $offset) {
            $sql->select()->offset($sql->adapter()->escape($offset));
        }

        // Set the SQL query to a specific order, if given.
        if (null !== $order) {
            $ord = $record->getOrder($order);
            $sql->select()->orderBy($ord['by'], $sql->adapter()->escape($ord['order']));
        }

        $params = [];
        foreach ($columns as $key => $value) {
            if (null !== $value) {
                $params[$key] = $value;
            }
        }

        // Prepare the statement, bind the parameters, execute the statement and set the return results.
        $sql->adapter()->prepare($sql->render(true));
        $sql->adapter()->bindParams($params);
        $sql->adapter()->execute();

        $record->setResults();

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

        // Build the SQL.
        $sql = $record->getSql();
        $sql->select();

        // If a specific column and value are passed.
        if (null !== $columns) {
            $record->setFinder(array_merge($record->getFinder(), $columns));
            $i = 1;
            foreach ($columns as $key => $value) {
                if (strpos($value, '%') !== false) {
                    $sql->select()->where()->like($key, $record->getPlaceholder($key, $i));
                    $i++;
                } else if (null === $value) {
                    $sql->select()->where()->isNull($key);
                } else {
                    $sql->select()->where()->equalTo($key, $record->getPlaceholder($key, $i));
                    $i++;
                }
            }
        } else {
            $record->finder = [];
        }

        // Set any limit to the SQL query.
        if (null !== $limit) {
            $sql->select()->limit($sql->adapter()->escape($limit));
        }

        // Set the offset, if passed
        if (null !== $offset) {
            $sql->select()->offset($sql->adapter()->escape($offset));
        }


        // Set the SQL query to a specific order, if given.
        if (null !== $order) {
            $ord = $record->getOrder($order);
            $sql->select()->orderBy($ord['by'], $sql->adapter()->escape($ord['order']));
        }

        // Prepare the SQL statement
        $sql->adapter()->prepare($sql->render(true));

        // Bind the parameters
        if (null !== $columns) {
            $params = [];
            foreach ($columns as $key => $value) {
                if (null !== $value) {
                    $params[$key] = $value;
                }
            }
            $sql->adapter()->bindParams($params);
        }

        // Execute the statement and set the return results.
        $sql->adapter()->execute();
        $record->setResults();

        return $record;
    }

    /**
     * Execute a custom prepared SQL query.
     *
     * @param  string $statement
     * @param  array  $params
     * @return \Pop\Db\Record
     */
    public static function execute($statement, $params = null)
    {
        $record = new static();

        $sql = $record->getSql();
        $sql->adapter()->prepare($statement);

        if ((null !== $params) && is_array($params)) {
            $sql->adapter()->bindParams((array)$params);
        }

        $sql->adapter()->execute();

        // Set the return results.
        if (stripos($statement, 'select') !== false) {
            $record->setResults();
        } else if (stripos($statement, 'delete') !== false) {
            $record->setValues();
        }

        return $record;
    }

    /**
     * Execute a custom SQL query.
     *
     * @param  string $statement
     * @return \Pop\Db\Record
     */
    public static function query($statement)
    {
        $record = new static();

        $sql = $record->getSql();
        $sql->adapter()->query($statement);

        // Set the return results.
        if (stripos($statement, 'select') !== false) {
            // If there is more than one result returned, create an array of results.
            if ($sql->adapter()->numRows() > 1) {
                while (($row = $sql->adapter()->fetch()) != false) {
                    $record->rows[] = new \ArrayObject($row, \ArrayObject::ARRAY_AS_PROPS);
                }
                // Else, set the _columns array to the single returned result.
            } else {
                while (($row = $sql->adapter()->fetch()) != false) {
                    $record->rows[0] = new \ArrayObject($row, \ArrayObject::ARRAY_AS_PROPS);
                }
            }
        } else if (stripos($statement, 'delete') !== false) {
            $record->setValues();
        }

        return $record;
    }

    /**
     * Get total count of records
     *
     * @param  array $columns
     * @return int
     */
    public static function getCount(array $columns = null)
    {
        $record = new static();

        // Build the SQL.
        $sql = $record->getSql();
        $sql->select(['total_count' => 'COUNT(*)']);

        if (null !== $columns) {
            $i = 1;
            $params = [];
            foreach ($columns as $key => $value) {
                $sql->select()->where()->equalTo($sql->adapter()->escape($key), $record->getPlaceholder($key, $i));
                $params[$sql->adapter()->escape($key)] = $sql->adapter()->escape($value);
                $i++;
            }
            $sql->adapter()->prepare($sql->render(true));
            $sql->adapter()->bindParams($params);
        } else {
            $sql->adapter()->prepare($sql->render(true));
        }

        $sql->adapter()->execute();
        $record->setResults();

        return $record->total_count;
    }

    /**
     * Get the SQL object.
     *
     * @return \Pop\Db\Sql
     */
    public static function sql()
    {
        $record = new static();
        return $record->getSql();
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
        $info = [
            'tableName' => $tableName,
            'primaryId' => $record->getId(),
            'columns'   => []
        ];

        $sql       = null;
        $field     = 'column_name';
        $type      = 'data_type';
        $nullField = 'is_nullable';

        // SQLite
        if ($record->getSql()->getDbType() == \Pop\Db\Sql::SQLITE) {
            $sql       = 'PRAGMA table_info(\'' . $tableName . '\')';
            $field     = 'name';
            $type      = 'type';
            $nullField = 'notnull';
        // PostgreSQL
        } else if ($record->getSql()->getDbType() == \Pop\Db\Sql::PGSQL) {
            $sql = 'SELECT * FROM information_schema.COLUMNS WHERE table_name = \'' . $tableName . '\' ORDER BY ordinal_position ASC';
        // SQL Server
        } else if ($record->getSql()->getDbType() == \Pop\Db\Sql::SQLSRV) {
            $sql = 'SELECT c.name \'column_name\', t.Name \'data_type\', c.is_nullable, c.column_id FROM sys.columns c INNER JOIN sys.types t ON c.system_type_id = t.system_type_id WHERE object_id = OBJECT_ID(\'' . $tableName . '\') ORDER BY c.column_id ASC';
        // Oracle
        } else if ($record->getSql()->getDbType() == \Pop\Db\Sql::ORACLE) {
            $sql       = 'SELECT column_name, data_type, nullable FROM all_tab_cols where table_name = \'' . $tableName . '\'';
            $field     = 'COLUMN_NAME';
            $type      = 'DATA_TYPE';
            $nullField = 'NULLABLE';
        // MySQL
        } else {
            $sql        = 'SHOW COLUMNS FROM `' . $tableName . '`';
            $field      = 'Field';
            $type       = 'Type';
            $nullField  = 'Null';
        }

        $record->getSql()->adapter()->query($sql);

        while (($row = $record->getSql()->adapter()->fetch()) != false) {
            if ($record->getSql()->getDbType() == \Pop\Db\Sql::SQLITE) {
                $nullResult = ($row[$nullField]) ? false : true;
            } else if ($record->getSql()->getDbType() == \Pop\Db\Sql::MYSQL) {
                $nullResult = (strtoupper($row[$nullField]) != 'NO') ? true : false;
            } else if ($record->getSql()->getDbType() == \Pop\Db\Sql::ORACLE) {
                $nullResult = (strtoupper($row[$nullField]) != 'Y') ? true : false;
            } else {
                $nullResult = $row[$nullField];
            }

            $info['columns'][$row[$field]] = [
                'type'    => $row[$type],
                'null'    => $nullResult
            ];
        }

        return $info;
    }

    /**
     * Get the SQL object.
     *
     * @return \Pop\Db\Sql
     */
    public function getSql()
    {
        return $this->sql;
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
     * Method to return the current finder columns.
     *
     * @return array
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * Method to return the current finder columns.
     *
     * @param  mixed $finder
     * @return void
     */
    public function setFinder($finder)
    {
        $this->finder = $finder;
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
            $this->columns = [];
            $this->rows = [];
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
        $class = get_class($this);
        $this->sql = new Sql($class::getDb(), $this->tableName);

        if (null === $this->primaryId) {
            if ($type == \Pop\Db\Record::UPDATE) {
                if (count($this->finder) > 0) {
                    $columns = [];
                    $params = $this->columns;
                    $i = 1;
                    foreach ($this->columns as $key => $value) {
                        if (!array_key_exists($key, $this->finder)) {
                            $columns[$key] = $this->getPlaceholder($key, $i);
                            $i++;
                        }
                    }

                    foreach ($this->finder as $key => $value) {
                        if (isset($params[$key])) {
                            $val = $params[$key];
                            unset($params[$key]);
                            $params[$key] = $val;
                        }
                    }

                    $this->sql->update((array)$columns);
                    $this->sql->update()->where(true);

                    $i = 1;
                    foreach ($this->finder as $key => $value) {
                        if (null === $value) {
                            $this->sql->update()->where()->isNull($key);
                        } else {
                            $this->sql->update()->where()->equalTo($key, $this->getPlaceholder($key, $i));
                            $i++;
                        }
                    }

                    $realParams = [];
                    foreach ($params as $key => $value) {
                        if (null !== $value) {
                            $realParams[$key] = $value;
                        }
                    }

                    $this->sql->adapter()->prepare($this->sql->render(true));
                    $this->sql->adapter()->bindParams($realParams);
                } else {
                    $columns = [];
                    $i = 1;
                    foreach ($this->columns as $key => $value) {
                        $columns[$key] = $this->getPlaceholder($key, $i);
                        $i++;
                    }
                    $this->sql->update((array)$columns);
                    $this->sql->adapter()->prepare($this->sql->render(true));
                    $this->sql->adapter()->bindParams((array)$this->columns);
                }
                // Execute the SQL statement
                $this->sql->adapter()->execute();
            } else {
                $columns = [];
                $i = 1;
                foreach ($this->columns as $key => $value) {
                    $columns[$key] = $this->getPlaceholder($key, $i);
                    $i++;
                }
                $this->sql->insert((array)$columns);
                $this->sql->adapter()->prepare($this->sql->render(true));
                $this->sql->adapter()->bindParams((array)$this->columns);
                $this->sql->adapter()->execute();
            }
        } else {
            if ($this->auto == false) {
                $action = ($type == \Pop\Db\Record::INSERT) ? 'insert' : 'update';
            } else {
                if (is_array($this->primaryId)) {
                    $isset = true;
                    foreach ($this->primaryId as $value) {
                        if (!isset($this->columns[$value])) {
                            $isset = false;
                        }
                    }
                    $action = ($isset) ? 'update' : 'insert';
                } else {
                    $action = (isset($this->columns[$this->primaryId])) ? 'update' : 'insert';
                }
            }

            if ($action == 'update') {
                $columns = [];
                $params  = $this->columns;

                $i = 1;
                foreach ($this->columns as $key => $value) {
                    if (is_array($this->primaryId)) {
                        if (!in_array($key, $this->primaryId)) {
                            $columns[$key] = $this->getPlaceholder($key, $i);
                            $i++;
                        }
                    } else {
                        if ($key != $this->primaryId) {
                            $columns[$key] = $this->getPlaceholder($key, $i);
                            $i++;
                        }
                    }
                }

                $this->sql->update((array)$columns);
                $this->sql->update()->where(true);

                if (is_array($this->primaryId)) {
                    foreach ($this->primaryId as $key => $value) {
                        if (isset($params[$value])) {
                            $id = $params[$value];
                            unset($params[$value]);
                        } else {
                            $id = $params[$value];
                        }
                        $params[$value] = $id;
                        if (null === $this->columns[$value]) {
                            $this->sql->update()->where()->isNull($value);
                            unset($params[$value]);
                        } else {
                            $this->sql->update()->where()->equalTo($value, $this->getPlaceholder($value, ($i + $key)));
                        }
                    }
                    $realParams = $params;
                } else {
                    if (isset($params[$this->primaryId])) {
                        $id = $params[$this->primaryId];
                        unset($params[$this->primaryId]);
                    } else {
                        $id = $params[$this->primaryId];
                    }
                    $params[$this->primaryId] = $id;
                    $this->sql->update()->where()->equalTo($this->primaryId, $this->getPlaceholder($this->primaryId, $i));
                    $realParams = $params;
                }

                $this->sql->adapter()->prepare($this->sql->render(true));
                $this->sql->adapter()->bindParams((array)$realParams);
                $this->sql->adapter()->execute();
            } else {
                $columns = [];
                $i = 1;

                foreach ($this->columns as $key => $value) {
                    $columns[$key] = $this->getPlaceholder($key, $i);
                    $i++;
                }

                $this->sql->insert((array)$columns);
                $this->sql->adapter()->prepare($this->sql->render(true));
                $this->sql->adapter()->bindParams((array)$this->columns);
                $this->sql->adapter()->execute();

                if ($this->auto) {
                    $this->columns[$this->primaryId] = $this->sql->adapter()->lastId();
                    $this->rows[0][$this->primaryId] = $this->sql->adapter()->lastId();
                }
            }
        }

        if (count($this->columns) > 0) {
            $this->rows[0] = $this->columns;
        }

    }

    /**
     * Delete the database record.
     *
     * @param  array $columns
     * @throws Exception
     * @return void
     */
    public function delete(array $columns = null)
    {
        if (null === $this->primaryId) {
            if ((null === $columns) && (count($this->finder) == 0)) {
                throw new Exception('The column and value parameters were not defined to describe the row(s) to delete.');
            } else if (null === $columns) {
                $columns = $this->finder;
            }

            $this->sql->delete();

            $i = 1;
            foreach ($columns as $key => $value) {
                if (null === $value) {
                    $this->sql->delete()->where()->isNull($this->sql->adapter()->escape($key));
                } else {
                    $this->sql->delete()->where()->equalTo($this->sql->adapter()->escape($key), $this->getPlaceholder($key, $i));
                    $i++;
                }
            }

            $params = [];
            foreach ($columns as $key => $value) {
                if (null !== $value) {
                    $params[$this->primaryId[$key]] = $value;
                }
            }

            $this->sql->adapter()->prepare($this->sql->render(true));
            $this->sql->adapter()->bindParams($params);
            $this->sql->adapter()->execute();

            $this->columns = [];
            $this->rows = [];
        } else {
            $this->sql->delete();

            // Specific column override.
            if (null !== $columns) {
                $i = 1;
                foreach ($columns as $key => $value) {
                    if (null === $value) {
                        $this->sql->delete()->where()->isNull($this->sql->adapter()->escape($key));
                    } else {
                        $this->sql->delete()->where()->equalTo($this->sql->adapter()->escape($key), $this->getPlaceholder($key, $i));
                        $i++;
                    }
                }
                // Else, continue with the primaryId column(s)
            } else if (is_array($this->primaryId)) {
                foreach ($this->primaryId as $key => $value) {
                    if (null === $this->columns[$value]) {
                        $this->sql->delete()->where()->isNull($this->sql->adapter()->escape($value));
                    } else {
                        $this->sql->delete()->where()->equalTo($this->sql->adapter()->escape($value), $this->getPlaceholder($value, ($key + 1)));
                    }
                }
            } else {
                $this->sql->delete()->where()->equalTo($this->sql->adapter()->escape($this->primaryId), $this->getPlaceholder($this->primaryId));
            }

            $this->sql->adapter()->prepare($this->sql->render(true));

            // Specific column override.
            if (null !== $columns) {
                $params = $columns;
                // Else, continue with the primaryId column(s)
            } else if (is_array($this->primaryId)) {
                $params = [];
                foreach ($this->primaryId as $value) {
                    if (null !== $this->columns[$value]) {
                        $params[$value] = $this->columns[$value];
                    }
                }
            } else {
                $params = [$this->primaryId => $this->columns[$this->primaryId]];
            }

            $this->sql->adapter()->bindParams((array)$params);
            $this->sql->adapter()->execute();

            $this->columns = [];
            $this->rows    = [];
        }
    }

    /**
     * Return the escaped string value.
     *
     * @param  string $value
     * @return string
     */
    public function escape($value)
    {
        return $this->sql->adapter()->escape($value);
    }

    /**
     * Return the auto-increment ID of the last query.
     *
     * @return int
     */
    public function lastId()
    {
        return $this->sql->adapter()->lastId();
    }

    /**
     * Return the number of rows in the result.
     *
     * @return int
     */
    public function numRows()
    {
        return $this->sql->adapter()->numRows();
    }

    /**
     * Return the number of fields in the result.
     *
     * @return int
     */
    public function numFields()
    {
        return $this->sql->adapter()->numFields();
    }

    /**
     * Get the placeholder for a prepared statement
     *
     * @param  string $column
     * @param  int    $i
     * @return string
     */
    public function getPlaceholder($column, $i = 1)
    {
        $placeholder =  $this->placeholder;

        if ($this->placeholder == ':') {
            $placeholder .= $column;
        } else if ($this->placeholder == '$') {
            $placeholder .= $i;
        }

        return $placeholder;
    }

    /**
     * Get the order by values
     *
     * @param  string $order
     * @return array
     */
    protected function getOrder($order)
    {
        $by = null;
        $ord = null;

        if (stripos($order, 'ASC') !== false) {
            $by = trim(str_replace('ASC', '', $order));
            $ord = 'ASC';
        } else if (stripos($order, 'DESC') !== false) {
            $by = trim(str_replace('DESC', '', $order));
            $ord = 'DESC';
        } else if (stripos($order, 'RAND()') !== false) {
            $by = trim(str_replace('RAND()', '', $order));
            $ord = 'RAND()';
        } else {
            $by = $order;
            $ord = null;
        }

        if (strpos($by, ',') !== false) {
            $by = str_replace(', ', ',', $by);
            $by = explode(',', $by);
        }

        return ['by' => $by, 'order' => $ord];
    }

    /**
     * Set the query results.
     *
     * @return void
     */
    protected function setResults()
    {
        $this->rows = [];
        $rows = $this->sql->adapter()->fetchResult();

        foreach ($rows as $row) {
            $this->rows[] = new \ArrayObject($row, \ArrayObject::ARRAY_AS_PROPS);
        }

        if (isset($this->rows[0])) {
            $this->columns = $this->rows[0];
        }
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
