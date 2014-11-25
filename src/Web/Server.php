<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Web
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Web;

/**
 * Server class
 *
 * @category   Pop
 * @package    Pop_Web
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Server
{

    /**
     * Server OS
     * @var string
     */
    protected $os = null;

    /**
     * Server Distribution
     * @var string
     */
    protected $distro = null;

    /**
     * Full Server Software String
     * @var string
     */
    protected $software = null;

    /**
     * Server Software
     * @var string
     */
    protected $server = null;

    /**
     * Server Software Version
     * @var string
     */
    protected $serverVersion = null;

    /**
     * PHP Version
     * @var string
     */
    protected $php = null;

    /**
     * Linux flag
     * @var boolean
     */
    protected $linux = false;

    /**
     * Unix flag
     * @var boolean
     */
    protected $unix = false;

    /**
     * Windows flag
     * @var boolean
     */
    protected $windows = false;

    /**
     * Mac flag
     * @var boolean
     */
    protected $mac = false;

    /**
     * Constructor
     *
     * Instantiate the server session object
     *
     * @return Server
     */
    public function __construct()
    {
        $this->software = (isset($_SERVER['SERVER_SOFTWARE'])) ? $_SERVER['SERVER_SOFTWARE'] : null;
        $this->php      = PHP_VERSION;

        $this->detect();
    }

    /**
     * Get OS
     *
     * @return string
     */
    public function getOs()
    {
        return $this->os;
    }

    /**
     * Get distro
     *
     * @return string
     */
    public function getDistro()
    {
        return $this->distro;
    }

    /**
     * Get software
     *
     * @return string
     */
    public function getSoftware()
    {
        return $this->software;
    }

    /**
     * Get server
     *
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Get PHP version
     *
     * @return string
     */
    public function getPhp()
    {
        return $this->php;
    }

    /**
     * Get server version
     *
     * @return string
     */
    public function getServerVersion()
    {
        return $this->serverVersion;
    }

    /**
     * Get Linux flag
     *
     * @return boolean
     */
    public function isLinux()
    {
        return $this->linux;
    }

    /**
     * Get Unix flag
     *
     * @return boolean
     */
    public function isUnix()
    {
        return $this->unix;
    }

    /**
     * Get Windows flag
     *
     * @return boolean
     */
    public function isWindows()
    {
        return $this->windows;
    }

    /**
     * Get Mac flag
     *
     * @return boolean
     */
    public function isMac()
    {
        return $this->mac;
    }

    /**
     * Get Apache flag
     *
     * @return boolean
     */
    public function isApache()
    {
        return ($this->server == 'Apache');
    }

    /**
     * Get IIS flag
     *
     * @return boolean
     */
    public function isIIS()
    {
        return ($this->server == 'IIS');
    }

    /**
     * Detect properties
     *
     * @return void
     */
    protected function detect()
    {
        $matches = [];

        // Set the server OS and distro, if applicable.
        if (preg_match('/(debian|ubuntu|kbuntu|red hat|centos|fedora|suse|knoppix|gentoo|linux)/i', $this->software, $matches) != 0) {
            $this->os     = 'Linux';
            $this->linux  = true;
            $this->distro = $matches[0];
        } else if (preg_match('/(bsd|sun|solaris|unix)/i', $this->software, $matches) != 0) {
            $this->os     = 'Unix';
            $this->unix   = true;
            $this->distro = $matches[0];
        } else if (preg_match('/(win|microsoft)/i', $this->software, $matches) != 0) {
            $this->os      = 'Windows';
            $this->windows = true;
            $this->distro  = 'Microsoft';
        } else if (stripos($this->software, 'mac') !== false) {
            $this->os     = 'Mac';
            $this->mac    = true;
            $this->distro = 'Darwin';
        } else {
            if (stripos(PHP_OS, 'win') !== false) {
                $this->os     = 'Windows';
                $this->distro = 'Microsoft';
            } else {
                $this->os = 'Linux/Unix';
            }
        }

        // Set the server software.
        if (stripos($this->software, 'apache') !== false) {
            $this->server = 'Apache';
        } else if (stripos($this->software, 'iis') !== false) {
            $this->server = 'IIS';
        } else if (stripos($this->software, 'litespeed') !== false) {
            $this->server = 'LiteSpeed';
        } else if (stripos($this->software, 'lighttpd') !== false) {
            $this->server = 'lighttpd';
        } else if (stripos($this->software, 'nginx') !== false) {
            $this->server = 'nginx';
        } else if (stripos($this->software, 'zeus') !== false) {
            $this->server = 'Zeus';
        } else if (stripos($this->software, 'oracle') !== false) {
            $this->server = 'Oracle';
        } else if (stripos($this->software, 'ncsa') !== false) {
            $this->server = 'NCSA';
        }

        // Set the server software version.
        $matches = [];
        preg_match('/\d.\d/', $this->software, $matches);
        if (isset($matches[0])) {
            $this->serverVersion = $matches[0];
        }
    }

}
