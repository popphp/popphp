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
 * File adapter cache class
 *
 * @category   Pop
 * @package    Pop_Cache
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class File implements AdapterInterface
{

    /**
     * Cache dir
     * @var string
     */
    protected $dir = null;

    /**
     * Constructor
     *
     * Instantiate the cache file object
     *
     * @param  string $dir
     * @throws Exception
     * @return \Pop\Cache\Adapter\File
     */
    public function __construct($dir)
    {
        if (!file_exists($dir)) {
            throw new Exception('Error: That cache directory does not exist.');
        } else if (!is_writable($dir)) {
            throw new Exception('Error: That cache directory is not writable.');
        }

        $this->dir = realpath($dir);
    }

    /**
     * Method to get the current cache dir.
     *
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
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
        $file = $this->dir . DIRECTORY_SEPARATOR . sha1($id);
        $timestamp = ($time != 0) ? time() + (int)$time : 0;
        file_put_contents($file, serialize(['timestamp' => $timestamp, 'value' => $value]));
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
        $fileId = $this->dir . DIRECTORY_SEPARATOR . sha1($id);
        $value = false;

        if (file_exists($fileId)) {
            $data = unserialize(file_get_contents($fileId));
            if (($data['timestamp'] == 0) || ((time() - $data['timestamp']) <= $time)) {
                $value = $data['value'];
            }
        }

        return $value;
    }

    /**
     * Method to delete a value in cache.
     *
     * @param  string $id
     * @return void
     */
    public function remove($id)
    {
        $fileId = $this->dir . DIRECTORY_SEPARATOR . sha1($id);
        if (file_exists($fileId)) {
            unlink($fileId);
        }
    }

    /**
     * Method to clear all stored values from cache.
     *
     * @param  boolean $del
     * @param  string  $path
     * @return void
     */
    public function clear($del = false, $path = null)
    {
        if (null === $path) {
            $path = $this->dir;
        }

        // Get a directory handle.
        if (!$dh = @opendir($path)) {
            return;
        }

        // Recursively dig through the directory, deleting files where applicable.
        while (false !== ($obj = readdir($dh))) {
            if ($obj == '.' || $obj == '..') {
                continue;
            }
            if (!@unlink($path . DIRECTORY_SEPARATOR . $obj)) {
                $this->clear(true, $path . DIRECTORY_SEPARATOR . $obj);
            }
        }

        // Close the directory handle.
        closedir($dh);

        // If the delete flag was passed, remove the top level directory.
        if ($del) {
            @rmdir($path);
        }
    }

}
