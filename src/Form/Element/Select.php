<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Form\Element;

use Pop\Dom\Child;

/**
 * Form select element class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */

class Select extends AbstractElement
{

    /**
     * Constant for months, short
     * @var string
     */
    const MONTHS_SHORT = 'MONTHS_SHORT';

    /**
     * Constant for days of the month
     * @var string
     */
    const DAYS_OF_MONTH = 'DAYS_OF_MONTH';

    /**
     * Constant for 12 hours
     * @var string
     */
    const HOURS_12 = 'HOURS_12';

    /**
     * Constant for 24 hours
     * @var string
     */
    const HOURS_24 = 'HOURS_24';

    /**
     * Constant for 60 minutes (0-59)
     * @var string
     */
    const MINUTES = 'MINUTES';

    /**
     * Constant for minutes in increments of 5
     * @var string
     */
    const MINUTES_5 = 'MINUTES_5';

    /**
     * Constant for minutes in increments of 10
     * @var string
     */
    const MINUTES_10 = 'MINUTES_10';

    /**
     * Constant for minutes in increments of 15
     * @var string
     */
    const MINUTES_15 = 'MINUTES_15';

    /**
     * Element type
     * @var string
     */
    protected $type = 'select';

    /**
     * Constructor
     *
     * Instantiate the select form element object
     *
     * @param  string       $name
     * @param  string|array $values
     * @param  string       $indent
     * @param  array        $config
     * @return Select
     */
    public function __construct($name, $values, $indent = null, array $config = null)
    {
        $marked   = (isset($config['marked'])   ? $config['marked']            : null);
        $multiple = (isset($config['multiple']) ? (boolean)$config['multiple'] : false);
        $data     = (isset($config['data'])     ? $config['data']              : null);

        parent::__construct($this->type, null, null, false, $indent);
        $this->setAttributes(['name' => $name, 'id' => $name]);
        $this->setAsMultiple($multiple);
        $this->setMarked($marked);

        $values = $this->parseValues($values, $data);

        // Create the child option elements.
        foreach ($values as $k => $v) {
            if (is_array($v)) {
                $opt = new Child('optgroup', null, null, false, $indent);
                $opt->setAttribute('label', $k);
                foreach ($v as $ky => $vl) {
                    $o = new Child('option', null, null, false, $indent);
                    $o->setAttribute('value', $ky);
                    // Determine if the current option element is selected.
                    if (is_array($this->marked)) {
                        if (in_array($ky, $this->marked)) {
                            $o->setAttribute('selected', 'selected');
                        }
                    } else {
                        if ($ky == $this->marked) {
                            $o->setAttribute('selected', 'selected');
                        }
                    }
                    $o->setNodeValue($vl);
                    $opt->addChild($o);
                }
            } else {
                $opt = new Child('option', null, null, false, $indent);
                $opt->setAttribute('value', $k);
                // Determine if the current option element is selected.
                if (is_array($this->marked)) {
                    if (in_array($k, $this->marked, true)) {
                        $opt->setAttribute('selected', 'selected');
                    }
                } else {
                    if ($k == $this->marked) {
                        $opt->setAttribute('selected', 'selected');
                    }
                }
                $opt->setNodeValue($v);
            }
            $this->addChild($opt);
        }

        $this->setValue($values);
        $this->setName($name);
    }

    /**
     * Set whether the form element is required
     *
     * @param  boolean $required
     * @return Select
     */
    public function setRequired($required)
    {
        $this->setAttribute('required', 'required');
        return parent::setRequired($required);
    }

    /**
     * Set an attribute for the child element object
     *
     * @param  string $a
     * @param  string $v
     * @return Select
     */
    public function setAttribute($a, $v)
    {
        parent::setAttribute($a, $v);
        $this->isMultiple();

        return $this;
    }

    /**
     * Set attributes for the child element object
     *
     * @param  array $a
     * @return Select
     */
    public function setAttributes(array $a)
    {
        parent::setAttributes($a);
        $this->isMultiple();

        return $this;
    }

    /**
     * Check if multiple and set the name attribute accordingly
     *
     * @return boolean
     */
    public function isMultiple()
    {
        $multiple = false;
        if (array_key_exists('multiple', $this->attributes)) {
            $multiple = true;
            if (substr($this->name, -2) != '[]') {
                $this->name .= '[]';
            }
            if (array_key_exists('name', $this->attributes)) {
                if (substr($this->attributes['name'], -2) != '[]') {
                    $this->attributes['name'] .= '[]';
                }
            }
        }

        return $multiple;
    }

