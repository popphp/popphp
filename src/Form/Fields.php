<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Form;

/**
 * Form fields class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Fields
{

    /**
     * Fields array
     * @var array
     */
    protected $fields = array();

    /**
     * Constructor
     *
     * Instantiate the fields object
     *
     * @param  mixed $fields
     * @param  array $attribs
     * @param  array $values
     * @param  mixed $omit
     * @return \Pop\Form\Fields
     */
    public function __construct($fields = null, array $attribs = null, array $values = null, $omit = null)
    {
        if (null !== $fields) {
            if (is_array($fields) && isset($fields['tableName'])) {
                $this->addFieldsFromTable($fields, $attribs, $values, $omit);
            } else {
                $this->addFields($fields);
            }
        }
    }

    /**
     * Static method to instantiate the fields object and return itself
     * to facilitate chaining methods together.
     *
     * @param  mixed $fields
     * @param  array $attribs
     * @param  array $values
     * @param  mixed $omit
     * @return \Pop\Form\Fields
     */
    public static function factory($fields = null, array $attribs = null, array $values = null, $omit = null)
    {
        return new self($fields, $attribs, $values, $omit);
    }

    /**
     * Add form fields from a related database table. The $tableInfo
     * parameter should be the returned array result from calling the
     * static Pop\Db\Record method, Record::getTableInfo();
     *
     * @param  array $tableInfo
     * @param  array $attribs
     * @param  array $values
     * @param  mixed $omit
     * @throws Exception
     * @return \Pop\Form\Fields
     */
    public function addFieldsFromTable(array $tableInfo, array $attribs = null, array $values = null, $omit = null)
    {
        if (!isset($tableInfo['tableName']) || !isset($tableInfo['primaryId']) || !isset($tableInfo['columns'])) {
            throw new Exception('Error: The table info parameter is not in the correct format. It should be a returned array value from the getTableInfo() method of the Record component.');
        }

        if (null !== $omit) {
            if (!is_array($omit)) {
                $omit = array($omit);
            }
        } else {
            $omit = array();
        }

        foreach ($tableInfo['columns'] as $key => $value) {
            if (!in_array($key, $omit)) {
                $fieldName = $key;
                $fieldValue = null;
                $fieldLabel = null;
                $required = ($value['null']) ? false : true;
                $attributes = null;
                $marked = null;
                $validators = (isset($values[$key]['validators'])) ? $values[$key]['validators'] : null;

                $fieldType = (stripos($key, 'password') !== false) ?
                    'password' :
                    ((stripos($value['type'], 'text') !== false) ? 'textarea' : 'text');

                if ((null !== $values) && isset($values[$key])) {
                    if (isset($values[$key]['type'])) {
                        $fieldType = $values[$key]['type'];
                    }
                    $fieldValue = (isset($values[$key]['value'])) ? $values[$key]['value'] : null;
                    if ((!$_POST) && !isset($_GET[$key])) {
                        $marked = (isset($values[$key]['marked'])) ? $values[$key]['marked'] : null;
                    }
                }

                if ($fieldType != 'hidden') {
                    $fieldLabel = ucwords(str_replace('_', ' ', $key)) . ':';
                } else {
                    if ((null === $fieldValue) && ($required)) {
                        $fieldValue = '0';
                    }
                }

                if (null !== $attribs) {
                    if (isset($attribs[$fieldType])) {
                        $attributes =  $attribs[$fieldType];
                    }
                }

                if ((stripos($key, 'email') !== false) || (stripos($key, 'e-mail') !== false) || (stripos($key, 'e_mail') !== false)) {
                    $validators = new \Pop\Validator\Email();
                }

                $this->fields[$fieldName] = array(
                    'type'       => $fieldType,
                    'label'      => $fieldLabel,
                    'value'      => $fieldValue,
                    'required'   => $required,
                    'attributes' => $attributes,
                    'marked'     => $marked,
                    'validators' => $validators
                );
            }
        }

        return $this;
    }

    /**
     * Add form fields
     *
     * @param  array $fields
     * @return \Pop\Form\Fields
     */
    public function addFields(array $fields)
    {
        $this->fields = array_merge($this->fields, $fields);
        return $this;
    }

    /**
     * Set form field
     *
     * @param  string $field
     * @param  mixed  $attrib
     * @param  mixed  $value
     * @return \Pop\Form\Fields
     */
    public function setField($field, $attrib, $value = null)
    {
        if (isset($this->fields[$field])) {
            if (is_string($attrib) && (null !== $value)) {
                $this->fields[$field][$attrib] = $value;
            } else if (is_array($attrib)) {
                foreach ($attrib as $k => $v) {
                    $this->fields[$field][$k] = $v;
                }
            }
        }

        return $this;
    }

    /**
     * Get the form fields
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Set method to set the property to the value of fields[$name].
     *
     * @param  string $name
     * @param  mixed $value
     * @throws Exception
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
        unset($this->fields[$name]);
    }

}
