<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Archive
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Archive;

/**
 * Archive class
 *
 * @category   Pop
 * @package    Pop_Archive
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Archive
{

    /**
     * Array of allowed file types.
     * @var array
     */
    protected $allowed = array(
        'bz2'  => 'application/bzip2',
        'gz'   => 'application/x-gzip',
        'rar'  => 'application/x-rar-compressed',
        'tar'  => 'application/x-tar',
        'tbz'  => 'application/bzip2',
        'tbz2' => 'application/bzip2',
        'tgz'  => 'application/x-gzip',
        'zip'  => 'application/x-zip'
    );

    /**
     * Archive adapter
     * @var mixed
     */
    protected $adapter = null;

    /**
     * Full path of archive, i.e. '/path/to/archive.ext'
     * @var string
     */
    protected $fullpath = null;

    /**
     * Full basename of archive, i.e. 'archive.ext'
     * @var string
     */
    protected $basename = null;

    /**
     * Full filename of archive, i.e. 'archive'
     * @var string
     */
    protected $filename = null;

    /**
     * Archive extension, i.e. 'ext'
     * @var string
     */
    protected $extension = null;

    /**
     * Archive size in bytes
     * @var int
     */
    protected $size = 0;

    /**
     * Archive mime type
     * @var string
     */
    protected $mime = null;

    /**
     * Constructor
     *
     * Instantiate the archive object
     *
     * @param  string $archive
     * @param  string $password
     * @param  string $prefix
     * @throws Exception
     * @return \Pop\Archive\Archive
     */
    public function __construct($archive, $password = null, $prefix = 'Pop\\Archive\\Adapter\\')
    {
        $this->allowed   = self::formats();
        $this->fullpath  = $archive;
        $parts           = pathinfo($archive);
        $this->size      = filesize($archive);
        $this->basename  = $parts['basename'];
        $this->filename  = $parts['filename'];
        $this->extension = (isset($parts['extension']) && ($parts['extension'] != '')) ? $parts['extension'] : null;

        if (null === $this->extension) {
            throw new Exception('Error: Unable able to detect archive extension or mime type.');
        }
        if (!isset(self::$allowed[$this->extension])) {
            throw new Exception('Error: That archive type is not allowed.');
        }

        $this->mime = self::$allowed[$this->extension];
        $this->setAdapter($password, $prefix);
    }

    /**
     * Get formats
     *
     * @return array
     */
    public static function formats()
    {
        $allowed = array(
            'bz2'  => 'application/bzip2',
            'gz'   => 'application/x-gzip',
            'rar'  => 'application/x-rar-compressed',
            'tar'  => 'application/x-tar',
            'tbz'  => 'application/bzip2',
            'tbz2' => 'application/bzip2',
            'tgz'  => 'application/x-gzip',
            'zip'  => 'application/x-zip'
        );

        // Check if Bzip2 is available.
        if (!function_exists('bzcompress')) {
            unset($allowed['bz2']);
            unset($allowed['tbz']);
            unset($allowed['tbz2']);
        }
        // Check if Gzip is available.
        if (!function_exists('gzcompress')) {
            unset($allowed['gz']);
            unset($allowed['tgz']);
        }
        // Check if Rar is available.
        if (!class_exists('RarArchive', false)) {
            unset($allowed['rar']);
        }

        $tar = false;
        $includePath = explode(PATH_SEPARATOR, get_include_path());
        foreach ($includePath as $path) {
            if (file_exists($path . DIRECTORY_SEPARATOR . 'Archive' . DIRECTORY_SEPARATOR . 'Tar.php')) {
                $tar = true;
            }
        }

        // Check if Tar is available.
        if (!$tar) {
            unset($allowed['bz']);
            unset($allowed['bz2']);
            unset($allowed['gz']);
            unset($allowed['tar']);
            unset($allowed['tgz']);
            unset($allowed['tbz']);
            unset($allowed['tbz2']);
        }

        // Check if Zip is available.
        if (!class_exists('ZipArchive', false)) {
            unset($allowed['zip']);
        }

        return $allowed;
    }

    /**
     * Method to return the fullpath
     *
     * @return string
     */
    public function getFullPath()
    {
        return $this->fullpath;
    }

    /**
     * Method to return the basename
     *
     * @return string
     */
    public function getBasename()
    {
        return $this->basename;
    }

    /**
     * Method to return the filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Method to return the extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Method to return the size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Method to return the mime
     *
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * Method to return the adapter object
     *
     * @return mixed
     */
    public function adapter()
    {
        return $this->adapter;
    }

    /**
     * Method to return the archive object within the adapter object
     *
     * @return mixed
     */
    public function archive()
    {
        return $this->adapter->archive();
    }

    /**
     * Method to extract an archived and/or compressed file
     *
     * @param  string $to
     * @return \Pop\Archive\Archive
     */
    public function extract($to = null)
    {
        $this->adapter->extract($to);
        return $this;
    }

    /**
     * Method to create an archive file
     *
     * @param  string|array $files
     * @return \Pop\Archive\Archive
     */
    public function addFiles($files)
    {
        $this->adapter->addFiles($files);
        self::__construct($this->fullpath);
        return $this;
    }

    /**
     * Method to return a listing of the contents of an archived file
     *
     * @param  boolean $full
     * @return array
     */
    public function listFiles($full = false)
    {
        return $this->adapter->listFiles($full);
    }

    /**
     * Method to compress an archive file with Gzip or Bzip2
     *
     * @param  string $ext
     * @return \Pop\Archive\Archive
     */
    public function compress($ext = 'gz')
    {
        if ($ext == 'bz') {
            $ext .= '2';
        }
        switch ($ext) {
            case 'gz':
                $newArchive = self::compressGz($this->fullpath);
                break;
            case 'tgz':
                $tmpArchive = self::compressGz($this->fullpath);
                $newArchive = str_replace('.tar.gz', '.tgz', $tmpArchive);
                rename($tmpArchive, $newArchive);
                break;
            case 'bz2':
                $newArchive = self::compressBz2($this->fullpath);
                break;
            case 'tbz':
                $tmpArchive = self::compressBz2($this->fullpath);
                $newArchive = str_replace('.tar.bz2', '.tbz', $tmpArchive);
                rename($tmpArchive, $newArchive);
                break;
            case 'tbz2':
                $tmpArchive = self::compressBz2($this->fullpath);
                $newArchive = str_replace('.tar.bz2', '.tbz2', $tmpArchive);
                rename($tmpArchive, $newArchive);
                break;
            default:
                $newArchive = $this->fullpath;
        }

        if (file_exists($this->fullpath)) {
            unlink($this->fullpath);
        }

        self::__construct($newArchive);
        return $this;
    }

    /**
     * Static method to compress a file with gzip
     *
     * @param  string $file
     * @param  int    $level
     * @param  int    $mode
     * @return mixed
     */
    public static function compressGz($file, $level = 9, $mode = FORCE_GZIP)
    {
        $fullpath = realpath($file);
        $file     = file_get_contents($file);

        // Create the new Gzip file resource, write data and close it
        $gzResource = fopen($fullpath . '.gz', 'w');
        fwrite($gzResource, gzencode($file, $level, $mode));
        fclose($gzResource);

        return $fullpath . '.gz';
    }

    /**
     * Static method to decompress a file with gzip
     *
     * @param  string $file
     * @return mixed
     */
    public static function decompressGz($file)
    {
        $gz = gzopen($file, 'r');
        $uncompressed = '';

        // Read the uncompressed data
        while (!feof($gz)) {
            $uncompressed .= gzread($gz, 4096);
        }

        // Close the Gzip compressed file and write
        // the data to the uncompressed file
        gzclose($gz);
        $newFile = (stripos($file, '.tgz') !== false)
            ? str_replace('.tgz', '.tar', $file) : str_replace('.gz', '', $file);

        file_put_contents($newFile, $uncompressed);

        return $newFile;
    }

    /**
     * Static method to compress a file with bzip2
     *
     * @param  string $file
     * @return mixed
     */
    public static function compressBz2($file)
    {
        $fullpath = realpath($file);
        $file     = file_get_contents($file);

        // Create the new Bzip2 file resource, write data and close it
        $bzResource = bzopen($fullpath . '.bz2', 'w');
        bzwrite($bzResource, $file, strlen($file));
        bzclose($bzResource);

        return $fullpath . '.bz2';
    }

    /**
     * Static method to decompress a file with bzip2
     *
     * @param  string $file
     * @return mixed
     */
    public static function decompressBz2($file)
    {
        $bz = bzopen($file, 'r');
        $uncompressed = '';

        // Read the uncompressed data.
        while (!feof($bz)) {
            $uncompressed .= bzread($bz, 4096);
        }

        // Close the Bzip2 compressed file and write
        // the data to the uncompressed file.
        bzclose($bz);
        if (stripos($file, '.tbz2') !== false) {
            $newFile = str_replace('.tbz2', '.tar', $file);
        } else if (stripos($file, '.tbz') !== false) {
            $newFile = str_replace('.tbz', '.tar', $file);
        } else {
            $newFile = str_replace('.bz2', '', $file);
        }

        file_put_contents($newFile, $uncompressed);

        return $newFile;
    }

    /**
     * Method to set the adapter based on the file name
     *
     * @param  string $password
     * @param  string $prefix
     * @return void
     */
    protected function setAdapter($password = null, $prefix)
    {
        $ext = strtolower($this->extension);

        if (($ext == 'tar') || (stripos($ext, 'bz') !== false) || (stripos($ext, 'gz') !== false)) {
            $class = $prefix . 'Tar';
        } else {
            $class = $prefix . ucfirst($ext);
        }

        $this->adapter = (null !== $password) ? new $class($this, $password) : new $class($this);
    }

}
