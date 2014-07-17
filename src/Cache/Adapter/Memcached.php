<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Cache
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Cache\Adapter;

/**
 * Memcached cache adapter class
 *
 * @category   Pop
 * @package    Pop_Cache
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Memcached implements AdapterInterface
{

    /**
     * Memcache object
     * @var \Memcache
     */
    protected $memcache = null;

    /**
     * Memcache version
     * @var string
     */
    protected $version = null;

    /**
     * Constructor
     *
     * Instantiate the memcache cache object
     *
     * @param  string $host
     * @param  int    $port
     * @throws Exception
     * @return Memcached
     */
    public function __construct($host = 'localhost', $port = 11211)
    {
        if (!class_exists('Memcache', false)) {
            throw new Exception('Error: Memcache is not available.');
        }

        $this->memcache = new \Memcache();
        if (!$this->memcache->connect($host, (int)$port)) {
            throw new Exception('Error: Unable to connect to the memcached server.');
        }

        $this->version = $this->memcache->getVersion();
    }

    /**
     * Method to get the current version of memcache.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Method to save a value to cache.
     *
     * @param  string $id
     * @param  mixed  $value
     * @param  string $time
     * @return void
     */
    public function save($id, $value, $time)
    {
        $this->memcache->set($id, $value, false, (int)$time);
    }

    /**
     * Method to load a value from cache.
     *
     * @param  string $id
     * @param  string $time
     * @return mixed
     */
    public function load($id, $time)
    {
        return $this->memcache->get($id);
    }

    /**
     * Method to delete a value in cache.
     *
     * @param  string $id
     * @return void
     */
    public function remove($id)
    {
        $this->memcache->delete($id);
    }

    /**
     * Method to clear all stored values from cache.
     *
     * @return void
     */
    public function clear()
    {
        $this->memcache->flush();
    }

}
