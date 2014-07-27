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
 * Payment adapter interface
 *
 * @category   Pop
 * @package    Pop_Payment
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
interface AdapterInterface
{

    /**
     * Send transaction
     *
     * @param  boolean $verifyPeer
     * @throws Exception
     * @return mixed
     */
    public function send($verifyPeer = true);

    /**
     * Get raw response
     *
     * @return string
     */
    public function getResponse();

    /**
     * Get response codes
     *
     * @return array
     */
    public function getResponseCodes();

    /**
     * Get specific response code from a field in the array
     *
     * @param string $key
     * @return string
     */
    public function getCode($key);

    /**
     * Get response code
     *
     * @return string
     */
    public function getResponseCode();

    /**
     * Get response message
     *
     * @return int
     */
    public function getMessage();

    /**
     * Return whether transaction data is valid
     *
     * @return boolean
     */
    public function isValid();

    /**
     * Return whether currently set to test environment
     *
     * @return boolean
     */
    public function isTest();

    /**
     * Return whether the transaction is approved
     *
     * @return boolean
     */
    public function isApproved();

    /**
     * Return whether the transaction is declined
     *
     * @return boolean
     */
    public function isDeclined();

    /**
     * Return whether the transaction is an error
     *
     * @return boolean
     */
    public function isError();

}
