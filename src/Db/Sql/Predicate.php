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
 * SQL Predicate collection class
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Predicate
{

    /**
     * SQL object
     * @var \Pop\Db\Sql
     */
    protected $sql = null;

    /**
     * Predicates array
     * @var array
     */
    protected $predicates = array();

    /**
     * Nested predicates
     * @var array
     */
    protected $nested = array();

    /**
     * Constructor
     *
     * Instantiate the predicate collection object.
     *
     * @param  \Pop\Db\Sql $sql
     * @return \Pop\Db\Sql\Predicate
     */
    public function __construct(\Pop\Db\Sql $sql)
    {
        $this->sql = $sql;
    }

    /**
     * Add a nested predicate
     *
     * @return \Pop\Db\Sql\Predicate
     */
    public function nest()
    {
        $this->nested[] = new Predicate($this->sql);
        return $this->nested[count($this->nested) - 1];
    }

    /**
     * Determine if it has a nested predicate branch
     *
     * @param  int $i
     * @return boolean
     */
    public function hasNest($i = null)
    {
        return (null === $i) ? (count($this->nested) > 0) : (isset($this->nested[$i]));
    }

    /**
     * Get a nested predicate
     *
     * @param  int $i
     * @return mixed
     */
    public function getNest($i)
    {
        return (isset($this->nested[$i])) ? $this->nested[$i] : null;
    }

    /**
     * Predicate for =
     *
     * @param  string $column
     * @param  string $value
     * @param  string $combine
     * @return \Pop\Db\Sql\Predicate
     */
    public function equalTo($column, $value, $combine = 'AND')
    {
        $this->predicates[] = array(
            'format' => '%1 = %2',
            'values' => array($column, $value),
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        );
        return $this;
    }

    /**
     * Predicate for !=
     *
     * @param  string $column
     * @param  string $value
     * @param  string $combine
     * @return \Pop\Db\Sql\Predicate
     */
    public function notEqualTo($column, $value, $combine = 'AND')
    {
        $this->predicates[] = array(
            'format' => '%1 != %2',
            'values' => array($column, $value),
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        );
        return $this;
    }

    /**
     * Predicate for >
     *
     * @param  string $column
     * @param  string $value
     * @param  string $combine
     * @return \Pop\Db\Sql\Predicate
     */
    public function greaterThan($column, $value, $combine = 'AND')
    {
        $this->predicates[] = array(
            'format' => '%1 > %2',
            'values' => array($column, $value),
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        );
        return $this;
    }

    /**
     * Predicate for >=
     *
     * @param  string $column
     * @param  string $value
     * @param  string $combine
     * @return \Pop\Db\Sql\Predicate
     */
    public function greaterThanOrEqualTo($column, $value, $combine = 'AND')
    {
        $this->predicates[] = array(
            'format' => '%1 >= %2',
            'values' => array($column, $value),
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        );
        return $this;
    }

    /**
     * Predicate for <
     *
     * @param  string $column
     * @param  string $value
     * @param  string $combine
     * @return \Pop\Db\Sql\Predicate
     */
    public function lessThan($column, $value, $combine = 'AND')
    {
        $this->predicates[] = array(
            'format' => '%1 < %2',
            'values' => array($column, $value),
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        );
        return $this;
    }

    /**
     * Predicate for <=
     *
     * @param  string $column
     * @param  string $value
     * @param  string $combine
     * @return \Pop\Db\Sql\Predicate
     */
    public function lessThanOrEqualTo($column, $value, $combine = 'AND')
    {
        $this->predicates[] = array(
            'format' => '%1 <= %2',
            'values' => array($column, $value),
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        );
        return $this;
    }

    /**
     * Predicate for LIKE
     *
     * @param  string $column
     * @param  string $value
     * @param  string $combine
     * @return \Pop\Db\Sql\Predicate
     */
    public function like($column, $value, $combine = 'AND')
    {
        $this->predicates[] = array(
            'format' => '%1 LIKE %2',
            'values' => array($column, $value),
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        );
        return $this;
    }

    /**
     * Predicate for NOT LIKE
     *
     * @param  string $column
     * @param  string $value
     * @param  string $combine
     * @return \Pop\Db\Sql\Predicate
     */
    public function notLike($column, $value, $combine = 'AND')
    {
        $this->predicates[] = array(
            'format' => '%1 NOT LIKE %2',
            'values' => array($column, $value),
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        );
        return $this;
    }

    /**
     * Predicate for BETWEEN
     *
     * @param  string $column
     * @param  string $value1
     * @param  string $value2
     * @param  string $combine
     * @return \Pop\Db\Sql\Predicate
     */
    public function between($column, $value1, $value2, $combine = 'AND')
    {
        $this->predicates[] = array(
            'format' => '%1 BETWEEN %2 AND %3',
            'values' => array($column, $value1, $value2),
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        );
        return $this;
    }

    /**
     * Predicate for NOT BETWEEN
     *
     * @param  string $column
     * @param  string $value1
     * @param  string $value2
     * @param  string $combine
     * @return \Pop\Db\Sql\Predicate
     */
    public function notBetween($column, $value1, $value2, $combine = 'AND')
    {
        $this->predicates[] = array(
            'format' => '%1 NOT BETWEEN %2 AND %3',
            'values' => array($column, $value1, $value2),
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        );
        return $this;
    }

    /**
     * Predicate for IN
     *
     * @param  string $column
     * @param  mixed  $values
     * @param  string $combine
     * @return \Pop\Db\Sql\Predicate
     */
    public function in($column, $values, $combine = 'AND')
    {
        $this->predicates[] = array(
            'format' => '%1 IN (%2)',
            'values' => array($column, $values),
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        );
        return $this;
    }

    /**
     * Predicate for NOT IN
     *
     * @param  string $column
     * @param  mixed  $values
     * @param  string $combine
     * @return \Pop\Db\Sql\Predicate
     */
    public function notIn($column, $values, $combine = 'AND')
    {
        $this->predicates[] = array(
            'format' => '%1 NOT IN (%2)',
            'values' => array($column, $values),
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        );
        return $this;
    }

    /**
     * Predicate for IS NULL
     *
     * @param  string $column
     * @param  string $combine
     * @return \Pop\Db\Sql\Predicate
     */
    public function isNull($column, $combine = 'AND')
    {
        $this->predicates[] = array(
            'format' => '%1 IS NULL',
            'values' => array($column),
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        );
        return $this;
    }

    /**
     * Predicate for IS NOT NULL
     *
     * @param  string $column
     * @param  string $combine
     * @return \Pop\Db\Sql\Predicate
     */
    public function isNotNull($column, $combine = 'AND')
    {
        $this->predicates[] = array(
            'format' => '%1 IS NOT NULL',
            'values' => array($column),
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        );
        return $this;
    }

    /**
     * Predicate render method
     *
     * @param  int $count
     * @return string
     */
    public function render($count = 1)
    {
        $where = null;

        // Build any nested predicates
        //if (null !== $this->nested) {
        //    $where = '(' . $this->nested . ')';
        //}
        if (count($this->nested) > 0) {
            $where = '(' . implode(') AND (', $this->nested) . ')';
        }

        // Loop through and format the predicates
        if (count($this->predicates) > 0) {
            if (null !== $where) {
                $where .= ' ' . $this->predicates[0]['combine'] . ' ';
            }

            $paramCount = $count;
            $dbType = $this->sql->getDbType();

            foreach ($this->predicates as $key => $predicate) {
                $format = $predicate['format'];
                $curWhere = '(';
                for ($i = 0; $i < count($predicate['values']); $i++) {
                    if ($i == 0) {
                        $format = str_replace('%1', $this->sql->quoteId($predicate['values'][$i]), $format);
                    } else {
                        if (is_array($predicate['values'][$i])) {
                            $vals = $predicate['values'][$i];
                            foreach ($vals as $k => $v) {
                                $predValue = (strpos($predicate['values'][0], '.') !== false) ?
                                    substr($predicate['values'][0], (strpos($predicate['values'][0], '.') + 1)) : $predicate['values'][0];

                                // Check for named parameters
                                if ((':' . $predValue == substr($v, 0, strlen(':' . $predValue))) &&
                                    ($dbType !== \Pop\Db\Sql::SQLITE) &&
                                    ($dbType !== \Pop\Db\Sql::ORACLE)) {
                                    if (($dbType == \Pop\Db\Sql::MYSQL) || ($dbType == \Pop\Db\Sql::SQLSRV)) {
                                        $v = '?';
                                    } else if (($dbType == \Pop\Db\Sql::PGSQL) && (!$this->sql->getDb()->isPdo())) {
                                        $v = '$' . $paramCount;
                                        $paramCount++;
                                    }
                                }
                                $vals[$k] = (null === $v) ? 'NULL' : $this->sql->quote($v);
                            }
                            $format = str_replace('%' . ($i + 1), implode(', ', $vals), $format);
                        } else {
                            if ($predicate['values'][$i] instanceof \Pop\Db\Sql) {
                                $val = (string)$predicate['values'][$i];
                            } else {
                                $val = (null === $predicate['values'][$i]) ? 'NULL' :
                                    $this->sql->quote($predicate['values'][$i]);
                            }

                            $predValue = (strpos($predicate['values'][0], '.') !== false) ?
                                substr($predicate['values'][0], (strpos($predicate['values'][0], '.') + 1)) : $predicate['values'][0];

                            // Check for named parameters
                            if ((':' . $predValue == substr($val, 0, strlen(':' . $predValue))) &&
                                ($dbType !== \Pop\Db\Sql::SQLITE) &&
                                ($dbType !== \Pop\Db\Sql::ORACLE)) {
                                if (($dbType == \Pop\Db\Sql::MYSQL) || ($dbType == \Pop\Db\Sql::SQLSRV)) {
                                    $val = '?';
                                } else if (($dbType == \Pop\Db\Sql::PGSQL) && (!$this->sql->getDb()->isPdo())) {
                                    $val = '$' . $paramCount;
                                    $paramCount++;
                                }
                            }
                            $format = str_replace('%' . ($i + 1), $val, $format);
                        }
                    }
                }
                $curWhere .= $format . ')';

                if ($key == 0) {
                    $where .= $curWhere;
                } else {
                    $where .= ' ' . $predicate['combine'] . ' ' . $curWhere;
                }
            }
        }

        return $where;
    }

    /**
     * Predicate return string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

}
