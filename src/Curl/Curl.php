<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Curl
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Curl;

/**
 * Curl class
 *
 * @category   Pop
 * @package    Pop_Curl
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Curl
{

    /**
     * cURL resource
     * @var cURL resource
     */
    protected $curl = null;

    /**
     * HTTP version from response
     * @var string
     */
    protected $version = null;

    /**
     * Response code
     * @var int
     */
    protected $code = null;

    /**
     * Response message
     * @var string
     */
    protected $message = null;

    /**
     * Response string
     * @var string
     */
    protected $response = null;

    /**
     * Raw response header
     * @var string
     */
    protected $header = null;

    /**
     * Raw response header size
     * @var int
     */
    protected $headerSize = 0;

    /**
     * Response headers
     * @var array
     */
    protected $headers = [];

    /**
     * Response body
     * @var string
     */
    protected $body = null;

    /**
     * Fields
     * @var array
     */
    protected $fields = [];

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
     * @return \Pop\Curl\Curl
     */
    public function __construct($url, array $opts = null)
    {
        $this->curl = curl_init();

        $this->setOption(CURLOPT_URL, $url);
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
     * @return \Pop\Curl\Curl
     */
    public function setOption($opt, $val)
    {
        curl_setopt($this->curl, $opt, $val);
        $this->options[$opt] = $val;

        return $this;
    }

    /**
     * Set cURL session options.
     *
     * @param  array $opts
     * @return \Pop\Curl\Curl
     */
    public function setOptions($opts)
    {
        curl_setopt_array($this->curl, $opts);

        // Set the protected property to the cURL options.
        foreach ($opts as $k => $v) {
            $this->options[$k] = $v;
        }

        return $this;
    }

    /**
     * Set a field
     *
     * @param  string $name
     * @param  mixed  $value
     * @return \Pop\Curl\Curl
     */
    public function setField($name, $value)
    {
        $this->fields[$name] = $value;
        return $this;
    }

    /**
     * Set all fields
     *
     * @param  array $fields
     * @return \Pop\Curl\Curl
     */
    public function setFields(array $fields)
    {
        foreach ($fields as $name => $value) {
            $this->setField($name, $value);
        }
        return $this;
    }

    /**
     * Set cURL option to return the header
     *
     * @param  boolean $header
     * @return \Pop\Curl\Curl
     */
    public function setReturnHeader($header = false)
    {
        $this->setOption(CURLOPT_HEADER, (bool)$header);
        return $this;
    }

    /**
     * Set cURL option to return the transfer
     *
     * @param  boolean $transfer
     * @return \Pop\Curl\Curl
     */
    public function setReturnTransfer($transfer = false)
    {
        $this->setOption(CURLOPT_RETURNTRANSFER, (bool)$transfer);
        return $this;
    }

    /**
     * Set cURL option for POST
     *
     * @param  boolean $post
     * @return \Pop\Curl\Curl
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
     * Get a field
     *
     * @param  string $name
     * @return mixed
     */
    public function getField($name)
    {
        return (isset($this->fields[$name])) ? $this->fields[$name] : null;
    }

    /**
     * Get all field
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Remove a field
     *
     * @param  string $name
     * @return \Pop\Curl\Curl
     */
    public function removeField($name)
    {
        if (isset($this->fields[$name])) {
            unset($this->fields[$name]);
        }

        return $this;
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
        return (null !== $opt) ? curl_getinfo($this->curl, $opt) : curl_getinfo($this->curl);
    }

    /**
     * Get the full cURL response
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get a response header
     *
     * @param  string $name
     * @return mixed
     */
    public function getHeader($name)
    {
        return (isset($this->headers[$name])) ? $this->headers[$name] : null;
    }

    /**
     * Get all response headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get raw response header
     *
     * @return string
     */
    public function getRawHeader()
    {
        return $this->header;
    }

    /**
     * Get the cURL response body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get the cURL response code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get the cURL response HTTP version
     *
     * @return string
     */
    public function getHttpVersion()
    {
        return $this->version;
    }

    /**
     * Get the cURL response HTTP message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Execute the cURL session.
     *
     * @return mixed
     */
    public function execute()
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

        $this->response = curl_exec($this->curl);
        if ($this->response === false) {
            $this->showError();
        }

        // If the CURLOPT_RETURNTRANSFER option is set, get the response body and parse the headers.
        if (isset($this->options[CURLOPT_RETURNTRANSFER]) && ($this->options[CURLOPT_RETURNTRANSFER] == true)) {
            $this->headerSize = $this->getInfo(CURLINFO_HEADER_SIZE);
            if ($this->options[CURLOPT_HEADER]) {
                $this->header = substr($this->response, 0, $this->headerSize);
                $this->body = substr($this->response, $this->headerSize);
                $this->parseHeaders();
            } else {
                $this->body = $this->response;
            }
        }

        return $this->response;
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
     * Determine whether or not connected
     *
     * @return boolean
     */
    public function isConnected()
    {
        return is_resource($this->curl);
    }

    /**
     * Get the connection resource
     *
     * @return resource
     */
    public function getConnection()
    {
        return $this->curl;
    }

    /**
     * Close the FTP connection.
     *
     * @return void
     */
    public function disconnect()
    {
        if ($this->isConnected()) {
            curl_close($this->curl);
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
                    $this->code = $match[0];
                    $this->message = trim(str_replace('HTTP/' . $this->version . ' ' . $this->code . ' ', '', $header));
                } else if (strpos($header, ':') !== false) {
                    $name = substr($header, 0, strpos($header, ':'));
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
        throw new Exception('Error: ' . curl_errno($this->curl) . ' => ' . curl_error($this->curl) . '.');
    }

}
