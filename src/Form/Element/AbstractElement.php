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
use Pop\Validator;

/**
 * Form element class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractElement extends Child implements ElementInterface
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
     * Set the name of the form element object
     *
     * @param  string $name
     * @return AbstractElement
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set the value of the form element object
     *
     * @param  mixed $value
     * @return AbstractElement
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Set the marked value of the form element object
     *
     * @param  mixed $marked
     * @return AbstractElement
     */
    public function setMarked($marked)
    {
        $this->marked = $marked;
        return $this;
    }

    /**
     * Set the label of the form element object
     *
     * @param  mixed $label
     * @return AbstractElement
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
     * Set the attributes of the label of the form element object
     *
     * @param  array $attribs
     * @return AbstractElement
     */
    public function setLabelAttributes(array $attribs)
    {
        foreach ($attribs as $a => $v) {
            $this->labelAttributes[$a] = $v;
        }
        return $this;
    }

    /**
     * Set whether the form element is required
     *
     * @param  boolean $required
     * @return AbstractElement
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
     * @return AbstractElement
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
     * @return AbstractElement
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
     * @return AbstractElement
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
     * @return AbstractElement
     */
    public function setValidators(array $validators = [])
    {
        $this->validators = $validators;
        return $this;
    }

    /**
     * Clear errors
     *
     * @return AbstractElement
     */
    public function clearErrors()
    {
        $this->errors = [];
        return $this;
    }

    /**
     * Get form element object name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get form element object type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get form element object value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get form element object marked values
     *
     * @return mixed
     */
    public function getMarked()
    {
        return $this->marked;
    }

    /**
     * Get form element object label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get the attributes of the form element object label
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
     * Get whether the form element object is required
     *
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Get form element object errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get if form element object has errors
     *
     * @return array
     */
    public function hasErrors()
    {
        return (count($this->errors) > 0);
    }

    /**
     * Add a validator the form element
     *
     * @param  mixed $validator
     * @throws Exception
     * @return AbstractElement
     */
    public function addValidator($validator)
    {
        if (!($validator instanceof \Pop\Validator\AbstractValidator) && !is_callable($validator)) {
            throw new Exception('Error: The validator must be an instance of Pop\Validator\AbstractValidator or a callable object.');
        }
        $this->validators[] = $validator;
        return $this;
    }

    /**
     * Add multiple validators the form element
     *
     * @param  array $validators
     * @return AbstractElement
     */
    public function addValidators(array $validators)
    {
        foreach ($validators as $validator) {
            $this->addValidator($validator);
        }
        return $this;
    }

    /**
     * Validate the form element object
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
     * Render the child and its child nodes
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

}
