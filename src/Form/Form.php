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

use Pop\Dom\Child;

/**
 * Form class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Form extends \Pop\Dom\Dom
{

    /**
     * Form element node
     * @var \Pop\Dom\Child
     */
    protected $form = null;

    /**
     * Form action
     * @var string
     */
    protected $action = null;

    /**
     * Form method
     * @var string
     */
    protected $method = null;

    /**
     * Form template for HTML formatting.
     * @var string
     */
    protected $template = null;

    /**
     * Form field values
     * @var array
     */
    protected $fields = array();

    /**
     * Form field groups
     * @var array
     */
    protected $groups = array();

    /**
     * Form init field values
     * @var array
     */
    protected $initFieldsValues = array();

    /**
     * Global Form error display format
     * @var array
     */
    protected $errorDisplay = null;

    /**
     * Has file flag
     * @var boolean
     */
    protected $hasFile = false;

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @param  array  $fields
     * @param  string $indent
     * @return \Pop\Form\Form
     */
    public function __construct($action = null, $method = 'post', array $fields = null, $indent = null)
    {
        // Set the form's action and method.
        $this->action = (null !== $action) ? $action : $_SERVER['REQUEST_URI'];
        $this->method = $method;

        // Create the parent DOM element and the form child element.
        parent::__construct(null, 'utf-8', null, $indent);
        $this->form = new Child('form', null, null, false, $indent);
        $this->form->setAttributes(array('action' => $this->action, 'method' => $this->method));
        $this->addChild($this->form);

        if (null !== $fields) {
            $this->setFields($fields);
        }
    }

    /**
     * Static method to instantiate the form object and return itself
     * to facilitate chaining methods together.
     *
     * @param  string $action
     * @param  string $method
     * @param  array  $fields
     * @param  string $indent
     * @return \Pop\Form\Form
     */
    public static function factory($action = null, $method = 'post', array $fields = null, $indent = null)
    {
        return new self($action, $method, $fields, $indent);
    }

    /**
     * Set the fields of the form object.
     *
     * @param  array $fields
     * @return \Pop\Form\Form
     */
    public function setFields(array $fields)
    {
        $keys = array_keys($fields);
        if (is_numeric($keys[0])) {
            foreach ($fields as $field) {
                foreach ($field as $name => $value) {
                    $field[$name]['name'] = $name;
                    $this->fields[$name] = (isset($value['value'])) ? $value['value'] : null;
                    if ($field[$name]['type'] == 'file') {
                        $this->hasFile = true;
                    }
                }

            }
        } else {
            foreach ($fields as $name => $value) {
                $fields[$name]['name'] = $name;
                $this->fields[$name] = (isset($value['value'])) ? $value['value'] : null;
                if ($fields[$name]['type'] == 'file') {
                    $this->hasFile = true;
                }
            }
        }

        $this->initFieldsValues = (count($this->initFieldsValues) > 0) ? array_merge($this->initFieldsValues, $fields) : $fields;

        return $this;
    }

    /**
     * Set a single field in $initFieldsValues
     *
     * @param  string $name
     * @param  array  $field
     * @return \Pop\Form\Form
     */
    public function setField($name, array $field)
    {
        $match = false;
        if (array_key_exists($name, $this->initFieldsValues)) {
            $this->initFieldsValues[$name] = $field;
            $match = true;
        } else {
            foreach ($this->initFieldsValues as $key => $value) {
                if (array_key_exists($name, $value)) {
                    $this->initFieldsValues[$key][$name] = $field;
                    $match = true;
                }
            }
        }

        if (!$match) {
            $keys = array_keys($this->initFieldsValues);
            if (is_numeric($keys[0])) {
                $last = $keys[(count($keys) - 1)];
                $this->initFieldsValues[$last][$name] = $field;
            } else {
                $this->initFieldsValues[$name] = $field;
            }
        }

        return $this;
    }

    /**
     * Alias for setFields()
     *
     * @param  array $fields
     * @return \Pop\Form\Form
     */
    public function addFields(array $fields)
    {
        $this->setFields($fields);
    }

    /**
     * Set the field values. Optionally, you can apply filters
     * to the passed values via callbacks and their parameters
     *
     * @param  array $values
     * @param  array $filters
     * @return \Pop\Form\Form
     */
    public function setFieldValues(array $values = null, $filters = null)
    {
        // Filter values if passed
        if ((null !== $values) && (null !== $filters)) {
            $values = $this->filterValues($values, $filters);
        }

        // Loop through the initial fields values and build the fields
        // based on the _initFieldsValues property.
        if (count($this->initFieldsValues) > 0) {
            // If the fields are a group of fields
            $keys = array_keys($this->initFieldsValues);
            if (is_numeric($keys[0])) {
                $fields = array();
                foreach ($this->initFieldsValues as $ary) {
                    $k = array_keys($ary);
                    if (isset($k[0])) {
                        $this->groups[] = $k[0];
                    }
                    $fields = array_merge($fields, $ary);
                }
                $this->initFieldsValues = $fields;
            }

            foreach ($this->initFieldsValues as $name => $field) {
                if (is_array($field) && isset($field['type'])) {
                    $type = $field['type'];
                    $label = (isset($field['label'])) ? $field['label'] : null;
                    $required = (isset($field['required'])) ? $field['required'] : null;
                    $attributes = (isset($field['attributes'])) ? $field['attributes'] : null;
                    $validators = (isset($field['validators'])) ? $field['validators'] : null;
                    $expire = (isset($field['expire'])) ? $field['expire'] : 300;
                    $captcha = (isset($field['captcha'])) ? $field['captcha'] : null;
                    $data = (isset($field['data'])) ? $field['data'] : null;

                    if ($type == 'file') {
                        $this->hasFile = true;
                    }

                    if (isset($field['error'])) {
                        $error = array(
                            'container'  => 'div',
                            'attributes' => array(
                                'class' => 'error'
                            ),
                            'pre' => false
                        );
                        foreach ($field['error'] as $key => $value) {
                            if ($key != 'pre') {
                                $error['container'] = $key;
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
                            $value = (isset($field['value'])) ? $field['value'] : null;
                            $marked = $values[$name];
                        } else {
                            $value = $values[$name];
                            $marked = (isset($field['marked'])) ? $field['marked'] : null;
                        }
                    } else {
                        $value = (isset($field['value'])) ? $field['value'] : null;
                        $marked = (isset($field['marked'])) ? $field['marked'] : null;
                    }
                    // Initialize the form element.
                    switch (strtolower($type)) {
                        case 'checkbox':
                            $elem = new Element\Checkbox($name, $value, $marked);
                            break;
                        case 'radio':
                            $elem = new Element\Radio($name, $value, $marked);
                            break;
                        case 'select':
                            $elem = new Element\Select($name, $value, $marked, null, $data);
                            break;
                        case 'textarea':
                            $elem = new Element\Textarea($name, $value, $marked);
                            break;
                        case 'csrf':
                            $elem = new Element\Csrf($name, $value, $expire);
                            break;
                        case 'captcha':
                            $elem = new Element\Captcha($name, $value, $expire, $captcha);
                            break;
                        default:
                            $elem = new Element($type, $name, $value, $marked);
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
                        foreach ($attributes as $a => $v) {
                            $elem->setAttributes($a, $v);
                        }
                    }

                    // Set any validators.
                    if (null !== $validators) {
                        if (is_array($validators)) {
                            foreach ($validators as $val) {
                                $elem->addValidator($val);
                            }
                        } else {
                            $elem->addValidator($validators);
                        }
                    }

                    $this->addElements($elem);
                }
            }
        // Else, set the passed values to the elements that
        // are already added to the form object
        } else {
            $fields = $this->getElements();
            if ((null !== $values) && (count($fields) > 0)) {
                foreach ($fields as $field) {
                    $fieldName = str_replace('[]', '', $field->getName());
                    if (isset($values[$fieldName])) {
                        // If a multi-value form element
                        if ($field->hasChildren()) {
                            $field->setMarked($values[$fieldName]);
                            $this->fields[$fieldName] = $values[$fieldName];
                            // Loop through the field's children
                            if ($field->hasChildren()) {
                                $children = $field->getChildren();
                                foreach ($children as $key => $child) {
                                    // If checkbox or radio
                                    if (($child->getAttribute('type') == 'checkbox') || ($child->getAttribute('type') == 'radio')) {
                                        if (is_array($field->getMarked()) && in_array($child->getAttribute('value'), $field->getMarked())) {
                                            $field->getChild($key)->setAttributes('checked', 'checked');
                                        } else if ($child->getAttribute('value') == $field->getMarked()) {
                                            $field->getChild($key)->setAttributes('checked', 'checked');
                                        }
                                    // If select option
                                    } else if ($child->getNodeName() == 'option') {
                                        if (is_array($field->getMarked()) && in_array($child->getAttribute('value'), $field->getMarked())) {
                                            $field->getChild($key)->setAttributes('selected', 'selected');
                                        } else if ($child->getAttribute('value') == $field->getMarked()) {
                                            $field->getChild($key)->setAttributes('selected', 'selected');
                                        }
                                    }
                                }
                            }
                        // Else, if a single-value form element
                        } else {
                            $field->setValue($values[$fieldName]);
                            $this->fields[$fieldName] = $values[$fieldName];
                            if ($field->getNodeName() == 'textarea') {
                                $field->setNodeValue($values[$fieldName]);
                            } else {
                                $field->setAttributes('value', $values[$fieldName]);
                            }
                        }
                    }
                }
            }
        }

        if (null !== $this->errorDisplay) {
            $this->setErrorDisplay(
                $this->errorDisplay['container'],
                $this->errorDisplay['attributes'],
                $this->errorDisplay['pre']
            );
        }

        return $this;
    }

    /**
     * Set a form template for the render method to utilize.
     *
     * @param  string $tmpl
     * @return \Pop\Form\Form
     */
    public function setTemplate($tmpl)
    {
        if (preg_match('/(.*)\.(x|ht|pht|xht)ml/i', $tmpl) ||
            (substr($tmpl, -4) == '.htm') ||
            (substr($tmpl, -4) == '.php') ||
            (substr($tmpl, -5) == '.php3') ||
            (substr($tmpl, -4) == '.txt')) {
            if (file_exists($tmpl)) {
                $this->template = ((stripos($tmpl, '.phtml') === false) && (stripos($tmpl, '.php') === false)) ?
                    file_get_contents($tmpl) :
                    $tmpl;
            } else {
                $this->template = $tmpl;
            }
        } else {
            $this->template = $tmpl;
        }
        return $this;
    }

    /**
     * Set the form action.
     *
     * @param  string $action
     * @return \Pop\Form\Form
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Set the form method.
     *
     * @param  string $method
     * @return \Pop\Form\Form
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Get the form template for the render method to utilize.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set an attribute or attributes for the form object.
     *
     * @param  array|string $a
     * @param  string $v
     * @return \Pop\Form\Form
     */
    public function setAttributes($a, $v = null)
    {
        $this->form->setAttributes($a, $v);
        return $this;
    }

    /**
     * Add a form element or elements to the form object.
     *
     * @param  mixed $e
     * @return \Pop\Form\Form
     */
    public function addElements($e)
    {
        if (is_array($e)) {
            $this->form->addChildren($e);
        } else {
            $this->form->addChild($e);
        }

        $children = $this->form->getChildren();

        foreach ($children as $child) {
            $attribs = $child->getAttributes();
            if ($child instanceof Element\Textarea) {
                if (isset($attribs['name'])) {
                    $this->fields[$attribs['name']] = ((null !== $child->getValue()) ? $child->getValue() : null);
                }
            } else if ($child instanceof Element\Select) {
                if (isset($attribs['name'])) {
                    $name = (strpos($attribs['name'], '[]') !== false) ? substr($attribs['name'], 0, strpos($attribs['name'], '[]')) : $attribs['name'];
                    $this->fields[$name] = ((null !== $child->getMarked()) ? $child->getMarked() : null);
                }
            } else if ($child instanceof Element\Radio) {
                $radioChildren = $child->getChildren();
                if (isset($radioChildren[0])) {
                    $childAttribs = $radioChildren[0]->getAttributes();
                    if (isset($childAttribs['name'])) {
                        $this->fields[$childAttribs['name']] = ((null !== $child->getMarked()) ? $child->getMarked() : null);
                    }
                }
            } else if ($child instanceof Element\Checkbox) {
                $checkChildren = $child->getChildren();
                if (isset($checkChildren[0])) {
                    $childAttribs = $checkChildren[0]->getAttributes();
                    if (isset($childAttribs['name'])) {
                        $key = str_replace('[]', '', $childAttribs['name']);
                        $this->fields[$key] = ((null !== $child->getMarked()) ? $child->getMarked() : null);
                    }
                }
            } else {
                if (isset($attribs['name'])) {
                    $this->fields[$attribs['name']] = (isset($attribs['value']) ? $attribs['value'] : null);
                    if ($attribs['type'] == 'file') {
                        $this->hasFile = true;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Get the $hasFile property
     *
     * @return boolean
     */
    public function hasFile()
    {
        return $this->hasFile;
    }

    /**
     * Get the main form element.
     *
     * @return array
     */
    public function getFormElement()
    {
        return $this->form;
    }

    /**
     * Get the attributes of the form object.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->form->getAttributes();
    }

    /**
     * Get the form action.
     *
     * @return array
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Get the form method.
     *
     * @return array
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get the elements of the form object.
     *
     * @return array
     */
    public function getElements()
    {
        return $this->form->getChildren();
    }

    /**
     * Get a single field from $initFieldsValues
     *
     * @param $name
     * @return array
     */
    public function getField($name)
    {
        $field = array();

        if (array_key_exists($name, $this->initFieldsValues)) {
            $field = $this->initFieldsValues[$name];
        } else {
            foreach ($this->initFieldsValues as $f) {
                if (array_key_exists($name, $f)) {
                    $field = $f[$name];
                }
            }
        }

        return $field;
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
     * Get an element object of the form by name.
     *
     * @param string $elementName
     * @return \Pop\Form\Element
     */
    public function getElement($elementName)
    {
        $i = $this->getElementIndex($elementName);
        return (null !== $i) ? $this->form->getChild($this->getElementIndex($elementName)) : null;
    }

    /**
     * Get the index of an element object of the form by name.
     *
     * @param string $elementName
     * @return int
     */
    public function getElementIndex($elementName)
    {
        $name = null;
        $elem = null;
        $index = null;
        $elems =  $this->form->getChildren();

        foreach ($elems as $i => $e) {
            if ($e->getNodeName() == 'fieldset') {
                $children = $e->getChildren();
                foreach ($children as $c) {
                    if ($c->getNodeName() == 'input') {
                        $attribs = $c->getAttributes();
                        $name = str_replace('[]', '', $attribs['name']);
                    }
                }
            } else {
                $attribs = $e->getAttributes();
                $name = $attribs['name'];
            }
            if ($name == $elementName) {
                $index = $i;
            }
        }

        return $index;
    }

    /**
     * Remove a form element
     *
     * @param string $elementName
     * @return $this
     */
    public function removeElement($elementName)
    {
        $i = $this->getElementIndex($elementName);

        $newInitValues = array();
        $keys = array_keys($this->initFieldsValues);

        if (isset($keys[0]) && is_numeric($keys[0])) {
            foreach ($this->initFieldsValues as $fields) {
                $newInitValuesAry = array();
                foreach ($fields as $name => $field) {
                    if (isset($name) && ($name == $elementName)) {
                        unset($fields[$name]);
                    } else {
                        $newInitValuesAry[$name] = $field;
                    }
                }
                $newInitValues[] = $newInitValuesAry;
            }
        } else {
            foreach ($this->initFieldsValues as $name => $field) {
                if (isset($name) && ($name == $elementName)) {
                    unset($this->initFieldsValues[$name]);
                } else {
                    $newInitValues[$name] = $field;
                }
            }
        }
        $this->initFieldsValues = $newInitValues;

        if (isset($this->fields[$elementName])) {
            unset($this->fields[$elementName]);
        }

        if (null !== $i) {
            $this->form->removeChild($i);
        }

        return $this;
    }

    /**
     * Determine whether or not the form object is valid and return the result.
     *
     * @return boolean
     */
    public function isValid()
    {
        $noErrors = true;
        $children = $this->form->getChildren();

        // Check each element for validators, validate them and return the result.
        foreach ($children as $child) {
            if ($child->validate() == false) {
                $noErrors = false;
            }
        }

        return $noErrors;
    }

    /**
     * Set error pre-display globally across all form element objects
     *
     * @param  string  $container
     * @param  array   $attribs
     * @param  boolean $pre
     * @return \Pop\Form\Form
     */
    public function setErrorDisplay($container, array $attribs, $pre = false)
    {
        if (null === $this->errorDisplay) {
            $this->errorDisplay = array(
                'container'  => 'div',
                'attributes' => array(
                    'class' => 'error'
                ),
                'pre' => false
            );
        }

        foreach ($this->form->childNodes as $child) {
            $child->setErrorDisplay($container, $attribs, $pre);
        }

        $this->errorDisplay['container'] = $container;
        $this->errorDisplay['attributes'] = $attribs;
        $this->errorDisplay['pre'] = $pre;

        return $this;
    }

    /**
     * Get all form element errors.
     *
     * @return array
     */
    public function getErrors()
    {
        $errors = array();
        foreach ($this->form->childNodes as $child) {
            if ($child->hasErrors()) {
                $errors[str_replace('[]', '', $child->getName())] = $child->getErrors();
            }
        }
        return $errors;
    }

    /**
     * Render the form object either using the defined template or
     * by a basic 1:1 DT/DD tag structure.
     *
     * @param  boolean $ret
     * @throws Exception
     * @return void
     */
    public function render($ret = false)
    {
        // Check to make sure form elements exist.
        if ((count($this->form->getChildren()) == 0) && (count($this->initFieldsValues) == 0)) {
            throw new Exception('Error: There are no form elements declared for this form object.');
        } else if ((count($this->form->getChildren()) == 0) && (count($this->initFieldsValues) > 0)) {
            $this->setFieldValues();
        }

        // If the form has a file field
        if ($this->hasFile) {
            $this->setAttributes('enctype', 'multipart/form-data');
        }

        // If the template is not set, default to the basic output.
        if (null === $this->template) {
            $this->renderWithoutTemplate();
        // Else, start building the form's HTML output based on the template.
        } else {
            $this->renderWithTemplate();
        }

        // Return or print the form output.
        if ($ret) {
            return $this->output;
        } else {
            echo $this->output;
        }

    }

    /**
     * Method to clear any session data used with the form for
     * security tokens, captchas, etc.
     *
     * @return \Pop\Form\Form
     */
    public function clear()
    {
        // Start a session.
        if (session_id() == '') {
            session_start();
        }

        if (isset($_SESSION['pop_csrf'])) {
            unset($_SESSION['pop_csrf']);
        }

        if (isset($_SESSION['pop_captcha'])) {
            unset($_SESSION['pop_captcha']);
        }

        return $this;
    }

    /**
     * Method to filter current form values with the
     * applied callbacks and their parameters
     *
     * @param  array $filters
     * @return \Pop\Form\Form
     */
    public function filter($filters)
    {
        $this->setFieldValues($this->fields, $filters);
        return $this;
    }

    /**
     * Method to filter the values with the applied
     * callbacks and their parameters
     *
     * @param  array $values
     * @param  array $filters
     * @return array
     */
    protected function filterValues($values, $filters)
    {
        $filteredValues = array();

        foreach ($values as $key => $value) {
            foreach ($filters as $func => $params) {
                if (function_exists($func)) {
                    if ($value instanceof \ArrayObject) {
                        $value = (array)$value;
                    }
                    if (is_array($value)) {
                        $filteredAry = array();
                        foreach ($value as $k => $v) {
                            if (null !== $params) {
                                $pars = (!is_array($params)) ?
                                    array($v, $params) :
                                    array_merge(array($v), $params);
                                $filteredAry[$k] = call_user_func_array($func, $pars);
                            } else {
                                $filteredAry[$k] = $func($v);
                            }
                        }
                        $filteredValues[$key] = $filteredAry;
                        $value = $filteredAry;
                    } else {
                        if (null !== $params) {
                            $pars = (!is_array($params)) ?
                                    array($value, $params) :
                                    array_merge(array($value), $params);
                            $filteredValues[$key] = call_user_func_array($func, $pars);
                        } else {
                            $filteredValues[$key] = $func($value);
                        }
                        $value = $filteredValues[$key];
                    }
                } else {
                    $filteredValues[$key] = $value;
                }
            }
        }

        return $filteredValues;
    }

    /**
     * Method to render the form using a basic 1:1 DT/DD layout
     *
     * @return void
     */
    protected function renderWithoutTemplate()
    {
        // Initialize properties.
        $this->output = null;
        $children = $this->form->getChildren();
        $this->form->removeChildren();

        $id = (null !== $this->form->getAttribute('id')) ? $this->form->getAttribute('id') . '-field-group-' : 'pop-form-field-group-';

        // Create DL element.
        $i = 1;
        $dl = new Child('dl', null, null, false, $this->form->getIndent());
        $dl->setAttributes('id', $id . $i);

        // Loop through the children and create and attach the appropriate DT and DT elements, with labels where applicable.
        foreach ($children as $child) {
            if ($child->getNodeName() == 'fieldset') {
                $chdrn = $child->getChildren();
                if (isset($chdrn[0])) {
                    $attribs = $chdrn[0]->getAttributes();
                }
            } else {
                $attribs = $child->getAttributes();
            }

            $name = (isset($attribs['name'])) ? $attribs['name'] : '';
            $name = str_replace('[]', '', $name);

            if (count($this->groups) > 0) {
                if (isset($this->groups[$i]) && ($this->groups[$i] == $name)) {
                    $this->form->addChild($dl);
                    $i++;
                    $dl = new Child('dl', null, null, false, $this->form->getIndent());
                    $dl->setAttributes('id', $id . $i);
                }
            }

            // Clear the password field from display.
            if ($child->getAttribute('type') == 'password') {
                $child->setValue(null);
                $child->setAttributes('value', null);
            }

            // If the element label is set, render the appropriate DT and DD elements.
            if (null !== $child->getLabel()) {
                // Create the DT and DD elements.
                $dt = new Child('dt', null, null, false, ($this->form->getIndent() . '    '));
                $dd = new Child('dd', null, null, false, ($this->form->getIndent() . '    '));

                // Format the label name.
                $lblName = ($child->getNodeName() == 'fieldset') ? '1' : '';
                $label = new Child('label', $child->getLabel(), null, false, ($this->form->getIndent() . '        '));
                $label->setAttributes('for', ($name . $lblName));

                $labelAttributes = $child->getLabelAttributes();
                if (null !== $labelAttributes) {
                    foreach ($labelAttributes as $a => $v) {
                        $label->setAttributes($a, $v);
                    }
                } else if ($child->isRequired()) {
                    $label->setAttributes('class', 'required');
                }

                // Add the appropriate children to the appropriate elements.
                $dt->addChild($label);
                $child->setIndent(($this->form->getIndent() . '        '));
                $childChildren = $child->getChildren();
                $child->removeChildren();

                foreach ($childChildren as $cChild) {
                    $cChild->setIndent(($this->form->getIndent() . '            '));
                    $child->addChild($cChild);
                }

                $dd->addChild($child);
                $dl->addChildren(array($dt, $dd));
            // Else, render only a DD element.
            } else {
                $dd = new Child('dd', null, null, false, ($this->form->getIndent() . '    '));
                $child->setIndent(($this->form->getIndent() . '        '));
                $dd->addChild($child);
                $dl->addChild($dd);
            }
        }

        // Add the DL element and its children to the form element.
        $this->form->addChild($dl);
        $this->output = $this->form->render(true);
    }

    /**
     * Method to render the form using the template
     *
     * @return void
     */
    protected function renderWithTemplate()
    {
        // Initialize properties and variables.
        $isFile = !((stripos($this->template, '.phtml') === false) && (stripos($this->template, '.php') === false));
        $template = $this->template;
        $fileContents = ($isFile) ? file_get_contents($this->template) : null;
        $this->output = null;
        $children = $this->form->getChildren();

        // Loop through the child elements of the form.
        foreach ($children as $child) {
            // Clear the password field from display.
            if ($child->getAttribute('type') == 'password') {
                $child->setValue(null);
                $child->setAttributes('value', null);
            }

            // Get the element name.
            if ($child->getNodeName() == 'fieldset') {
                $chdrn = $child->getChildren();
                $attribs = $chdrn[0]->getAttributes();
            } else {
                $attribs = $child->getAttributes();
            }

            $name = (isset($attribs['name'])) ? $attribs['name'] : '';
            $name = str_replace('[]', '', $name);

            // Set the element's label, if applicable.
            if (null !== $child->getLabel()) {

                // Format the label name.
                $label = new Child('label', $child->getLabel());
                $label->setAttributes('for', $name);

                $labelAttributes = $child->getLabelAttributes();
                if (null !== $labelAttributes) {
                    foreach ($labelAttributes as $a => $v) {
                        $label->setAttributes($a, $v);
                    }
                } else if ($child->isRequired()) {
                    $label->setAttributes('class', 'required');
                }

                // Swap the element's label placeholder with the rendered label element.
                $labelSearch = '[{' . $name . '_label}]';
                $labelReplace = $label->render(true);
                $labelReplace = substr($labelReplace, 0, -1);
                $template = str_replace($labelSearch, $labelReplace, $template);
                ${$name . '_label'} = $labelReplace;
            }

            // Calculate the element's indentation.
            if (null === $fileContents) {
                $childIndent = substr($template, 0, strpos($template, ('[{' . $name . '}]')));
                $childIndent = substr($childIndent, (strrpos($childIndent, "\n") + 1));
            } else {
                $childIndent = substr($fileContents, 0, strpos($fileContents, ('$' . $name)));
                $childIndent = substr($childIndent, (strrpos($childIndent, "\n") + 1));
            }

            // Some whitespace clean up
            $length = strlen($childIndent);
            $last = 0;
            $matches = array();
            preg_match_all('/[^\s]/', $childIndent, $matches, PREG_OFFSET_CAPTURE);
            if (isset($matches[0])) {
                foreach ($matches[0] as $str) {
                    $childIndent = str_replace($str[0], null, $childIndent);
                    $last = $str[1];
                }
            }

            // Final whitespace clean up
            if (null !== $fileContents) {
                $childIndent = substr($childIndent, 0, (0 - abs($length - $last)));
            }

            // Set each child element's indentation.
            $childChildren = $child->getChildren();
            $child->removeChildren();
            foreach ($childChildren as $cChild) {
                $cChild->setIndent(($childIndent . '    '));
                $child->addChild($cChild);
            }

            // Swap the element's placeholder with the rendered element.
            $elementSearch = '[{' . $name . '}]';
            $elementReplace = $child->render(true, 0, null, $childIndent);
            $elementReplace = substr($elementReplace, 0, -1);
            $elementReplace = str_replace('</select>', $childIndent . '</select>', $elementReplace);
            $elementReplace = str_replace('</fieldset>', $childIndent . '</fieldset>', $elementReplace);
            $template = str_replace($elementSearch, $elementReplace, $template);
            ${$name} = $elementReplace;
        }

        // Set the rendered form content and remove the children.
        if (!$isFile) {
            $this->form->setNodeValue("\n" . $template . "\n" . $this->form->getIndent());
            $this->form->removeChildren();
            $this->output = $this->form->render(true);
        } else {
            $action = $this->action;
            $method = $this->method;

            ob_start();
            include $this->template;
            $this->output = ob_get_clean();
        }
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
        return (!array_key_exists($name, $this->fields)) ? null : $this->fields[$name];
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
        $this->fields[$name] = null;
    }

    /**
     * Output the form object as a string
     *
     * @return string
     */

    public function __toString()
    {
        return $this->render(true);
    }

}
