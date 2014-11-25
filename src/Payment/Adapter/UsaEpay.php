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
 * USAEPay payment adapter class
 *
 * @category   Pop
 * @package    Pop_Payment
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class UsaEpay extends AbstractAdapter
{

    /**
     * Source Key
     * @var string
     */
    protected $sourceKey = null;

    /**
     * Test URL
     * @var string
     */
    protected $testUrl = 'https://sandbox.usaepay.com/gate';

    /**
     * Live URL
     * @var string
     */
    protected $liveUrl = 'https://www.usaepay.com/gate';

    /**
     * Transaction data
     * @var array
     */
    protected $transaction = [
        'UMkey'              => null,
        'UMallowPartialAuth' => null,
        'UMversion'          => '2.9',
        'UMcommand'          => 'cc:sale',
        'UMamount'           => null,
        'UMcurrency'         => 840,  // USD by default, http://wiki.usaepay.com/developer/currencycode
        'UMcard'             => null, // No spaces or dashes
        'UMexpir'            => null, // MMYY format only
        'UMcvv2'             => null,
        'UMorderid'          => null,
        'UMauthCode'         => null,
        'UMtestmode'         => null,
        'UMinvoice'          => null,
        'UMdescription'      => null,
        'UMbillfname'        => null,
        'UMbilllname'        => null,
        'UMbillcompany'      => null,
        'UMbillstreet'       => null,
        'UMbillcity'         => null,
        'UMbillstate'        => null,
        'UMbillzip'          => null,
        'UMbillcountry'      => null,
        'UMbillphone'        => null,
        'UMtestmode '        => null,
        'UMemail'            => null,
        'UMcustid'           => null,
        'UMip'               => null,
        'UMshipfname'        => null,
        'UMshiplname'        => null,
        'UMshipcompany'      => null,
        'UMshipstreet'       => null,
        'UMshipcity'         => null,
        'UMshipstate'        => null,
        'UMshipzip'          => null,
        'UMshipcountry'      => null,
        'UMtax'              => null,
        'UMshipping'         => null,
        'UMponum'            => null
    ];

    /**
     * Transaction fields for normalization purposes
     * @var array
     */
    protected $fields = [
        'amount'          => 'UMamount',
        'cardNum'         => 'UMcard',
        'expDate'         => 'UMexpir',
        'ccv'             => 'UMcvv2',
        'firstName'       => 'UMbillfname',
        'lastName'        => 'UMbilllname',
        'company'         => 'UMbillcompany',
        'address'         => 'UMbillstreet',
        'city'            => 'UMbillcity',
        'state'           => 'UMbillstate',
        'zip'             => 'UMbillzip',
        'country'         => 'UMbillcountry',
        'phone'           => 'UMbillphone',
        'fax'             => 'UMfax',
        'email'           => 'UMemail',
        'shipToFirstName' => 'UMshipfname',
        'shipToLastName'  => 'UMshiplname',
        'shipToCompany'   => 'UMshipcompany',
        'shipToAddress'   => 'UMshipstreet',
        'shipToCity'      => 'UMshipcity',
        'shipToState'     => 'UMshipstate',
        'shipToZip'       => 'UMshipzip',
        'shipToCountry'   => 'UMshipcountry'
    ];

    /**
     * Required fields
     * @var array
     */
    protected $requiredFields = [
        'UMkey',
        'UMamount',
        'UMcard',
        'UMexpir'
    ];

    /**
     * Constructor
     *
     * Instantiate an USAEpay payment adapter object
     *
     * @param  string  $sourceKey
     * @param  boolean $test
     * @return UsaEpay
     */
    public function __construct($sourceKey, $test = false)
    {
        $this->sourceKey            = $sourceKey;
        $this->transaction['UMkey'] = $sourceKey;

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
        $this->responseCode  = $this->responseCodes['UMerrorcode'];
        $this->message       = $this->responseCodes['UMerror'];

        switch ($this->responseCodes['UMstatus']) {
            case 'Approved':
                $this->approved = true;
                break;
            case 'Declined':
                $this->declined = true;
                break;
            case 'Error':
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

        $post['UMcard']  = $this->filterCardNum($post['UMcard']);
        $post['UMexpir'] = $this->filterExpDate($post['UMexpir']);

        if ((null !== $post['UMbillfname']) && (null !== $post['UMbilllname'])) {
            $post['UMname'] = $post['UMbillfname'] . ' ' . $post['UMbilllname'];
            unset($post['UMbillfname']);
            unset($post['UMbilllname']);
        }
        if (null !== $post['UMbillstreet']) {
            $post['UMstreet'] = $post['UMbillstreet'];
            unset($post['UMbillstreet']);
        }
        if (null !== $this->transaction['UMbillzip']) {
            $post['UMzip'] = $post['UMbillzip'];
            unset($post['UMbillzip']);
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
            $value    = urldecode($value);
            $valueAry = explode('=', $value);
            $codes[$valueAry[0]] = (!empty($valueAry[1])) ? $valueAry[1] : null;
        }

        return $codes;
    }

}
