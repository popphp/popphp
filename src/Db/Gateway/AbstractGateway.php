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
namespace Pop\Db\Gateway;

/**
 * Abstract Gateway class
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractGateway
{

    /**
     * Table
     * @var string
     */
    protected $table = null;

    /**
     * Primary keys
     * @var array
     */
    protected $primaryKeys = [];

    /**
     * Sql object
     * @var \Pop\Db\Sql
     */
    protected $sql = null;

    /**
     * Constructor
     *
     * Instantiate the AbstractGateway object.
     *
     * @param  \Pop\Db\Sql $sql
     * @param  string      $table
     * @return \Pop\Db\Gateway\AbstractGateway
     */
    public function __construct(\Pop\Db\Sql $sql, $table = null)
    {
        if (null !== $table) {
            $this->setTable($table);
        }
        $this->sql = $sql;
    }

    /**
     * Set the table
     *
     * @param  string $table
     * @return \Pop\Db\Gateway\AbstractGateway
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Set the primary keys
     *
     * @param  mixed $keys
     * @return \Pop\Db\Gateway\AbstractGateway
     */
    public function setPrimaryKeys($keys)
    {
        $this->primaryKeys = (is_array($keys)) ? $keys : [$keys];
        return $this;
    }

    /**
     * Get the SQL object
     *
     * @return \Pop\Db\Sql
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * Get the SQL object (alias method)
     *
     * @return \Pop\Db\Sql
     */
    public function sql()
    {
        return $this->sql;
    }

    /**
     * Get the table name
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get the primary keys
     *
     * @return array
     */
    public function getPrimaryKeys()
    {
        return $this->primaryKeys;
    }

    /**
     * Get table info
     *
     * @return array
     */
    public function getTableInfo()
    {
        $info      = [
            'tableName' => $this->table,
            'primaryId' => $this->primaryKeys,
            'columns'   => []
        ];

        $sql       = null;
        $field     = 'column_name';
        $type      = 'data_type';
        $nullField = 'is_nullable';

        switch ($this->sql->getDbType()) {
            case \Pop\Db\Sql::PGSQL:
                $sql = 'SELECT * FROM information_schema.COLUMNS WHERE table_name = \'' . $this->table . '\' ORDER BY ordinal_position ASC';
                break;
            case \Pop\Db\Sql::SQLSRV:
                $sql = 'SELECT c.name \'column_name\', t.Name \'data_type\', c.is_nullable, c.column_id FROM sys.columns c INNER JOIN sys.types t ON c.system_type_id = t.system_type_id WHERE object_id = OBJECT_ID(\'' . $this->table . '\') ORDER BY c.column_id ASC';
                break;
            case \Pop\Db\Sql::SQLITE:
                $sql       = 'PRAGMA table_info(\'' . $this->table . '\')';
                $field     = 'name';
                $type      = 'type';
                $nullField = 'notnull';
                break;
            case \Pop\Db\Sql::ORACLE:
                $sql       = 'SELECT column_name, data_type, nullable FROM all_tab_cols where table_name = \'' . $this->table . '\'';
                $field     = 'COLUMN_NAME';
                $type      = 'DATA_TYPE';
                $nullField = 'NULLABLE';
                break;
            default:
                $sql        = 'SHOW COLUMNS FROM `' . $this->table . '`';
                $field      = 'Field';
                $type       = 'Type';
                $nullField  = 'Null';
        }

        $this->sql->db()->query($sql);

        while (($row = $this->sql->db()->fetch()) != false) {
            switch ($this->sql->getDbType()) {
                case \Pop\Db\Sql::SQLITE:
                    $nullResult = ($row[$nullField]) ? false : true;
                    break;
                case \Pop\Db\Sql::MYSQL:
                    $nullResult = (strtoupper($row[$nullField]) != 'NO') ? true : false;
                    break;
                case \Pop\Db\Sql::ORACLE:
                    $nullResult = (strtoupper($row[$nullField]) != 'Y') ? true : false;
                    break;
                default:
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
     * Get the order by values
     *
     * @param  string $order
     * @return array
     */
    protected function getOrder($order)
    {
        $by  = null;
        $ord = null;

        if (stripos($order, 'ASC') !== false) {
            $by  = trim(str_replace('ASC', '', $order));
            $ord = 'ASC';
        } else if (stripos($order, 'DESC') !== false) {
            $by  = trim(str_replace('DESC', '', $order));
            $ord = 'DESC';
        } else if (stripos($order, 'RAND()') !== false) {
            $by  = trim(str_replace('RAND()', '', $order));
            $ord = 'RAND()';
        } else {
            $by  = $order;
            $ord = null;
        }

        if (strpos($by, ',') !== false) {
            $by = str_replace(', ', ',', $by);
            $by = explode(',', $by);
        }

        return ['by' => $by, 'order' => $ord];
    }

}
