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
 * Credit card validator class
 *
 * @category   Pop
 * @package    Pop_Validator
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class CreditCard extends AbstractValidator
{

    /**
     * Method to evaluate the validator
     *
     * @param  mixed $input
     * @return boolean
     */
    public function evaluate($input = null)
    {
        // Set the input, if passed
        if (null !== $input) {
            $this->input = $input;
            if (strpos($this->input, ' ') !== false) {
                $this->input = str_replace(' ', '', $this->input);
            }
            if (strpos($this->input, '-') !== false) {
                $this->input = str_replace('-', '', $this->input);
            }
        }

        // Set the default message
        if (null === $this->message) {
            $this->message = 'The value must be a valid credit card number.';
        }

        // Evaluate the input against the validator
        $nums   = str_split($this->input);
        $check  = $nums[count($nums) - 1];
        $start  = count($nums) - 2;
        $sum    = 0;
        $double = true;

        for ($i = $start; $i >= 0; $i--) {
            if ($double) {
                $num = $nums[$i] * 2;
                if ($num > 9) {
                    $num = substr($num, 0, 1) + substr($num, 1, 1);
                }
                $sum += $num;
                $double = false;
            } else {
                $sum += $nums[$i];
                $double = true;
            }
        }

        $sum += $check;
        $rem = $sum % 10;

        return ($rem == 0);
    }

}
