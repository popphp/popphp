<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Archive
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Archive\Adapter;

/**
 * Tar archive adapter class
 *
 * @category   Pop
 * @package    Pop_Archive
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Tar implements ArchiveInterface
{

    /**
     * Archive_Tar object
     * @var \Archive_Tar
     */
    protected $archive = null;

    /**
     * Archive path
     * @var string
     */
    protected $path = null;

    /**
     * Archive compression
     * @var string
     */
    protected $compression = null;

    /**
     * Method to instantiate an archive adapter object
     *
     * @param  \Pop\Archive\Archive $archive
     * @return Tar
     */
    public function __construct(\Pop\Archive\Archive $archive)
    {
        if (stripos($archive->getExtension(), 'bz') !== false) {
            $this->compression = 'bz';
        } else if (stripos($archive->getExtension(), 'gz') !== false) {
            $this->compression = 'gz';
        }
        $this->path = $archive->getFullpath();
        $this->archive = new \Archive_Tar($this->path);
    }

    /**
     * Method to return the archive object
     *
     * @return mixed
     */
    public function archive()
    {
        return $this->archive;
    }

    /**
     * Method to extract an archived and/or compressed file
     *
     * @param  string $to
     * @return void
     */
    public function extract($to = null)
    {
        if ($this->compression == 'bz') {
            $this->path = \Pop\Archive\Archive::decompressBz2($this->path);
            $this->archive = new \Archive_Tar($this->path);
        } else if ($this->compression == 'gz') {
            $this->path = \Pop\Archive\Archive::decompressGz($this->path);
            $this->archive = new \Archive_Tar($this->path);
        }
        $this->archive->extract((null !== $to) ? $to : './');
    }

    /**
     * Method to create an archive file
     *
     * @param  string|array $files
     * @return void
     */
    public function addFiles($files)
    {
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            // If file is a directory, loop through and add the files.
            if (file_exists($file) && is_dir($file)) {
                $realpath = realpath($file);
                $dirFiles = $this->getFiles($file);
                foreach ($dirFiles as $fle) {
                    if (file_exists($fle) && !is_dir($fle)) {
                        $fle = $file . DIRECTORY_SEPARATOR . str_replace($realpath . DIRECTORY_SEPARATOR, '', $fle);
                        $this->archive->add($fle);
                    }
                }
            // Else, just add the file.
            } else if (file_exists($file)) {
                $this->archive->add($file);
            }
        }
    }

    /**
     * Method to return a listing of the contents of an archived file
     *
     * @param  boolean $full
     * @return array
     */
    public function listFiles($full = false)
    {
        $files = [];
        $list = $this->archive->listContent();

        if (!$full) {
            foreach ($list as $file) {
                $files[] = $file['filename'];
            }
        } else {
            $files = $list;
        }

        return $files;
    }

    /**
     * Method to get files from the directory
     *
     * @param  string $dir
     * @return array
     */
    protected function getFiles($dir)
    {
        $files   = [];
        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($objects as $fileInfo) {
            if (($fileInfo->getFilename() != '.') && ($fileInfo->getFilename() != '..')) {
                $f = null;
                if (!$fileInfo->isDir()) {
                    $f = realpath($fileInfo->getPathname());
                }
                if (null !== $f) {
                    $files[] = $f;
                }
            }
        }

        return $files;
    }

}
