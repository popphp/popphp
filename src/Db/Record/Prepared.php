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
 * Prepared record adapter class
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Prepared extends AbstractRecord
{

    /**
     * Prepared statement parameter placeholder
     * @var string
     */
    protected $placeholder = '?';

    /**
     * Constructor
     *
     * Instantiate the record prepared object.
     *
     * @param  \Pop\Db\Db $db
     * @param  array      $options
     * @return \Pop\Db\Record\Prepared
     */
    public function __construct(\Pop\Db\Db $db, $options)
    {
        $this->sql = new \Pop\Db\Sql($db, $options['tableName']);
        $this->tableName = $options['tableName'];
        $this->primaryId = $options['primaryId'];
        $this->auto = $options['auto'];

        if (($this->sql->getDbType() == \Pop\Db\Sql::SQLITE) ||
            (stripos($this->sql->getDb()->getAdapterType(), 'pdo') !== false)) {
            $this->placeholder = ':';
        } else if ($this->sql->getDbType() == \Pop\Db\Sql::PGSQL) {
            $this->placeholder = '$';
        }
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
                    $this->sql->select()->where()->equalTo($this->primaryId[$key], $this->getPlaceholder($this->primaryId[$key], ($key + 1)));
                }
            }
        } else {
            $this->sql->select()->where()->equalTo($this->primaryId, $this->getPlaceholder($this->primaryId));
        }

        // Set the limit, if passed
        if (null !== $limit) {
            $this->sql->select()->limit($this->sql->adapter()->escape($limit));
        }

        // Set the offset, if passed
        if (null !== $offset) {
            $this->sql->select()->offset($this->sql->adapter()->escape($offset));
        }

        // Prepare the statement
        $this->sql->adapter()->prepare($this->sql->render(true));

        if (is_array($this->primaryId)) {
            $params = array();
            foreach ($id as $key => $value) {
                if (null !== $value) {
                    $params[$this->primaryId[$key]] = $value;
                }
            }
        } else {
            $params = array($this->primaryId => $id);
        }

        // Bind the parameters, execute the statement and set the return results.
        $this->sql->adapter()->bindParams((array)$params);
        $this->sql->adapter()->execute();
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

        $i = 1;
        foreach ($columns as $key => $value) {
            if (strpos($value, '%') !== false) {
                $this->sql->select()->where()->like($key, $this->getPlaceholder($key, $i));
                $i++;
            } else if (null === $value) {
                $this->sql->select()->where()->isNull($key);
            } else {
                $this->sql->select()->where()->equalTo($key, $this->getPlaceholder($key, $i));
                $i++;
            }
        }

        // Set the limit, if passed
        if (null !== $limit) {
            $this->sql->select()->limit($this->sql->adapter()->escape($limit));
        }

        // Set the offset, if passed
        if (null !== $offset) {
            $this->sql->select()->offset($this->sql->adapter()->escape($offset));
        }

        // Set the SQL query to a specific order, if given.
        if (null !== $order) {
            $ord = $this->getOrder($order);
            $this->sql->select()->orderBy($ord['by'], $this->sql->adapter()->escape($ord['order']));
        }

        $params = array();
        foreach ($columns as $key => $value) {
            if (null !== $value) {
                $params[$key] = $value;
            }
        }

        // Prepare the statement, bind the parameters, execute the statement and set the return results.
        $this->sql->adapter()->prepare($this->sql->render(true));
        $this->sql->adapter()->bindParams($params);
        $this->sql->adapter()->execute();
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
            $i = 1;
            foreach ($columns as $key => $value) {
                if (strpos($value, '%') !== false) {
                    $this->sql->select()->where()->like($key, $this->getPlaceholder($key, $i));
                    $i++;
                } else if (null === $value) {
                    $this->sql->select()->where()->isNull($key);
                } else {
                    $this->sql->select()->where()->equalTo($key, $this->getPlaceholder($key, $i));
                    $i++;
                }
            }
        } else {
            $this->finder = array();
        }

        // Set any limit to the SQL query.
        if (null !== $limit) {
            $this->sql->select()->limit($this->sql->adapter()->escape($limit));
        }

        // Set the offset, if passed
        if (null !== $offset) {
            $this->sql->select()->offset($this->sql->adapter()->escape($offset));
        }


        // Set the SQL query to a specific order, if given.
        if (null !== $order) {
            $ord = $this->getOrder($order);
            $this->sql->select()->orderBy($ord['by'], $this->sql->adapter()->escape($ord['order']));
        }

        // Prepare the SQL statement
        $this->sql->adapter()->prepare($this->sql->render(true));

        // Bind the parameters
        if (null !== $columns) {
            $params = array();
            foreach ($columns as $key => $value) {
                if (null !== $value) {
                    $params[$key] = $value;
                }
            }
            $this->sql->adapter()->bindParams($params);
        }

        // Execute the statement and set the return results.
        $this->sql->adapter()->execute();
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

        if (null === $this->primaryId) {
            if ($type == \Pop\Db\Record::UPDATE) {
                if (count($this->finder) > 0) {
                    $columns = array();
                    $params = $this->columns;
                    $i = 1;
                    foreach ($this->columns as $key => $value) {
                        if (!array_key_exists($key, $this->finder)) {
                            $columns[$key] = $this->getPlaceholder($key, $i);
                            $i++;
                        }
                    }

                    foreach ($this->finder as $key => $value) {
                        if (isset($params[$key])) {
                            $val = $params[$key];
                            unset($params[$key]);
                            $params[$key] = $val;
                        }
                    }

                    $this->sql()->update((array)$columns);
                    $this->sql->update()->where(true);

                    $i = 1;
                    foreach ($this->finder as $key => $value) {
                        if (null === $value) {
                            $this->sql()->update()->where()->isNull($key);
                        } else {
                            $this->sql()->update()->where()->equalTo($key, $this->getPlaceholder($key, $i));
                            $i++;
                        }
                    }

                    $realParams = array();
                    foreach ($params as $key => $value) {
                        if (null !== $value) {
                            $realParams[$key] = $value;
                        }
                    }

                    $this->sql->adapter()->prepare($this->sql->render(true));
                    $this->sql->adapter()->bindParams($realParams);
                } else {
                    $columns = array();
                    $i = 1;
                    foreach ($this->columns as $key => $value) {
                        $columns[$key] = $this->getPlaceholder($key, $i);
                        $i++;
                    }
                    $this->sql()->update((array)$columns);
                    $this->sql->adapter()->prepare($this->sql->render(true));
                    $this->sql->adapter()->bindParams((array)$this->columns);
                }
                // Execute the SQL statement
                $this->sql->adapter()->execute();
            } else {
                $columns = array();
                $i = 1;
                foreach ($this->columns as $key => $value) {
                    $columns[$key] = $this->getPlaceholder($key, $i);
                    $i++;
                }
                $this->sql->insert((array)$columns);
                $this->sql->adapter()->prepare($this->sql->render(true));
                $this->sql->adapter()->bindParams((array)$this->columns);
                $this->sql->adapter()->execute();
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

            if ($action == 'update') {
                $columns = array();
                $params = $this->columns;

                $i = 1;
                foreach ($this->columns as $key => $value) {
                    if (is_array($this->primaryId)) {
                        if (!in_array($key, $this->primaryId)) {
                            $columns[$key] = $this->getPlaceholder($key, $i);
                            $i++;
                        }
                    } else {
                        if ($key != $this->primaryId) {
                            $columns[$key] = $this->getPlaceholder($key, $i);
                            $i++;
                        }
                    }
                }

                $this->sql->update((array)$columns);
                $this->sql->update()->where(true);

                if (is_array($this->primaryId)) {
                    foreach ($this->primaryId as $key => $value) {
                        if (isset($params[$value])) {
                            $id = $params[$value];
                            unset($params[$value]);
                        } else {
                            $id = $params[$value];
                        }
                        $params[$value] = $id;
                        if (null === $this->columns[$value]) {
                            $this->sql->update()->where()->isNull($value);
                            unset($params[$value]);
                        } else {
                            $this->sql->update()->where()->equalTo($value, $this->getPlaceholder($value, ($i + $key)));
                        }
                    }
                    $realParams = $params;
                } else {
                    if (isset($params[$this->primaryId])) {
                        $id = $params[$this->primaryId];
                        unset($params[$this->primaryId]);
                    } else {
                        $id = $params[$this->primaryId];
                    }
                    $params[$this->primaryId] = $id;
                    $this->sql()->update()->where()->equalTo($this->primaryId, $this->getPlaceholder($this->primaryId, $i));
                    $realParams = $params;
                }

                $this->sql->adapter()->prepare($this->sql->render(true));
                $this->sql->adapter()->bindParams((array)$realParams);
                $this->sql->adapter()->execute();
            } else {
                $columns = array();
                $i = 1;

                foreach ($this->columns as $key => $value) {
                    $columns[$key] = $this->getPlaceholder($key, $i);
                    $i++;
                }

                $this->sql->insert((array)$columns);
                $this->sql->adapter()->prepare($this->sql->render(true));
                $this->sql->adapter()->bindParams((array)$this->columns);
                $this->sql->adapter()->execute();

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

        if (null === $this->primaryId) {
            if ((null === $columns) && (count($this->finder) == 0)) {
                throw new Exception('The column and value parameters were not defined to describe the row(s) to delete.');
            } else if (null === $columns) {
                $columns = $this->finder;
            }

            $this->sql->delete();

            $i = 1;
            foreach ($columns as $key => $value) {
                if (null === $value) {
                    $this->sql->delete()->where()->isNull($this->sql->adapter()->escape($key));
                } else {
                    $this->sql->delete()->where()->equalTo($this->sql->adapter()->escape($key), $this->getPlaceholder($key, $i));
                    $i++;
                }
            }

            $params = array();
            foreach ($columns as $key => $value) {
                if (null !== $value) {
                    $params[$this->primaryId[$key]] = $value;
                }
            }

            $this->sql->adapter()->prepare($this->sql->render(true));
            $this->sql->adapter()->bindParams($params);
            $this->sql->adapter()->execute();

            $this->columns = array();
            $this->rows = array();
        } else {
            $this->sql->delete();

            // Specific column override.
            if (null !== $columns) {
                $i = 1;
                foreach ($columns as $key => $value) {
                    if (null === $value) {
                        $this->sql->delete()->where()->isNull($this->sql->adapter()->escape($key));
                    } else {
                        $this->sql->delete()->where()->equalTo($this->sql->adapter()->escape($key), $this->getPlaceholder($key, $i));
                        $i++;
                    }
                }
            // Else, continue with the primaryId column(s)
            } else if (is_array($this->primaryId)) {
                foreach ($this->primaryId as $key => $value) {
                    if (null === $this->columns[$value]) {
                        $this->sql->delete()->where()->isNull($this->sql->adapter()->escape($value));
                    } else {
                        $this->sql->delete()->where()->equalTo($this->sql->adapter()->escape($value), $this->getPlaceholder($value, ($key + 1)));
                    }
                }
            } else {
                $this->sql->delete()->where()->equalTo($this->sql->adapter()->escape($this->primaryId), $this->getPlaceholder($this->primaryId));
            }

            $this->sql->adapter()->prepare($this->sql->render(true));

            // Specific column override.
            if (null !== $columns) {
                $params = $columns;
            // Else, continue with the primaryId column(s)
            } else if (is_array($this->primaryId)) {
                $params = array();
                foreach ($this->primaryId as $value) {
                    if (null !== $this->columns[$value]) {
                        $params[$value] = $this->columns[$value];
                    }
                }
            } else {
                $params = array($this->primaryId => $this->columns[$this->primaryId]);
            }

            $this->sql->adapter()->bindParams((array)$params);
            $this->sql->adapter()->execute();

            $this->columns = array();
            $this->rows = array();
        }
    }

    /**
     * Execute a custom prepared SQL query.
     *
     * @param  string $sql
     * @param  array  $params
     * @return void
     */
    public function execute($sql, $params = null)
    {
        $this->sql->adapter()->prepare($sql);

        if ((null !== $params) && is_array($params)) {
            $this->sql->adapter()->bindParams((array)$params);
        }

        $this->sql->adapter()->execute();

        // Set the return results.
        if (stripos($sql, 'select') !== false) {
            $this->setResults();
        } else if (stripos($sql, 'delete') !== false) {
            $this->columns = array();
            $this->rows = array();
        }
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
            // If there is more than one result returned, create an array of results.
            if ($this->sql->adapter()->numRows() > 1) {
                while (($row = $this->sql->adapter()->fetch()) != false) {
                    $this->rows[] = new \ArrayObject($row, \ArrayObject::ARRAY_AS_PROPS);
                }
            // Else, set the _columns array to the single returned result.
            } else {
                while (($row = $this->sql->adapter()->fetch()) != false) {
                    $this->rows[0] = new \ArrayObject($row, \ArrayObject::ARRAY_AS_PROPS);
                }
            }
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
            $i = 1;
            $params = array();
            foreach ($columns as $key => $value) {
                $this->sql->select()->where()->equalTo($this->sql->adapter()->escape($key), $this->getPlaceholder($key, $i));
                $params[$this->sql->adapter()->escape($key)] = $this->sql->adapter()->escape($value);
                $i++;
            }
            $this->sql->adapter()->prepare($this->sql->render(true));
            $this->sql->adapter()->bindParams($params);
        } else {
            $this->sql->adapter()->prepare($this->sql->render(true));
        }

        $this->sql->adapter()->execute();
        $this->setResults();

        return (isset($this->columns['total_count']) ? $this->columns['total_count'] : null);
    }

    /**
     * Get the placeholder for a prepared statement
     *
     * @param  string $column
     * @param  int    $i
     * @return string
     */
    protected function getPlaceholder($column, $i = 1)
    {
        $placeholder =  $this->placeholder;

        if ($this->placeholder == ':') {
            $placeholder .= $column;
        } else if ($this->placeholder == '$') {
            $placeholder .= $i;
        }

        return $placeholder;
    }

    /**
     * Set the query results.
     *
     * @return void
     */
    protected function setResults()
    {
        $this->rows = array();

        $rows = $this->sql->adapter()->fetchResult();

        foreach ($rows as $row) {
            $this->rows[] = new \ArrayObject($row, \ArrayObject::ARRAY_AS_PROPS);
        }

        if (isset($this->rows[0])) {
            $this->columns = $this->rows[0];
        }
    }

}
