<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Validator
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Validator;

/**
 * Is subnet of validator class
 *
 * @category   Pop
 * @package    Pop_Validator
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class IsSubnetOf extends AbstractValidator
{

    /**
     * Method to evaluate the validator
     *
     * @param  mixed $input
     * @throws Exception
     * @return boolean
     */
    public function evaluate($input = null)
    {
        // Check to make sure the input is a valid Ipv4 address.
        if (!(new Ipv4())->evaluate($input)) {
            throw new Exception('The IP address must be a valid IPv4 address.');
        }

        // Set the input, if passed
        if (null !== $input) {
            $this->input = $input;
        }

        // Set the default message
        if (null === $this->message) {
            $this->message = 'The value must be part of the subnet ' . $this->value . '.';
        }

        return (substr($this->input, 0, strrpos($this->input, '.')) == $this->value);
    }

}
