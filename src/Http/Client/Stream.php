<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Http
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Http\Client;

/**
 * HTTP response class
 *
 * @category   Pop
 * @package    Pop_Http
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Stream extends AbstractClient
{

    /**
     * Stream context
     * @var array
     */
    protected $context = null;

    /**
     * Stream mode
     * @var array
     */
    protected $mode = 'r';

    /**
     * Constructor
     *
     * Instantiate the stream object.
     *
     * @param  string $url
     * @param  array  $context
     * @param  string $mode
     * @return Stream
     */
    public function __construct($url, array $context = null, $mode = 'r')
    {
        $this->setUrl($url);
        $this->setMode($mode);
        if (null !== $context) {
            $this->setContext($context);
        }
    }

    /**
     * Check if stream is POST
     *
     * @return boolean
     */
    public function isPost()
    {
        return (isset($this->context['http']['method']) && (strtoupper($this->context['http']['method']) == 'POST'));
    }

    /**
     * Method to send the request and get the response
     *
     * @return void
     */
    public function send()
    {
        $http_response_header = null;
        $headers = [];

        if ((null !== $this->context) && isset($this->context['http']) && (count($this->fields) > 0)) {
            $this->context['http']['content'] = http_build_query($this->fields);
        }

        $this->resource = (null !== $this->context) ?
            @fopen($this->url, $this->mode, false, stream_context_create($this->context)) :
            @fopen($this->url, $this->mode);

        if ($this->resource != false) {
            $meta = stream_get_meta_data($this->resource);
            $rawHeader = implode("\r\n", $meta['wrapper_data']) . "\r\n\r\n";
            $body = stream_get_contents($this->resource);

            $firstLine = $meta['wrapper_data'][0];
            unset($meta['wrapper_data'][0]);
            $allHeadersAry = $meta['wrapper_data'];
            $bodyStr = $body;
        } else {
            $rawHeader = implode("\r\n", $http_response_header) . "\r\n\r\n";
            $firstLine = $http_response_header[0];
            unset($http_response_header[0]);
            $allHeadersAry = $http_response_header;
            $bodyStr = null;
        }


        // Get the version, code and message
        $version = substr($firstLine, 0, strpos($firstLine, ' '));
        $version = substr($version, (strpos($version, '/') + 1));
        preg_match('/\d\d\d/', trim($firstLine), $match);
        $code    = $match[0];
        $message = str_replace('HTTP/' . $version . ' ' . $code . ' ', '', $firstLine);

        // Get the headers
        foreach ($allHeadersAry as $hdr) {
            $name = substr($hdr, 0, strpos($hdr, ':'));
            $value = substr($hdr, (strpos($hdr, ' ') + 1));
            $headers[trim($name)] = trim($value);
        }

        $this->code     = $code;
        $this->header   = $rawHeader;
        $this->headers  = $headers;
        $this->body     = $bodyStr;
        $this->response = $rawHeader . $bodyStr;
        $this->message  = $message;
        $this->version  = $version;

        if (array_key_exists('Content-Encoding', $this->headers)) {
            $this->decodeBody();
        }
    }

    /**
     * Set the context
     *
     * @param  array $context
     * @return Stream
     */
    public function setContext(array $context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Set the mode
     *
     * @param  string $mode
     * @return Stream
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * Get the context
     *
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Get the mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Close the stream
     *
     * @return void
     */
    public function disconnect()
    {
        if ($this->hasResource()) {
            $this->resource = null;
        }
    }

}
