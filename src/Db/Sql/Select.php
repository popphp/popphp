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
 * @version    2.0.0a
 */
class Select extends AbstractSql
{

    /**
     * SQL functions
     * @var array
     */
    protected static $functions = [
        'AVG', 'COUNT', 'FIRST', 'LAST', 'MAX', 'MIN', 'SUM'
    ];

    /**
     * Allowed JOIN keywords
     * @var array
     */
    protected static $allowedJoins = [
        'JOIN', 'LEFT JOIN', 'RIGHT JOIN', 'FULL JOIN',
        'OUTER JOIN', 'LEFT OUTER JOIN', 'RIGHT OUTER JOIN', 'FULL OUTER JOIN',
        'INNER JOIN', 'LEFT INNER JOIN', 'RIGHT INNER JOIN', 'FULL INNER JOIN'
    ];

    /**
     * Distinct keyword
     * @var boolean
     */
    protected $distinct = false;

    /**
     * JOIN clauses
     * @var array
     */
    protected $joins = [];

    /**
     * WHERE predicate object
     * @var \Pop\Db\Sql\Where
     */
    protected $where = null;

    /**
     * GROUP BY value
     * @var string
     */
    protected $groupBy = null;

    /**
     * HAVING predicate object
     * @var \Pop\Db\Sql\Having
     */
    protected $having = null;

    /**
     * Set the JOIN clause
     *
     * @param  mixed  $foreignTables
     * @param  array  $columns
     * @param  string $typeOfJoin
     * @return \Pop\Db\Sql\Select
     */
    public function join($foreignTables, array $columns, $typeOfJoin = 'LEFT JOIN')
    {
        $join = (in_array(strtoupper($typeOfJoin), self::$allowedJoins)) ? strtoupper($typeOfJoin) : 'LEFT JOIN';

        if ($foreignTables instanceof \Pop\Db\Sql) {
            $subSelectAlias = ($foreignTables->hasAlias()) ? $foreignTables->getAlias() : $foreignTables->getTable();
            $table = '(' . $foreignTables . ') AS ' . $this->sql->quoteId($subSelectAlias);
        } else {
            if (is_array($foreignTables)) {
                $tables = [];
                foreach ($foreignTables as $foreignTable) {
                    $tables[] = $this->sql->quoteId($foreignTable);
                }
                $table = implode(', ', $tables);
            } else {
                $table = $this->sql->quoteId($foreignTables);
            }
        }

        $this->joins[] = [
            'foreignTables' => $table,
            'columns'       => $columns,
            'typeOfJoin'    => $join
        ];

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
     * @param  mixed $where
     * @return \Pop\Db\Sql\Select
     */
    public function where($where = null)
    {
        if (null !== $where) {
            if ($where instanceof Where) {
                $this->where = $where;
            } else {
                if (null === $this->where) {
                    $this->where = (new Where($this->sql))->add($where);
                } else {
                    $this->where->add($where);
                }
            }
        }
        if (null === $this->where) {
            $this->where = new Where($this->sql);
        }

        return $this;
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
            $quotedAry = [];
            foreach ($by as $value) {
                $quotedAry[] = $this->sql->quoteId(trim($value));
            }
            $byColumns = implode(', ', $quotedAry);
        } else if (strpos($by, ',') !== false) {
            $ary = explode(',' , $by);
            $quotedAry = [];
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
     * @param  mixed $having
     * @return \Pop\Db\Sql\Having
     */
    public function having($having = null)
    {
        if (null !== $having) {
            if ($having instanceof Having) {
                $this->having = $having;
            } else {
                if (null === $this->having) {
                    $this->having = (new Having($this->sql))->add($having);
                } else {
                    $this->having->add($having);
                }
            }
        }
        if (null === $this->having) {
            $this->having = new Having($this->sql);
        }

        return $this;
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
            $cols = [];
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
                $cols = [];
                foreach ($join['columns'] as $col1 => $col2) {
                    if (is_array($col2)) {
                        foreach ($col2 as $c) {
                            $cols[] = ((strpos($col1, '.') !== false) ? $this->sql->quoteId($col1) : $col1) . ' = ' .
                                ((strpos($c, '.') !== false) ? $this->sql->quoteId($c) : $c);
                        }
                    } else {
                        $cols[] = ((strpos($col1, '.') !== false) ? $this->sql->quoteId($col1) : $col1) . ' = ' .
                            ((strpos($col2, '.') !== false) ? $this->sql->quoteId($col2) : $col2);
                    }
                }

                $foreignTables = [];
                if (is_array($join['foreignTables'])) {
                    foreach ($join['foreignTables'] as $foreignTable) {
                        $foreignTables[] = (string)$foreignTable;
                    }
                } else {
                    $foreignTables[] = (string)$join['foreignTables'];
                }

                $sql .= ' ' . $join['typeOfJoin'] . ' (' .
                    implode(', ', $foreignTables) . ') ON (' . implode(' AND ', $cols) . ')';
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
     * Magic method to access $where and $having properties
     *
     * @param  string $name
     * @throws Exception
     * @return mixed
     */
    public function __get($name)
    {
        switch (strtolower($name)) {
            case 'where':
                if (null === $this->where) {
                    $this->where = new Where($this->sql);
                }
                return $this->where;
                break;
            case 'having':
                if (null === $this->having) {
                    $this->having = new Having($this->sql);
                }
                return $this->having;
                break;
            default:
                throw new Exception('Not a valid property for this object.');
        }
    }

    /**
     * Method to get the limit and offset
     *
     * @return array
     */
    protected function getLimitAndOffset()
    {
        $result = [
            'limit'  => null,
            'offset' => null
        ];

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
