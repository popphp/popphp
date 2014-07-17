<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Ftp
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Ftp;

/**
 * FTP class
 *
 * @category   Pop
 * @package    Pop_Ftp
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Ftp
{

    /**
     * FTP resource
     * @var resource
     */
    protected $connection = null;

    /**
     * Constructor
     *
     * Instantiate the FTP object.
     *
     * @param  string  $ftp
     * @param  string  $user
     * @param  string  $pass
     * @param  boolean $ssl
     * @throws Exception
     * @return Ftp
     */
    public function __construct($ftp, $user, $pass, $ssl = false)
    {
        if (!function_exists('ftp_connect')) {
            throw new Exception('Error: The FTP extension is not available.');
        } else if ($ssl) {
            if (!($this->connection = ftp_ssl_connect($ftp))) {
                throw new Exception('Error: There was an error connecting to the FTP server ' . $ftp);
            }
        } else {
            if (!($this->connection = ftp_connect($ftp))) {
                throw new Exception('Error: There was an error connecting to the FTP server ' . $ftp);
            }
        }

        if (!ftp_login($this->connection, $user, $pass)) {
            throw new Exception('Error: There was an error connecting to the FTP server ' . $ftp . ' with those credentials.');
        }
    }

    /**
     * Return current working directory.
     *
     * @return string
     */
    public function pwd()
    {
        return ftp_pwd($this->connection);
    }

    /**
     * Change directories.
     *
     * @param  string $dir
     * @throws Exception
     * @return Ftp
     */
    public function chdir($dir)
    {
        if (!ftp_chdir($this->connection, $dir)) {
            throw new Exception('Error: There was an error changing to the directory ' . $dir);
        }
        return $this;
    }

    /**
     * Make directory.
     *
     * @param  string $dir
     * @throws Exception
     * @return Ftp
     */
    public function mkdir($dir)
    {
        if (!ftp_mkdir($this->connection, $dir)) {
            throw new Exception('Error: There was an error making the directory ' . $dir);
        }
        return $this;
    }

    /**
     * Remove directory.
     *
     * @param  string $dir
     * @throws Exception
     * @return Ftp
     */
    public function rmdir($dir)
    {
        if (!ftp_mkdir($this->connection, $dir)) {
            throw new Exception('Error: There was an error removing the directory ' . $dir);
        }
        return $this;
    }

    /**
     * Get file.
     *
     * @param  string $local
     * @param  string $remote
     * @param  int|string $mode
     * @throws Exception
     * @return Ftp
     */
    public function get($local, $remote, $mode = FTP_BINARY)
    {
        if (!ftp_get($this->connection, $local, $remote, $mode)) {
            throw new Exception('Error: There was an error getting the file ' . $remote);
        }
        return $this;
    }

    /**
     * Put file.
     *
     * @param  string $remote
     * @param  string $local
     * @param  int|string $mode
     * @throws Exception
     * @return Ftp
     */
    public function put($remote, $local, $mode = FTP_BINARY)
    {
        if (!ftp_put($this->connection, $remote, $local, $mode)) {
            throw new Exception('Error: There was an error putting the file ' . $local);
        }
    }

    /**
     * Rename file.
     *
     * @param  string $old
     * @param  string $new
     * @throws Exception
     * @return Ftp
     */
    public function rename($old, $new)
    {
        if (!ftp_rename($this->connection, $old, $new)) {
            throw new Exception('Error: There was an error renaming the file ' . $old);
        }
        return $this;
    }

    /**
     * Change permissions.
     *
     * @param  string $file
     * @param  string $mode
     * @throws Exception
     * @return Ftp
     */
    public function chmod($file, $mode)
    {
        if (!ftp_chmod($this->connection, $mode, $file)) {
            throw new Exception('Error: There was an error changing the permission of ' . $file);
        }
        return $this;
    }

    /**
     * Delete file.
     *
     * @param  string $file
     * @throws Exception
     * @return Ftp
     */
    public function delete($file)
    {
        if (!ftp_delete($this->connection, $file)) {
            throw new Exception('Error: There was an error removing the file ' . $file);
        }
        return $this;
    }

    /**
     * Switch the passive mode.
     *
     * @param  boolean $flag
     * @return Ftp
     */
    public function pasv($flag = true)
    {
        ftp_pasv($this->connection, $flag);
        return $this;
    }

    /**
     * Determine whether or not connected
     *
     * @return boolean
     */
    public function isConnected()
    {
        return is_resource($this->connection);
    }

    /**
     * Get the connection resource
     *
     * @return resource
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Close the FTP connection.
     *
     * @return void
     */
    public function disconnect()
    {
        if ($this->isConnected()) {
            ftp_close($this->conn);
        }
    }

}
