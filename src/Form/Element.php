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
namespace Pop\Form;

use Pop\Dom\Child;
use Pop\Validator;

/**
 * Form element class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Element extends Child
{

    /**
     * Element name
     * @var string
     */
    protected $name = null;

    /**
     * Element type
     * @var string
     */
    protected $type = null;

    /**
     * Form element value(s)
     * @var string|array
     */
    protected $value = null;

    /**
     * Form element marked value(s)
     * @var string|array
     */
    protected $marked = null;

    /**
     * Form element label
     * @var string
     */
    protected $label = null;

    /**
     * Form element label attributes
     * @var array
     */
    protected $labelAttributes = null;

    /**
     * Form element required property
     * @var boolean
     */
    protected $required = false;

    /**
     * Form element validators
     * @var array
     */
    protected $validators = [];

    /**
     * Form element error display format
     * @var array
     */
    protected $errorDisplay = [
        'container'  => 'div',
        'attributes' => [
            'class' => 'error'
        ],
        'pre' => false
    ];

    /**
     * Form element errors
     * @var array
     */
    protected $errors = [];

    /**
     * Form element allowed types
     * @var array
     */
    protected $allowedTypes = [
        'button',
        'checkbox',
        'file',
        'hidden',
        'image',
        'password',
        'radio',
        'reset',
        'select',
        'submit',
        'text',
        'textarea'
    ];

    /**
     * Constructor
     *
     * Instantiate the form element object
     *
     * @param  string $type
     * @param  string $name
     * @param  string $value
     * @param  string|array $marked
     * @param  string $indent
     * @throws Exception
     * @return \Pop\Form\Element
     */
    public function __construct($type, $name, $value = null, $marked = null, $indent = null)
    {
        $this->name = $name;
        $this->type = $type;

        // Check the element type, else set the properties.
        if (!in_array($type, $this->allowedTypes)) {
            throw new Exception('Error: That input type is not allowed.');
        }

        // Create the element based on the type passed.
        switch ($type) {
            // Textarea element
            case 'textarea':
                parent::__construct('textarea', null, null, false, $indent);
                $this->setAttributes(['name' => $name, 'id' => $name]);
                $this->nodeValue = $value;
                $this->value = $value;
                break;

            // Select element
            case 'select':
                parent::__construct('select', null, null, false, $indent);
                $this->setAttributes(['name' => $name, 'id' => $name]);

                // Create the child option elements.
                foreach ($value as $k => $v) {
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
                            if (in_array($k, $this->marked)) {
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

                $this->value = $value;
                break;

            // Radio element(s)
            case 'radio':
                parent::__construct('fieldset', null, null, false, $indent);
                $this->setAttribute('class', 'radio-btn-fieldset');

                // Create the radio elements and related span elements.
                $i = null;
                foreach ($value as $k => $v) {
                    $rad = new Child('input', null, null, false, $indent);
                    $rad->setAttributes([
                        'type'  => $type,
                        'class' => 'radio-btn',
                        'name'  => $name,
                        'id'    => ($name . $i),
                        'value' => $k
                    ]);

                    // Determine if the current radio element is checked.
                    if ($k == $this->marked) {
                        $rad->setAttribute('checked', 'checked');
                    }

                    $span = new Child('span', null, null, false, $indent);
                    $span->setAttribute('class', 'radio-span');
                    $span->setNodeValue($v);
                    $this->addChildren([$rad, $span]);
                    $i++;
                }

                $this->value = $value;
                break;

            // Checkbox element(s)
            case 'checkbox':
                parent::__construct('fieldset', null, null, false, $indent);
                $this->setAttribute('class', 'check-box-fieldset');

                // Create the checkbox elements and related span elements.
                $i = null;
                foreach ($value as $k => $v) {
                    $chk = new Child('input', null, null, false, $indent);
                    $chk->setAttributes([
                        'type'  => $type,
                        'class' => 'check-box',
                        'name'  => ($name . '[]'),
                        'id'    => ($name . $i),
                        'value' => $k
                    ]);

                    // Determine if the current radio element is checked.
                    if (in_array($k, $this->marked)) {
                        $chk->setAttribute('checked', 'checked');
                    }

                    $span = new Child('span', null, null, false, $indent);
                    $span->setAttribute('class', 'check-span');
                    $span->setNodeValue($v);
                    $this->addChildren([$chk, $span]);
                    $i++;
                }

                $this->value = $value;
                break;

            // Input element
            default:
                if ($type == 'button') {
                    $nodeType = 'button';
                    $type = 'submit';
                } else {
                    $nodeType = 'input';
                }
                parent::__construct($nodeType, null, null, false, $indent);
                $this->setAttributes(['type' => $type, 'name' => $name, 'id' => $name]);
                if (!is_array($value)) {
                    if ($nodeType == 'button') {
                        $this->nodeValue = $value;
                    }
                    $this->setAttribute('value', $value);
                }
                $this->value = $value;
        }

        // If a certain value is marked (selected or checked), set the property here.
        if (null !== $marked) {
            $this->marked = $marked;
        }
    }

    /**
     * Set the name of the form element object.
     *
     * @param  string $name
     * @return \Pop\Form\Element
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set the value of the form element object.
     *
     * @param  mixed $value
     * @return \Pop\Form\Element
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Set the marked value of the form element object.
     *
     * @param  mixed $marked
     * @return \Pop\Form\Element
     */
    public function setMarked($marked)
    {
        $this->marked = ($this->isMultiple()) ? [] : null;

        if (is_array($marked)) {
            foreach ($marked as $v) {
                if (is_array($this->value)) {
                    $inArray = false;
                    foreach ($this->value as $key => $val) {
                        if ($key == $v) {
                            $inArray = true;
                        } else if (is_array($val)) {
                            foreach ($val as $ky => $va) {
                                if ($ky == $v) {
                                    $inArray = true;
                                }
                            }
                        }
                    }
                    if ($inArray) {
                        $val = null;
                        if (!isset($this->value[$v])) {
                            foreach ($this->value as $vl) {
                                if (is_array($vl) && isset($vl[$v])) {
                                    $val = $v;
                                }
                            }
                        } else {
                            $val = $v;
                        }
                        if (is_array($this->marked)) {
                            $this->marked[] = $val;
                        } else {
                            $this->marked = $val;
                        }
                    }
                }
            }
        } else {
            if (is_array($this->value)) {
                if (array_key_exists($marked, $this->value) !==  false) {
                    if (is_array($this->marked)) {
                        $this->marked[] = $marked;
                    } else {
                        $this->marked = $marked;
                    }
                }
            }
        }

        //print_r($this->marked);

        return $this;
    }

    /**
     * Set the label of the form element object.
     *
     * @param  mixed $label
     * @return \Pop\Form\Element
     */
    public function setLabel($label)
    {
        if (is_array($label)) {
            foreach ($label as $l => $a) {
                $this->label = $l;
                $this->labelAttributes = $a;
            }
        } else {
            $this->label = $label;
        }

        return $this;
    }

    /**
     * Set the attributes of the label of the form element object.
     *
     * @param  array $attribs
     * @return \Pop\Form\Element
     */
    public function setLabelAttributes(array $attribs)
    {
        foreach ($attribs as $a => $v) {
            $this->labelAttributes[$a] = $v;
        }
        return $this;
    }

    /**
     * Set whether the form element object is required.
     *
     * @param  boolean $required
     * @return \Pop\Form\Element
     */
    public function setRequired($required)
    {
        $this->required = (boolean)$required;
        return $this;
    }

    /**
     * Set error pre-display
     *
     * @param  boolean $pre
     * @return \Pop\Form\Element
     */
    public function setErrorPre($pre = true)
    {
        $this->errorDisplay['pre'] = (boolean)$pre;
        return $this;
    }

    /**
     * Set error post-display
     *
     * @param  boolean $post
     * @return \Pop\Form\Element
     */
    public function setErrorPost($post = true)
    {
        $this->errorDisplay['pre'] = !((boolean)$post);
        return $this;
    }

    /**
     * Set error display values
     *
     * @param  string  $container
     * @param  array   $attribs
     * @param  boolean $pre
     * @return \Pop\Form\Element
     */
    public function setErrorDisplay($container, array $attribs, $pre = false)
    {
        $this->errorDisplay['container'] = $container;
        foreach ($attribs as $a => $v) {
            $this->errorDisplay['attributes'][$a] = $v;
        }
        $this->errorDisplay['pre'] = (boolean)$pre;
        return $this;
    }

    /**
     * Set validators
     *
     * @param  array $validators
     * @return \Pop\Form\Element
     */
    public function setValidators(array $validators = [])
    {
        $this->validators = $validators;
        return $this;
    }

    /**
     * Clear errors.
     *
     * @return \Pop\Form\Element
     */
    public function clearErrors()
    {
        $this->errors = [];
        return $this;
    }

    /**
     * Get form element object name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get form element object type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get form element object value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get form element object marked values.
     *
     * @return mixed
     */
    public function getMarked()
    {
        return $this->marked;
    }

    /**
     * Get form element object label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get the attributes of the form element object label.
     *
     * @return array
     */
    public function getLabelAttributes()
    {
        return $this->labelAttributes;
    }

    /**
     * Get validators
     *
     * @return array
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * Get whether the form element object is required.
     *
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Get form element object errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get if form element object has errors.
     *
     * @return array
     */
    public function hasErrors()
    {
        return (count($this->errors) > 0);
    }

    /**
     * Get whether the form element object is a captcha element.
     *
     * @return boolean
     */
    public function isCaptcha()
    {
        return (get_class($this) == 'Pop\Form\Element\Captcha');
    }

    /**
     * Get whether the form element object is a checkbox element.
     *
     * @return boolean
     */
    public function isCheckbox()
    {
        return (get_class($this) == 'Pop\Form\Element\Checkbox');
    }

    /**
     * Get whether the form element object is a csrf element.
     *
     * @return boolean
     */
    public function isCsrf()
    {
        return (get_class($this) == 'Pop\Form\Element\Csrf');
    }

    /**
     * Get whether the form element object is a radio element.
     *
     * @return boolean
     */
    public function isRadio()
    {
        return (get_class($this) == 'Pop\Form\Element\Radio');
    }

    /**
     * Get whether the form element object is a select element.
     *
     * @return boolean
     */
    public function isSelect()
    {
        return (get_class($this) == 'Pop\Form\Element\Select');
    }

    /**
     * Get whether the form element object is a textarea element.
     *
     * @return boolean
     */
    public function isTextarea()
    {
        return (get_class($this) == 'Pop\Form\Element\Textarea');
    }

    /**
     * Get whether the form element object can have multiple input values.
     *
     * @return boolean
     */
    public function isMultiple()
    {
        return (substr($this->getAttribute('name'), -2) == '[]');
    }

    /**
     * Add a validator the form element object.
     *
     * @param  mixed $validator
     * @return \Pop\Form\Element
     */
    public function addValidator($validator)
    {
        $this->validators[] = $validator;
        return $this;
    }

    /**
     * Validate the form element object.
     *
     * @throws Exception
     * @return boolean
     */
    public function validate()
    {
        $this->errors = [];

        // Check if the element is required.
        if ($this->required == true) {
            if (is_array($this->value)) {
                $curElemValue = $this->marked;
            } else if (($_FILES) && (isset($_FILES[$this->name]['name']))) {
                $curElemValue = $_FILES[$this->name]['name'];
            } else {
                $curElemValue = $this->value;
            }

            if (empty($curElemValue) && ($curElemValue != '0')) {
                $this->errors[] = 'This field is required.';
            }
        }

        // Check the element's validators.
        if (isset($this->validators[0])) {
            foreach ($this->validators as $validator) {
                $curElemSize = null;
                if (is_array($this->value)) {
                    $curElemValue = $this->marked;
                } else if (($_FILES) && (isset($_FILES[$this->name]['name']))) {
                    $curElemValue = $_FILES[$this->name]['name'];
                    $curElemSize  = $_FILES[$this->name]['size'];
                } else {
                    $curElemValue = $this->value;
                }

                // If Pop\Validator\*
                if ($validator instanceof \Pop\Validator\ValidatorInterface) {
                    if ('Pop\Validator\NotEmpty' == get_class($validator)) {
                        if (!$validator->evaluate($curElemValue)) {
                            $this->errors[] = $validator->getMessage();
                        }
                    } else if ((null !== $curElemSize) && ('Pop\Validator\LessThanEqual' == get_class($validator))) {
                        if (!$validator->evaluate($curElemSize)) {
                            $this->errors[] = $validator->getMessage();
                        }
                    } else {
                        if (!empty($curElemValue) && !$validator->evaluate($curElemValue)) {
                            $this->errors[] = $validator->getMessage();
                        }
                    }
                // Else, if callable
                } else if (is_callable($validator)) {
                    if (null !== $curElemSize) {
                        $result = call_user_func_array($validator, [$curElemSize]);
                    } else {
                        $result = call_user_func_array($validator, [$curElemValue]);
                    }
                    if (null !== $result) {
                        $this->errors[] = $result;
                    }
                } else {
                    throw new Exception('That validator is not callable.');
                }
            }
        }

        // If errors are found on any of the form elements, return false.
        return (count($this->errors) > 0) ? false : true;
    }


    /**
     * Method to render the child and its child nodes.
     *
     * @param  boolean $ret
     * @param  int     $depth
     * @param  string  $indent
     * @param  string  $errorIndent
     * @return string
     */
    public function render($ret = false, $depth = 0, $indent = null, $errorIndent = null)
    {
        $output    = parent::render(true, $depth, $indent);
        $errors    = null;
        $container = $this->errorDisplay['container'];
        $attribs   = null;
        foreach ($this->errorDisplay['attributes'] as $a => $v) {
            $attribs .= ' ' . $a . '="' . $v . '"';
        }

        // Add error messages if there are any.
        if (count($this->errors) > 0) {
            foreach ($this->errors as $msg) {
                if ($this->errorDisplay['pre']) {
                    $errors .= "{$indent}{$this->indent}<" . $container . $attribs . ">{$msg}</" . $container . ">\n{$errorIndent}";
                } else {
                    $errors .= "{$errorIndent}{$indent}{$this->indent}<" . $container . $attribs . ">{$msg}</" . $container . ">\n";
                }
            }
        }

        $output = ($this->errorDisplay['pre']) ? $errors . $output : $output . $errors;
        return $output;
    }


    /**
     * Method to render the child and its child nodes.
     *
     * @return string
     */
    public function output()
    {
        echo $this->render(true);
    }

}
