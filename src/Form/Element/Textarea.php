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
 * Textarea form element class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Textarea extends \Pop\Form\Element
{

    /**
     * Constructor
     *
     * Instantiate the textarea form element object.
     *
     * @param  string $name
     * @param  string $value
     * @param  string|array $marked
     * @param  string $indent
     * @return \Pop\Form\Element\Textarea
     */
    public function __construct($name, $value = null, $marked = null, $indent = null)
    {
        parent::__construct('textarea', $name, $value, $marked, $indent);
    }

}
