<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
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
 * Curl class
 *
 * @category   Pop
 * @package    Pop_Http
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Curl extends AbstractClient
{

    /**
     * cURL options
     * @var array
     */
    protected $options = [];

    /**
     * Constructor
     *
     * Instantiate the cURL object.
     *
     * @param  string $url
     * @param  array  $opts
     * @throws Exception
     * @return Curl
     */
    public function __construct($url, array $opts = null)
    {
        if (!function_exists('curl_init')) {
            throw new Exception('Error: cURL is not available.');
        }

        $this->setUrl($url);
        $this->resource = curl_init();

        $this->setOption(CURLOPT_URL, $this->url);
        $this->setOption(CURLOPT_HEADER, true);
        $this->setOption(CURLOPT_RETURNTRANSFER, true);

        if (null !== $opts) {
            $this->setOptions($opts);
        }
    }

    /**
     * Set cURL session option.
     *
     * @param  int    $opt
     * @param  string $val
     * @return Curl
     */
    public function setOption($opt, $val)
    {
        curl_setopt($this->resource, $opt, $val);
        $this->options[$opt] = $val;

        return $this;
    }

    /**
     * Set cURL session options.
     *
     * @param  array $opts
     * @return Curl
     */
    public function setOptions($opts)
    {
        curl_setopt_array($this->resource, $opts);

        // Set the protected property to the cURL options.
        foreach ($opts as $k => $v) {
            $this->options[$k] = $v;
        }

        return $this;
    }

    /**
     * Set cURL option to return the header
     *
     * @param  boolean $header
     * @return Curl
     */
    public function setReturnHeader($header = true)
    {
        $this->setOption(CURLOPT_HEADER, (bool)$header);
        return $this;
    }

    /**
     * Set cURL option to return the transfer
     *
     * @param  boolean $transfer
     * @return Curl
     */
    public function setReturnTransfer($transfer = true)
    {
        $this->setOption(CURLOPT_RETURNTRANSFER, (bool)$transfer);
        return $this;
    }

    /**
     * Set cURL option for POST
     *
     * @param  boolean $post
     * @return Curl
     */
    public function setPost($post = false)
    {
        $this->setOption(CURLOPT_POST, (bool)$post);
        return $this;
    }

    /**
     * Check if cURL is set to return header
     *
     * @return boolean
     */
    public function isReturnHeader()
    {
        return (isset($this->options[CURLOPT_HEADER]) && ($this->options[CURLOPT_HEADER] == true));
    }

    /**
     * Check if cURL is set to return transfer
     *
     * @return boolean
     */
    public function isReturnTransfer()
    {
        return (isset($this->options[CURLOPT_RETURNTRANSFER]) && ($this->options[CURLOPT_RETURNTRANSFER] == true));
    }

    /**
     * Check if cURL is set to POST
     *
     * @return boolean
     */
    public function isPost()
    {
        return (isset($this->options[CURLOPT_POST]) && ($this->options[CURLOPT_POST] == true));
    }

    /**
     * Get a cURL session option.
     *
     * @param  int $opt
     * @return string
     */
    public function getOption($opt)
    {
        return (isset($this->options[$opt])) ? $this->options[$opt] : null;
    }

    /**
     * Return the cURL session last info.
     *
     * @param  int $opt
     * @return array|string
     */
    public function getInfo($opt = null)
    {
        return (null !== $opt) ? curl_getinfo($this->resource, $opt) : curl_getinfo($this->resource);
    }

    /**
     * Method to send the request and get the response
     *
     * @return void
     */
    public function send()
    {
        // Set query data if there is any
        if (count($this->fields) > 0) {
            if ($this->isPost()) {
                $this->setOption(CURLOPT_POSTFIELDS, $this->fields);
            } else {
                $url = $this->options[CURLOPT_URL] . '?' . http_build_query($this->fields);
                $this->setOption(CURLOPT_URL, $url);
            }
        }

        $this->response = curl_exec($this->resource);
        if ($this->response === false) {
            $this->showError();
        }

        // If the CURLOPT_RETURNTRANSFER option is set, get the response body and parse the headers.
        if (isset($this->options[CURLOPT_RETURNTRANSFER]) && ($this->options[CURLOPT_RETURNTRANSFER] == true)) {
            $headerSize = $this->getInfo(CURLINFO_HEADER_SIZE);
            if ($this->options[CURLOPT_HEADER]) {
                $this->header = substr($this->response, 0, $headerSize);
                $this->body   = substr($this->response, $headerSize);
                $this->parseHeaders();
            } else {
                $this->body = $this->response;
            }
        }

        if (array_key_exists('Content-Encoding', $this->headers)) {
            $this->decodeBody();
        }
    }

    /**
     * Return the cURL version.
     *
     * @return array
     */
    public function version()
    {
        return curl_version();
    }

    /**
     * Close the cURL connection.
     *
     * @return void
     */
    public function disconnect()
    {
        if ($this->hasResource()) {
            curl_close($this->resource);
        }
    }

    /**
     * Parse headers
     *
     * @return void
     */
    protected function parseHeaders()
    {
        if (null !== $this->header) {
            $headers = explode("\n", $this->header);
            foreach ($headers as $header) {
                if (strpos($header, 'HTTP') !== false) {
                    $this->version = substr($header, 0, strpos($header, ' '));
                    $this->version = substr($this->version, (strpos($this->version, '/') + 1));
                    preg_match('/\d\d\d/', trim($header), $match);
                    $this->code    = $match[0];
                    $this->message = trim(str_replace('HTTP/' . $this->version . ' ' . $this->code . ' ', '', $header));
                } else if (strpos($header, ':') !== false) {
                    $name  = substr($header, 0, strpos($header, ':'));
                    $value = substr($header, strpos($header, ':') + 1);
                    $this->headers[trim($name)] = trim($value);
                }
            }
        }
    }

    /**
     * Throw an exception upon a cURL error.
     *
     * @throws Exception
     * @return void
     */
    protected function showError()
    {
        throw new Exception('Error: ' . curl_errno($this->resource) . ' => ' . curl_error($this->resource) . '.');
    }

}
