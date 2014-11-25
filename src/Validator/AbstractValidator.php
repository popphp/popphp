<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Validator
 * @author     Nick Sagona, III <dev@nolainteractive.com>
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
 * @author     Nick Sagona, III <dev@nolainteractive.com>
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
     * @param  mixed  $value
     * @param  string $message
     * @return AbstractValidator
     */
    public function __construct($value = null, $message = null)
    {
        $this->setValue($value);
        if (null !== $message) {
            $this->setMessage($message);
        }
    }

    /**
     * Get the validator value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the validator default message
     *
     * @return boolean
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * GEt the validator input
     *
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Set the validator value
     *
     * @param  mixed $value
     * @return AbstractValidator
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Set the validator condition
     *
     * @param  string $msg
     * @return AbstractValidator
     */
    public function setMessage($msg = null)
    {
        $this->message = $msg;
        return $this;
    }

    /**
     * Set the validator input
     *
     * @param  mixed $input
     * @return AbstractValidator
     */
    public function setInput($input = null)
    {
        $this->input = $input;
        return $this;
    }

    /**
     * Evaluate

     * @param  mixed $input
     * @return boolean
     */
    abstract public function evaluate($input = null);

}
