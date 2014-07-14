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
namespace Pop\Form\Element;

/**
 * Form textarea element class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */

class Textarea extends AbstractElement
{

    /**
     * Constructor
     *
     * Instantiate the textarea form element.
     *
     * @param  string $name
     * @param  string $value
     * @param  string $indent
     * @return Textarea
     */
    public function __construct($name, $value = null, $indent = null)
    {
        $this->type = 'textarea';
        parent::__construct($this->type, $value, null, false, $indent);

        $this->setAttributes(['name' => $name, 'id' => $name]);
        $this->setValue($value);
        $this->setName($name);
    }

}
