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

/**
 * Form element interface
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
interface ElementInterface
{

    /**
     * Set the name of the form element
     *
     * @param  string $name
     * @return AbstractElement
     */
    public function setName($name);

    /**
     * Set the value of the form element
     *
     * @param  mixed $value
     * @return AbstractElement
     */
    public function setValue($value);

    /**
     * Set the marked value(s) of the form element
     *
     * @param  mixed $marked
     * @return AbstractElement
     */
    public function setMarked($marked);

    /**
     * Set the label of the form element
     *
     * @param  mixed $label
     * @return AbstractElement
     */
    public function setLabel($label);

    /**
     * Set the attributes of the label of the form element
     *
     * @param  array $attribs
     * @return AbstractElement
     */
    public function setLabelAttributes(array $attribs);

    /**
     * Set whether the form element is required
     *
     * @param  boolean $required
     * @return AbstractElement
     */
    public function setRequired($required);

    /**
     * Set error pre-display
     *
     * @param  boolean $pre
     * @return AbstractElement
     */
    public function setErrorPre($pre = true);

    /**
     * Set error post-display
     *
     * @param  boolean $post
     * @return AbstractElement
     */
    public function setErrorPost($post = true);

    /**
     * Set error display values
     *
     * @param  string  $container
     * @param  array   $attribs
     * @param  boolean $pre
     * @return AbstractElement
     */
    public function setErrorDisplay($container, array $attribs, $pre = false);

    /**
     * Set validators
     *
     * @param  array $validators
     * @return AbstractElement
     */
    public function setValidators(array $validators = []);

    /**
     * Clear errors
     *
     * @return AbstractElement
     */
    public function clearErrors();

    /**
     * Get form element name
     *
     * @return string
     */
    public function getName();

    /**
     * Get form element type
     *
     * @return string
     */
    public function getType();

    /**
     * Get form element value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Get form element marked value(s)
     *
     * @return mixed
     */
    public function getMarked();

    /**
     * Get form element label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Get the attributes of the form element label
     *
     * @return array
     */
    public function getLabelAttributes();

    /**
     * Get validators
     *
     * @return array
     */
    public function getValidators();

    /**
     * Get whether the form element is required
     *
     * @return boolean
     */
    public function isRequired();

    /**
     * Get form element errors
     *
     * @return array
     */
    public function getErrors();

    /**
     * Get if form element has errors
     *
     * @return array
     */
    public function hasErrors();

    /**
     * Add a validator the form element
     *
     * @param  mixed $validator
     * @return AbstractElement
     */
    public function addValidator($validator);

    /**
     * Validate the form element
     *
     * @throws Exception
     * @return boolean
     */
    public function validate();

}
