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
 * Mail message class
 *
 * @category   Pop
 * @package    Pop_Mail
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Message
{

    /**
     * Constants for message body types
     * @var int
     */
    const TEXT           = 1;
    const HTML           = 2;
    const TEXT_HTML      = 3;
    const TEXT_FILE      = 4;
    const HTML_FILE      = 5;
    const TEXT_HTML_FILE = 6;

    /**
     * Mail object
     * @var \Pop\Mail\Message
     */
    protected $mail = null;

    /**
     * Message body
     * @var string
     */
    protected $message = null;

    /**
     * MIME boundary
     * @var string
     */
    protected $mimeBoundary = null;

    /**
     * Text part of the message body
     * @var string
     */
    protected $text = null;

    /**
     * HTML part of the message body
     * @var string
     */
    protected $html = null;

    /**
     * Character set
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * EOL property
     * @var string
     */
    protected $eol = Mail::CRLF;

    /**
     * Constructor
     *
     * Instantiate the message object.
     *
     * @param  Mail $mail
     * @return \Pop\Mail\Message
     */
    public function __construct(\Pop\Mail\Mail $mail)
    {
        $this->mail = $mail;
    }

    /**
     * Get MIME boundary
     *
     * @return string
     */
    public function getBoundary()
    {
        return $this->mimeBoundary;
    }

    /**
     * Get EOL
     *
     * @return string
     */
    public function getEol()
    {
        return $this->eol;
    }

    /**
     * Get character set
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Get text part of the message.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Get HTML part of the message.
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Get the message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set MIME boundary
     *
     * @param  string $bnd
     * @return \Pop\Mail\Message
     */
    public function setBoundary($bnd = null)
    {
        $this->mimeBoundary = (null !== $bnd) ? $bnd : sha1(time());
        return $this;
    }

    /**
     * Set EOL
     *
     * @param  string $eol
     * @return \Pop\Mail\Mail
     */
    public function setEol($eol = Mail::CRLF)
    {
        $this->eol = $eol;
        return $this;
    }

    /**
     * Set character set
     *
     * @param  string $chr
     * @return \Pop\Mail\Message
     */
    public function setCharset($chr)
    {
        $this->charset = $chr;
        return $this;
    }

    /**
     * Set text part of the message.
     *
     * @param  string $text
     * @return \Pop\Mail\Message
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Set HTML part of the message.
     *
     * @param  string $html
     * @return \Pop\Mail\Message
     */
    public function setHtml($html)
    {
        $this->html = $html;
        return $this;
    }

    /**
     * Initialize the email message.
     *
     * @throws Exception
     * @return \Pop\Mail\Message
     */
    public function init()
    {
        $msgType = $this->getMessageType();

        if (null === $msgType) {
            throw new Exception('Error: The message body elements are not set.');
        }

        if (count($this->mail->getQueue()) == 0) {
            throw new Exception('Error: There are no recipients for this email.');
        }

        $this->message = null;
        $this->setBoundary();

        switch ($msgType) {
            // If the message contains files, HTML and text.
            case self::TEXT_HTML_FILE:
                $this->mail->setHeaders([
                    'MIME-Version' => '1.0',
                    'Content-Type' => 'multipart/mixed; boundary="' . $this->getBoundary() . '"' . $this->eol . "This is a multi-part message in MIME format.",
                ]);

                $attachments = $this->mail->getAttachments();
                foreach ($attachments as $attachment) {
                    $this->message .= $attachment->build($this->getBoundary(), $this->eol);
                }

                $this->message .= '--' . $this->getBoundary() . $this->eol .
                    'Content-type: text/html; charset=' . $this->getCharset() .
                    $this->eol . $this->eol . $this->html . $this->eol . $this->eol;

                $this->message .= '--' . $this->getBoundary() . $this->eol .
                    'Content-type: text/plain; charset=' . $this->getCharset() .
                    $this->eol . $this->eol . $this->text . $this->eol . $this->eol . '--' .
                    $this->getBoundary() . '--' . $this->eol . $this->eol;

                break;

            // If the message contains files and HTML.
            case self::HTML_FILE:
                $this->mail->setHeaders([
                    'MIME-Version' => '1.0',
                    'Content-Type' => 'multipart/mixed; boundary="' . $this->getBoundary() . '"' . $this->eol . "This is a multi-part message in MIME format.",
                ]);

                $attachments = $this->mail->getAttachments();
                foreach ($attachments as $attachment) {
                    $this->message .= $attachment->build($this->getBoundary(), $this->eol);
                }

                $this->message .= '--' . $this->getBoundary() . $this->eol .
                    'Content-type: text/html; charset=' . $this->getCharset() .
                    $this->eol . $this->eol . $this->html . $this->eol . $this->eol . '--' .
                    $this->getBoundary() . '--' . $this->eol . $this->eol;

                break;

            // If the message contains files and text.
            case self::TEXT_FILE:
                $this->mail->setHeaders([
                    'MIME-Version' => '1.0',
                    'Content-Type' => 'multipart/mixed; boundary="' . $this->getBoundary() . '"' . $this->eol . "This is a multi-part message in MIME format.",
                ]);

                $attachments = $this->mail->getAttachments();
                foreach ($attachments as $attachment) {
                    $this->message .= $attachment->build($this->getBoundary(), $this->eol);
                }

                $this->message .= '--' . $this->getBoundary() . $this->eol .
                    'Content-type: text/plain; charset=' . $this->getCharset() .
                    $this->eol . $this->eol . $this->text . $this->eol . $this->eol . '--' .
                    $this->getBoundary() . '--' . $this->eol . $this->eol;

                break;

            // If the message contains HTML and text.
            case self::TEXT_HTML:
                $this->mail->setHeaders([
                    'MIME-Version' => '1.0',
                    'Content-Type' => 'multipart/alternative; boundary="' . $this->getBoundary() . '"' . $this->eol . "This is a multi-part message in MIME format.",
                ]);

                $this->message .= '--' . $this->getBoundary() . $this->eol .
                    'Content-type: text/plain; charset=' . $this->getCharset() .
                    $this->eol . $this->eol . $this->text . $this->eol . $this->eol;
                $this->message .= '--' . $this->getBoundary() . $this->eol .
                    'Content-type: text/html; charset=' . $this->getCharset() .
                    $this->eol . $this->eol . $this->html . $this->eol . $this->eol .
                    '--' . $this->getBoundary() . '--' . $this->eol . $this->eol;

                break;

            // If the message contains HTML.
            case self::HTML:
                $this->mail->setHeaders([
                    'MIME-Version' => '1.0',
                    'Content-Type' => 'multipart/alternative; boundary="' . $this->getBoundary() . '"' . $this->eol . "This is a multi-part message in MIME format.",
                ]);

                $this->message .= '--' . $this->getBoundary() . $this->eol .
                    'Content-type: text/html; charset=' . $this->getCharset() .
                    $this->eol . $this->eol . $this->html . $this->eol . $this->eol . '--' .
                    $this->getBoundary() . '--' . $this->eol . $this->eol;

                break;

            // If the message contains text.
            case self::TEXT:
                $this->mail->setHeaders([
                    'Content-Type' => 'text/plain; charset=' . $this->getCharset()
                ]);

                $this->message = $this->text . $this->eol;

                break;

            // Else if nothing has been set yet
            default:
                $this->message = null;
        }

        return $this;
    }

    /**
     * Get message type.
     *
     * @return string
     */
    protected function getMessageType()
    {
        $type = null;
        $numAttach = count($this->mail->getAttachments());

        if (($numAttach > 0) && (null === $this->html) && (null === $this->text)) {
            $type = null;
        } else if (($numAttach > 0) && (null !== $this->html) && (null !== $this->text)) {
            $type = self::TEXT_HTML_FILE;
        } else if (($numAttach > 0) && (null !== $this->html)) {
            $type = self::HTML_FILE;
        } else if (($numAttach > 0) && (null !== $this->text)) {
            $type = self::TEXT_FILE;
        } else if ((null !== $this->html) && (null !== $this->text)) {
            $type = self::TEXT_HTML;
        } else if (null !== $this->html) {
            $type = self::HTML;
        } else if (null !== $this->text) {
            $type = self::TEXT;
        }

        return $type;
    }

}
