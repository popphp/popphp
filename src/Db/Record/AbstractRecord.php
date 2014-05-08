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

/**
 * Record abstract adapter class
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractRecord
{

    /**
     * Sql abstraction object
     * @var \Pop\Db\Sql
     */
    protected $sql = null;

    /**
     * Rows of multiple return results from a database query
     * in an ArrayObject format.
     * @var array
     */
    protected $rows = array();

    /**
     * Column names of the database table
     * @var array
     */
    protected $columns = array();

    /**
     * Table name of the database table
     * @var string
     */
    protected $tableName = null;

    /**
     * Primary ID column name of the database table
     * @var string
     */
    protected $primaryId = 'id';

    /**
     * Property that determines whether or not the primary ID is auto-increment or not
     * @var boolean
     */
    protected $auto = true;

    /**
     * Original query finder, if primary ID is not set.
     * @var array
     */
    protected $finder = array();

    /**
     * Get the SQL abtraction object.
     *
     * @return \Pop\Db\Sql
     */
    public function sql()
    {
        return $this->sql;
    }

    /**
     * Get the result rows.
     *
     * @return array
     */
    public function getResult()
    {
        return array(
            'columns' => $this->columns,
            'rows'    => $this->rows
        );
    }

    /**
     * Get the order by values
     *
     * @param  string $order
     * @return array
     */
    protected function getOrder($order)
    {
        $by = null;
        $ord = null;

        if (stripos($order, 'ASC') !== false) {
            $by = trim(str_replace('ASC', '', $order));
            $ord = 'ASC';
        } else if (stripos($order, 'DESC') !== false) {
            $by = trim(str_replace('DESC', '', $order));
            $ord = 'DESC';
        } else if (stripos($order, 'RAND()') !== false) {
            $by = trim(str_replace('RAND()', '', $order));
            $ord = 'RAND()';
        } else {
            $by = $order;
            $ord = null;
        }

        if (strpos($by, ',') !== false) {
            $by = str_replace(', ', ',', $by);
            $by = explode(',', $by);
        }

        return array('by' => $by, 'order' => $ord);
    }

}
