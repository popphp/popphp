<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Log
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Log\Writer;

/**
 * Db log writer class
 *
 * @category   Pop
 * @package    Pop_Log
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Db implements WriterInterface
{

    /**
     * Table record object that represents the log table in the database
     * @var \Pop\Db\Record
     */
    protected $table = null;

    /**
     * Constructor
     *
     * Instantiate the DB writer object.
     *
     * @param  \Pop\Db\Record $table
     * @throws Exception
     * @return \Pop\Log\Writer\Db
     */
    public function __construct(\Pop\Db\Record $table)
    {
        // Check that the table has the appropriate columns.
        $tableInfo = $table->getTableInfo();
        if (!array_key_exists('timestamp', $tableInfo['columns']) ||
            !array_key_exists('priority', $tableInfo['columns']) ||
            !array_key_exists('name', $tableInfo['columns']) ||
            !array_key_exists('message', $tableInfo['columns'])) {
            throw new Exception("Error: The required table fields ('timestamp', 'priority', 'name' & 'message') do not exist in the table.");
        }
        $this->table = $table;
    }

    /**
     * Method to write to the log
     *
     * @param  array $logEntry
     * @param  array $options
     * @return \Pop\Log\Writer\Db
     */
    public function writeLog(array $logEntry, array $options = array())
    {
        $this->table->setValues($logEntry)
                    ->save();

        return $this;
    }

}
