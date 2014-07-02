<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Form\Element;

/**
 * Select form element class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Select extends \Pop\Form\Element
{

    /**
     * Constant for months, short
     * @var int
     */
    const MONTHS_SHORT = 'MONTHS_SHORT';

    /**
     * Constant for months, long
     * @var int
     */
    const MONTHS_LONG = 'MONTHS_LONG';

    /**
     * Constant for days of the month
     * @var int
     */
    const DAYS_OF_MONTH = 'DAYS_OF_MONTH';

    /**
     * Constant for days of the week
     * @var int
     */
    const DAYS_OF_WEEK = 'DAYS_OF_WEEK';

    /**
     * Constant for 12 hours
     * @var int
     */
    const HOURS_12 = 'HOURS_12';

    /**
     * Constant for 24 hours
     * @var int
     */
    const HOURS_24 = 'HOURS_24';

    /**
     * Constant for 60 minutes (0-59)
     * @var int
     */
    const MINUTES = 'MINUTES';

    /**
     * Constant for minutes in increments of 5
     * @var int
     */
    const MINUTES_5 = 'MINUTES_5';

    /**
     * Constant for minutes in increments of 10
     * @var int
     */
    const MINUTES_10 = 'MINUTES_10';

    /**
     * Constant forminutes in increments of 15
     * @var int
     */
    const MINUTES_15 = 'MINUTES_15';

    /**
     * Constant for US states, short
     * @var int
     */
    const US_STATES_SHORT = 'US_STATES_SHORT';

    /**
     * Constant for US states, long
     * @var int
     */
    const US_STATES_LONG = 'US_STATES_LONG';

    /**
     * Constructor
     *
     * Instantiate the select form element object.
     *
     * @param  string $name
     * @param  string|array $value
     * @param  string|array $marked
     * @param  string $indent
     * @param  string $data
     * @return \Pop\Form\Element\Select
     */
    public function __construct($name, $value = null, $marked = null, $indent = null, $data = null)
    {
        $val = null;

        // If the value flag is YEAR-based, calculate the year range for the select drop-down menu.
        if (is_string($value) && (strpos($value, 'YEAR') !== false)) {
            $years = ['----' => '----'];
            $yearAry = explode('_', $value);
            // YEAR_1111_2222 (from year 1111 to 2222)
            if (isset($yearAry[1]) && isset($yearAry[2])) {
                if ($yearAry[1] < $yearAry[2]) {
                    for ($i = $yearAry[1]; $i <= $yearAry[2]; $i++) {
                        $years[$i] = $i;
                    }
                } else {
                    for ($i = $yearAry[1]; $i >= $yearAry[2]; $i--) {
                        $years[$i] = $i;
                    }
                }
            // YEAR_1111
            // If 1111 is less than today's year, then 1111 to present year,
            // else from present year to 1111
            } else if (isset($yearAry[1])) {
                $year = date('Y');
                if ($year < $yearAry[1]) {
                    for ($i = $year; $i <= $yearAry[1]; $i++) {
                        $years[$i] = $i;
                    }
                } else {
                    for ($i = $year; $i >= $yearAry[1]; $i--) {
                        $years[$i] = $i;
                    }
                }
            // YEAR, from present year to 10+ years
            } else {
                $year = date('Y');
                for ($i = $year; $i <= ($year + 10); $i++) {
                    $years[$i] = $i;
                }
            }
            $val = $years;
        // Else, if the value flag is one of the pre-defined array values, set the value of the select drop-down menu to it.
        } else {
            switch ($value) {
                // Months, numeric short values.
                case Select::MONTHS_SHORT:
                    $val = [
                        '--' => '--', '01' => '01', '02' => '02', '03' => '03', '04' => '04', '05' => '05', '06' => '06',
                        '07' => '07', '08' => '08', '09' => '09', '10' => '10', '11' => '11', '12' => '12'
                    ];
                    break;
                // Months, long name values.
                case Select::MONTHS_LONG:
                    $val = [
                        '--' => '------', '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
                        '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September',
                        '10' => 'October', '11' => 'November', '12' => 'December'
                    ];
                    break;
                // Days of Month, numeric short values.
                case Select::DAYS_OF_MONTH:
                    $val = [
                        '--' => '--', '01' => '01', '02' => '02', '03' => '03', '04' => '04', '05' => '05',
                        '06' => '06', '07' => '07', '08' => '08', '09' => '09', '10' => '10', '11' => '11',
                        '12' => '12', '13' => '13', '14' => '14', '15' => '15', '16' => '16', '17' => '17',
                        '18' => '18', '19' => '19', '20' => '20', '21' => '21', '22' => '22', '23' => '23',
                        '24' => '24', '25' => '25', '26' => '26', '27' => '27', '28' => '28', '29' => '29',
                        '30' => '30', '31' => '31'
                    ];
                    break;
                // Days of Week, long name values.
                case Select::DAYS_OF_WEEK:
                    $sun = 'Sunday';
                    $mon = 'Monday';
                    $tue = 'Tuesday';
                    $wed = 'Wednesday';
                    $thu = 'Thursday';
                    $fri = 'Friday';
                    $sat = 'Saturday';
                    $val = [
                        '--' => '------', $sun => $sun, $mon => $mon, $tue => $tue,
                        $wed => $wed, $thu => $thu, $fri => $fri, $sat => $sat
                    ];
                    break;
                // Hours, 12-hour values.
                case Select::HOURS_12:
                    $val = [
                        '--' => '--', '01' => '01', '02' => '02', '03' => '03', '04' => '04', '05' => '05', '06' => '06',
                        '07' => '07', '08' => '08', '09' => '09', '10' => '10', '11' => '11', '12' => '12'
                    ];
                    break;
                // Military Hours, 24-hour values.
                case Select::HOURS_24:
                    $val = [
                        '--' => '--', '00' => '00', '01' => '01', '02' => '02', '03' => '03', '04' => '04', '05' => '05',
                        '06' => '06', '07' => '07', '08' => '08', '09' => '09', '10' => '10', '11' => '11', '12' => '12',
                        '13' => '13', '14' => '14', '15' => '15', '16' => '16', '17' => '17', '18' => '18', '19' => '19',
                        '20' => '20', '21' => '21', '22' => '22', '23' => '23'
                    ];
                    break;
                // Minutes, incremental by 1 minute.
                case Select::MINUTES:
                    $val = [
                        '--' => '--', '00' => '00', '01' => '01', '02' => '02', '03' => '03', '04' => '04', '05' => '05',
                        '06' => '06', '07' => '07', '08' => '08', '09' => '09', '10' => '10', '11' => '11', '12' => '12',
                        '13' => '13', '14' => '14', '15' => '15', '16' => '16', '17' => '17', '18' => '18', '19' => '19',
                        '20' => '20', '21' => '21', '22' => '22', '23' => '23', '24' => '24', '25' => '25', '26' => '26',
                        '27' => '27', '28' => '28', '29' => '29', '30' => '30', '31' => '31', '32' => '32', '33' => '33',
                        '34' => '34', '35' => '35', '36' => '36', '37' => '37', '38' => '38', '39' => '39', '40' => '40',
                        '41' => '41', '42' => '42', '43' => '43', '44' => '44', '45' => '45', '46' => '46', '47' => '47',
                        '48' => '48', '49' => '49', '50' => '50', '51' => '51', '52' => '52', '53' => '53', '54' => '54',
                        '55' => '55', '56' => '56', '57' => '57', '58' => '58', '59' => '59'
                    ];
                    break;
                // Minutes, incremental by 5 minutes.
                case Select::MINUTES_5:
                    $val = [
                        '--' => '--', '00' => '00', '05' => '05', '10' => '10', '15' => '15', '20' => '20', '25' => '25',
                        '30' => '30', '35' => '35', '40' => '40', '45' => '45', '50' => '50', '55' => '55'
                    ];
                    break;
                // Minutes, incremental by 10 minutes.
                case Select::MINUTES_10:
                    $val = [
                        '--' => '--', '00' => '00', '10' => '10', '20' => '20', '30' => '30', '40' => '40', '50' => '50'
                    ];
                    break;
                // Minutes, incremental by 15 minutes.
                case Select::MINUTES_15:
                    $val = ['--' => '--', '00' => '00', '15' => '15', '30' => '30', '45' => '45'];
                    break;
                // US States, short name values.
                case Select::US_STATES_SHORT:
                    $val = [
                        '--' => '--', 'AK' => 'AK', 'AL' => 'AL', 'AR' => 'AR', 'AZ' => 'AZ', 'CA' => 'CA', 'CO' => 'CO',
                        'CT' => 'CT', 'DC' => 'DC', 'DE' => 'DE', 'FL' => 'FL', 'GA' => 'GA', 'HI' => 'HI', 'IA' => 'IA',
                        'ID' => 'ID', 'IL' => 'IL', 'IN' => 'IN', 'KS' => 'KS', 'KY' => 'KY', 'LA' => 'LA', 'MA' => 'MA',
                        'MD' => 'MD', 'ME' => 'ME', 'MI' => 'MI', 'MN' => 'MN', 'MO' => 'MO', 'MS' => 'MS', 'MT' => 'MT',
                        'NC' => 'NC', 'ND' => 'ND', 'NE' => 'NE', 'NH' => 'NH', 'NJ' => 'NJ', 'NM' => 'NM', 'NV' => 'NV',
                        'NY' => 'NY', 'OH' => 'OH', 'OK' => 'OK', 'OR' => 'OR', 'PA' => 'PA', 'RI' => 'RI', 'SC' => 'SC',
                        'SD' => 'SD', 'TN' => 'TN', 'TX' => 'TX', 'UT' => 'UT', 'VA' => 'VA', 'VT' => 'VT', 'WA' => 'WA',
                        'WI' => 'WI', 'WV' => 'WV', 'WY' => 'WY'
                    ];
                    break;
                // US States, long name values.
                case Select::US_STATES_LONG:
                    $val = [
                        '--' => '------', 'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
                        'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DC' => 'District of Columbia',
                        'DE' => 'Delaware', 'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
                        'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas', 'KY' => 'Kentucky',
                        'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland', 'MA' => 'Massachusetts', 'MI' => 'Michigan',
                        'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska',
                        'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico',
                        'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio',
                        'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island',
                        'SC' => 'South Carolina', 'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas',
                        'UT' => 'Utah', 'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
                        'WI' => 'Wisconsin', 'WY' => 'Wyoming'
                    ];
                    break;
                // Else, set the custom array of values passed.
                default:
                    // If it's an array, just set it.
                    if (is_array($value)) {
                        $val = $value;
                    // Else, check for the values in the XML options file.
                    } else {
                        $xmlFile = (file_exists($data)) ? $data :
                            __DIR__ . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'options.xml';
                        $val = self::parseXml($xmlFile, $value);
                    }
            }
        }

        $this->value = $val;
        $this->setMarked($marked);

        parent::__construct('select', $name, $val, $marked, $indent);
    }

    /**
     * Static method to parse an XML file of options
     *
     * @param  string $xmlFile
     * @param  string $value
     * @return array
     */
    public static function parseXml($xmlFile, $value)
    {
        if (file_exists($xmlFile)) {
            $xml = new \SimpleXMLElement($xmlFile, null, true);
            $xmlValues = [];
            foreach ($xml->set as $node) {
                $xmlValues[(string)$node->attributes()->name] = [];
                foreach ($node->opt as $opt) {
                    $xmlValues[(string)$node->attributes()->name][(string)$opt->attributes()->value] = (string)$opt;
                }
            }
            $val = (array_key_exists($value, $xmlValues)) ? $xmlValues[$value] : [$value];
        } else {
            $val = [$value];
        }

        return $val;
    }

    /**
     * Set an attribute for the child element object.
     *
     * @param  string $a
     * @param  string $v
     * @return \Pop\Dom\Child
     */
    public function setAttribute($a, $v)
    {
        parent::setAttribute($a, $v);

        if (array_key_exists('multiple', $this->attributes)) {
            if (strpos($this->name, '[]') === false) {
                $this->name .= '[]';
            }
            if (array_key_exists('name', $this->attributes)) {
                if (strpos($this->attributes['name'], '[]') === false) {
                    $this->attributes['name'] .= '[]';
                }
            }
        }

        return $this;
    }

    /**
     * Set attributes for the child element object.
     *
     * @param  array $a
     * @return Select
     */
    public function setAttributes(array $a)
    {
        parent::setAttributes($a);

        if (array_key_exists('multiple', $this->attributes)) {
            if (strpos($this->name, '[]') === false) {
                $this->name .= '[]';
            }
            if (array_key_exists('name', $this->attributes)) {
                if (strpos($this->attributes['name'], '[]') === false) {
                    $this->attributes['name'] .= '[]';
                }
            }
        }

        return $this;
    }

}
