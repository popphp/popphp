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
namespace Pop\Payment;

/**
 * Payment class
 *
 * @category   Pop
 * @package    Pop_Payment
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Payment
{

    /**
     * Payment adapter
     * @var mixed
     */
    protected $adapter = null;

    /**
     * Common transaction fields.
     *
     * These are the common transaction fields that will be normalized to the
     * proper field names by the adapter. You can also add direct adapter-specific
     * fields to the payment transaction object that won't be affected by
     * the field normalization, for example:
     *
     * (Authorize.net)
     * $payment->x_invoice_num = '12345';
     *
     * (UsaEPay)
     * $payment->UMinvoice = '12345';
     *
     * @var array
     */
    protected $fields = [
        'amount'          => null,
        'cardNum'         => null,
        'expDate'         => null,
        'ccv'             => null,
        'firstName'       => null,
        'lastName'        => null,
        'company'         => null,
        'address'         => null,
        'city'            => null,
        'state'           => null,
        'zip'             => null,
        'country'         => null,
        'phone'           => null,
        'fax'             => null,
        'email'           => null,
        'shipToFirstName' => null,
        'shipToLastName'  => null,
        'shipToCompany'   => null,
        'shipToAddress'   => null,
        'shipToCity'      => null,
        'shipToState'     => null,
        'shipToZip'       => null,
        'shipToCountry'   => null
    ];

    /**
     * Constructor
     *
     * Instantiate the payment object
     *
     * @param  Adapter\AbstractAdapter $adapter
     * @return Payment
     */
    public function __construct(Adapter\AbstractAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Access the adapter
     *
     * @return Adapter\AbstractAdapter
     */
    public function adapter()
    {
        return $this->adapter;
    }

    /**
     * Send transaction data
     *
     * @param  boolean $verifyPeer
     * @return void
     */
    public function send($verifyPeer = true)
    {
        $this->adapter->set($this->fields);
        $this->adapter->send($verifyPeer);
    }

    /**
     * Validate transaction data
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->adapter->isValid();
    }

    /**
     * Return whether the transaction is in test mode
     *
     * @return boolean
     */
    public function isTest()
    {
        return $this->adapter->isTest();
    }

    /**
     * Return whether the transaction is approved
     *
     * @return boolean
     */
    public function isApproved()
    {
        return $this->adapter->isApproved();
    }

    /**
     * Return whether the transaction is declined
     *
     * @return boolean
     */
    public function isDeclined()
    {
        return $this->adapter->isDeclined();
    }

    /**
     * Return whether the transaction is an error
     *
     * @return boolean
     */
    public function isError()
    {
        return $this->adapter->isError();
    }

    /**
     * Get raw response
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->adapter->getResponse();
    }

    /**
     * Get response codes
     *
     * @return array
     */
    public function getResponseCodes()
    {
        return $this->adapter->getResponseCodes();
    }

    /**
     * Get specific response code from a field in the array
     *
     * @param  string $key
     * @return string
     */
    public function getCode($key)
    {
        return $this->adapter->getCode($key);
    }

    /**
     * Get response code
     *
     * @return string
     */
    public function getResponseCode()
    {
        return $this->adapter->getResponseCode();
    }

    /**
     * Get response message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->adapter->getMessage();
    }

    /**
     * Set the shipping data fields to the same as billing data fields
     *
     * @return Payment
     */
    public function shippingSameAsBilling()
    {
        $this->fields['shipToFirstName'] = $this->fields['firstName'];
        $this->fields['shipToLastName']  = $this->fields['lastName'];
        $this->fields['shipToCompany']   = $this->fields['company'];
        $this->fields['shipToAddress']   = $this->fields['address'];
        $this->fields['shipToCity']      = $this->fields['city'];
        $this->fields['shipToState']     = $this->fields['state'];
        $this->fields['shipToZip']       = $this->fields['zip'];
        $this->fields['shipToCountry']   = $this->fields['country'];

        return $this;
    }

    /**
     * Set method to set the property to the value of fields[$name].
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->fields[$name] = $value;
    }

    /**
     * Get method to return the value of fields[$name].
     *
     * @param  string $name
     * @throws Exception
     * @return mixed
     */
    public function __get($name)
    {
        return (isset($this->fields[$name])) ? $this->fields[$name] : null;
    }

    /**
     * Return the isset value of fields[$name].
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->fields[$name]);
    }

    /**
     * Unset fields[$name].
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        $this->fields[$name] = null;
    }

}
