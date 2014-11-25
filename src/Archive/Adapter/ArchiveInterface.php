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
 * Archive adapter interface
 *
 * @category   Pop
 * @package    Pop_Archive
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
interface ArchiveInterface
{

    /**
     * Instantiate an archive adapter object
     *
     * @param  \Pop\Archive\Archive $archive
     * @return ArchiveInterface
     */
    public function __construct(\Pop\Archive\Archive $archive);

    /**
     * Return the archive object
     *
     * @return mixed
     */
    public function archive();

    /**
     * Extract an archived and/or compressed file
     *
     * @param  string $to
     * @return void
     */
    public function extract($to = null);

    /**
     * Create an archive file
     *
     * @param  string|array $files
     * @return void
     */
    public function addFiles($files);

    /**
     * Return a listing of the contents of an archived file
     *
     * @param  boolean $full
     * @return array
     */
    public function listFiles($full = false);

}