    /**
     * Set the select element as multiple
     *
     * @param  boolean $multiple
     * @return Select
     */
    public function setAsMultiple($multiple = true)
    {
        // Set multiple attribute
        if ($multiple) {
            $this->setAttribute('multiple', 'multiple');
        // Else, make the select element not multiple
        } else {
            if (array_key_exists('multiple', $this->attributes)) {
                unset($this->attributes['multiple']);
            }
            if (substr($this->name, -2) == '[]') {
                $this->name = substr($this->name, 0, -2);
            }
            if (substr($this->attributes['name'], -2) == '[]') {
                $this->attributes['name'] = substr($this->attributes['name'], 0, -2);
            }
        }

        return $this;
    }

    /**
     * Set the select element as multiple
     *
     * @param  string|array $values
     * @param  string       $data
     * @return array
     */
    public function parseValues($values, $data = null)
    {
        $parsedValues = null;

        // If the value flag is YEAR-based, calculate the year range for the select drop-down menu.
        if (is_string($values) && (strpos($values, 'YEAR') !== false)) {
            $years = ['----' => '----'];
            $yearAry = explode('_', $values);
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
            $parsedValues = $years;
        // Else, if the value flag is one of the pre-defined , set the value of the select drop-down menu to it.
        } else {
            switch ($values) {
                // Hours, 12-hour values.
                // Months, numeric short values.
                case Select::HOURS_12:
                case Select::MONTHS_SHORT:
                    $parsedValues = [
                        '--' => '--', '01' => '01', '02' => '02', '03' => '03', '04' => '04', '05' => '05', '06' => '06',
                        '07' => '07', '08' => '08', '09' => '09', '10' => '10', '11' => '11', '12' => '12'
                    ];
                    break;
                // Days of Month, numeric short values.
                case Select::DAYS_OF_MONTH:
                    $parsedValues = [
                        '--' => '--', '01' => '01', '02' => '02', '03' => '03', '04' => '04', '05' => '05',
                        '06' => '06', '07' => '07', '08' => '08', '09' => '09', '10' => '10', '11' => '11',
                        '12' => '12', '13' => '13', '14' => '14', '15' => '15', '16' => '16', '17' => '17',
                        '18' => '18', '19' => '19', '20' => '20', '21' => '21', '22' => '22', '23' => '23',
                        '24' => '24', '25' => '25', '26' => '26', '27' => '27', '28' => '28', '29' => '29',
                        '30' => '30', '31' => '31'
                    ];
                    break;
                // Military hours, 24-hour values.
                case Select::HOURS_24:
                    $parsedValues = [
                        '--' => '--', '00' => '00', '01' => '01', '02' => '02', '03' => '03', '04' => '04', '05' => '05',
                        '06' => '06', '07' => '07', '08' => '08', '09' => '09', '10' => '10', '11' => '11', '12' => '12',
                        '13' => '13', '14' => '14', '15' => '15', '16' => '16', '17' => '17', '18' => '18', '19' => '19',
                        '20' => '20', '21' => '21', '22' => '22', '23' => '23'
                    ];
                    break;
                // Minutes, incremental by 1 minute.
                case Select::MINUTES:
                    $parsedValues = [
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
                    $parsedValues = [
                        '--' => '--', '00' => '00', '05' => '05', '10' => '10', '15' => '15', '20' => '20', '25' => '25',
                        '30' => '30', '35' => '35', '40' => '40', '45' => '45', '50' => '50', '55' => '55'
                    ];
                    break;
                // Minutes, incremental by 10 minutes.
                case Select::MINUTES_10:
                    $parsedValues = [
                        '--' => '--', '00' => '00', '10' => '10', '20' => '20', '30' => '30', '40' => '40', '50' => '50'
                    ];
                    break;
                // Minutes, incremental by 15 minutes.
                case Select::MINUTES_15:
                    $parsedValues = ['--' => '--', '00' => '00', '15' => '15', '30' => '30', '45' => '45'];
                    break;
                // Else, set the custom array of values passed.
                default:
                    // If it's an array, just set it.
                    if (is_array($values)) {
                        $parsedValues = $values;
                    // Else, check for the values in the XML options file.
                    } else {
                        $xmlFile = ((null !== $data) && file_exists($data)) ? $data :
                            __DIR__ . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'options.xml';
                        $parsedValues = self::parseXml($xmlFile, $values);
                    }
            }
        }

        return $parsedValues;
    }

    /**
     * Static method to parse an XML file of options
     *
     * @param  string $xmlFile
     * @param  string $value
     * @return array
     */
    protected static function parseXml($xmlFile, $value)
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

}
