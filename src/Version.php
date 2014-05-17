<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Version
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop;

/**
 * Version class
 *
 * @category   Pop
 * @package    Pop_Version
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Version
{

    /**
     * Current version
     */
    const VERSION = '2.0.0a';

    /**
     * Returns the latest version available.
     *
     * @param  string $version
     * @return mixed
     */
    public static function compareVersion($version)
    {
        return version_compare($version, self::VERSION);
    }

    /**
     * Returns the latest version available.
     *
     * @return mixed
     */
    public static function getLatest()
    {
        $latest = null;

        $handle = fopen('http://www.popphp.org/version', 'r');
        if ($handle !== false) {
            $latest = stream_get_contents($handle);
            fclose($handle);
        }

        return $latest;
    }

    /**
     * Returns whether or not this is the latest version.
     *
     * @return mixed
     */
    public static function isLatest()
    {
        return (self::compareVersion(self::getLatest()) < 1);
    }

    /**
     * Returns an output of dependencies
     *
     * @return array
     */
    public static function check()
    {
        $pdoDrivers  = (class_exists('Pdo', false)) ? \PDO::getAvailableDrivers() : [];
        $includePath = explode(PATH_SEPARATOR, get_include_path());
        $latest      = self::getLatest();

        // Define initial system environment
        $system = [
            'pop' => [
                'installed' => self::VERSION,
                'latest'    => $latest,
                'compare'   => version_compare(self::VERSION, $latest)
            ],
            'php' => [
                'installed' => PHP_VERSION,
                'required'  => '5.4.0',
                'compare'   => version_compare(PHP_VERSION, '5.4.0')
            ],
            'windows' => (stripos(PHP_OS, 'win') !== false),
            'environment' => [
                'apc'     => (function_exists('apc_add')),
                'archive' => [
                    'tar'  => (class_exists('Archive_Tar', false)),
                    'rar'  => (class_exists('RarArchive', false)),
                    'zip'  => (class_exists('ZipArchive', false)),
                    'bz2'  => (function_exists('bzcompress')),
                    'zlib' => (function_exists('gzcompress'))

                ],
                'curl' => (function_exists('curl_init')),
                'db'   => [
                    'mysql'  => (function_exists('mysql_connect')),
                    'mysqli' => (class_exists('mysqli', false)),
                    'oracle' => (function_exists('oci_connect')),
                    'pdo'    => [
                        'mysql'  => (in_array('mysql', $pdoDrivers)),
                        'pgsql'  => (in_array('pgsql', $pdoDrivers)),
                        'sqlite' => (in_array('sqlite', $pdoDrivers)),
                        'sqlsrv' => (in_array('sqlsrv', $pdoDrivers))
                    ],
                    'pgsql'  => (function_exists('pg_connect')),
                    'sqlite' => (class_exists('Sqlite3', false)),
                    'sqlsrv' => (function_exists('sqlsrv_connect'))
                ],
                'dom' => [
                    'dom_document' => (class_exists('DOMDocument', false)),
                    'simple_xml'   => (class_exists('SimpleXMLElement', false))
                ],
                'ftp'   => (function_exists('ftp_connect')),
                'geoip' => (function_exists('geoip_db_get_all_info')),
                'image' => [
                    'gd'       => (function_exists('getimagesize')),
                    'imagick'  => (class_exists('Imagick', false))
                ],
                'mcrypt'   => (function_exists('mcrypt_encrypt')),
                'memcache' => (class_exists('Memcache', false)),
                'soap'     => (class_exists('SoapClient', false))
            ]
        ];

        return $system;
    }

}
