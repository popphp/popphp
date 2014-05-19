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
namespace Pop\Db\Sql;

/**
 * Update SQL class
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Update extends AbstractSql
{

    /**
     * WHERE predicate object
     * @var \Pop\Db\Sql\Predicate
     */
    protected $where = null;

    /**
     * Set the WHERE clause
     *
     * @param  boolean $reset
     * @return \Pop\Db\Sql\Predicate
     */
    public function where($reset = false)
    {
        if ((null === $this->where) || ($reset)) {
            $this->where = new Predicate($this->sql);
        }

        return $this->where;
    }

    /**
     * Render the UPDATE statement
     *
     * @return string
     */
    public function render()
    {
        // Start building the UPDATE statement
        $sql = 'UPDATE ' . $this->sql->quoteId($this->sql->getTable()) . ' SET ';
        $set = [];

        $paramCount = 1;
        $dbType = $this->sql->getDbType();

        foreach ($this->columns as $column => $value) {
            $colValue = (strpos($column, '.') !== false) ?
                substr($column, (strpos($column, '.') + 1)) : $column;

            // Check for named parameters
            if ((':' . $colValue == substr($value, 0, strlen(':' . $colValue))) &&
                ($dbType !== \Pop\Db\Sql::SQLITE) &&
                ($dbType !== \Pop\Db\Sql::ORACLE)) {
                if (($dbType == \Pop\Db\Sql::MYSQL) || ($dbType == \Pop\Db\Sql::SQLSRV)) {
                    $value = '?';
                } else if (($dbType == \Pop\Db\Sql::PGSQL) && (!$this->sql->getDb()->isPdo())) {
                    $value = '$' . $paramCount;
                    $paramCount++;
                }
            }
            $val = (null === $value) ? 'NULL' : $this->sql->quote($value);
            $set[] = $this->sql->quoteId($column) .' = ' . $val;
        }

        $sql .= implode(', ', $set);

        // Build any WHERE clauses
        if (null !== $this->where) {
            $sql .= ' WHERE ' . $this->where->render($paramCount);
        }

        // Build any ORDER BY clause
        if (null !== $this->orderBy) {
            $sql .= ' ORDER BY ' . $this->orderBy;
        }

        // Build any LIMIT clause
        if (null !== $this->limit) {
            $sql .= ' LIMIT ' . (int)$this->limit;
        }

        return $sql;
    }

}
