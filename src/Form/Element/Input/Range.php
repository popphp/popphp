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
namespace Pop\Form\Element\Input;

use Pop\Form\Element\AbstractElement;

/**
 * Form range element class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */

class Range extends AbstractElement
{

    /**
     * Constructor
     *
     * Instantiate the text input form element.
     *
     * @param  string $name
     * @param  string $value
     * @param  string $indent
     * @return Range
     */
    public function __construct($name, $value = null, $indent = null)
    {
        $this->type = 'input';
        parent::__construct($this->type, null, null, false, $indent);

        $this->setAttributes(['type' => 'range', 'name' => $name, 'id' => $name, 'value' => $value]);
        $this->setValue($value);
        $this->setName($name);
    }

}
