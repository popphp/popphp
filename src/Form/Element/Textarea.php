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
 * Form textarea element class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */

class Textarea extends AbstractElement
{

    /**
     * Element type
     * @var string
     */
    protected $type = 'textarea';

    /**
     * Constructor
     *
     * Instantiate the textarea form element
     *
     * @param  string $name
     * @param  string $value
     * @param  string $indent
     * @return Textarea
     */
    public function __construct($name, $value = null, $indent = null)
    {
        parent::__construct($this->type, $value, null, false, $indent);

        $this->setAttributes(['name' => $name, 'id' => $name]);
        $this->setValue($value);
        $this->setName($name);
    }

    /**
     * Set whether the form element is required
     *
     * @param  boolean $required
     * @return Textarea
     */
    public function setRequired($required)
    {
        $this->setAttribute('required', 'required');
        return parent::setRequired($required);
    }

}
