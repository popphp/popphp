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
 * Authorize payment adapter class
 *
 * @category   Pop
 * @package    Pop_Payment
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Authorize extends AbstractAdapter
{

    /**
     * API Login ID
     * @var string
     */
    protected $apiLoginId = null;

    /**
     * Transaction Key
     * @var string
     */
    protected $transKey = null;

    /**
     * Test URL
     * @var string
     */
    protected $testUrl = 'https://test.authorize.net/gateway/transact.dll';

    /**
     * Live URL
     * @var string
     */
    protected $liveUrl = 'https://secure.authorize.net/gateway/transact.dll';

    /**
     * Transaction data
     * @var array
     */
    protected $transaction = [
        'x_login'                           => null,
        'x_tran_key'                        => null,
        'x_allow_partial_Auth'              => null,
        'x_version'                         => '3.1',
        'x_type'                            => 'AUTH_CAPTURE',
        'x_method'                          => 'CC',
        'x_recurring_billing'               => null,
        'x_amount'                          => null,
        'x_card_num'                        => null,
        'x_exp_date'                        => null,
        'x_card_code'                       => null,
        'x_trans_id'                        => null,
        'x_split_tender_id'                 => null,
        'x_auth_code'                       => null,
        'x_test_request'                    => null,
        'x_duplicate_window'                => null,
        'x_merchant_descriptor'             => null,
        'x_invoice_num'                     => null,
        'x_description'                     => null,
        'x_line_item'                       => null,
        'x_first_name'                      => null,
        'x_last_name'                       => null,
        'x_company'                         => null,
        'x_address'                         => null,
        'x_city'                            => null,
        'x_state'                           => null,
        'x_zip'                             => null,
        'x_country'                         => null,
        'x_phone'                           => null,
        'x_fax'                             => null,
        'x_email'                           => null,
        'x_cust_id'                         => null,
        'x_customer_ip'                     => null,
        'x_ship_to_first_name'              => null,
        'x_ship_to_last_name'               => null,
        'x_ship_to_company'                 => null,
        'x_ship_to_address'                 => null,
        'x_ship_to_city'                    => null,
        'x_ship_to_state'                   => null,
        'x_ship_to_zip'                     => null,
        'x_ship_to_country'                 => null,
        'x_tax'                             => null,
        'x_freight'                         => null,
        'x_duty'                            => null,
        'x_tax_exempt'                      => null,
        'x_po_num'                          => null,
        'x_authentication_indicator'        => null,
        'x_cardholder_authentication_value' => null
    ];

    /**
     * Transaction fields for normalization purposes
     * @var array
     */
    protected $fields = [
        'amount'          => 'x_amount',
        'cardNum'         => 'x_card_num',
        'expDate'         => 'x_exp_date',
        'ccv'             => 'x_card_code',
        'firstName'       => 'x_first_name',
        'lastName'        => 'x_last_name',
        'company'         => 'x_company',
        'address'         => 'x_address',
        'city'            => 'x_city',
        'state'           => 'x_state',
        'zip'             => 'x_zip',
        'country'         => 'x_country',
        'phone'           => 'x_phone',
        'fax'             => 'x_fax',
        'email'           => 'x_email',
        'shipToFirstName' => 'x_ship_to_first_name',
        'shipToLastName'  => 'x_ship_to_last_name',
        'shipToCompany'   => 'x_ship_to_company',
        'shipToAddress'   => 'x_ship_to_address',
        'shipToCity'      => 'x_ship_to_city',
        'shipToState'     => 'x_ship_to_state',
        'shipToZip'       => 'x_ship_to_zip',
        'shipToCountry'   => 'x_ship_to_country'
    ];

    /**
     * Required fields
     * @var array
     */
    protected $requiredFields = [
        'x_login',
        'x_tran_key',
        'x_version',
        'x_amount',
        'x_card_num',
        'x_exp_date'
    ];

    /**
     * Response subcode
     * @var int
     */
    protected $responseSubcode = 0;

    /**
     * Reason code
     * @var int
     */
    protected $reasonCode = 0;

    /**
     * Constructor
     *
     * Instantiate an Authorize.net payment adapter object
     *
     * @param  string  $apiLoginId
     * @param  string  $transKey
     * @param  boolean $test
     * @return Authorize
     */
    public function __construct($apiLoginId, $transKey, $test = false)
    {
        $this->apiLoginId = $apiLoginId;
        $this->transKey   = $transKey;

        $this->transaction['x_login']    = $apiLoginId;
        $this->transaction['x_tran_key'] = $transKey;

        $this->test = $test;
        if ($this->test) {
            $this->transaction['x_test_request'] = 'TRUE';
        }
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

        $this->response        = $this->parseResponse($curl);
        $this->responseCodes   = explode('|', $this->response);
        $this->responseCode    = $this->responseCodes[0];
        $this->responseSubcode = $this->responseCodes[1];
        $this->reasonCode      = $this->responseCodes[2];
        $this->message         = $this->responseCodes[3];

        switch ($this->responseCode) {
            case 1:
                $this->approved = true;
                break;
            case 2:
                $this->declined = true;
                break;
            case 3:
                $this->error = true;
                break;
        }
    }

    /**
     * Get response subcode
     *
     * @return int
     */
    public function getResponseSubcode()
    {
        return $this->responseSubcode;
    }

    /**
     * Get reason code
     *
     * @return int
     */
    public function getReasonCode()
    {
        return $this->reasonCode;
    }

    /**
     * Build the POST string
     *
     * @return string
     */
    protected function buildPostString()
    {
        $post = $this->transaction;
        $post['x_card_num']   = $this->filterCardNum($post['x_card_num']);
        $post['x_delim_data'] = 'TRUE';
        $post['x_delim_char'] = '|';

        return http_build_query($post);
    }

}
