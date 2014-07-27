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
 * Rar archive adapter class
 *
 * @category   Pop
 * @package    Pop_Archive
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Rar implements ArchiveInterface
{

    /**
     * RarArchive object
     * @var \RarArchive
     */
    protected $archive = null;

    /**
     * Archive path
     * @var string
     */
    protected $path = null;

    /**
     * Archive password
     * @var string
     */
    protected $password = null;

    /**
     * Method to instantiate an archive adapter object
     *
     * @param  \Pop\Archive\Archive $archive
     * @param  string               $password
     * @throws Exception
     * @return Rar
     */
    public function __construct(\Pop\Archive\Archive $archive, $password = null)
    {
        $this->path     = $archive->getFullpath();
        $this->password = $password;

        if (file_exists($this->path)) {
            $this->archive = \RarArchive::open($this->path, $this->password);
        } else {
            throw new Exception('Due to licensing restrictions, RAR files cannot be created and can only be extracted.');
        }
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
        $entries = $this->archive->getEntries();
        if (!empty($entries)) {
            foreach ($entries as $entry) {
                $entry->extract((null !== $to) ? $to : './');
            }
        }
    }

    /**
     * Method to create an archive file
     *
     * @param  string|array $files
     * @throws Exception
     * @return void
     */
    public function addFiles($files)
    {
        throw new Exception('Due to licensing restrictions, RAR files cannot be created and can only be decompressed.');
    }

    /**
     * Method to return a listing of the contents of an archived file
     *
     * @param  boolean $full
     * @return array
     */
    public function listFiles($full = false)
    {
        $list = [];
        $entries = $this->archive->getEntries();

        if (!empty($entries)) {
            foreach ($entries as $entry) {
                if (!$full) {
                    $list[] = $entry->getName();
                } else {
                    $list[] = [
                        'name'          => $entry->getName(),
                        'unpacked_size' => $entry->getUnpackedSize(),
                        'packed_size'   => $entry->getPackedSize(),
                        'host_os'       => $entry->getHostOs(),
                        'file_time'     => $entry->getFileTime(),
                        'crc'           => $entry->getCrc(),
                        'attr'          => $entry->getAttr(),
                        'version'       => $entry->getVersion(),
                        'method'        => $entry->getMethod()
                    ];
                }
            }
        }

        return $list;
    }

}
