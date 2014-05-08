<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Web
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Web;

/**
 * Browser class
 *
 * @category   Pop
 * @package    Pop_Web
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Browser
{

    /**
     * User IP address
     * @var string
     */
    protected $ip = null;

    /**
     * User Subnet
     * @var string
     */
    protected $subnet = null;

    /**
     * User agent property
     * @var string
     */
    protected $ua = null;

    /**
     * Platform
     * @var string
     */
    protected $platform = null;

    /**
     * Operating system
     * @var string
     */
    protected $os = null;

    /**
     * Browser name
     * @var string
     */
    protected $name = null;

    /**
     * Browser version
     * @var string
     */
    protected $version = null;

    /**
     * Mozilla flag
     * @var boolean
     */
    protected $mozilla = false;

    /**
     * Chrome flag
     * @var boolean
     */
    protected $chrome = false;

    /**
     * WebKit flag
     * @var boolean
     */
    protected $webkit = false;

    /**
     * MSIE flag
     * @var boolean
     */
    protected $msie = false;

    /**
     * Opera flag
     * @var boolean
     */
    protected $opera = false;

    /**
     * Constructor
     *
     * Instantiate the browser session object.
     *
     * @return \Pop\Web\Browser
     */
    public function __construct()
    {
        // Set the user agent and object properties.
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $this->ip     = $_SERVER['REMOTE_ADDR'];
            $this->subnet = substr($_SERVER['REMOTE_ADDR'], 0, strrpos($_SERVER['REMOTE_ADDR'], '.'));
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $this->ua = $_SERVER['HTTP_USER_AGENT'];
            $this->detect();
        }
    }

    /**
     * Method to get IP
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Method to get subnet
     *
     * @return string
     */
    public function getSubnet()
    {
        return $this->subnet;
    }

    /**
     * Method to get user-agent
     *
     * @return string
     */
    public function getUa()
    {
        return $this->ua;
    }

    /**
     * Method to get platform
     *
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Method to get OS
     *
     * @return string
     */
    public function getOs()
    {
        return $this->os;
    }

    /**
     * Method to get browser name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Method to get version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Method to get Mozilla flag
     *
     * @return boolean
     */
    public function isMozilla()
    {
        return $this->mozilla;
    }

    /**
     * Method to get Chrome flag
     *
     * @return boolean
     */
    public function isChrome()
    {
        return $this->chrome;
    }

    /**
     * Method to get WebKit flag
     *
     * @return boolean
     */
    public function isWebkit()
    {
        return $this->webkit;
    }

    /**
     * Method to get MSIE flag
     *
     * @return boolean
     */
    public function isMsie()
    {
        return $this->msie;
    }

    /**
     * Method to get Opera flag
     *
     * @return boolean
     */
    public function isOpera()
    {
        return $this->opera;
    }

    /**
     * Method to detect properties.
     *
     * @return void
     */
    protected function detect()
    {
        // Determine system platform and OS version.
        if (stripos($this->ua, 'Windows') !== false) {
            $this->platform = 'Windows';
            $this->os = (stripos($this->ua, 'Windows NT') !== false) ? substr($this->ua, stripos($this->ua, 'Windows NT'), 14) : 'Windows';
        } else if (stripos($this->ua, 'Macintosh') !== false) {
            $this->platform = 'Macintosh';
            if (stripos($this->ua, 'Intel') !== false) {
                $this->os = substr($this->ua, stripos($this->ua, 'Intel'));
                $this->os = substr($this->os, 0, stripos($this->os, ';'));
            } else if (stripos($this->ua, 'PPC') !== false) {
                $this->os = substr($this->ua, stripos($this->ua, 'PPC'));
                $this->os = substr($this->os, 0, stripos($this->os, ';'));
            } else {
                $this->os = 'Macintosh';
            }
        } else if (stripos($this->ua, 'Linux') !== false) {
            $this->platform = 'Linux';
            if (stripos($this->ua, 'Linux') !== false) {
                $this->os = substr($this->ua, stripos($this->ua, 'Linux '));
                $this->os = substr($this->os, 0, stripos($this->os, ';'));
            } else {
                $this->os = 'Linux';
            }
        } else if (stripos($this->ua, 'SunOS') !== false) {
            $this->platform = 'SunOS';
            if (stripos($this->ua, 'SunOS') !== false) {
                $this->os = substr($this->ua, stripos($this->ua, 'SunOS '));
                $this->os = substr($this->os, 0, stripos($this->os, ';'));
            } else {
                $this->os = 'SunOS';
            }
        } else if (stripos($this->ua, 'OpenBSD') !== false) {
            $this->platform = 'OpenBSD';
            if (stripos($this->ua, 'OpenBSD') !== false) {
                $this->os = substr($this->ua, stripos($this->ua, 'OpenBSD '));
                $this->os = substr($this->os, 0, stripos($this->os, ';'));
            } else {
                $this->os = 'OpenBSD';
            }
        } else if (stripos($this->ua, 'NetBSD') !== false) {
            $this->platform = 'NetBSD';
            if (stripos($this->ua, 'NetBSD') !== false) {
                $this->os = substr($this->ua, stripos($this->ua, 'NetBSD '));
                $this->os = substr($this->os, 0, stripos($this->os, ';'));
            } else {
                $this->os = 'NetBSD';
            }
        } else if (stripos($this->ua, 'FreeBSD') !== false) {
            $this->platform = 'FreeBSD';
            if (stripos($this->ua, 'FreeBSD') !== false) {
                $this->os = substr($this->ua, stripos($this->ua, 'FreeBSD '));
                $this->os = substr($this->os, 0, stripos($this->os, ';'));
            } else {
                $this->os = 'FreeBSD';
            }
        }

        // Determine browser and browser version.
        if (stripos($this->ua, 'Camino') !== false) {
            $this->name = 'Camino';
            $this->webkit = true;
            $this->version = substr($this->ua, (stripos($this->ua, 'Camino/') + 7));
        } else if (stripos($this->ua, 'Chrome') !== false) {
            $this->name = 'Chrome';
            $this->chrome = true;
            $this->webkit = true;
            $this->version = substr($this->ua, (stripos($this->ua, 'Chrome/') + 7));
            $this->version = substr($this->version, 0, (stripos($this->version, ' ')));
        } else if (stripos($this->ua, 'Firefox') !== false) {
            $this->name = 'Firefox';
            $this->mozilla = true;
            $this->version = substr($this->ua, (stripos($this->ua, 'Firefox/') + 8));
        } else if (stripos($this->ua, 'MSIE') !== false) {
            $this->name = 'MSIE';
            $this->msie = true;
            $this->version = substr($this->ua, (stripos($this->ua, 'MSIE ') + 5));
            $this->version = substr($this->version, 0, stripos($this->version, ';'));
        } else if (stripos($this->ua, 'Trident') !== false) {
            $this->name = 'MSIE';
            $this->msie = true;
            $this->version = substr($this->ua, (stripos($this->ua, 'rv:') + 3));
            $this->version = substr($this->version, 0, stripos($this->version, ')'));
        } else if (stripos($this->ua, 'Konqueror') !== false) {
            $this->name = 'Konqueror';
            $this->webkit = true;
            $this->version = substr($this->ua, (stripos($this->ua, 'Konqueror/') + 10));
            $this->version = substr($this->version, 0, stripos($this->version, ';'));
        } else if (stripos($this->ua, 'Navigator') !== false) {
            $this->name = 'Navigator';
            $this->mozilla = true;
            $this->version = substr($this->ua, (stripos($this->ua, 'Navigator/') + 10));
        } else if (stripos($this->ua, 'Opera') !== false) {
            $this->name = 'Opera';
            $this->opera = true;
            $this->version = substr($this->ua, (stripos($this->ua, 'Opera/') + 6));
            $this->version = substr($this->version, 0, stripos($this->version, ' '));
        } else if (stripos($this->ua, 'Safari') !== false) {
            $this->name = 'Safari';
            $this->webkit = true;
            $this->version = substr($this->ua, (stripos($this->ua, 'Version/') + 8));
            $this->version = substr($this->version, 0, stripos($this->version, ' '));
        }
    }

}
