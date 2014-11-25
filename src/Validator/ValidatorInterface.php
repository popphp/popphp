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
 * Validator interface
 *
 * @category   Pop
 * @package    Pop_Validator
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
interface ValidatorInterface
{

    /**
     * Get the validator value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Get the validator default message
     *
     * @return boolean
     */
    public function getMessage();

    /**
     * Get the validator input
     *
     * @return mixed
     */
    public function getInput();

    /**
     * Set the validator value
     *
     * @param  mixed $value
     * @return AbstractValidator
     */
    public function setValue($value);

    /**
     * Set the validator default message
     *
     * @param  string $msg
     * @return AbstractValidator
     */
    public function setMessage($msg = null);

    /**
     * Set the validator input
     *
     * @param  mixed $input
     * @return AbstractValidator
     */
    public function setInput($input = null);

    /**
     * Evaluate
     *
     * @param  mixed $input
     * @return boolean
     */
    public function evaluate($input = null);

}
