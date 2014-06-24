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
     * Sql object
     * @var \Pop\Db\Sql
     */
    protected $sql = null;

    /**
     * Constructor
     *
     * Instantiate the DB writer object.
     *
     * @param  \Pop\Db\Sql $sql
     * @throws Exception
     * @return \Pop\Log\Writer\Db
     */
    public function __construct(\Pop\Db\Sql $sql)
    {
        if (null === $sql->getTable()) {
            throw new Exception('Error: The SQL object does not have a table defined.');
        }
        $this->sql = $sql;
    }

    /**
     * Method to write to the log
     *
     * @param  array $logEntry
     * @param  array $options
     * @return \Pop\Log\Writer\Db
     */
    public function writeLog(array $logEntry, array $options = [])
    {
        $columns = [];
        $params  = [];

        $i = 1;
        foreach ($logEntry as $column => $value) {
            $placeholder = $this->sql->getPlaceholder();

            if ($placeholder == ':') {
                $placeholder .= $column;
            } else if ($placeholder == '$') {
                $placeholder .= $i;
            }
            $columns[$column] = $placeholder;
            $params[]         = $value;
            $i++;
        }

        $this->sql->insert($columns);

        $this->sql->db()->prepare((string)$this->sql)
                        ->bindParams($params)
                        ->execute();

        return $this;
    }

}
