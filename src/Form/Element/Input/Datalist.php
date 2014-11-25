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
namespace Pop\Form\Element\Input;

use Pop\Dom\Child;

/**
 * Form text element class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */

class Datalist extends Text
{

    /**
     * Datalist object.
     * @var Child
     */
    protected $datalist = null;

    /**
     * Constructor
     *
     * Instantiate the datalist text input form element
     *
     * @param  string $name
     * @param  string $value
     * @param  string $indent
     * @param  array  $values
     * @return Datalist
     */
    public function __construct($name, $value = null, $indent = null, array $values = null)
    {
        parent::__construct($name, $value, $indent);
        $this->setAttribute('list', $name . '-datalist');

        if (null !== $values) {
            $this->datalist = new Child('datalist', null, null, $this->indent);
            $this->datalist->setAttribute('id', $name . '-datalist');
            foreach ($values as $val) {
                $this->datalist->addChild((new Child('option'))->setAttribute('value', $val));
            }
        }
    }

    /**
     * Set whether the form element is required
     *
     * @param  boolean $required
     * @return Datalist
     */
    public function setRequired($required)
    {
        $this->setAttribute('required', 'required');
        return parent::setRequired($required);
    }

    /**
     * Render the child and its child nodes
     *
     * @param  boolean $ret
     * @param  int     $depth
     * @param  string  $indent
     * @param  string  $errorIndent
     * @return mixed
     */
    public function render($ret = false, $depth = 0, $indent = null, $errorIndent = null)
    {
        $datalist = parent::render(true, $depth, $indent) . $this->datalist->render(true, $depth, $indent);

        // Return or print the rendered child node output.
        if ($ret) {
            return $datalist;
        } else {
            echo $datalist;
        }
    }

}
