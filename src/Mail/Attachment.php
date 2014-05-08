<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Mail
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mail;

/**
 * Mail attachment class
 *
 * @category   Pop
 * @package    Pop_Mail
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Attachment
{

    /**
     * File attachment basename
     * @var string
     */
    protected $basename = null;

    /**
     * File attachment encoded content
     * @var string
     */
    protected $encoded = null;

    /**
     * Constructor
     *
     * Instantiate the mail attachment object.
     *
     * @param  string $file
     * @throws Exception
     * @return \Pop\Mail\Attachment
     */
    public function __construct($file)
    {
        // Determine if the file is valid.
        if (!file_exists($file)) {
            throw new Exception('Error: The file does not exist.');
        }

        $fileParts = pathinfo($file);
        $fileContents = file_get_contents($file);

        // Encode the file contents and set the file into the attachments array property.
        $encoded = chunk_split(base64_encode($fileContents));
        $this->basename = $fileParts['basename'];
        $this->encoded = $encoded;
    }

    /**
     * Build attachment
     *
     * @param  string $boundary
     * @param  string $eol
     * @return string
     */
    public function build($boundary, $eol = "\r\n")
    {
        $attachment = $eol . '--' . $boundary.
            $eol . 'Content-Type: file; name="' . $this->basename .
            '"' . $eol . 'Content-Transfer-Encoding: base64' . $eol .
            'Content-Description: ' . $this->basename . $eol .
            'Content-Disposition: attachment; filename="' . $this->basename .
            '"' . $eol . $eol . $this->encoded . $eol . $eol;

        return $attachment;
    }

}
