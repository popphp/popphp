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

use Pop\Dom\Child;

/**
 * Form class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Form extends AbstractForm
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return Form
     */
    public function __construct(array $fields = null, $action = null, $method = 'post')
    {
        parent::__construct('form');

        $this->setAttributes([
            'action' => ((null !== $action) ? $action : $_SERVER['REQUEST_URI']),
            'method' => $method
        ]);

        if (null !== $fields) {
            $this->setFieldConfig($fields);
            $this->setFieldValues();
        }
    }

    /**
     * Set the field config
     *
     * @param  array $fields
     * @return Form
     */
    public function setFieldConfig(array $fields)
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

        $this->fieldConfig = (count($this->fieldConfig) > 0) ? array_merge($this->fieldConfig, $fields) : $fields;

        return $this;
    }

    /**
     * Add a single field config
     *
     * @param  string $name
     * @param  array  $field
     * @return Form
     */
    public function addFieldConfig($name, array $field)
    {
        $match = false;
        if (array_key_exists($name, $this->fieldConfig)) {
            $this->fieldConfig[$name] = $field;
            $match = true;
        } else {
            foreach ($this->fieldConfig as $key => $value) {
                if (array_key_exists($name, $value)) {
                    $this->fieldConfig[$key][$name] = $field;
                    $match = true;
                }
            }
        }

        if (!$match) {
            $keys = array_keys($this->fieldConfig);
            if (is_numeric($keys[0])) {
                $last = $keys[(count($keys) - 1)];
                $this->fieldConfig[$last][$name] = $field;
            } else {
                $this->fieldConfig[$name] = $field;
            }
        }

        $this->childNodes = [];
        $this->setFieldValues();

        return $this;
    }

    /**
     * Add multiple field configs
     *
     * @param  array  $fields
     * @return Form
     */
    public function addFieldConfigs(array $fields)
    {
        foreach ($fields as $name => $field) {
            $this->addFieldConfig($name, $field);
        }
        return $this;
    }

    /**
     * Insert a field config before another field config
     *
     * @param  string $before
     * @param  string $name
     * @param  array  $field
     * @throws Exception
     * @return Form
     */
    public function insertFieldConfigBefore($before, $name, array $field)
    {
        if (!array_key_exists($before, $this->fieldConfig)) {
            throw new Exception('Error: That field does not exist.');
        }
        $keys = array_keys($this->fieldConfig);
        $i    = array_search($before, $keys);
        $keys = array_merge(array_slice($keys, 0, $i), [$name], array_slice($keys, $i));

        $fields = [];

        foreach ($keys as $key) {
            $fields[$key] = ($key == $name) ? $field : $this->fieldConfig[$key];
        }

        $this->childNodes = [];
        $this->fieldConfig = $fields;
        $this->setFieldConfig($fields);
        $this->setFieldValues();

        // If the element is the top of element of a group, switch out for the new element being inserted before
        foreach ($this->groups as $key => $group) {
            if ($group == $before) {
                $this->groups[$key] = $name;
            }
        }

        return $this;
    }

    /**
     * Insert a field config before another field config
     *
     * @param  string $after
     * @param  string $name
     * @param  array  $field
     * @throws Exception
     * @return Form
     */
    public function insertFieldConfigAfter($after, $name, array $field)
    {
        if (!array_key_exists($after, $this->fieldConfig)) {
            throw new Exception('Error: That field does not exist.');
        }
        $keys = array_keys($this->fieldConfig);
        $i    = array_search($after, $keys);
        $keys = array_merge(array_slice($keys, 0, $i + 1), [$name], array_slice($keys, $i + 1));

        $fields = [];

        foreach ($keys as $key) {
            $fields[$key] = ($key == $name) ? $field : $this->fieldConfig[$key];
        }

        $this->childNodes = [];
        $this->fieldConfig = $fields;
        $this->setFieldConfig($fields);
        $this->setFieldValues();

        return $this;
    }

    /**
     * Set the field values. Optionally, you can apply filters
     * to the passed values via callbacks and their parameters
     *
     * @param  array $values
     * @param  array $filters
     * @return Form
     */
    public function setFieldValues(array $values = null, array $filters = null)
    {
        // Filter values if passed
        if ((null !== $values) && (null !== $filters)) {
            $values = $this->filterValues($values, $filters);
        }

        // If no fields have been created yet, create the fields assigning the field values
        if ((count($this->getChildren()) == 0) && (count($this->fieldConfig) > 0)) {
            $this->createFields($values);
        // Else, set the field values for the already existing fields
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
                                            $field->getChild($key)->setAttribute('checked', 'checked');
                                        } else if ($child->getAttribute('value') == $field->getMarked()) {
                                            $field->getChild($key)->setAttribute('checked', 'checked');
                                        }
                                    // If select option
                                    } else if ($child->getNodeName() == 'option') {
                                        if (is_array($field->getMarked()) && in_array($child->getAttribute('value'), $field->getMarked())) {
                                            $field->getChild($key)->setAttribute('selected', 'selected');
                                        } else if ($child->getAttribute('value') == $field->getMarked()) {
                                            $field->getChild($key)->setAttribute('selected', 'selected');
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
                                $field->setAttribute('value', $values[$fieldName]);
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
     * Insert a form element before another element
     *
     * @param  string                  $name
     * @param  Element\AbstractElement $e
     * @throws Exception
     * @return Form
     */
    public function insertElementBefore($name, Element\AbstractElement $e)
    {
        $i = $this->getElementIndex($name);
        if (null === $i) {
            throw new Exception('Error: That element does not exist.');
        }

        // If the element is the top of element of a group, switch out for the new element being inserted before
        foreach ($this->groups as $key => $group) {
            if ($group == $name) {
                $this->groups[$key] = $e->getName();
            }
        }

        $this->childNodes = array_merge(array_slice($this->childNodes, 0, $i), [$e], array_slice($this->childNodes, $i));

        return $this;
    }

    /**
     * Insert a form element after another element
     *
     * @param  string                  $name
     * @param  Element\AbstractElement $e
     * @throws Exception
     * @return Form
     */
    public function insertElementAfter($name, Element\AbstractElement $e)
    {
        $i = $this->getElementIndex($name);
        if (null === $i) {
            throw new Exception('Error: That element does not exist.');
        }

        $this->childNodes = array_merge(array_slice($this->childNodes, 0, $i + 1), [$e], array_slice($this->childNodes, $i + 1));

        return $this;
    }

    /**
     * Add a form element or elements to the form object.
     *
     * @param  Element\AbstractElement $e
     * @return Form
     */
    public function addElement(Element\AbstractElement $e)
    {
        return $this->addElements([$e]);
    }

    /**
     * Add a form element or elements to the form object.
     *
     * @param  array $e
     * @throws Exception
     * @return Form
     */
    public function addElements(array $e)
    {
        foreach ($e as $c) {
            if (!($c instanceof Element\AbstractElement)) {
                throw new Exception('Error: One of the elements passed is not an instance of Pop\\Form\\Element\\AbstractElement.');
            }
        }

        $this->addChildren($e);
        $children = $this->getChildren();

        foreach ($children as $child) {
            $attribs = $child->getAttributes();
            if (($child instanceof Element\Textarea) || ($child instanceof Element\Button)) {
                if (isset($attribs['name'])) {
                    $this->fields[$attribs['name']] = ((null !== $child->getValue()) ? $child->getValue() : null);
                }
            } else if ($child instanceof Element\Select) {
                if (isset($attribs['name'])) {
                    $name = (strpos($attribs['name'], '[]') !== false) ? substr($attribs['name'], 0, strpos($attribs['name'], '[]')) : $attribs['name'];
                    $this->fields[$name] = ((null !== $child->getMarked()) ? $child->getMarked() : null);
                }
            } else if ($child instanceof Element\Input\Radio) {
                $radioChildren = $child->getChildren();
                if (isset($radioChildren[0])) {
                    $childAttribs = $radioChildren[0]->getAttributes();
                    if (isset($childAttribs['name'])) {
                        $this->fields[$childAttribs['name']] = ((null !== $child->getMarked()) ? $child->getMarked() : null);
                    }
                }
            } else if ($child instanceof Element\Input\Checkbox) {
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
     * Get a single field from $fieldConfig
     *
     * @param $name
     * @return array
     */
    public function getFieldConfig($name = null)
    {
        if (null !== $name) {
            return (array_key_exists($name, $this->fieldConfig)) ? $this->fieldConfig[$name] : [];
        } else {
            return $this->fieldConfig;
        }
    }

    /**
     * Alias method to getElements())
     *
     * @return array
     */
    public function elements()
    {
        return $this->getElements();
    }

    /**
     * Get the elements of the form object.
     *
     * @return array
     */
    public function getElements()
    {
        $children = $this->getChildren();
        $elements = [];

        foreach ($children as $child) {
            if ($child instanceof Element\AbstractElement){
                $elements[] = $child;
            }
        }

        return $elements;
    }

    /**
     * Alias method to getElement()
     *
     * @param string $elementName
     * @return Element\AbstractElement
     */
    public function element($elementName)
    {
        return $this->getElement($elementName);
    }

    /**
     * Get an element object of the form by name.
     *
     * @param string $elementName
     * @return Element\AbstractElement
     */
    public function getElement($elementName)
    {
        $i = $this->getElementIndex($elementName);
        return (null !== $i) ? $this->getChild($this->getElementIndex($elementName)) : null;
    }

    /**
     * Get the index of an element object of the form by name.
     *
     * @param string $elementName
     * @return int
     */
    public function getElementIndex($elementName)
    {
        $name  = null;
        $elem  = null;
        $index = null;
        $elems = $this->getChildren();

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

        $newInitValues = [];
        $keys = array_keys($this->fieldConfig);

        if (isset($keys[0]) && is_numeric($keys[0])) {
            foreach ($this->fieldConfig as $fields) {
                $newInitValuesAry = [];
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
            foreach ($this->fieldConfig as $name => $field) {
                if (isset($name) && ($name == $elementName)) {
                    unset($this->fieldConfig[$name]);
                } else {
                    $newInitValues[$name] = $field;
                }
            }
        }
        $this->fieldConfig = $newInitValues;

        if (isset($this->fields[$elementName])) {
            unset($this->fields[$elementName]);
        }

        if (null !== $i) {
            $this->removeChild($i);
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
        $children = $this->getChildren();

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
     * @return Form
     */
    public function setErrorDisplay($container, array $attribs, $pre = false)
    {
        if (null === $this->errorDisplay) {
            $this->errorDisplay = [
                'container'  => 'div',
                'attributes' => ['class' => 'error'],
                'pre'        => false
            ];
        }

        $elements = $this->getElements();
        foreach ($elements as $element) {
            $element->setErrorDisplay($container, $attribs, $pre);
        }

        $this->errorDisplay['container']  = $container;
        $this->errorDisplay['attributes'] = $attribs;
        $this->errorDisplay['pre']        = $pre;

        return $this;
    }

    /**
     * Check if form has errors
     *
     * @param  string $field
     * @return boolean
     */
    public function hasErrors($field = null)
    {
        return (count($this->getErrors($field)) > 0);
    }

    /**
     * Get all form element errors.
     *
     * @param  string $field
     * @return array
     */
    public function getErrors($field = null)
    {
        $errors   = [];
        $elements = $this->getElements();
        foreach ($elements as $element) {
            if ($element->hasErrors()) {
                $errors[str_replace('[]', '', $element->getName())] = $element->getErrors();
            }
        }

        if (null !== $field) {
            return (isset($errors[$field])) ? $errors[$field] : [];
        } else {
            return $errors;
        }
    }

    /**
     * Render the form object either using the defined template or
     * by a basic 1:1 DT/DD tag structure.
     *
     * @param  boolean $ret
     * @param  int $depth
     * @param  string $indent
     * @throws Exception
     * @return mixed
     */
    public function render($ret = false, $depth = 0, $indent = null)
    {
        // Check to make sure form elements exist.
        if ((count($this->getChildren()) == 0) && (count($this->fieldConfig) == 0)) {
            throw new Exception('Error: There are no form elements declared for this form object.');
        } else if ((count($this->getChildren()) == 0) && (count($this->fieldConfig) > 0)) {
            $this->createFields();
        }

        // If the form has a file field
        if ($this->hasFile) {
            $this->setAttribute('enctype', 'multipart/form-data');
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
     * Method to filter current form values with the
     * applied callbacks and their parameters
     *
     * @param  array $filters
     * @return Form
     */
    public function filter($filters)
    {
        $this->setFieldValues($this->fields, $filters);
        return $this;
    }

    /**
     * Method to create the form fields
     *
     * @param  array $values
     * @throws Exception
     * @return void
     */
    protected function createFields(array $values = null)
    {
        // Loop through the field config and build the fields and build the fields
        if (count($this->fieldConfig) > 0) {
            // If the fields are a group of fields
            $keys = array_keys($this->fieldConfig);
            if (is_numeric($keys[0])) {
                $fields = [];
                foreach ($this->fieldConfig as $ary) {
                    $k = array_keys($ary);
                    if (isset($k[0])) {
                        $this->groups[] = $k[0];
                    }
                    $fields = array_merge($fields, $ary);
                }
                $this->fieldConfig = $fields;
            }

            foreach ($this->fieldConfig as $name => $field) {
                if (is_array($field) && isset($field['type'])) {
                    if ($field['type'] == 'file') {
                        $this->hasFile = true;
                    }
                    $this->addElement(Fields::factory($name, $field, $values));
                }
            }
        }
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
        $children     = $this->getChildren();
        $this->removeChildren();

        $id = (null !== $this->getAttribute('id')) ? $this->getAttribute('id') . '-field-group-' : 'pop-form-field-group-';

        // Create DL element.
        $i = 1;
        $dl = new Child('dl', null, null, false, $this->getIndent());
        $dl->setAttribute('id', $id . $i);

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
                    $this->addChild($dl);
                    $i++;
                    $dl = new Child('dl', null, null, false, $this->getIndent());
                    $dl->setAttribute('id', $id . $i);
                }
            }

            // Clear the password field from display.
            if ($child->getAttribute('type') == 'password') {
                $child->setValue(null);
                $child->setAttribute('value', null);
            }

            // If the element label is set, render the appropriate DT and DD elements.
            if (($child instanceof Element\AbstractElement) && (null !== $child->getLabel())) {
                // Create the DT and DD elements.
                $dt = new Child('dt', null, null, false, ($this->getIndent() . '    '));
                $dd = new Child('dd', null, null, false, ($this->getIndent() . '    '));

                // Format the label name.
                $lblName = ($child->getNodeName() == 'fieldset') ? '1' : '';
                $label   = new Child('label', $child->getLabel(), null, false, ($this->getIndent() . '        '));
                $label->setAttribute('for', ($name . $lblName));

                $labelAttributes = $child->getLabelAttributes();
                if (null !== $labelAttributes) {
                    foreach ($labelAttributes as $a => $v) {
                        $label->setAttribute($a, $v);
                    }
                } else if ($child->isRequired()) {
                    $label->setAttribute('class', 'required');
                }

                // Add the appropriate children to the appropriate elements.
                $dt->addChild($label);
                $child->setIndent(($this->getIndent() . '        '));
                $childChildren = $child->getChildren();
                $child->removeChildren();

                foreach ($childChildren as $cChild) {
                    $cChild->setIndent(($this->getIndent() . '            '));
                    $child->addChild($cChild);
                }

                $dd->addChild($child);
                $dl->addChildren([$dt, $dd]);
            // Else, render only a DD element.
            } else {
                $dd = new Child('dd', null, null, false, ($this->getIndent() . '    '));
                $child->setIndent(($this->getIndent() . '        '));
                $dd->addChild($child);
                $dl->addChild($dd);
            }
        }

        // Add the DL element and its children to the form element.
        $this->addChild($dl);
        $this->output = parent::render(true);
    }

    /**
     * Method to render the form using the template
     *
     * @return void
     */
    protected function renderWithTemplate()
    {
        // Initialize properties and variables.
        $isFile       = !((stripos($this->template, '.phtml') === false) && (stripos($this->template, '.php') === false));
        $template     = $this->template;
        $fileContents = ($isFile) ? file_get_contents($this->template) : null;
        $this->output = null;
        $children     = $this->getChildren();

        // Loop through the child elements of the form.
        foreach ($children as $child) {
            // Clear the password field from display.
            if ($child->getAttribute('type') == 'password') {
                $child->setValue(null);
                $child->setAttribute('value', null);
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
                $label->setAttribute('for', $name);

                $labelAttributes = $child->getLabelAttributes();
                if (null !== $labelAttributes) {
                    foreach ($labelAttributes as $a => $v) {
                        $label->setAttribute($a, $v);
                    }
                } else if ($child->isRequired()) {
                    $label->setAttribute('class', 'required');
                }

                // Swap the element's label placeholder with the rendered label element.
                $labelSearch        = '[{' . $name . '_label}]';
                $labelReplace       = $label->render(true);
                $labelReplace       = substr($labelReplace, 0, -1);
                $template           = str_replace($labelSearch, $labelReplace, $template);
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
            $length  = strlen($childIndent);
            $last    = 0;
            $matches = [];
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
            $elementSearch  = '[{' . $name . '}]';
            $elementReplace = $child->render(true, 0, null, $childIndent);
            $elementReplace = substr($elementReplace, 0, -1);
            $elementReplace = str_replace('</select>', $childIndent . '</select>', $elementReplace);
            $elementReplace = str_replace('</fieldset>', $childIndent . '</fieldset>', $elementReplace);
            $template       = str_replace($elementSearch, $elementReplace, $template);
            ${$name}        = $elementReplace;
        }

        // Set the rendered form content and remove the children.
        if (!$isFile) {
            $this->setNodeValue("\n" . $template . "\n" . $this->getIndent());
            $this->removeChildren();
            $this->output = parent::render(true);
        } else {
            $action = $this->getAttribute('action');
            $method = $this->getAttribute('method');
            $form   = $this;

            ob_start();
            include $this->template;
            $this->output = ob_get_clean();
        }
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
