<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_File
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\File;

/**
 * File directory class
 *
 * @category   Pop
 * @package    Pop_File
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Dir
{

    /**
     * The directory path
     * @var string
     */
    protected $path = null;

    /**
     * The files within the directory
     * @var array
     */
    protected $files = [];

    /**
     * The nested tree map of the directory and its files
     * @var array
     */
    protected $tree = [];

    /**
     * Flag to store the full path.
     * @var boolean
     */
    protected $full = false;

    /**
     * Flag to dig recursively.
     * @var boolean
     */
    protected $rec = false;

    /**
     * Flag to include base sub directory listings or just the files.
     * @var boolean
     */
    protected $dirs = true;

    /**
     * Constructor
     *
     * Instantiate a directory object
     *
     * @param  string  $dir
     * @param  boolean $full
     * @param  boolean $rec
     * @param  boolean $dirs
     * @throws Exception
     * @return Dir
     */
    public function __construct($dir, $full = false, $rec = false, $dirs = true)
    {
        // Check to see if the directory exists.
        if (!file_exists(dirname($dir))) {
            throw new Exception('Error: The directory does not exist.');
        }

        $this->tree[realpath($dir)] = $this->buildTree(new \DirectoryIterator($dir));
        $this->full = $full;
        $this->rec  = $rec;
        $this->dirs = $dirs;

        // Set the directory path.
        if ((strpos($dir, '/') !== false) && (DIRECTORY_SEPARATOR != '/')) {
            $this->path = str_replace('/', "\\", $dir);
        } else if ((strpos($dir, "\\") !== false) && (DIRECTORY_SEPARATOR != "\\")) {
            $this->path = str_replace("\\", '/', $dir);
        } else {
            $this->path = $dir;
        }

        // Trim the trailing slash.
        if (strrpos($this->path, DIRECTORY_SEPARATOR) == (strlen($this->path) - 1)) {
            $this->path = substr($this->path, 0, -1);
        }

        // If the recursive flag is passed, traverse recursively.
        if ($this->rec) {
            $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->path), \RecursiveIteratorIterator::SELF_FIRST);
            foreach ($objects as $fileInfo) {
                if (($fileInfo->getFilename() != '.') && ($fileInfo->getFilename() != '..')) {
                    // If full path flag was passed, store the full path.
                    if ($this->full) {
                        $f = null;
                        if ($this->dirs) {
                            $f = ($fileInfo->isDir()) ? (realpath($fileInfo->getPathname())) : realpath($fileInfo->getPathname());
                        } else if (!$fileInfo->isDir()) {
                            $f = realpath($fileInfo->getPathname());
                        }
                        if (null !== $f) {
                            $this->files[] = $f;
                        }
                    // Else, store only the directory or file name.
                    } else {
                        if ($this->dirs) {
                            $this->files[] = ($fileInfo->isDir()) ? ($fileInfo->getFilename()) : $fileInfo->getFilename();
                        } else if (!$fileInfo->isDir()) {
                            $this->files[] = $fileInfo->getFilename();
                        }
                    }
                }
            }
        // Else, only traverse the single directory that was passed.
        } else {
            foreach (new \DirectoryIterator($this->path) as $fileInfo) {
                if(!$fileInfo->isDot()) {
                    // If full path flag was passed, store the full path.
                    if ($this->full) {
                        if ($this->dirs) {
                            $f = ($fileInfo->isDir()) ? ($this->path . DIRECTORY_SEPARATOR . $fileInfo->getFilename() . DIRECTORY_SEPARATOR) : ($this->path . DIRECTORY_SEPARATOR . $fileInfo->getFilename());
                        } else if (!$fileInfo->isDir()) {
                            $f = $this->path . DIRECTORY_SEPARATOR . $fileInfo->getFilename();
                        }
                        $this->files[] = $f;
                    // Else, store only the directory or file name.
                    } else {
                        if ($this->dirs) {
                            $this->files[] = ($fileInfo->isDir()) ? ($fileInfo->getFilename()) : $fileInfo->getFilename();
                        } else if (!$fileInfo->isDir()) {
                            $this->files[] = $fileInfo->getFilename();
                        }
                    }
                }
            }
        }
    }

    /**
     * Get the path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get the files.
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Get the tree.
     *
     * @return array
     */
    public function getTree()
    {
        return $this->tree;
    }

    /**
     * Empty an entire directory.
     *
     * @param  boolean $remove
     * @return void
     */
    public function emptyDir($remove = false)
    {
        // Get a directory handle.
        if (!$dh = @opendir($this->path)) {
            return;
        }

        // Recursively dig through the directory, deleting files where applicable.
        while (false !== ($obj = readdir($dh))) {
            if ($obj == '.' || $obj == '..') {
                continue;
            }
            if (!@unlink($this->path . DIRECTORY_SEPARATOR . $obj)) {
                $this->emptyDir(true, $this->path . DIRECTORY_SEPARATOR . $obj);
            }
        }

        // Close the directory handle.
        closedir($dh);

        // If the delete flag was passed, remove the top level directory.
        if ($remove) {
            @rmdir($this->path);
        }
    }

    /**
     * Build the directory tree
     *
     * @param  \DirectoryIterator $it
     * @return array
     */
    protected function buildTree(\DirectoryIterator $it)
    {
        $result = [];

        foreach ($it as $key => $child) {
            if ($child->isDot()) {
                continue;
            }

            $name = $child->getBasename();

            if ($child->isDir()) {
                $subdir = new \DirectoryIterator($child->getPathname());
                $result[DIRECTORY_SEPARATOR . $name] = $this->buildTree($subdir);
            } else {
                $result[] = $name;
            }
        }

        return $result;
    }

}
