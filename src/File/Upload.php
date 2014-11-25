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
 * File class
 *
 * @category   Pop
 * @package    Pop_File
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Upload
{

    /**
     * Array of allowed file types.
     * @var array
     */
    protected static $allowedTypes = [
        'afm'    => 'application/x-font-afm',
        'ai'     => 'application/postscript',
        'aif'    => 'audio/x-aiff',
        'aiff'   => 'audio/x-aiff',
        'avi'    => 'video/x-msvideo',
        'bmp'    => 'image/x-ms-bmp',
        'bz2'    => 'application/bzip2',
        'css'    => 'text/css',
        'csv'    => 'text/csv',
        'doc'    => 'application/msword',
        'docx'   => 'application/msword',
        'eps'    => 'application/octet-stream',
        'fla'    => 'application/octet-stream',
        'flv'    => 'application/octet-stream',
        'gif'    => 'image/gif',
        'gz'     => 'application/x-gzip',
        'html'   => 'text/html',
        'htm'    => 'text/html',
        'jpe'    => 'image/jpeg',
        'jpg'    => 'image/jpeg',
        'jpeg'   => 'image/jpeg',
        'js'     => 'text/plain',
        'json'   => 'text/plain',
        'log'    => 'text/plain',
        'md'     => 'text/plain',
        'mov'    => 'video/quicktime',
        'mp2'    => 'audio/mpeg',
        'mp3'    => 'audio/mpeg',
        'mp4'    => 'video/mp4',
        'mpg'    => 'video/mpeg',
        'mpeg'   => 'video/mpeg',
        'otf'    => 'application/x-font-otf',
        'pdf'    => 'application/pdf',
        'pfb'    => 'application/x-font-pfb',
        'pfm'    => 'application/x-font-pfm',
        'pgsql'  => 'text/plain',
        'phar'   => 'application/x-phar',
        'php'    => 'text/plain',
        'php3'   => 'text/plain',
        'phtml'  => 'text/plain',
        'png'    => 'image/png',
        'ppt'    => 'application/msword',
        'pptx'   => 'application/msword',
        'psb'    => 'image/x-photoshop',
        'psd'    => 'image/x-photoshop',
        'rar'    => 'application/x-rar-compressed',
        'shtml'  => 'text/html',
        'shtm'   => 'text/html',
        'sit'    => 'application/x-stuffit',
        'sitx'   => 'application/x-stuffit',
        'sql'    => 'text/plain',
        'sqlite' => 'application/octet-stream',
        'svg'    => 'image/svg+xml',
        'swf'    => 'application/x-shockwave-flash',
        'tar'    => 'application/x-tar',
        'tbz'    => 'application/bzip2',
        'tbz2'   => 'application/bzip2',
        'tgz'    => 'application/x-gzip',
        'tif'    => 'image/tiff',
        'tiff'   => 'image/tiff',
        'tsv'    => 'text/tsv',
        'ttf'    => 'application/x-font-ttf',
        'txt'    => 'text/plain',
        'wav'    => 'audio/x-wav',
        'wma'    => 'audio/x-ms-wma',
        'wmv'    => 'audio/x-ms-wmv',
        'xls'    => 'application/msword',
        'xlsx'   => 'application/msword',
        'xhtml'  => 'application/xhtml+xml',
        'xml'    => 'application/xml',
        'yaml'   => 'text/plain',
        'yml'    => 'text/plain',
        'zip'    => 'application/x-zip'
    ];

    /**
     * Static method to upload a file and return it
     *
     * @param  string $upload
     * @param  string $file
     * @param  int    $maxSize
     * @param  array  $allowedTypes
     * @throws Exception
     * @return string
     */
    public static function upload($upload, $file, $maxSize = 0, $allowedTypes = null)
    {
        // Check to see if the upload directory exists.
        if (!file_exists(dirname($file))) {
            throw new Exception('Error: The upload directory does not exist.');
        }

        // Check to see if the permissions are set correctly.
        if (!is_writable(dirname($file))) {
            throw new Exception('Error: Permission denied. The upload directory is not writable.');
        }

        // Move the uploaded file, creating a file object with it.
        if (move_uploaded_file($upload, $file)) {
            $fileSize  = filesize($file);
            $fileParts = pathinfo($file);

            $ext = (isset($fileParts['extension'])) ? $fileParts['extension'] : null;

            // Check the file size requirement.
            if (((int)$maxSize > 0) && ($fileSize > $maxSize)) {
                unlink($file);
                throw new Exception('Error: The file uploaded is too big.');
            }

            if (null === $allowedTypes) {
                $allowedTypes = self::$allowedTypes;
            }

            // Check to see if the file is an accepted file format.
            if ((null !== $ext) && (count($allowedTypes) > 0) && (!array_key_exists(strtolower($ext), $allowedTypes))) {
                throw new Exception('Error: The file type ' . strtoupper($ext) . ' is not an accepted file format.');
            }

            return $file;
        } else {
            throw new Exception('Error: There was an error in uploading the file.');
        }
    }

    /**
     * Static method to check for a duplicate file, returning
     * the next incremented filename, i.e. filename_1.txt
     *
     * @param  string $file
     * @param  string $dir
     * @return string
     */
    public static function checkForDuplicate($file, $dir = null)
    {
        if (null === $dir) {
            $dir = getcwd();
        }

        $newFilename  = $file;
        $parts        = pathinfo($file);
        $origFilename = $parts['filename'];
        $ext          = (isset($parts['extension']) && ($parts['extension'] != '')) ? '.' . $parts['extension'] : null;

        $i = 1;

        while (file_exists($dir . DIRECTORY_SEPARATOR . $newFilename)) {
            $newFilename = $origFilename . '_' . $i . $ext;
            $i++;
        }

        return $newFilename;
    }

}
