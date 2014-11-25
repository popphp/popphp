<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Cache
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Cache;

/**
 * Cache class
 *
 * @category   Pop
 * @package    Pop_Cache
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Cache
{

    /**
     * Lifetime value, in seconds
     * @var int
     */
    protected $lifetime = 0;

    /**
     * Cache adapter
     * @var mixed
     */
    protected $adapter = null;

    /**
     * Constructor
     *
     * Instantiate the cache object
     *
     * @param  Adapter\AdapterInterface $adapter
     * @param  int                      $lifetime
     * @return Cache
     */
    public function __construct(Adapter\AdapterInterface $adapter, $lifetime = 0)
    {
        $this->lifetime = $lifetime;
        $this->adapter  = $adapter;
    }

    /**
     * Determine available adapters
     *
     * @return array
     */
    public static function getAvailableAdapters()
    {
        $pdoDrivers = (class_exists('Pdo', false)) ? \PDO::getAvailableDrivers() : [];
        if (class_exists('Sqlite3') || in_array('sqlite', $pdoDrivers)) {
            $adapters[] = 'Sqlite';
        }

        return [
            'apc'       => (function_exists('apc_cache_info')),
            'file'      => true,
            'memcached' => (class_exists('Memcache', false)),
            'sqlite'    => (class_exists('Sqlite3') || in_array('sqlite', $pdoDrivers))
        ];
    }

    /**
     * Determine if an adapter is available
     *
     * @param  string $adapter
     * @return boolean
     */
    public static function isAvailable($adapter)
    {
        $adapter  = strtolower($adapter);
        $adapters = self::getAvailableAdapters();
        return (isset($adapters[$adapter]) && ($adapters[$adapter]));
    }

    /**
     * Get the adapter
     *
     * @return mixed
     */
    public function adapter()
    {
        return $this->adapter;
    }

    /**
     * Set the cache lifetime.
     *
     * @param  int $time
     * @return Cache
     */
    public function setLifetime($time = 0)
    {
        $this->lifetime = (int)$time;
        return $this;
    }

    /**
     * Get the cache lifetime.
     *
     * @return int
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * Save a value to cache.
     *
     * @param  string $id
     * @param  mixed  $value
     * @return void
     */
    public function save($id, $value)
    {
        $this->adapter->save($id, $value, $this->lifetime);
    }

    /**
     * Load a value from cache.
     *
     * @param  string $id
     * @return mixed
     */
    public function load($id)
    {
        return $this->adapter->load($id, $this->lifetime);
    }

    /**
     * Remove a value in cache.
     *
     * @param  string $id
     * @return void
     */
    public function remove($id)
    {
        $this->adapter->remove($id);
    }

    /**
     * Clear all stored values from cache.
     *
     * @return void
     */
    public function clear()
    {
        $this->adapter->clear();
    }

}
