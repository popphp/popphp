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
 * Form button element class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */

class Button extends AbstractElement
{

    /**
     * Element type
     * @var string
     */
    protected $type = 'button';

    /**
     * Constructor
     *
     * Instantiate the button form element.
     *
     * @param  string $name
     * @param  string $value
     * @param  string $indent
     * @return Button
     */
    public function __construct($name, $value = null, $indent = null)
    {
        parent::__construct($this->type, $value, null, false, $indent);

        $this->setAttributes(['name' => $name, 'id' => $name]);

        if (strtolower($name) == 'submit') {
            $this->setAttribute('type', 'submit');
        } else if (strtolower($name) == 'reset') {
            $this->setAttribute('type', 'reset');
        } else{
            $this->setAttribute('type', 'button');
        }

        $this->setValue($value);
        $this->setName($name);
    }

}
