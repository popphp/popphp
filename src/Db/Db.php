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
namespace Pop\Db;

/**
 * Db class
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Db
{

    /**
     * Default database adapter object
     * @var mixed
     */
    protected $adapter = null;

    /**
     * Flag for a PDO adapter
     * @var boolean
     */
    protected $isPdo = false;

    /**
     * Constructor
     *
     * Instantiate the database connection object.
     *
     * @param  string $type
     * @param  array  $options
     * @param  string $prefix
     * @throws Exception
     * @return \Pop\Db\Db
     */
    public function __construct($type, array $options, $prefix = 'Pop\Db\Adapter\\')
    {
        $this->isPdo = (strtolower($type) == 'pdo');
        $class = $prefix . ucfirst(strtolower($type));

        if (!class_exists($class)) {
            throw new Exception('Error: That database adapter class does not exist.');
        }

        $this->adapter = new $class($options);
    }

    /**
     * Get the database adapter.
     *
     * @return Adapter\AbstractAdapter
     */
    public function adapter()
    {
        return $this->adapter;
    }

    /**
     * Get the PDO flag
     *
     * @return boolean
     */
    public function isPdo()
    {
        return $this->isPdo;
    }

    /**
     * Get the database adapter type.
     *
     * @return string
     */
    public function getAdapterType()
    {
        $type = null;

        $class = get_class($this->adapter);

        if (stripos($class, 'Pdo') !== false) {
            $this->isPdo = true;
            $type = 'Pdo\\' . ucfirst($this->adapter->getDbtype());
        } else {
            $this->isPdo = false;
            $type = ucfirst(str_replace('Pop\Db\Adapter\\', '', $class));
        }

        return $type;
    }

}
