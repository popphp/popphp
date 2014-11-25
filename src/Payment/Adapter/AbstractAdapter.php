<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Payment
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Payment\Adapter;

/**
 * Payment adapter abstract class
 *
 * @category   Pop
 * @package    Pop_Payment
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */

abstract class AbstractAdapter implements AdapterInterface
{

    /**
     * Transaction data
     * @var array
     */
    protected $transaction = [];

    /**
     * Transaction fields for normalization purposes
     * @var array
     */
    protected $fields = [];

    /**
     * Required fields
     * @var array
     */
    protected $requiredFields = [];

    /**
     * Boolean flag to use test environment or not
     * @var boolean
     */
    protected $test = true;

    /**
     * Boolean flag for approved transaction
     * @var boolean
     */
    protected $approved = false;

    /**
     * Boolean flag for declined transaction
     * @var boolean
     */
    protected $declined = false;

    /**
     * Boolean flag for error transaction
     * @var boolean
     */
    protected $error = false;

    /**
     * Response string
     * @var string
     */
    protected $response = null;

    /**
     * Response codes
     * @var array
     */
    protected $responseCodes = [];

    /**
     * Response code
     * @var string
     */
    protected $responseCode = null;

    /**
     * Response message
     * @var string
     */
    protected $message = null;

    /**
     * Send transaction
     *
     * @param  boolean $verifyPeer
     * @throws Exception
     * @return mixed
     */
    abstract public function send($verifyPeer = true);

    /**
     * Get raw response
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get response codes
     *
     * @return array
     */
    public function getResponseCodes()
    {
        return $this->responseCodes;
    }

    /**
     * Get specific response code from a field in the array
     *
     * @param  string $key
     * @return string
     */
    public function getCode($key)
    {
        return (isset($this->responseCodes[$key])) ? $this->responseCodes[$key] : null;
    }

    /**
     * Get response code
     *
     * @return string
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Get response message
     *
     * @return int
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Return whether currently set to test environment
     *
     * @return boolean
     */
    public function isTest()
    {
        return $this->test;
    }

    /**
     * Return whether the transaction is approved
     *
     * @return boolean
     */
    public function isApproved()
    {
        return $this->approved;
    }

    /**
     * Return whether the transaction is declined
     *
     * @return boolean
     */
    public function isDeclined()
    {
        return $this->declined;
    }

    /**
     * Return whether the transaction is an error
     *
     * @return boolean
     */
    public function isError()
    {
        return $this->error;
    }

    /**
     * Return whether the required transaction data is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->validate();
    }

    /**
     * Set transaction data
     *
     * @param  array|string $data
     * @param  string       $value
     * @return mixed
     */
    public function set($data, $value = null)
    {
        if (!is_array($data)) {
            $data = [$data => $value];
        }

        foreach ($data as $key => $value) {
            if (null !== $value) {
                if (array_key_exists($key, $this->fields)) {
                    $this->transaction[$this->fields[$key]] = $value;
                } else {
                    $this->transaction[$key] = $value;
                }
            }
        }

        return $this;
    }

    /**
     * Validate that the required transaction data is set
     *
     * @return boolean
     */
    protected function validate()
    {
        $valid = true;

        if (count($this->requiredFields) > 0) {
            foreach ($this->requiredFields as $field) {
                if (null === $this->transaction[$field]) {
                    $valid = false;
                }
            }
        }

        return $valid;
    }

    /**
     * Filter the card num to remove dashes or spaces
     *
     * @param  string $ccNum
     * @return string
     */
    protected function filterCardNum($ccNum)
    {
        $filtered = $ccNum;

        if (strpos($filtered, '-') !== false) {
            $filtered = str_replace('-', '', $filtered);
        }
        if (strpos($filtered, ' ') !== false) {
            $filtered = str_replace(' ', '', $filtered);
        }

        return $filtered;
    }

    /**
     * Filter the exp date
     *
     * @param  string $date
     * @param  int    $length
     * @return string
     */
    protected function filterExpDate($date, $length = 4)
    {
        $filtered = $date;

        if ($length == 4) {
            $regex = '/^\d\d\d\d$/';
        } else {
            $regex = '/^\d\d\d\d\d\d$/';
        }

        if (preg_match($regex, $filtered) == 0) {
            $delim = null;
            if (strpos($filtered, '/') !== false) {
                $delim = '/';
            } else if (strpos($filtered, '-') !== false) {
                $delim = '-';
            }
            if ($length == 4) {
                if (null !== $delim) {
                    $dateAry = explode($delim, $filtered);
                    $month = $dateAry[0];
                    $year = (strlen($dateAry[1]) == 4) ? substr($dateAry[1], -2) : $dateAry[1];
                    $filtered = $month . $year;
                } else {
                    if (strlen($filtered) == 6) {
                        $filtered = substr($filtered, 0, 2) . substr($filtered, -2);
                    }
                }
            } else {
                if (null !== $delim) {
                    $dateAry = explode($delim, $filtered);
                    $month = $dateAry[0];
                    $year = (strlen($dateAry[1]) == 2) ? substr(date('Y'), 0, 2) . $dateAry[1] : $dateAry[1];
                    $filtered = $month . $year;
                } else {
                    if (strlen($filtered) == 4) {
                        $filtered = substr($filtered, 0, 2) . substr(date('Y'), 0, 2) . substr($filtered, -2);
                    }
                }
            }
        }

        return $filtered;
    }

    /**
     * Parse the curl response
     *
     * @param  resource $curl
     * @return string
     */
    protected function parseResponse($curl)
    {
        $response = curl_exec($curl);

        if (curl_getinfo($curl, CURLOPT_HEADER)) {
            $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $body       = substr($response, $headerSize);
        } else {
            $body       = $response;
        }

        return $body;
    }

}
