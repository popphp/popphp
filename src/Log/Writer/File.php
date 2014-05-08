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
namespace Pop\Log\Writer;

/**
 * File log writer class
 *
 * @category   Pop
 * @package    Pop_Log
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class File extends \Pop\File\File implements WriterInterface
{

    /**
     * Array of allowed log file types.
     * @var array
     */
    protected $allowed = array(
        'csv' => 'text/csv',
        'log' => 'text/plain',
        'tsv' => 'text/tsv',
        'txt' => 'text/plain',
        'xml' => 'application/xml'
    );

    /**
     * Constructor
     *
     * Instantiate the file writer object.
     *
     * @param  string $file
     * @param  array  $types
     * @return \Pop\Log\Writer\File
     */
    public function __construct($file, $types = null)
    {
        parent::__construct($file, $types);
    }

    /**
     * Method to write to the log
     *
     * @param  array $logEntry
     * @param  array $options
     * @return \Pop\Log\Writer\File
     */
    public function writeLog(array $logEntry, array $options = array())
    {
        switch ($this->mime) {
            case 'text/plain':
                $entry = implode("\t", $logEntry) . PHP_EOL;
                $this->write($entry, true)
                     ->save();
                break;

            case 'text/csv':
                $logEntry['message'] = '"' . $logEntry['message'] . '"' ;
                $entry = implode(",", $logEntry) . PHP_EOL;
                $this->write($entry, true)
                     ->save();
                break;

            case 'text/tsv':
                $logEntry['message'] = '"' . $logEntry['message'] . '"' ;
                $entry = implode("\t", $logEntry) . PHP_EOL;
                $this->write($entry, true)
                     ->save();
                break;

            case 'application/xml':
                if (null === $this->output) {
                    $this->write('<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL)
                         ->write('<log>' . PHP_EOL, true)
                         ->write('</log>' . PHP_EOL, true);
                }
                $entry = '    <entry timestamp="' . $logEntry['timestamp'] . '" priority="' . $logEntry['priority'] . '" name="' . $logEntry['name'] . '"><![CDATA[' . $logEntry['message'] . ']]></entry>' . PHP_EOL;
                $entry .= '</log>' . PHP_EOL;
                $this->output = str_replace('</log>' . PHP_EOL, $entry, $this->output);
                $this->save();
                break;
        }

        return $this;
    }

}
