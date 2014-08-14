<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <dev@nolainteractive.com>
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
 * @author     Nick Sagona, III <dev@nolainteractive.com>
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
    protected $predicates = [];

    /**
     * Nested predicates
     * @var array
     */
    protected $nested = [];

    /**
     * Allowed operators
     * @var array
     */
    protected $operators = [
        '>=', '<=', '!=', '=', '>', '<',
        'NOT LIKE', 'LIKE', 'NOT BETWEEN', 'BETWEEN',
        'NOT IN', 'IN', 'IS NOT NULL', 'IS NULL'
    ];

    /**
     * Constructor
     *
     * Instantiate the predicate collection object.
     *
     * @param  \Pop\Db\Sql $sql
     * @return Predicate
     */
    public function __construct(\Pop\Db\Sql $sql)
    {
        $this->sql = $sql;
    }

    /**
     * Add a predicate from a string
     *
     * @param  mixed $predicate
     * @return Predicate
     */
    public function add($predicate)
    {
        $predicates = [];

        // If the predicate is a string
        if (is_string($predicate)) {
            $predicates = [$this->parse($predicate)];
        // If the predicate is an array of strings
        } else if (is_array($predicate) && isset($predicate[0]) && is_string($predicate[0])) {
            foreach ($predicate as $pred) {
                $predicates[] = $this->parse($pred);
            }
        // If the predicate is an array of associative array values, i.e., [['id' => 1], ...]
        } else if (is_array($predicate) && isset($predicate[0]) && is_array($predicate[0])) {
            foreach ($predicate as $pred) {
                $key = current(array_keys($pred));
                if (is_string($key) && !is_numeric($key)) {
                    $val = $pred[$key];
                    if (substr($val, -3) == ' OR') {
                        $val   = substr($val, 0, -3);
                        $combine = 'OR';
                    } else {
                        $combine = 'AND';
                    }
                    $predicates[] = [$key, '=', $val, $combine];
                }
            }
        // If the predicate is a single associative array, i.e., ['id' => 1]
        } else {
            $key = current(array_keys($predicate));
            if (is_string($key) && !is_numeric($key)) {
                $val = $predicate[$key];
                if (substr($val, -3) == ' OR') {
                    $val   = substr($val, 0, -3);
                    $combine = 'OR';
                } else {
                    $combine = 'AND';
                }
                $predicates[] = [$key, '=', $val, $combine];
            }
        }

        // Loop through and add the predicates
        foreach ($predicates as $predicate) {
            if (count($predicate) >= 2) {
                switch ($predicate[1]) {
                    case '>=':
                        $this->greaterThanOrEqualTo($predicate[0], $predicate[2], $predicate[3]);
                        break;
                    case '<=':
                        $this->lessThanOrEqualTo($predicate[0], $predicate[2], $predicate[3]);
                        break;
                    case '!=':
                        $this->notEqualTo($predicate[0], $predicate[2], $predicate[3]);
                        break;
                    case '=':
                        $this->equalTo($predicate[0], $predicate[2], $predicate[3]);
                        break;
                    case '>':
                        $this->greaterThan($predicate[0], $predicate[2], $predicate[3]);
                        break;
                    case '<':
                        $this->lessThan($predicate[0], $predicate[2], $predicate[3]);
                        break;
                    case 'NOT LIKE':
                        $this->notLike($predicate[0], $predicate[2], $predicate[3]);
                        break;
                    case 'LIKE':
                        $this->like($predicate[0], $predicate[2], $predicate[3]);
                        break;
                    case 'NOT BETWEEN':
                        $this->notBetween($predicate[0], $predicate[2][0], $predicate[2][1], $predicate[3]);
                        break;
                    case 'BETWEEN':
                        $this->between($predicate[0], $predicate[2][0], $predicate[2][1], $predicate[3]);
                        break;
                    case 'NOT IN':
                        $this->notIn($predicate[0], $predicate[2], $predicate[3]);
                        break;
                    case 'IN':
                        $this->in($predicate[0], $predicate[2], $predicate[3]);
                        break;
                    case 'IS NOT NULL':
                        $this->isNotNull($predicate[0], $predicate[3]);
                        break;
                    case 'IS NULL':
                        $this->isNull($predicate[0], $predicate[3]);
                        break;
                }
            }
        }

        return $this;
    }

    /**
     * Add a nested predicate
     *
     * @return Predicate
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
     * @return Predicate
     */
    public function equalTo($column, $value, $combine = 'AND')
    {
        $this->predicates[] = [
            'format' => '%1 = %2',
            'values' => [$column, $value],
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        ];
        return $this;
    }

    /**
     * Predicate for !=
     *
     * @param  string $column
     * @param  string $value
     * @param  string $combine
     * @return Predicate
     */
    public function notEqualTo($column, $value, $combine = 'AND')
    {
        $this->predicates[] = [
            'format' => '%1 != %2',
            'values' => [$column, $value],
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        ];
        return $this;
    }

    /**
     * Predicate for >
     *
     * @param  string $column
     * @param  string $value
     * @param  string $combine
     * @return Predicate
     */
    public function greaterThan($column, $value, $combine = 'AND')
    {
        $this->predicates[] = [
            'format' => '%1 > %2',
            'values' => [$column, $value],
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        ];
        return $this;
    }

    /**
     * Predicate for >=
     *
     * @param  string $column
     * @param  string $value
     * @param  string $combine
     * @return Predicate
     */
    public function greaterThanOrEqualTo($column, $value, $combine = 'AND')
    {
        $this->predicates[] = [
            'format' => '%1 >= %2',
            'values' => [$column, $value],
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        ];
        return $this;
    }

    /**
     * Predicate for <
     *
     * @param  string $column
     * @param  string $value
     * @param  string $combine
     * @return Predicate
     */
    public function lessThan($column, $value, $combine = 'AND')
    {
        $this->predicates[] = [
            'format' => '%1 < %2',
            'values' => [$column, $value],
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        ];
        return $this;
    }

    /**
     * Predicate for <=
     *
     * @param  string $column
     * @param  string $value
     * @param  string $combine
     * @return Predicate
     */
    public function lessThanOrEqualTo($column, $value, $combine = 'AND')
    {
        $this->predicates[] = [
            'format' => '%1 <= %2',
            'values' => [$column, $value],
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        ];
        return $this;
    }

    /**
     * Predicate for LIKE
     *
     * @param  string $column
     * @param  string $value
     * @param  string $combine
     * @return Predicate
     */
    public function like($column, $value, $combine = 'AND')
    {
        $this->predicates[] = [
            'format' => '%1 LIKE %2',
            'values' => [$column, $value],
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        ];
        return $this;
    }

    /**
     * Predicate for NOT LIKE
     *
     * @param  string $column
     * @param  string $value
     * @param  string $combine
     * @return Predicate
     */
    public function notLike($column, $value, $combine = 'AND')
    {
        $this->predicates[] = [
            'format' => '%1 NOT LIKE %2',
            'values' => [$column, $value],
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        ];
        return $this;
    }

    /**
     * Predicate for BETWEEN
     *
     * @param  string $column
     * @param  string $value1
     * @param  string $value2
     * @param  string $combine
     * @return Predicate
     */
    public function between($column, $value1, $value2, $combine = 'AND')
    {
        $this->predicates[] = [
            'format' => '%1 BETWEEN %2 AND %3',
            'values' => [$column, $value1, $value2],
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        ];
        return $this;
    }

    /**
     * Predicate for NOT BETWEEN
     *
     * @param  string $column
     * @param  string $value1
     * @param  string $value2
     * @param  string $combine
     * @return Predicate
     */
    public function notBetween($column, $value1, $value2, $combine = 'AND')
    {
        $this->predicates[] = [
            'format' => '%1 NOT BETWEEN %2 AND %3',
            'values' => [$column, $value1, $value2],
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        ];
        return $this;
    }

    /**
     * Predicate for IN
     *
     * @param  string $column
     * @param  mixed  $values
     * @param  string $combine
     * @return Predicate
     */
    public function in($column, $values, $combine = 'AND')
    {
        $this->predicates[] = [
            'format' => '%1 IN (%2)',
            'values' => [$column, $values],
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        ];
        return $this;
    }

    /**
     * Predicate for NOT IN
     *
     * @param  string $column
     * @param  mixed  $values
     * @param  string $combine
     * @return Predicate
     */
    public function notIn($column, $values, $combine = 'AND')
    {
        $this->predicates[] = [
            'format' => '%1 NOT IN (%2)',
            'values' => [$column, $values],
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        ];
        return $this;
    }

    /**
     * Predicate for IS NULL
     *
     * @param  string $column
     * @param  string $combine
     * @return Predicate
     */
    public function isNull($column, $combine = 'AND')
    {
        $this->predicates[] = [
            'format' => '%1 IS NULL',
            'values' => [$column],
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        ];
        return $this;
    }

    /**
     * Predicate for IS NOT NULL
     *
     * @param  string $column
     * @param  string $combine
     * @return Predicate
     */
    public function isNotNull($column, $combine = 'AND')
    {
        $this->predicates[] = [
            'format' => '%1 IS NOT NULL',
            'values' => [$column],
            'combine' => ($combine == 'OR') ? 'OR' : 'AND'
        ];
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
                $curPredicate = '(';
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
                $curPredicate .= $format . ')';

                if ($key == 0) {
                    $where .= $curPredicate;
                } else {
                    $where .= ' ' . $predicate['combine'] . ' ' . $curPredicate;
                }
            }
        }

        return $where;
    }

    /**
     * Method to parse a predicate string
     *
     * @param  string $predicate
     * @return array
     */
    protected function parse($predicate)
    {
        $pred = [];

        foreach ($this->operators as $op) {
            // If operator IS NULL or IS NOT NULL
            if ((strpos($op, 'NULL') !== false) && (strpos($predicate, $op) !== false)) {
                $combine = (substr($op, -3) == ' OR') ? 'OR' : 'AND';
                $value   = null;
                $column  = trim(substr($predicate, 0, strpos($predicate, ' ')));
                // Remove any quotes from the column
                if (((substr($column, 0, 1) == '"') && (substr($column, -1) == '"')) ||
                    ((substr($column, 0, 1) == "'") && (substr($column, -1) == "'")) ||
                    ((substr($column, 0, 1) == '`') && (substr($column, -1) == '`'))) {
                    $column = substr($column, 1);
                    $column = substr($column, 0, -1);
                }

                $pred = [$column, $op, $value, $combine];
            } else if ((strpos($predicate, ' ' . $op . ' ') !== false) && ((strpos($predicate, ' NOT ' . $op . ' ') === false))) {
                $ary    = explode($op, $predicate);
                $column = trim($ary[0]);
                $value  = trim($ary[1]);

                // Remove any quotes from the column
                if (((substr($column, 0, 1) == '"') && (substr($column, -1) == '"')) ||
                    ((substr($column, 0, 1) == "'") && (substr($column, -1) == "'")) ||
                    ((substr($column, 0, 1) == '`') && (substr($column, -1) == '`'))) {
                    $column = substr($column, 1);
                    $column = substr($column, 0, -1);
                }

                // Remove any quotes from the value
                if (((substr($value, 0, 1) == '"') && (substr($value, -1) == '"')) ||
                    ((substr($value, 0, 1) == "'") && (substr($value, -1) == "'")) ||
                    ((substr($column, 0, 1) == '`') && (substr($column, -1) == '`'))) {
                    $value = substr($value, 1);
                    $value = substr($value, 0, -1);
                // Else, create array of values if the value is a comma-separated list
                } else if ((substr($value, 0, 1) == '(') && (substr($value, -1) == ')') && (strpos($value, ',') !== false)) {
                    $value = substr($value, 1);
                    $value = substr($value, 0, -1);
                    $value = str_replace(', ', ',', $value);
                    $value = explode(',', $value);
                }

                if (substr($value, -3) == ' OR') {
                    $value   = substr($value, 0, -3);
                    $combine = 'OR';
                } else {
                    $combine = 'AND';
                }

                if (is_numeric($value)) {
                    if (strpos($value, '.') !== false) {
                        $value = (float)$value;
                    } else {
                        $value = (int)$value;
                    }
                } else if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        if (is_numeric($v)) {
                            if (strpos($v, '.') !== false) {
                                $value[$k] = (float)$v;
                            } else {
                                $value[$k] = (int)$v;
                            }
                        }
                    }
                }
                $pred = [$column, $op, $value, $combine];
            }

        }

        return $pred;
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
