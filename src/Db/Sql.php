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
 * SQL class
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Sql
{

    /**
     * Constant for MYSQL database type
     * @var int
     */
    const MYSQL = 1;

    /**
     * Constant for Oracle database type
     * @var int
     */
    const ORACLE = 2;

    /**
     * Constant for PGSQL database type
     * @var int
     */
    const PGSQL = 3;

    /**
     * Constant for SQLITE database type
     * @var int
     */
    const SQLITE = 4;

    /**
     * Constant for SQLSRV database type
     * @var int
     */
    const SQLSRV = 5;

    /**
     * Constant for backtick quote id type
     * @var int
     */
    const BACKTICK = 6;

    /**
     * Constant for bracket quote id type
     * @var int
     */
    const BRACKET = 7;

    /**
     * Constant for double quote id type
     * @var int
     */
    const DOUBLE_QUOTE = 8;

    /**
     * Constant for double quote id type
     * @var int
     */
    const NO_QUOTE = 0;

    /**
     * Database object
     * @var \Pop\Db\Db
     */
    protected $db = null;

    /**
     * Database type
     * @var int
     */
    protected $dbType = null;

    /**
     * ID quote type
     * @var int
     */
    protected $quoteIdType = 0;

    /**
     * Current table
     * @var string
     */
    protected $table = null;

    /**
     * Alias name for sub-queries
     * @var string
     */
    protected $alias = null;

    /**
     * SQL clause object
     * @var mixed
     */
    protected $clause = null;

    /**
     * SQL statement
     * @var string
     */
    protected $sql = null;

    /**
     * Constructor
     *
     * Instantiate the SQL object.
     *
     * @param  \Pop\Db\Db $db
     * @param  mixed      $table
     * @param  string     $alias
     * @return \Pop\Db\Sql
     */
    public function __construct(Db $db, $table = null, $alias = null)
    {
        $this->setDb($db);
        $this->setTable($table);
        $this->setAlias($alias);
    }

    /**
     * Static method to instantiate the SQL object and return itself
     * to facilitate chaining methods together.
     *
     * @param  \Pop\Db\Db $db
     * @param  mixed      $table
     * @param  string     $alias
     * @return \Pop\Db\Sql
     */
    public static function factory(Db $db, $table = null, $alias = null)
    {
        return new self($db, $table, $alias);
    }

    /**
     * Set the database object
     *
     * @param  \Pop\Db\Db $db
     * @return \Pop\Db\Sql
     */
    public function setDb(Db $db)
    {
        $this->db = $db;

        $adapter = strtolower($this->db->getAdapterType());

        if (strpos($adapter, 'mysql') !== false) {
            $this->dbType = self::MYSQL;
            $this->quoteIdType = self::BACKTICK;
        } else if (strpos($adapter, 'oracle') !== false) {
            $this->dbType = self::ORACLE;
            $this->quoteIdType = self::DOUBLE_QUOTE;
        } else if (strpos($adapter, 'pgsql') !== false) {
            $this->dbType = self::PGSQL;
            $this->quoteIdType = self::DOUBLE_QUOTE;
        } else if (strpos($adapter, 'sqlite') !== false) {
            $this->dbType = self::SQLITE;
            $this->quoteIdType = self::DOUBLE_QUOTE;
        } else if (strpos($adapter, 'sqlsrv') !== false) {
            $this->dbType = self::SQLSRV;
            $this->quoteIdType = self::BRACKET;
        }

        return $this;
    }

    /**
     * Set the quote ID type
     *
     * @param  int $type
     * @return \Pop\Db\Sql
     */
    public function setQuoteId($type = \Pop\Db\Sql::NO_QUOTE)
    {
        $this->quoteIdType = (int)$type;
        return $this;
    }

    /**
     * Set current table to operate on.
     *
     * @param  mixed $table
     * @return \Pop\Db\Sql
     */
    public function setTable($table = null)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Set alias name
     *
     * @param  string $alias
     * @return \Pop\Db\Sql
     */
    public function setAlias($alias = null)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * Determine if the Sql object has a table set
     *
     * @return boolean
     */
    public function hasTable()
    {
        return ($this->table != null);
    }

    /**
     * Determine if the Sql object has an alias name
     *
     * @return boolean
     */
    public function hasAlias()
    {
        return ($this->alias != null);
    }

    /**
     * Get the current database object.
     *
     * @return \Pop\Db\Db
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Get the current database object's adapter.
     *
     * @return \Pop\Db\Adapter\AbstractAdapter
     */
    public function adapter()
    {
        return $this->db->adapter();
    }

    /**
     * Get the current database type.
     *
     * @return int
     */
    public function getDbType()
    {
        return $this->dbType;
    }

    /**
     * Get the quote ID type
     *
     * @return int
     */
    public function getQuoteId()
    {
        return $this->quoteIdType;
    }

    /**
     * Get the current table.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get the alias name.
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Get the current SQL statement string.
     *
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * Quote the value with the quoted identifier
     *
     * @param  string $id
     * @return string
     */
    public function quoteId($id)
    {
        $quotedId = null;
        $startQuote = null;
        $endQuote = null;

        switch ($this->quoteIdType) {
            case self::BACKTICK:
                $startQuote = '`';
                $endQuote = '`';
                break;
            case self::BRACKET:
                $startQuote = '[';
                $endQuote = ']';
                break;
            case self::DOUBLE_QUOTE:
                $startQuote = '"';
                $endQuote = '"';
                break;
        }

        if (strpos($id, '.') !== false) {
            $idAry = explode('.', $id);
            foreach ($idAry as $key => $value) {
                $idAry[$key] = $startQuote . $value . $endQuote;
            }
            $quotedId = implode('.', $idAry);
        } else {
            $quotedId = $startQuote . $id . $endQuote;
        }

        return $quotedId;
    }

    /**
     * Quote the value with single quotes
     *
     * @param  string $value
     * @return string
     */
    public function quote($value)
    {
        if (($value != '?') && (substr($value, 0, 1) != ':') && (preg_match('/^\$\d*\d$/', $value) == 0) && (!is_int($value)) && (!is_float($value))) {
            $value = "'" . $this->db->adapter()->escape($value) . "'";
        }
        return $value;
    }

    /**
     * Create a select statement
     *
     * @param  mixed $columns
     * @return \Pop\Db\Sql\Select
     */
    public function select($columns = null)
    {
        if ((null === $this->clause) || !($this->clause instanceof \Pop\Db\Sql\Select)) {
            $this->clause = new Sql\Select($this, $columns);
        }

        return $this->clause;
    }

    /**
     * Create a insert statement
     *
     * @param  array $columns
     * @throws Exception
     * @return \Pop\Db\Sql\Insert
     */
    public function insert(array $columns = null)
    {
        if ((null === $this->clause) || !($this->clause instanceof \Pop\Db\Sql\Insert)) {
            if (null === $columns) {
                throw new Exception('Error: The columns parameter cannot be null for a new INSERT clause.');
            }
            $this->clause = new Sql\Insert($this, $columns);
        }

        return $this->clause;
    }

    /**
     * Create a update statement
     *
     * @param  array $columns
     * @throws Exception
     * @return \Pop\Db\Sql\Update
     */
    public function update(array $columns = null)
    {
        if ((null === $this->clause) || !($this->clause instanceof \Pop\Db\Sql\Update)) {
            if (null === $columns) {
                throw new Exception('Error: The columns parameter cannot be null for a new UPDATE clause.');
            }
            $this->clause = new Sql\Update($this, $columns);
        }

        return $this->clause;
    }

    /**
     * Create a delete statement
     *
     * @return \Pop\Db\Sql\Update
     */
    public function delete()
    {
        if ((null === $this->clause) || !($this->clause instanceof \Pop\Db\Sql\Delete)) {
            $this->clause = new Sql\Delete($this);
        }

        return $this->clause;
    }

    /**
     * Render SQL string
     *
     * @param  boolean $ret
     * @throws Exception
     * @return mixed
     */
    public function render($ret = false)
    {
        if (null === $this->clause) {
            throw new Exception('Error: No SQL clause has been created yet.');
        }

        $this->sql = $this->clause->render();

        if ($ret) {
            return $this->sql;
        } else {
            echo $this->sql;
        }
    }

    /**
     * Method to return the SQL as a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render(true);
    }

}
