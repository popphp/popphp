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
use Pop\Form\Element\AbstractElement;

/**
 * Form radio element class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */

class Radio extends AbstractElement
{

    /**
     * Element type
     * @var string
     */
    protected $type = 'input';

    /**
     * Constructor
     *
     * Instantiate the radio input form elements
     *
     * @param  string $name
     * @param  array  $values
     * @param  string $indent
     * @param  string $marked
     * @return Radio
     */
    public function __construct($name, array $values, $indent = null, $marked = null)
    {
        parent::__construct('fieldset', null, null, false, $indent);
        $this->setAttribute('class', 'radio-fieldset');
        $this->setMarked($marked);

        // Create the radio elements and related span elements.
        $i = null;
        foreach ($values as $k => $v) {
            $rad = new Child($this->type, null, null, false, $indent);
            $rad->setAttributes([
                'type'  => 'radio',
                'class' => 'radio',
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

        $this->value = $values;
    }

}
