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
namespace Pop\Form;

/**
 * Form fields class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <dev@nolainteractive.com>
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
    protected $fields = [];

    /**
     * Constructor
     *
     * Instantiate the fields object
     *
     * @param  mixed $fields
     * @param  array $attribs
     * @param  array $values
     * @param  mixed $omit
     * @return Fields
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
     * Static factory method to create a field element object from a field config array
     *
     * @param  string $name
     * @param  array  $field
     * @param  array  $values
     * @throws Exception
     * @return Element\AbstractElement
     */
    public static function factory($name, array $field, array $values = null)
    {
        if (!isset($field['type'])) {
            throw new Exception('Error: The field type was not set.');
        }

        $type       = $field['type'];
        $label      = (isset($field['label']))      ? $field['label']      : null;
        $required   = (isset($field['required']))   ? $field['required']   : null;
        $attributes = (isset($field['attributes'])) ? $field['attributes'] : null;
        $validators = (isset($field['validators'])) ? $field['validators'] : null;
        $expire     = (isset($field['expire']))     ? $field['expire']     : 300;
        $captcha    = (isset($field['captcha']))    ? $field['captcha']    : null;
        $data       = (isset($field['data']))       ? $field['data']       : null;
        $multiple   = (isset($field['multiple']))   ? $field['multiple']   : false;

        if (isset($field['error'])) {
            $error = [
                'container'  => 'div',
                'attributes' => ['class' => 'error'],
                'pre'        => false
            ];
            foreach ($field['error'] as $key => $value) {
                if ($key != 'pre') {
                    $error['container']  = $key;
                    $error['attributes'] = $value;
                } else if ($key == 'pre') {
                    $error['pre'] = $value;
                }
            }
        } else {
            $error = null;
        }

        if ((null !== $values) && array_key_exists($name, $values)) {
            if (($type == 'checkbox') || ($type == 'radio') || ($type == 'select')) {
                $value  = (isset($field['value'])) ? $field['value'] : null;
                $marked = $values[$name];
            } else {
                $value  = $values[$name];
                $marked = (isset($field['marked'])) ? $field['marked'] : null;
            }
        } else {
            $value  = (isset($field['value']))  ? $field['value'] : null;
            $marked = (isset($field['marked'])) ? $field['marked'] : null;
        }

        // Initialize the form element.
        switch (strtolower($type)) {
            case 'button':
                $elem = new Element\Button($name, $value);
                break;
            case 'select':
                $config = [
                    'marked'   => $marked,
                    'multiple' => $multiple,
                    'data'     => $data
                ];
                $elem = new Element\Select($name, $value, null, $config);
                break;
            case 'textarea':
                $elem = new Element\Textarea($name, $value);
                break;
            case 'checkbox':
                $elem = new Element\Input\Checkbox($name, $value, null, $marked);
                break;
            case 'radio':
                $elem = new Element\Input\Radio($name, $value, null, $marked);
                break;
            case 'csrf':
                $elem = new Element\Input\Csrf($name, $value, null, $expire);
                break;
            case 'captcha':
                $elem = new Element\Input\Captcha($name, $value, null, $expire, $captcha);
                break;
            case 'input-button':
                $elem = new Element\Input\Button($name, $value);
                break;
            default:
                $class = 'Pop\\Form\\Element\\Input\\' . ucfirst(strtolower($type));
                if (!class_exists($class)) {
                    throw new Exception('Error: That class for that form element does not exist.');
                }
                $elem = new $class($name, $value);
        }

        // Set the label.
        if (null !== $label) {
            $elem->setLabel($label);
        }
        // Set if required.
        if (null !== $required) {
            $elem->setRequired($required);
        }
        // Set if error display.
        if (null !== $error) {
            $elem->setErrorDisplay($error['container'], $error['attributes'], $error['pre']);
        }
        // Set any attributes.
        if (null !== $attributes) {
            $elem->setAttributes($attributes);
        }
        // Set any validators.
        if (null !== $validators) {
            if (is_array($validators)) {
                $elem->addValidators($validators);
            } else {
                $elem->addValidator($validators);
            }
        }

        return $elem;
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
     * @return Fields
     */
    public function addFieldsFromTable(array $tableInfo, array $attribs = null, array $values = null, $omit = null)
    {
        if (!isset($tableInfo['tableName']) || !isset($tableInfo['primaryId']) || !isset($tableInfo['columns'])) {
            throw new Exception('Error: The table info parameter is not in the correct format. It should be a returned array value from the getTableInfo() method of the Db\\Record component.');
        }

        if (null !== $omit) {
            if (!is_array($omit)) {
                $omit = [$omit];
            }
        } else {
            $omit = [];
        }

        foreach ($tableInfo['columns'] as $key => $value) {
            if (!in_array($key, $omit)) {
                $fieldName  = $key;
                $fieldValue = null;
                $fieldLabel = null;
                $required   = ($value['null']) ? false : true;
                $attributes = null;
                $marked     = null;
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
                    $fieldType  = 'email';
                    $validators = new \Pop\Validator\Email();
                }

                $this->fields[$fieldName] = [
                    'type'       => $fieldType,
                    'label'      => $fieldLabel,
                    'value'      => $fieldValue,
                    'required'   => $required,
                    'attributes' => $attributes,
                    'marked'     => $marked,
                    'validators' => $validators
                ];
            }
        }

        return $this;
    }

    /**
     * Add form fields
     *
     * @param  array $fields
     * @return Fields
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
     * @return Fields
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
     * Set method to set the property to the value of fields[$name]
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
     * Get method to return the value of fields[$name]
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
     * Return the isset value of fields[$name]
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
