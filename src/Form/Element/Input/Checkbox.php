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
 * Form checkbox element class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */

class Checkbox extends AbstractElement
{

    /**
     * Element type
     * @var string
     */
    protected $type = 'input';

    /**
     * Constructor
     *
     * Instantiate the checkbox input form elements
     *
     * @param  string       $name
     * @param  array        $values
     * @param  string       $indent
     * @param  string|array $marked
     * @return Checkbox
     */
    public function __construct($name, array $values, $indent = null, $marked = null)
    {
        if (null !== $marked) {
            if (!is_array($marked)) {
                $marked = [$marked];
            }
        } else {
            $marked = [];
        }

        parent::__construct('fieldset', null, null, false, $indent);
        $this->setAttribute('class', 'checkbox-fieldset');
        $this->setMarked($marked);

        // Create the checkbox elements and related span elements.
        $i = null;
        foreach ($values as $k => $v) {
            $chk = new Child($this->type, null, null, false, $indent);
            $chk->setAttributes([
                'type'  => 'checkbox',
                'class' => 'checkbox',
                'name'  => ($name . '[]'),
                'id'    => ($name . $i),
                'value' => $k
            ]);

            // Determine if the current radio element is checked.
            if (in_array($k, $this->marked)) {
                $chk->setAttribute('checked', 'checked');
            }

            $span = new Child('span', null, null, false, $indent);
            $span->setAttribute('class', 'checkbox-span');
            $span->setNodeValue($v);
            $this->addChildren([$chk, $span]);
            $i++;
        }

        $this->value = $values;
    }

}
