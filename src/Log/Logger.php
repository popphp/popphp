<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Log
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Log;

/**
 * Logger class
 *
 * @category   Pop
 * @package    Pop_Log
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Logger
{

    /**
     * Constants for message priorities
     * @var int
     */
    const EMERG  = 0;
    const ALERT  = 1;
    const CRIT   = 2;
    const ERR    = 3;
    const WARN   = 4;
    const NOTICE = 5;
    const INFO   = 6;
    const DEBUG  = 7;

    /**
     * Message priority short codes
     * @var array
     */
    protected $priorities = [
        0 => 'EMERG',
        1 => 'ALERT',
        2 => 'CRIT',
        3 => 'ERR',
        4 => 'WARN',
        5 => 'NOTICE',
        6 => 'INFO',
        7 => 'DEBUG',
    ];

    /**
     * Log writers
     * @var array
     */
    protected $writers = [];

    /**
     * Log timestamp format
     * @var string
     */
    protected $timestamp = 'Y-m-d H:i:s';

    /**
     * Constructor
     *
     * Instantiate the logger object.
     *
     * @param  Writer\WriterInterface $writer
     * @return \Pop\Log\Logger
     */
    public function __construct(Writer\WriterInterface $writer = null)
    {
        if (null !== $writer) {
            $this->addWriter($writer);
        }
    }

    /**
     * Method to add a log writer
     *
     * @param  Writer\WriterInterface $writer
     * @return \Pop\Log\Logger
     */
    public function addWriter(Writer\WriterInterface $writer)
    {
        $this->writers[] = $writer;
        return $this;
    }

    /**
     * Method to get all log writers
     *
     * @return array
     */
    public function getWriters()
    {
        return $this->writers;
    }

    /**
     * Method to set timestamp format
     *
     * @param  string $format
     * @return \Pop\Log\Logger
     */
    public function setTimestamp($format = 'Y-m-d H:i:s')
    {
        $this->timestamp = $format;
        return $this;
    }

    /**
     * Method to get timestamp format
     *
     * @return string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Method to add a log entry
     *
     * @param  int   $priority
     * @param  mixed $message
     * @param  array $options
     * @return \Pop\Log\Logger
     */
    public function log($priority, $message, array $options = [])
    {
        $logEntry = [
            'timestamp' => date($this->timestamp),
            'priority'  => (int) $priority,
            'name'      => $this->priorities[$priority],
            'message'   => (string) $message
        ];

        foreach ($this->writers as $writer) {
            $writer->writeLog($logEntry, $options);
        }

        return $this;
    }

    /**
     * Method to add an EMERG log entry
     *
     * @param  mixed $message
     * @param  array $options
     * @return \Pop\Log\Logger
     */
    public function emerg($message, array $options = [])
    {
        return $this->log(self::EMERG, $message, $options);
    }

    /**
     * Method to add an ALERT log entry
     *
     * @param  mixed $message
     * @param  array $options
     * @return \Pop\Log\Logger
     */
    public function alert($message, array $options = [])
    {
        return $this->log(self::ALERT, $message, $options);
    }

    /**
     * Method to add a CRIT log entry
     *
     * @param  mixed $message
     * @param  array $options
     * @return \Pop\Log\Logger
     */
    public function crit($message, array $options = [])
    {
        return $this->log(self::CRIT, $message, $options);
    }

    /**
     * Method to add an ERR log entry
     *
     * @param  mixed $message
     * @param  array $options
     * @return \Pop\Log\Logger
     */
    public function err($message, array $options = [])
    {
        return $this->log(self::ERR, $message, $options);
    }

    /**
     * Method to add a WARN log entry
     *
     * @param  mixed $message
     * @param  array $options
     * @return \Pop\Log\Logger
     */
    public function warn($message, array $options = [])
    {
        return $this->log(self::WARN, $message, $options);
    }

    /**
     * Method to add a NOTICE log entry
     *
     * @param  mixed $message
     * @param  array $options
     * @return \Pop\Log\Logger
     */
    public function notice($message, array $options = [])
    {
        return $this->log(self::NOTICE, $message, $options);
    }

    /**
     * Method to add an INFO log entry
     *
     * @param  mixed $message
     * @param  array $options
     * @return \Pop\Log\Logger
     */
    public function info($message, array $options = [])
    {
        return $this->log(self::INFO, $message, $options);
    }

    /**
     * Method to add a DEBUG log entry
     *
     * @param  mixed $message
     * @param  array $options
     * @return \Pop\Log\Logger
     */
    public function debug($message, array $options = [])
    {
        return $this->log(self::DEBUG, $message, $options);
    }

}
