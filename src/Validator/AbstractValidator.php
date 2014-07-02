<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
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
 * Validator class
 *
 * @category   Pop
 * @package    Pop_Validator
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractValidator implements ValidatorInterface
{

    /**
     * Validator value to test against
     * @var mixed
     */
    protected $value = null;

    /**
     * Input value to test
     * @var mixed
     */
    protected $input = null;

    /**
     * Validator message
     * @var string
     */
    protected $message = null;

    /**
     * Constructor
     *
     * Instantiate the validator object
     *
     * @param  mixed   $value
     * @param  string  $msg
     * @return self
     */
    public function __construct($value = null, $msg = null)
    {
        $this->value = $value;

        if (null !== $msg) {
            $this->message = $msg;
        }
    }

    /**
     * Method to get the validator value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Method to get the validator default message
     *
     * @return boolean
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Method to get the validator input
     *
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Method to set the validator value
     *
     * @param  mixed $value
     * @return \Pop\Validator\ValidatorInterface
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Method to set the validator condition
     *
     * @param  string $msg
     * @return \Pop\Validator\ValidatorInterface
     */
    public function setMessage($msg = null)
    {
        $this->message = $msg;
        return $this;
    }

    /**
     * Method to set the validator input
     *
     * @param  mixed $input
     * @return \Pop\Validator\ValidatorInterface
     */
    public function setInput($input = null)
    {
        $this->input = $input;
        return $this;
    }

    /**
     * Method to get the validator value

     * @param  mixed $input
     * @return boolean
     */
    abstract public function evaluate($input = null);

}
