<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
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
 * HSB color class
 *
 * @category   Pop
 * @package    Pop_Color
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Hsb implements ColorInterface
{

    /**
     * Hue angle value in degrees
     * @var int
     */
    protected $hue = null;

    /**
     * Saturation percentage value
     * @var int
     */
    protected $saturation = null;

    /**
     * Brightness percentage value
     * @var int
     */
    protected $brightness = null;

    /**
     * Constructor
     *
     * Instantiate the RGB color object
     *
     * @param int $h
     * @param int $s
     * @param int $b
     * @throws \Pop\Color\Space\Exception
     * @return \Pop\Color\Space\Hsb
     */
    public function __construct($h, $s, $b)
    {
        $max = max($s, $b);
        $min = min($s, $b);

        if (($h > 360) || ($h < 0) || ($max > 100) || ($min < 0)) {
            throw new Exception('One or more of the color values is out of range.');
        }

        $this->hue = (int)$h;
        $this->saturation = (int)$s;
        $this->brightness = (int)$b;
    }

    /**
     * Method to get the full HSB value
     *
     * @param  string $type
     * @return mixed
     */
    public function get($type = \Pop\Color\Color::ASSOC_ARRAY)
    {
        $hsb = null;

        switch ($type) {
            case 1:
                $hsb = ['h' => $this->hue, 's' => $this->saturation, 'b' => $this->brightness];
                break;
            case 2:
                $hsb = [$this->hue, $this->saturation, $this->brightness];
                break;
            case 3:
                $hsb = $this->hue . ',' . $this->saturation . ',' . $this->brightness;
                break;
        }

        return $hsb;
    }

    /**
     * Method to get the hue value
     *
     * @return int
     */
    public function getHue()
    {
        return $this->hue;
    }

    /**
     * Method to get the saturation value
     *
     * @return int
     */
    public function getSaturation()
    {
        return $this->saturation;
    }

    /**
     * Method to get the brightness value
     *
     * @return int
     */
    public function getBrightness()
    {
        return $this->brightness;
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
