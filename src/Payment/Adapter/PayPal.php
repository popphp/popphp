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
 * PayPal payment adapter class
 *
 * @category   Pop
 * @package    Pop_Payment
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class PayPal extends AbstractAdapter
{

    /**
     * API username
     * @var string
     */
    protected $apiUsername = null;

    /**
     * API password
     * @var string
     */
    protected $apiPassword = null;

    /**
     * API signature
     * @var string
     */
    protected $signature = null;

    /**
     * Test URL
     * @var string
     */
    protected $testUrl = 'https://api-3t.sandbox.paypal.com/nvp';

    /**
     * Live URL
     * @var string
     */
    protected $liveUrl = 'https://api-3t.paypal.com/nvp';

    /**
     * Transaction data
     * @var array
     */
    protected $transaction = [
        'USER'             => null,
        'PWD'              => null,
        'SIGNATURE'        => null,
        'METHOD'           => 'DoDirectPayment',
        'VERSION'          => '84.0',
        'PAYMENTACTION'    => 'Sale',
        'CREDITCARDTYPE'   => null,
        'RECURRING'        => null,
        'AMT'              => null,
        'CURRENCYCODE'     => 'USD',
        'ACCT'             => null,
        'EXPDATE'          => null,
        'CVV2'             => null,
        'INVNUM'           => null,
        'DESC'             => null,
        'FIRSTNAME'        => null,
        'LASTNAME'         => null,
        'COMPANY'          => null,
        'STREET'           => null,
        'CITY'             => null,
        'STATE'            => null,
        'ZIP'              => null,
        'COUNTRYCODE'      => 'US',
        'SHIPTOPHONENUM'   => null,
        'FAX'              => null,
        'EMAIL'            => null,
        'IPADDRESS'        => null,
        'SHIPTOFNAME'      => null,
        'SHIPTOLNAME'      => null,
        'SHIPTOCOMPANY'    => null,
        'SHIPTOSTREET'     => null,
        'SHIPTOCITY'       => null,
        'SHIPTOSTATE'      => null,
        'SHIPTOZIP'        => null,
        'SHIPTOCOUNTRY'    => null,
        'TAXAMT'           => null,
        'SHIPPINGAMT'      => null,
        'RETURNFMFDETAILS' => 1
    ];

    /**
     * Transaction fields for normalization purposes
     * @var array
     */
    protected $fields = [
        'amount'          => 'AMT',
        'cardNum'         => 'ACCT',
        'expDate'         => 'EXPDATE',
        'ccv'             => 'CVV2',
        'firstName'       => 'FIRSTNAME',
        'lastName'        => 'LASTNAME',
        'company'         => 'COMPANY',
        'address'         => 'STREET',
        'city'            => 'CITY',
        'state'           => 'STATE',
        'zip'             => 'ZIP',
        'country'         => 'COUNTRYCODE',
        'phone'           => 'SHIPTOPHONENUM',
        'fax'             => 'FAX',
        'email'           => 'EMAIL',
        'shipToFirstName' => 'SHIPTOFNAME',
        'shipToLastName'  => 'SHIPTOLNAME',
        'shipToCompany'   => 'SHIPTOCOMPANY',
        'shipToAddress'   => 'SHIPTOSTREET',
        'shipToCity'      => 'SHIPTOCITY',
        'shipToState'     => 'SHIPTOSTATE',
        'shipToZip'       => 'SHIPTOZIP',
        'shipToCountry'   => 'SHIPTOCOUNTRY'
    ];

    /**
     * Required fields
     * @var array
     */
    protected $requiredFields = [
        'USER',
        'PWD',
        'SIGNATURE',
        'METHOD',
        'ACCT',
        'EXPDATE',
        'CVV2',
        'AMT',
        'FIRSTNAME',
        'LASTNAME',
        'STREET',
        'CITY',
        'STATE',
        'ZIP',
        'COUNTRYCODE',
        'IPADDRESS'
    ];

    /**
     * Constructor
     *
     * Instantiate an PayPal payment adapter object
     *
     * @param  string  $apiUser
     * @param  string  $apiPass
     * @param  string  $sign
     * @param  boolean $test
     * @return PayPal
     */
    public function __construct($apiUser, $apiPass, $sign, $test = false)
    {
        $this->apiUsername = $apiUser;
        $this->apiPassword = $apiPass;
        $this->signature   = $sign;

        $this->transaction['USER']      = $apiUser;
        $this->transaction['PWD']       = $apiPass;
        $this->transaction['SIGNATURE'] = $sign;

        $this->test = $test;
    }

    /**
     * Send transaction
     *
     * @param  boolean $verifyPeer
     * @throws Exception
     * @return void
     */
    public function send($verifyPeer = true)
    {
        if (null === $this->transaction['IPADDRESS']) {
            $this->transaction['IPADDRESS'] = $_SERVER['REMOTE_ADDR'];
        }

        if (!$this->validate()) {
            throw new Exception('The required transaction data has not been set.');
        }

        $options = [
            CURLOPT_HEADER         => false,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $this->buildPostString(),
            CURLOPT_URL            => (($this->test) ? $this->testUrl : $this->liveUrl),
            CURLOPT_RETURNTRANSFER => true
        ];

        if (!$verifyPeer) {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
        }

        $curl = curl_init();
        curl_setopt_array($curl, $options);

        $this->response      = $this->parseResponse($curl);
        $this->responseCodes = $this->parseResponseCodes();

        if (stripos($this->responseCodes['ACK'], 'Success') !== false) {
            $this->approved = true;
            $this->message  = 'The transaction has been approved.';
        } else {
            if (isset($this->responseCodes['L_SHORTMESSAGE0']) && (stripos($this->responseCodes['L_SHORTMESSAGE0'], 'Decline') !== false)) {
                $this->declined = true;
            }
            if (isset($this->responseCodes['L_SEVERITYCODE0']) && (stripos($this->responseCodes['L_SEVERITYCODE0'], 'Error') !== false)) {
                $this->error = true;
            }
            if (isset($this->responseCodes['L_LONGMESSAGE0'])) {
                $this->message = $this->responseCodes['L_LONGMESSAGE0'];
            }
        }
    }

    /**
     * Build the POST string
     *
     * @return string
     */
    protected function buildPostString()
    {
        $post = $this->transaction;

        $post['ACCT']    = $this->filterCardNum($post['ACCT']);
        $post['EXPDATE'] = $this->filterExpDate($post['EXPDATE'], 6);

        if ((null !== $post['SHIPTOFNAME']) || (null !== $post['SHIPTOLNAME'])) {
            $post['SHIPTONAME'] = $post['SHIPTOFNAME'] . ' ' . $post['SHIPTOLNAME'];
            unset($post['SHIPTOFNAME']);
            unset($post['SHIPTOLNAME']);
        }

        return http_build_query($post);
    }

    /**
     * Parse the response codes
     *
     * @return array
     */
    protected function parseResponseCodes()
    {
        $responseCodes = explode('&', $this->response);
        $codes = [];

        foreach ($responseCodes as $value) {
            $value = urldecode($value);
            $valueAry = explode('=', $value);
            $codes[$valueAry[0]] = (!empty($valueAry[1])) ? $valueAry[1] : null;
        }

        return $codes;
    }

}
