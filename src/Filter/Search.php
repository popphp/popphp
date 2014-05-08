<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Filter
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Filter;

/**
 * Filter search class
 *
 * @category   Pop
 * @package    Pop_Filter
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Search
{

    /**
     * Data to search
     * @var array
     */
    protected $data = null;

    /**
     * Delimiter
     * @var string
     */
    protected $delim = null;

    /**
     * Search property
     * @var string
     */
    protected $search = null;

    /**
     * Case search property
     * @var boolean
     */
    protected $case = false;

    /**
     * Search pattern property
     * @var string
     */
    protected $pattern = null;

    /**
     * Constructor
     *
     * Instantiate the search object.
     *
     * @param  mixed  $data
     * @param  string $delim
     * @return \Pop\Filter\Search
     */
    public function __construct($data, $delim = "\n")
    {
        if (is_array($data)) {
            $this->data = $data;
        } else {
            $this->data = explode($delim, trim($data));
        }
    }

    /**
     * Static method to instantiate the search filter object and return itself
     * to facilitate chaining methods together.
     *
     * @param  mixed  $data
     * @param  string $delim
     * @return \Pop\Filter\Search
     */
    public static function factory($data, $delim = "\n")
    {
        return new self($data, $delim);
    }

    /**
     * Method to perform a search over the data and return an array of the results.
     * Wildcards (*) are supported, such as:
     *
     *   string* (beginning with 'string')
     *
     *   - or -
     *
     *   *string (ending with 'string')
     *
     * @param  string  $search
     * @param  boolean $case
     * @return array
     */
    public function search($search, $case = false)
    {
       $this->case = $case;

        if (substr($search, 0, 1) == '*') {
            $this->pattern = 'back';
            $this->search = substr($search, 1);
        } else if (substr($search, -1) == '*') {
            $this->pattern = 'front';
            $this->search = substr($search, 0, -1);
        } else {
            $this->pattern = 'all';
            $this->search = $search;
        }

        // Execute the array filter to search the array.
        return array_filter($this->data, array($this, 'searchArray'));
    }

    /**
     * Method to search an array for a string, non-inclusive.
     *
     * @param  string $val
     * @return string
     */
    protected function searchArray($val)
    {
        // If case sensitive.
        if ($this->case) {
            switch ($this->pattern) {
                // For 'string*'
                case 'front':
                    if (substr($val, 0, strlen($this->search)) == $this->search) {
                        return $val;
                    }
                    break;

                // For '*string'
                case 'back':
                    if (substr($val, (0 - strlen($this->search)), strlen($this->search)) == $this->search) {
                        return $val;
                    }
                    break;

                // For 'string' (all-inclusive)
                case 'all':
                    if (strpos($val, $this->search) !== false) {
                        return $val;
                    }
                    break;
            }
        // Else, case insensitive.
        } else {
            switch ($this->pattern) {
                // For 'string*'
                case 'front':
                    if (substr(strtolower($val), 0, strlen($this->search)) == strtolower($this->search)) {
                        return $val;
                    }
                    break;

                // For '*string'
                case 'back':
                    if (substr(strtolower($val), (0 - strlen($this->search)), strlen($this->search)) == strtolower($this->search)) {
                        return $val;
                    }
                    break;

                // For 'string' (all-inclusive)
                case 'all':
                    if (stripos($val, $this->search) !== false) {
                        return $val;
                    }
                    break;
            }
        }
    }

}
