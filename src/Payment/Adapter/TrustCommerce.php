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
 * TrustCommerce payment adapter class
 *
 * @category   Pop
 * @package    Pop_Payment
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class TrustCommerce extends AbstractAdapter
{

    /**
     * Customer ID
     * @var string
     */
    protected $custId = null;

    /**
     * Password
     * @var string
     */
    protected $password = null;

    /**
     * URL
     * @var string
     */
    protected $url = 'https://vault.trustcommerce.com/trans/';

    /**
     * Transaction data
     * @var array
     */
    protected $transaction = [
        'custid'           => null,
        'password'         => null,
        'action'           => 'sale',
        'cc'               => null,
        'amount'           => null,
        'exp'              => null,
        'cvv'              => null,
        'checkcvv'         => 'n',
        'avs'              => 'n',
        'transid'          => null,
        'fname'            => null,
        'lname'            => null,
        'address1'         => null,
        'city'             => null,
        'state'            => null,
        'zip'              => null,
        'country'          => null,
        'phone'            => null,
        'email'            => null,
        'ip'               => null,
        'shipto_fname'     => null,
        'shipto_lname'     => null,
        'shipto_address1'  => null,
        'shipto_city'      => null,
        'shipto_state'     => null,
        'shipto_zip'       => null,
        'shipto_country'   => null,
        'tax'              => null,
        'duty'             => null,
        'shippinghandling' => null,
        'partialauth'      => null
    ];

    /**
     * Transaction fields for normalization purposes
     * @var array
     */
    protected $fields = [
        'amount'          => 'amount',
        'cardNum'         => 'cc',
        'expDate'         => 'exp',
        'ccv'             => 'cvv',
        'firstName'       => 'fname',
        'lastName'        => 'lname',
        'address'         => 'address1',
        'city'            => 'city',
        'state'           => 'state',
        'zip'             => 'zip',
        'country'         => 'country',
        'phone'           => 'phone',
        'fax'             => 'fax',
        'email'           => 'email',
        'shipToFirstName' => 'shipto_fname',
        'shipToLastName'  => 'shipto_lname',
        'shipToAddress'   => 'shipto_address1',
        'shipToCity'      => 'shipto_city',
        'shipToState'     => 'shipto_state',
        'shipToZip'       => 'shipto_zip',
        'shipToCountry'   => 'shipto_country'
    ];

    /**
     * Required fields
     * @var array
     */
    protected $requiredFields = [
        'custid',
        'password',
        'action',
        'cc',
        'exp',
        'amount'
    ];

    /**
     * Constructor
     *
     * Instantiate an TrustCommerce payment adapter object
     *
     * @param  string  $custId
     * @param  string  $password
     * @param  boolean $test
     * @return TrustCommerce
     */
    public function __construct($custId, $password, $test = false)
    {
        $this->custId   = $custId;
        $this->password = $password;

        $this->transaction['custid']   = $custId;
        $this->transaction['password'] = $password;

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
        if (!$this->validate()) {
            throw new Exception('The required transaction data has not been set.');
        }

        $this->transaction['demo'] = ($this->test) ? 'y' : 'n';

        $options = [
            CURLOPT_HEADER         => false,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $this->buildPostString(),
            CURLOPT_URL            => $this->url,
            CURLOPT_RETURNTRANSFER => true
        ];

        if (!$verifyPeer) {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
        }

        $curl = curl_init();
        curl_setopt_array($curl, $options);

        $this->response      = $this->parseResponse($curl);
        $this->responseCodes = $this->parseResponseCodes();
        $this->responseCode  = (isset($this->responseCodes['transid']) ? $this->responseCodes['transid'] : null);
        $this->message       = $this->responseCodes['status'];

        switch ($this->responseCodes['status']) {
            case 'approved':
                $this->approved = true;
                break;
            case 'decline':
                $this->declined = true;
                break;
            case 'error':
                $this->error = true;
                break;
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

        $post['cc']     = $this->filterCardNum($post['cc']);
        $post['exp']    = $this->filterExpDate($post['exp']);
        $post['amount'] = str_replace('.', '', $post['amount']);

        if ((null !== $post['fname']) && (null !== $post['lname'])) {
            $post['name'] =  $post['fname'] . ' ' . $post['lname'];
            unset($post['fname']);
            unset($post['lname']);
        }

        if ((null !== $post['shipto_fname']) && (null !== $post['shipto_lname'])) {
            $post['shipto_name'] = $post['shipto_fname'] . ' ' . $post['shipto_lname'];
            unset($post['shipto_fname']);
            unset($post['shipto_lname']);
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
        $responseCodes = explode("\n", $this->response);
        $codes = [];

        foreach ($responseCodes as $key => $value) {
            $value    = trim($value);
            $valueAry = explode('=', $value);
            $codes[$valueAry[0]] = (!empty($valueAry[1])) ? $valueAry[1] : null;
        }

        return $codes;
    }

}
