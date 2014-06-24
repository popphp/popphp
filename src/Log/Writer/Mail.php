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
 * Mail log writer class
 *
 * @category   Pop
 * @package    Pop_Log
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Mail implements WriterInterface
{

    /**
     * Array of emails in which to send the log messages
     * @var array
     */
    protected $emails = [];

    /**
     * Constructor
     *
     * Instantiate the Mail writer object.
     *
     * @param  array $emails
     * @throws Exception
     * @return \Pop\Log\Writer\Mail
     */
    public function __construct(array $emails)
    {
        if (count($emails) == 0) {
            throw new Exception('Error: There must be at least one email address passed.');
        }

        foreach ($emails as $key => $value) {
            if (!is_numeric($key)) {
                $this->emails[] = [
                    'name'  => $key,
                    'email' => $value
                ];
            } else {
                $this->emails[] = [
                    'email' => $value
                ];
            }
        }
    }

    /**
     * Method to write to the log
     *
     * @param  array $logEntry
     * @param  array $options
     * @return \Pop\Log\Writer\Mail
     */
    public function writeLog(array $logEntry, array $options = [])
    {
        $subject = (isset($options['subject'])) ?
            $options['subject'] :
            'Log Entry:';

        $subject .= ' ' . $logEntry['name'] . ' (' . $logEntry['priority'] . ')';

        $mail = new \Pop\Mail\Mail($subject, $this->emails);
        if (isset($options['headers'])) {
            $mail->setHeaders($options['headers']);
        }

        $entry = implode("\t", $logEntry) . PHP_EOL;

        $mail->setText($entry)
             ->send();

        return $this;
    }

}
