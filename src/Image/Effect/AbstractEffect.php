<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Image\Effect;

/**
 * Image class
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractEffect implements EffectInterface
{

    /**
     * Image object
     * @var \Pop\Image\AbstractImage
     */
    protected $image = null;

    /**
     * Constructor
     *
     * Instantiate an image object
     *
     * @param  \Pop\Image\AbstractImage
     * @return AbstractEffect
     */
    public function __construct(\Pop\Image\AbstractImage $image = null)
    {
        if (null !== $image) {
            $this->setImage($image);
        }
    }

    /**
     * Get the image object
     *
     * @return \Pop\Image\AbstractImage
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set the image object
     *
     * @param  \Pop\Image\AbstractImage
     * @return AbstractEffect
     */
    public function setImage(\Pop\Image\AbstractImage $image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * Get the blend between 2 colors
     *
     * @param  array $color1
     * @param  array $color2
     * @param  int   $tween
     * @throws Exception
     * @return array
     */
    protected function getBlend(array $color1, array $color2, $tween)
    {
        if ((count($color1) != 3) || (count($color2) != 3)) {
            throw new Exception('Error: The color arrays for the gradient are not correct.');
        }

        $blend = ['r' => [], 'g' => [], 'b' => []];

        $r1 = (int)$color1[0];
        $g1 = (int)$color1[1];
        $b1 = (int)$color1[2];

        $r2 = (int)$color2[0];
        $g2 = (int)$color2[1];
        $b2 = (int)$color2[2];

        $rTotal = $r2 - $r1;
        $gTotal = $g2 - $g1;
        $bTotal = $b2 - $b1;

        for ($i = 0; $i <= $tween; $i++) {
            $blend['r'][] = round($this->calculateSteps($i, $r1, $rTotal, $tween));
            $blend['g'][] = round($this->calculateSteps($i, $g1, $gTotal, $tween));
            $blend['b'][] = round($this->calculateSteps($i, $b1, $bTotal, $tween));
        }

        return $blend;
    }

    /**
     * Calculate the steps between two points
     *
     * @param  int $curStep
     * @param  int $start
     * @param  int $end
     * @param  int $totalSteps
     * @return int
     */
    protected function calculateSteps($curStep, $start, $end, $totalSteps)
    {
        return ($end * ($curStep / $totalSteps)) + $start;
    }

}
