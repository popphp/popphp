<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Color
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Color\Space;

/**
 * LAB color class
 *
 * @category   Pop
 * @package    Pop_Color
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Lab implements ColorInterface
{

    /**
     * Lightness value
     * @var int
     */
    protected $l = null;

    /**
     * A value
     * @var int
     */
    protected $a = null;

    /**
     * B value
     * @var int
     */
    protected $b = null;

    /**
     * Constructor
     *
     * Instantiate the LAB color object
     *
     * @param int $l
     * @param int $a
     * @param int $b
     * @throws \Pop\Color\Space\Exception
     * @return \Pop\Color\Space\Lab
     */
    public function __construct($l, $a, $b)
    {
        $max = max($l, $a, $b);
        $min = min($l, $a, $b);

        if (($l > 100) || ($l < 0) || ($max > 127) || ($min < -128)) {
            throw new Exception('One or more of the color values is out of range.');
        }

        $this->l = (int)$l;
        $this->a = (int)$a;
        $this->b = (int)$b;
    }

    /**
     * Method to get the full LAB value
     *
     * @param  string $type
     * @return mixed
     */
    public function get($type = \Pop\Color\Color::ASSOC_ARRAY)
    {
        $lab = null;

        switch ($type) {
            case 1:
                $lab = ['l' => $this->l, 'a' => $this->a, 'b' => $this->b];
                break;
            case 2:
                $lab = [$this->l, $this->a, $this->b];
                break;
            case 3:
                $lab = $this->l . ',' . $this->a . ',' . $this->b;
                break;
        }

        return $lab;
    }

    /**
     * Method to get the L value
     *
     * @return int
     */
    public function getL()
    {
        return $this->l;
    }

    /**
     * Method to get the A value
     *
     * @return int
     */
    public function getA()
    {
        return $this->a;
    }

    /**
     * Method to get the B value
     *
     * @return int
     */
    public function getB()
    {
        return $this->b;
    }

    /**
     * Method to return the string value for printing output.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->get(\Pop\Color\Color::STRING);
    }

}
