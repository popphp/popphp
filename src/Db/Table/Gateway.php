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
namespace Pop\Db\Table;

/**
 * Table gateway class
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Gateway
{

    /**
     * Sql object
     * @var \Pop\Db\Sql
     */
    protected $sql = null;

    /**
     * Result rows
     * @var array
     */
    protected $rows = [];

    /**
     * Constructor
     *
     * Instantiate the Row\Gateway object.
     *
     * @param  \Pop\Db\Sql $sql
     * @throws Exception
     * @return \Pop\Db\Table\Gateway
     */
    public function __construct(\Pop\Db\Sql $sql)
    {
        if (null === $sql->getTable()) {
            throw new Exception('Error: The SQL object must have a table name set in it.');
        }
        $this->sql = $sql;
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
        return $this->sql->getTable();
    }

    /**
     * Get the number of result rows
     *
     * @return int
     */
    public function getNumberOfRows()
    {
        return count($this->rows);
    }

    /**
     * Get the result rows
     *
     * @return array
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Get the result rows (alias method)
     *
     * @return \Pop\Db\Sql
     */
    public function rows()
    {
        return $this->rows;
    }

    /**
     * Select rows from the table
     *
     * @param  mixed $set
     * @param  mixed $where
     * @return \Pop\Db\Table\Gateway
     */
    public function select($set = null, $where = null)
    {
        $this->sql->select($set)->where($where);
        echo $this->sql;
        return $this;
    }

    /**
     * Insert rows into the table
     *
     * @return \Pop\Db\Table\Gateway
     */
    public function insert()
    {
        return $this;
    }

    /**
     * Update rows in the table
     *
     * @return \Pop\Db\Table\Gateway
     */
    public function update()
    {
        return $this;
    }

    /**
     * Delete rows from the table
     *
     * @return \Pop\Db\Table\Gateway
     */
    public function delete()
    {
        return $this;
    }

}
