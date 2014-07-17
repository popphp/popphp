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
 * Validator interface
 *
 * @category   Pop
 * @package    Pop_Validator
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
interface ValidatorInterface
{

    /**
     * Method to get the validator value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Method to get the validator default message
     *
     * @return boolean
     */
    public function getMessage();

    /**
     * Method to get the validator input
     *
     * @return mixed
     */
    public function getInput();

    /**
     * Method to set the validator value
     *
     * @param  mixed $value
     * @return AbstractValidator
     */
    public function setValue($value);

    /**
     * Method to set the validator default message
     *
     * @param  string $msg
     * @return AbstractValidator
     */
    public function setMessage($msg = null);

    /**
     * Method to set the validator input
     *
     * @param  mixed $input
     * @return AbstractValidator
     */
    public function setInput($input = null);

    /**
     * Method to evaluate
     *
     * @param  mixed $input
     * @return boolean
     */
    public function evaluate($input = null);

}
