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
namespace Pop\Db\Record;

use Pop\Db\Sql;

/**
 * Escaped record adapter class
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Escaped extends AbstractRecord
{

    /**
     * Constructor
     *
     * Instantiate the record escaped object.
     *
     * @param  \Pop\Db\Db $db
     * @param  array      $options
     * @return \Pop\Db\Record\Escaped
     */
    public function __construct(\Pop\Db\Db $db, $options)
    {
        $this->sql = new \Pop\Db\Sql($db, $options['tableName']);
        $this->tableName = $options['tableName'];
        $this->primaryId = $options['primaryId'];
        $this->auto = $options['auto'];
    }

    /**
     * Find a database row by the primary ID passed through the method argument.
     *
     * @param  mixed $id
     * @param  int   $limit
     * @param  int   $offset
     * @throws Exception
     * @return void
     */
    public function findById($id, $limit = null, $offset = null)
    {
        if (null === $this->primaryId) {
            throw new Exception('This primary ID of this table either is not set or does not exist.');
        }

        // Build the SQL.
        $this->sql->select();

        if (is_array($this->primaryId)) {
            if (!is_array($id) || (count($id) != count($this->primaryId))) {
                throw new Exception('The array of ID values does not match the number of IDs.');
            }
            foreach ($id as $key => $value) {
                if (null === $value) {
                    $this->sql->select()->where()->isNull($this->primaryId[$key]);
                } else {
                    $this->sql->select()->where()->equalTo($this->primaryId[$key], $this->sql->adapter()->escape($value));
                }
            }
        } else {
            $this->sql->select()->where()->equalTo($this->primaryId, $this->sql->adapter()->escape($id));
        }

        // Set the limit, if passed
        if (null !== $limit) {
            $this->sql->select()->limit($this->sql->adapter()->escape($limit));
        }

        // Set the offset, if passed
        if (null !== $offset) {
            $this->sql->select()->offset($this->sql->adapter()->escape($offset));
        }

        // Perform the query and set the return results.
        $this->sql->adapter()->query($this->sql->render(true));
        $this->setResults();
    }

    /**
     * Find a database row by the column passed through the method argument.
     *
     * @param  array  $columns
     * @param  string $order
     * @param  int    $limit
     * @param  int    $offset
     * @return void
     */
    public function findBy(array $columns, $order = null, $limit = null, $offset = null)
    {
        $this->finder = array_merge($this->finder, $columns);

        // Build the SQL.
        $this->sql->select();

        foreach ($columns as $key => $value) {
            if (strpos($value, '%') !== false) {
                $this->sql->select()->where()->like($this->sql->adapter()->escape($key), $this->sql->adapter()->escape($value));
            } else if (null === $value) {
                $this->sql->select()->where()->isNull($this->sql->adapter()->escape($key));
            } else {
                $this->sql->select()->where()->equalTo($this->sql->adapter()->escape($key), $this->sql->adapter()->escape($value));
            }
        }

        // Set the SQL query to a specific order, if given.
        if (null !== $order) {
            $ord = $this->getOrder($order);
            $this->sql->select()->orderBy($ord['by'], $this->sql->adapter()->escape($ord['order']));
        }

        // Set the limit, if passed
        if (null !== $limit) {
            $this->sql->select()->limit($this->sql->adapter()->escape($limit));
        }

        // Set the offset, if passed
        if (null !== $offset) {
            $this->sql->select()->offset($this->sql->adapter()->escape($offset));
        }

        // Perform the query and set the return results.
        $this->sql->adapter()->query($this->sql->render(true));
        $this->setResults();
    }

    /**
     * Find all of the database rows by the column passed through the method argument.
     *
     * @param  string $order
     * @param  array  $columns
     * @param  int    $limit
     * @param  int    $offset
     * @return void
     */
    public function findAll($order = null, array $columns = null, $limit = null, $offset = null)
    {
        // Build the SQL.
        $this->sql->select();

        // If a specific column and value are passed.
        if (null !== $columns) {
            $this->finder = array_merge($this->finder, $columns);
            foreach ($columns as $key => $value) {
                if (strpos($value, '%') !== false) {
                    $this->sql->select()->where()->like($this->sql->adapter()->escape($key), $this->sql->adapter()->escape($value));
                } else if (null === $value) {
                    $this->sql->select()->where()->isNull($this->sql->adapter()->escape($key));
                } else {
                    $this->sql->select()->where()->equalTo($this->sql->adapter()->escape($key), $this->sql->adapter()->escape($value));
                }
            }
        } else {
            $this->finder = array();
        }

        // Set the SQL query to a specific order, if given.
        if (null !== $order) {
            $ord = $this->getOrder($order);
            $this->sql->select()->orderBy($ord['by'], $this->sql->adapter()->escape($ord['order']));
        }

        // Set any limit to the SQL query.
        if (null !== $limit) {
            $this->sql->select()->limit($this->sql->adapter()->escape($limit));
        }

        // Set the offset, if passed
        if (null !== $offset) {
            $this->sql->select()->offset($this->sql->adapter()->escape($offset));
        }

        // Perform the query and set the return results.
        $this->sql->adapter()->query($this->sql->render(true));
        $this->setResults();
    }

    /**
     * Save the database record.
     *
     * @param  array $columnsPassed
     * @param  int   $type
     * @return void
     */
    public function save($columnsPassed, $type = \Pop\Db\Record::INSERT)
    {
        $this->columns = $columnsPassed;

        foreach ($this->columns as $key => $value) {
            $this->columns[$key] = $this->sql->adapter()->escape($value);
        }

        if (null === $this->primaryId) {
            // Build the UPDATE SQL
            if ($type == \Pop\Db\Record::UPDATE) {
                $this->sql->update((array)$this->columns);
                $this->sql->update()->where(true);

                if (count($this->finder) > 0) {
                    foreach ($this->finder as $key => $value) {
                        if (null === $value) {
                            $this->sql()->update()->where()->isNull($key);
                        } else {
                            $this->sql->update()->where()->equalTo($key, $this->sql->adapter()->escape($value));
                        }
                    }
                }

                $this->sql->adapter()->query($this->sql->render(true));
            // Else, build the INSERT SQL
            } else {
                $this->sql->insert((array)$this->columns);
                $this->sql->adapter()->query($this->sql->render(true));
            }
        } else {
            if ($this->auto == false) {
                $action = ($type == \Pop\Db\Record::INSERT) ? 'insert' : 'update';
            } else {
                if (is_array($this->primaryId)) {
                    $isset = true;
                    foreach ($this->primaryId as $value) {
                        if (!isset($this->columns[$value])) {
                            $isset = false;
                        }
                    }
                    $action = ($isset) ? 'update' : 'insert';
                } else {
                    $action = (isset($this->columns[$this->primaryId])) ? 'update' : 'insert';
                }
            }

            // Build the UPDATE SQL
            if ($action == 'update') {
                $this->sql->update((array)$this->columns);
                $this->sql->update()->where(true);

                if (is_array($this->primaryId)) {
                    foreach ($this->primaryId as $value) {
                        if (null === $this->columns[$value]) {
                            $this->sql->update()->where()->isNull($this->sql->adapter()->escape($value));
                        } else {
                            $this->sql->update()->where()->equalTo($this->sql->adapter()->escape($value), $this->sql->adapter()->escape($this->columns[$value]));
                        }
                    }
                } else {
                    $this->sql->update()->where()->equalTo($this->sql->adapter()->escape($this->primaryId), $this->sql->adapter()->escape($this->columns[$this->primaryId]));
                }

                $this->sql->adapter()->query($this->sql->render(true));
            // Else, build the INSERT SQL
            } else {
                $this->sql->insert((array)$this->columns);
                $this->sql->adapter()->query($this->sql->render(true));

                if ($this->auto) {
                    $this->columns[$this->primaryId] = $this->sql->adapter()->lastId();
                    $this->rows[0][$this->primaryId] = $this->sql->adapter()->lastId();
                }
            }
        }

        if (count($this->columns) > 0) {
            $this->rows[0] = $this->columns;
        }
    }

    /**
     * Delete the database record.
     *
     * @param  array $columnsPassed
     * @param  array $columns
     * @throws Exception
     * @return void
     */
    public function delete($columnsPassed, array $columns = null)
    {
        $this->columns = $columnsPassed;

        // Build the DELETE SQL
        if (null === $this->primaryId) {
            if ((null === $columns) && (count($this->finder) == 0)) {
                throw new Exception('The column and value parameters were not defined to describe the row(s) to delete.');
            } else if (null === $columns) {
                $columns = $this->finder;
            }

            $this->sql->delete();

            foreach ($columns as $key => $value) {
                if (null === $value) {
                    $this->sql->delete()->where()->isNull($this->sql->adapter()->escape($key));
                } else {
                    $this->sql->delete()->where()->equalTo($this->sql->adapter()->escape($key), $this->sql->adapter()->escape($value));
                }
            }

            $this->sql->adapter()->query($this->sql->render(true));

            $this->columns = array();
            $this->rows = array();
        } else {
            $this->sql->delete();

            // Specific column override.
            if (null !== $columns) {
                foreach ($columns as $key => $value) {
                    if (null === $value) {
                        $this->sql->delete()->where()->isNull($this->sql->adapter()->escape($key));
                    } else {
                        $this->sql->delete()->where()->equalTo($this->sql->adapter()->escape($key), $this->sql->adapter()->escape($value));
                    }
                }
            // Else, continue with the primaryId column(s)
            } else if (is_array($this->primaryId)) {
                foreach ($this->primaryId as $value) {
                    if (null === $this->columns[$value]) {
                        $this->sql->delete()->where()->isNull($this->sql->adapter()->escape($value));
                    } else {
                        $this->sql->delete()->where()->equalTo($this->sql->adapter()->escape($value), $this->sql->adapter()->escape($this->columns[$value]));
                    }
                }
            } else {
                $this->sql->delete()->where()->equalTo($this->sql->adapter()->escape($this->primaryId), $this->sql->adapter()->escape($this->columns[$this->primaryId]));
            }

            $this->sql->adapter()->query($this->sql->render(true));

            $this->columns = array();
            $this->rows = array();
        }
    }

    /**
     * Execute a custom SQL query.
     *
     * @param  string $sql
     * @param  array  $params
     * @return void
     */
    public function execute($sql, $params = null)
    {
        $this->query($sql);
    }

    /**
     * Execute a custom SQL query.
     *
     * @param  string $sql
     * @return void
     */
    public function query($sql)
    {
        $this->sql->adapter()->query($sql);

        // Set the return results.
        if (stripos($sql, 'select') !== false) {
            $this->setResults();
        } else if (stripos($sql, 'delete') !== false) {
            $this->columns = array();
            $this->rows = array();
        }
    }

    /**
     * Get total count of records
     *
     * @param  array $columns
     * @return int
     */
    public function getCount(array $columns = null)
    {
        // Build the SQL.
        $this->sql->select(array('total_count' => 'COUNT(*)'));

        if (null !== $columns) {
            foreach ($columns as $key => $value) {
                $this->sql->select()->where()->equalTo($this->sql->adapter()->escape($key), $this->sql->adapter()->escape($value));
            }
        }

        $this->sql->adapter()->query($this->sql->render(true));
        $this->setResults();

        return $this->columns['total_count'];
    }

    /**
     * Set the query results.
     *
     * @return void
     */
    protected function setResults()
    {
        $this->rows = array();

        while (($row = $this->sql->adapter()->fetch()) != false) {
            $this->rows[] = new \ArrayObject($row, \ArrayObject::ARRAY_AS_PROPS);
        }

        if (isset($this->rows[0])) {
            $this->columns = $this->rows[0];
        }
    }

}
