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
 * Select SQL class
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Select extends AbstractSql
{

    /**
     * SQL functions
     * @var boolean
     */
    protected static $functions = array(
        'AVG', 'COUNT', 'FIRST', 'LAST', 'MAX', 'MIN', 'SUM'
    );

    /**
     * Allowed JOIN keywords
     * @var boolean
     */
    protected static $allowedJoins = array(
        'JOIN', 'LEFT JOIN', 'RIGHT JOIN', 'FULL JOIN',
        'OUTER JOIN', 'LEFT OUTER JOIN', 'RIGHT OUTER JOIN', 'FULL OUTER JOIN',
        'INNER JOIN', 'LEFT INNER JOIN', 'RIGHT INNER JOIN', 'FULL INNER JOIN'
    );

    /**
     * Distinct keyword
     * @var boolean
     */
    protected $distinct = false;

    /**
     * JOIN clauses
     * @var array
     */
    protected $joins = array();

    /**
     * WHERE predicate object
     * @var \Pop\Db\Sql\Predicate
     */
    protected $where = null;

    /**
     * GROUP BY value
     * @var string
     */
    protected $groupBy = null;

    /**
     * HAVING predicate object
     * @var \Pop\Db\Sql\Predicate
     */
    protected $having = null;

    /**
     * Set the JOIN clause
     *
     * @param mixed  $tableToJoin
     * @param mixed  $commonColumn
     * @param string $typeOfJoin
     * @return \Pop\Db\Sql\Select
     */
    public function join($tableToJoin, $commonColumn, $typeOfJoin = 'JOIN')
    {
        $join = (in_array(strtoupper($typeOfJoin), self::$allowedJoins)) ? strtoupper($typeOfJoin) : 'JOIN';

        if (is_array($commonColumn)) {
            $col1 = $this->sql->quoteId($commonColumn[0]);
            $col2 = $this->sql->quoteId($commonColumn[1]);
            $cols = array($col1, $col2);
        } else {
            $cols = $this->sql->quoteId($commonColumn);
        }

        if ($tableToJoin instanceof \Pop\Db\Sql) {
            $subSelectAlias = ($tableToJoin->hasAlias()) ? $tableToJoin->getAlias() : $tableToJoin->getTable();
            $table = '(' . $tableToJoin . ') AS ' . $this->sql->quoteId($subSelectAlias);
        } else {
            $subSelectAlias = null;
            $table = $this->sql->quoteId($tableToJoin);
        }

        $this->joins[] = array(
            'tableToJoin'  => $table,
            'commonColumn' => $cols,
            'typeOfJoin'   => $join,
            'alias'        => $subSelectAlias
        );

        return $this;
    }

    /**
     * Set the DISTINCT keyword
     *
     * @return \Pop\Db\Sql\Select
     */
    public function distinct()
    {
        $this->distinct = true;
        return $this;
    }

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
     * Set the GROUP BY value
     *
     * @param mixed $by
     * @return \Pop\Db\Sql\Select
     */
    public function groupBy($by)
    {
        $byColumns = null;

        if (is_array($by)) {
            $quotedAry = array();
            foreach ($by as $value) {
                $quotedAry[] = $this->sql->quoteId(trim($value));
            }
            $byColumns = implode(', ', $quotedAry);
        } else if (strpos($by, ',') !== false) {
            $ary = explode(',' , $by);
            $quotedAry = array();
            foreach ($ary as $value) {
                $quotedAry[] = $this->sql->quoteId(trim($value));
            }
            $byColumns = implode(', ', $quotedAry);
        } else {
            $byColumns = $this->sql->quoteId(trim($by));
        }

        $this->groupBy = $byColumns;
        return $this;
    }

    /**
     * Set the HAVING clause
     *
     * @return \Pop\Db\Sql\Predicate
     */
    public function having()
    {
        if (null === $this->having) {
            $this->having = new Predicate($this->sql);
        }

        return $this->having;
    }

    /**
     * Render the SELECT statement
     *
     * @throws Exception
     * @return string
     */
    public function render()
    {
        // Start building the SELECT statement
        $sql = 'SELECT ' . (($this->distinct) ? 'DISTINCT ' : null);

        if (count($this->columns) > 0) {
            $cols = array();
            foreach ($this->columns as $as => $col) {
                // If column is a SQL function, don't quote it
                $c = ((strpos($col, '(') !== false) && (in_array(substr($col, 0, strpos($col, '(')), self::$functions))) ?
                    $col : $this->sql->quoteId($col);
                if (!is_numeric($as)) {
                    $cols[] = $c . ' AS ' . $this->sql->quoteId($as);
                } else {
                    $cols[] = $c;
                }
            }
            $sql .= implode(', ', $cols) . ' ';
        } else {
            $sql .= '* ';
        }

        $sql .= 'FROM ';

        // Account for LIMIT and OFFSET clauses if the database is ORACLE
        if (($this->sql->getDbType() == \Pop\Db\Sql::ORACLE) && ((null !== $this->limit) || (null !== $this->offset))) {
            if (null === $this->orderBy) {
                throw new Exception('Error: You must set an order by clause to execute a limit clause on the Oracle database.');
            }

            $result = $this->getLimitAndOffset();

            $sql .= '(SELECT t.*, ROW_NUMBER() OVER (ORDER BY ' . $this->orderBy . ') ' .
                $this->sql->quoteId('RowNumber') . ' FROM ' .
                $this->sql->quoteId($this->sql->getTable()) . ' t)';

            if (null !== $result['offset']) {
                if ($result['limit'] > 0) {
                    $this->where()->between('RowNumber', $result['offset'], $result['limit']);
                } else {
                    $this->where()->greaterThanOrEqualTo('RowNumber', $result['offset']);
                }
            } else {
                $this->where()->lessThanOrEqualTo('RowNumber', $result['limit']);
            }
        // Account for LIMIT and OFFSET clauses if the database is SQLSRV
        } else if (($this->sql->getDbType() == \Pop\Db\Sql::SQLSRV) && ((null !== $this->limit) || (null !== $this->offset))) {
            if (null === $this->orderBy) {
                throw new Exception('Error: You must set an order by clause to execute a limit clause on the SQL server database.');
            }

            $result = $this->getLimitAndOffset();

            if (null !== $result['offset']) {
                $sql .= '(SELECT *, ROW_NUMBER() OVER (ORDER BY ' . $this->orderBy . ') AS RowNumber FROM ' .
                    $this->sql->quoteId($this->sql->getTable()) . ') AS OrderedTable';
                if ($result['limit'] > 0) {
                    $this->where()->between('OrderedTable.RowNumber', $result['offset'], $result['limit']);
                } else {
                    $this->where()->greaterThanOrEqualTo('OrderedTable.RowNumber', $result['offset']);
                }
            } else {
                $sql = str_replace('SELECT', 'SELECT TOP ' . $result['limit'], $sql);
                $sql .= $this->sql->quoteId($this->sql->getTable());
            }
        // Else, if there is a nested SELECT statement.
        } else if ($this->sql->getTable() instanceof \Pop\Db\Sql) {
            $subSelect = $this->sql->getTable();
            $subSelectAlias = ($subSelect->hasAlias()) ? $subSelect->getAlias() : $subSelect->getTable();
            $sql .= '(' . $subSelect . ') AS ' . $this->sql->quoteId($subSelectAlias);
        // Else, just select from the table
        } else {
            $sql .=  $this->sql->quoteId($this->sql->getTable());
        }

        // Build any JOIN clauses
        if (count($this->joins) > 0) {
            foreach ($this->joins as $join) {
                if (is_array($join['commonColumn'])) {
                    $col1 = $join['commonColumn'][0];
                    $col2 = $join['commonColumn'][1];
                } else {
                    $col1 = $join['commonColumn'];
                    $col2 = $join['commonColumn'];
                }
                if (strpos($col1, '.') === false) {
                    $col1 = $this->sql->quoteId($this->sql->getTable()) . '.' . $col1;
                }
                $sql .= ' ' . $join['typeOfJoin'] . ' ' .
                    $join['tableToJoin'] . ' ON ' .
                    $col1 . ' = ' .
                        (isset($join['alias']) ? $this->sql->quoteId($join['alias']) : $join['tableToJoin']) . '.' . $col2;
            }
        }

        // Build any WHERE clauses
        if (null !== $this->where) {
            $sql .= ' WHERE ' . $this->where;
        }

        // Build any GROUP BY clause
        if (null !== $this->groupBy) {
            $sql .= ' GROUP BY ' . $this->groupBy;
        }

        // Build any HAVING clause
        if (null !== $this->having) {
            $sql .= ' HAVING ' . $this->having;
        }

        // Build any ORDER BY clause
        if (null !== $this->orderBy) {
            $sql .= ' ORDER BY ' . $this->orderBy;
        }

        // Build any LIMIT clause for all other database types.
        if (($this->sql->getDbType() != \Pop\Db\Sql::SQLSRV) && ($this->sql->getDbType() != \Pop\Db\Sql::ORACLE)) {
            if (null !== $this->limit) {
                if ((strpos($this->limit, ',') !== false) && ($this->sql->getDbType() == \Pop\Db\Sql::PGSQL)) {
                    $ary = explode(',', $this->limit);
                    $this->offset = (int)trim($ary[0]);
                    $this->limit = (int)trim($ary[1]);
                }
                $sql .= ' LIMIT ' . $this->limit;
            }
        }

        // Build any OFFSET clause for all other database types.
        if (($this->sql->getDbType() != \Pop\Db\Sql::SQLSRV) && ($this->sql->getDbType() != \Pop\Db\Sql::ORACLE)) {
            if (null !== $this->offset) {
                $sql .= ' OFFSET ' . $this->offset;
            }
        }

        return $sql;
    }

    /**
     * Method to get the limit and offset
     *
     * @return array
     */
    protected function getLimitAndOffset()
    {
        $result = array(
            'limit'  => null,
            'offset' => null
        );

        // Calculate the limit and/or offset
        if (null !== $this->offset) {
            $result['offset'] = (int)$this->offset + 1;
            $result['limit'] = (null !== $this->limit) ? (int)$this->limit + (int)$this->offset : 0;
        } else if (strpos($this->limit, ',') !== false) {
            $ary  = explode(',', $this->limit);
            $result['offset'] = (int)trim($ary[0]) + 1;
            $result['limit'] = (int)trim($ary[1]) + (int)trim($ary[0]);
        } else {
            $result['limit'] = (int)$this->limit;
        }

        return $result;
    }

}
