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
 * CMYK color class
 *
 * @category   Pop
 * @package    Pop_Color
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Cmyk implements ColorInterface
{

    /**
     * Cyan percentage value
     * @var int
     */
    protected $cyan = null;

    /**
     * Magenta percentage value
     * @var int
     */
    protected $magenta = null;

    /**
     * Yellow percentage value
     * @var int
     */
    protected $yellow = null;

    /**
     * Black percentage value
     * @var int
     */
    protected $black = null;

    /**
     * Constructor
     *
     * Instantiate the CMYK color object
     *
     * @param int $c
     * @param int $m
     * @param int $y
     * @param int $k
     * @throws \Pop\Color\Space\Exception
     * @return \Pop\Color\Space\Cmyk
     */
    public function __construct($c, $m, $y, $k)
    {
        $max = max($c, $m, $y, $k);
        $min = min($c, $m, $y, $k);

        if (($max > 100) || ($min < 0)) {
            throw new Exception('One or more of the color values is out of range.');
        }

        $this->cyan = (int)$c;
        $this->magenta = (int)$m;
        $this->yellow = (int)$y;
        $this->black = (int)$k;
    }

    /**
     * Method to get the full CMYK value
     *
     * @param string $type
     * @return mixed
     */
    public function get($type = \Pop\Color\Color::ASSOC_ARRAY)
    {
        $cmyk = null;

        switch ($type) {
            case 1:
                $cmyk = ['c' => $this->cyan, 'm' => $this->magenta, 'y' => $this->yellow, 'k' => $this->black];
                break;
            case 2:
                $cmyk = [$this->cyan, $this->magenta, $this->yellow, $this->black];
                break;
            case 3:
                $cmyk = $this->cyan . ',' . $this->magenta . ',' . $this->yellow . ',' . $this->black;
                break;
        }

        return $cmyk;
    }

    /**
     * Method to get the cyan value
     *
     * @return int
     */
    public function getCyan()
    {
        return $this->cyan;
    }

    /**
     * Method to get the magenta value
     *
     * @return int
     */
    public function getMagenta()
    {
        return $this->magenta;
    }

    /**
     * Method to get the yellow value
     *
     * @return int
     */
    public function getYellow()
    {
        return $this->yellow;
    }

    /**
     * Method to get the black value
     *
     * @return int
     */
    public function getBlack()
    {
        return $this->black;
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
