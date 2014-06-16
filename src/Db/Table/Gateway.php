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
     * Table
     * @var string
     */
    protected $table = null;

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
     * @param  string      $table
     * @return \Pop\Db\Table\Gateway
     */
    public function __construct(\Pop\Db\Sql $sql, $table = null)
    {
        if (null !== $table) {
            $this->table = $table;
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
        return $this->table;
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
     * Set the table
     *
     * @param  string $table
     * @return \Pop\Db\Row\Gateway
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
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
     * @param  array $set
     * @param  mixed $where
     * @param  array $params
     * @param  array $options
     * @throws Exception
     * @return \Pop\Db\Table\Gateway
     */
    public function select($set = null, $where = null, array $params = null, array $options = [])
    {
        if (null === $this->table) {
            throw new Exception('Error: The table has not been set');
        }

        $this->sql->from($this->table)->select($set);

        if (null !== $where) {
            $this->sql->select()->where($where);
        }

        if (isset($options['limit'])) {
            $this->sql->select()->limit((int)$options['limit']);
        }

        if (isset($options['offset'])) {
            $this->sql->select()->offset((int)$options['offset']);
        }

        if (isset($options['order'])) {
            $ord = $this->getOrder($options['order']);
            $this->sql->select()->orderBy($ord['by'], $this->sql->db()->escape($ord['order']));
        }

        $this->sql->db()->prepare((string)$this->sql);
        if (null !== $params) {
            $this->sql->db()->bindParams($params);
        }
        $this->sql->db()->execute();

        $this->rows = $this->sql->db()->fetchResult();

        return $this;
    }

    /**
     * Insert rows into the table
     *
     * @param  array $set
     * @throws Exception
     * @return \Pop\Db\Table\Gateway
     */
    public function insert(array $set)
    {
        if (null === $this->table) {
            throw new Exception('Error: The table has not been set');
        }

        // If an array of rows of values, else, make it an array of rows of values
        $rowSets = (isset($set[0]) && is_array($set[0])) ? $set : [$set];

        foreach ($rowSets as $set) {
            $columns = [];
            $params  = [];

            $i = 1;
            foreach ($set as $column => $value) {
                $placeholder = $this->sql->getPlaceholder();

                if ($placeholder == ':') {
                    $placeholder .= $column;
                } else if ($placeholder == '$') {
                    $placeholder .= $i;
                }
                $columns[$column] = $placeholder;
                $params[]  = $value;
                $i++;
            }

            $this->sql->into($this->table)->insert($columns);
            $this->sql->db()->prepare((string)$this->sql)
                            ->bindParams($params)
                            ->execute();
        }

        return $this;
    }

    /**
     * Update rows in the table
     *
     * @param  array $set
     * @param  mixed $where
     * @param  array $pars
     * @throws Exception
     * @return \Pop\Db\Table\Gateway
     */
    public function update(array $set, $where = null, array $pars = [])
    {
        if (null === $this->table) {
            throw new Exception('Error: The table has not been set');
        }

        $columns = [];
        $params  = [];

        $i = 1;
        foreach ($set as $column => $value) {
            $placeholder = $this->sql->getPlaceholder();

            if ($placeholder == ':') {
                $placeholder .= $column;
            } else if ($placeholder == '$') {
                $placeholder .= ($i + 1);
            }
            $columns[$column] = $placeholder;
            $params[]  = $value;
            $i++;
        }

        $this->sql->setTable($this->table)->update($columns);

        if (null !== $where) {
            $this->sql->update()->where($where);
        }

        if (count($pars) > 0) {
            foreach ($pars as $p) {
                $params[] = $p;
            }
        }

        $this->sql->db()->prepare((string)$this->sql)
                        ->bindParams($params)
                        ->execute();

        return $this;
    }

    /**
     * Delete rows from the table
     *
     * @param  mixed $where
     * @param  array $pars
     * @throws Exception
     * @return \Pop\Db\Table\Gateway
     */
    public function delete($where = null, array $pars = [])
    {
        if (null === $this->table) {
            throw new Exception('Error: The table has not been set');
        }

        $params  = [];

        $this->sql->from($this->table)->delete();

        if (null !== $where) {
            $this->sql->delete()->where($where);
        }

        if (count($pars) > 0) {
            foreach ($pars as $p) {
                $params[] = $p;
            }
        }

        $this->sql->db()->prepare((string)$this->sql);
        if (count($params) > 0) {
            $this->sql->db()->bindParams($params);
        }
        $this->sql->db()->execute();

        return $this;
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
