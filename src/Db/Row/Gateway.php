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
namespace Pop\Db\Row;

/**
 * Row gateway class
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
     * Primary keys
     * @var array
     */
    protected $primaryKeys = [];

    /**
     * Primary values
     * @var array
     */
    protected $primaryValues = [];

    /**
     * Sql object
     * @var \Pop\Db\Sql
     */
    protected $sql = null;

    /**
     * Row column values
     * @var array
     */
    protected $columns = [];

    /**
     * Constructor
     *
     * Instantiate the Row\Gateway object.
     *
     * @param  \Pop\Db\Sql $sql
     * @param  mixed       $keys
     * @throws Exception
     * @return \Pop\Db\Row\Gateway
     */
    public function __construct(\Pop\Db\Sql $sql, $keys = null)
    {
        if (null === $sql->getTable()) {
            throw new Exception('Error: The SQL object must have a table name set in it.');
        }
        if (null !== $keys) {
            $this->setPrimaryKeys($keys);
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
     * Get the columns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Set the primary keys
     *
     * @param  mixed $keys
     * @return \Pop\Db\Row\Gateway
     */
    public function setPrimaryKeys($keys)
    {
        $this->primaryKeys = (is_array($keys)) ? $keys : [$keys];
        return $this;
    }

    /**
     * Set the columns
     *
     * @param  array $columns
     * @return \Pop\Db\Row\Gateway
     */
    public function setColumns(array $columns = [])
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Find row by primary key values
     *
     * @param  mixed $values
     * @throws Exception
     * @return void
     */
    public function find($values)
    {
        if (count($this->primaryKeys) == 0) {
            throw new Exception('Error: The primary key(s) have not been set.');
        }

        $this->primaryValues = (is_array($values)) ? $values : [$values];

        if (count($this->primaryKeys) != count($this->primaryValues)) {
            throw new Exception('Error: The number of primary key(s) and primary value(s) do not match.');
        }

        $this->sql->select();
        $params = [];

        foreach ($this->primaryKeys as $i => $primaryKey) {
            $placeholder = $this->sql->getPlaceholder();

            if ($placeholder == ':') {
                $placeholder .= $primaryKey;
            } else if ($placeholder == '$') {
                $placeholder .= ($i + 1);
            }
            $this->sql->select()->where()->equalTo($primaryKey, $placeholder);
            $params[$primaryKey] = $this->primaryValues[$i];
        }

        $this->sql->select()->limit(1);

        $this->sql->db()->prepare((string)$this->sql)
                        ->bindParams($params)
                        ->execute();

        $rows = $this->sql->db()->fetchResult();

        if (isset($rows[0])) {
            $this->columns = $rows[0];
        }
    }

    /**
     * Save (insert new or update existing) row in the table
     *
     * @return \Pop\Db\Row\Gateway
     */
    public function save()
    {
        $columns = [];
        $params  = [];

        // If the row was found and exists, then update
        if ((count($this->primaryValues) > 0) && (count($this->columns) > 0)) {
            $i = 1;
            foreach ($this->columns as $column => $value) {
                if (!in_array($column, $this->primaryKeys)) {
                    $placeholder = $this->sql->getPlaceholder();

                    if ($placeholder == ':') {
                        $placeholder .= $column;
                    } else if ($placeholder == '$') {
                        $placeholder .= ($i + 1);
                    }
                    $columns[$column] = $placeholder;
                    $params[$column]  = $value;
                    $i++;
                }
            }

            $this->sql->update($columns);

            foreach ($this->primaryKeys as $key => $primaryKey) {
                $placeholder = $this->sql->getPlaceholder();

                if ($placeholder == ':') {
                    $placeholder .= $primaryKey;
                } else if ($placeholder == '$') {
                    $placeholder .= $i;
                }
                $this->sql->update()->where()->equalTo($primaryKey, $placeholder);
                $params[$primaryKey] = $this->primaryValues[$key];
                $i++;
            }

            $this->sql->db()->prepare((string)$this->sql)
                            ->bindParams($params)
                            ->execute();
        // Else, insert new
        } else {
            $i = 1;
            foreach ($this->columns as $column => $value) {
                $placeholder = $this->sql->getPlaceholder();

                if ($placeholder == ':') {
                    $placeholder .= $column;
                } else if ($placeholder == '$') {
                    $placeholder .= $i;
                }
                $columns[$column] = $placeholder;
                $params[$column]  = $value;
                $i++;
            }
            $this->sql->insert($columns);

            $this->sql->db()->prepare((string)$this->sql)
                            ->bindParams($params)
                            ->execute();

            if ((count($this->primaryKeys) == 1) && !isset($this->columns[$this->primaryKeys[0]])) {
                $this->columns[$this->primaryKeys[0]] = $this->sql->db()->lastId();
            }
        }

        return $this;
    }

    /**
     * Delete row from the table
     *
     * @throws Exception
     * @return \Pop\Db\Row\Gateway
     */
    public function delete()
    {
        if (count($this->primaryKeys) == 0) {
            throw new Exception('Error: The primary key(s) have not been set.');
        }

        if (count($this->primaryKeys) != count($this->primaryValues)) {
            throw new Exception('Error: The number of primary key(s) and primary value(s) do not match.');
        }

        $this->sql->delete();
        $params = [];

        foreach ($this->primaryKeys as $i => $primaryKey) {
            $placeholder = $this->sql->getPlaceholder();

            if ($placeholder == ':') {
                $placeholder .= $primaryKey;
            } else if ($placeholder == '$') {
                $placeholder .= ($i + 1);
            }
            $this->sql->delete()->where()->equalTo($primaryKey, $placeholder);
            $params[$primaryKey] = $this->primaryValues[$i];
        }

        $this->sql->delete()->limit(1);

        $this->sql->db()->prepare((string)$this->sql)
                        ->bindParams($params)
                        ->execute();

        $this->columns       = [];
        $this->primaryValues = [];

        return $this;
    }

    /**
     * Magic method to set the property to the value of $this->columns[$name].
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
     * Magic method to return the value of $this->columns[$name].
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return (isset($this->columns[$name])) ? $this->columns[$name] : null;
    }

    /**
     * Magic method to return the isset value of $this->columns[$name].
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->columns[$name]);
    }

    /**
     * Magic method to unset $this->columns[$name].
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        if (isset($this->columns[$name])) {
            unset($this->columns[$name]);
        }
    }

}
