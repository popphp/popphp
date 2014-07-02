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
 * Abstract HTTP client class
 *
 * @category   Pop
 * @package    Pop_Http
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractClient implements ClientInterface
{

    /**
     * Client resource object
     * @var resource
     */
    protected $resource = null;

    /**
     * URL
     * @var string
     */
    protected $url = null;

    /**
     * Fields
     * @var array
     */
    protected $fields = [];

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
     * Raw response string
     * @var string
     */
    protected $response = null;

    /**
     * Raw response header
     * @var string
     */
    protected $header = null;

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
     * Set the URL
     *
     * @param  string $url
     * @return AbstractClient
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get the URL
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set a field
     *
     * @param  string $name
     * @param  mixed  $value
     * @return AbstractClient
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
     * @return AbstractClient
     */
    public function setFields(array $fields)
    {
        foreach ($fields as $name => $value) {
            $this->setField($name, $value);
        }
        return $this;
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
     * @return AbstractClient
     */
    public function removeField($name)
    {
        if (isset($this->fields[$name])) {
            unset($this->fields[$name]);
        }

        return $this;
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
     * Get the raw response
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Determine whether or not resource is available
     *
     * @return boolean
     */
    public function hasResource()
    {
        return is_resource($this->resource);
    }

    /**
     * Get the resource
     *
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Decode the body
     *
     * @return resource
     */
    public function decodeBody()
    {
        if (isset($this->headers['Transfer-Encoding']) && ($this->headers['Transfer-Encoding'] == 'chunked')) {
            $this->body = \Pop\Http\Response::decodeChunkedBody($this->body);
        }
        $this->body = \Pop\Http\Response::decodeBody($this->body, $this->headers['Content-Encoding']);
    }

    /**
     * Method to send the request and get the response
     *
     * @return void
     */
    abstract public function send();

}