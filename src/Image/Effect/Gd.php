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
class Gd extends AbstractEffect
{

    /**
     * Draw a border around the image.
     *
     * @param  int    $w
     * @param  int    $h
     * @return Gd
     */
    public function border($w, $h = null)
    {
        return $this;
    }

    /**
     * Flood the image with a color fill.
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return Gd
     */
    public function fill($r, $g, $b)
    {
        if ($this->image->getMime() == 'image/gif') {
            imagefill($this->image->resource(), 0, 0, $this->image->getColor([$r, $g, $b], false));
        } else {
            imagefill($this->image->resource(), 0, 0, $this->image->getColor([$r, $g, $b]));
        }
        return $this;
    }

    /**
     * Flood the image with a color fill.
     *
     * @param  array   $color1
     * @param  array   $color2
     * @param  boolean $vertical
     * @throws Exception
     * @return Gd
     */
    public function linearGradient(array $color1, array $color2, $vertical = true)
    {
        if ((count($color1) != 3) || (count($color2) != 3)) {
            throw new Exception('Error: The color arrays for the gradient are not correct.');
        }

        $step  = ($vertical) ? $this->image->getHeight() : $this->image->getWidth();
        $steps = ['r' => [], 'g' => [], 'b' => []];

        if ($color1[0] > $color2[0]) {
            $rDiff  = $color1[0] - $color2[0];
            if ($rDiff == 0) {
                $rDiff++;
            }
            $rStep  = round(($step / $rDiff));
            $rStart = $color1[0];
            $rEnd   = $color2[0];
        } else {
            $rDiff  = $color2[0] - $color1[0];
            if ($rDiff == 0) {
                $rDiff++;
            }
            $rStep  = round(($step / $rDiff));
            $rStart = $color2[0];
            $rEnd   = $color1[0];
        }

        while ($rStart > $rEnd) {
            $steps['r'][] = $rStart;
            $rStart -= $rStep;
        }

        if (count($steps['r']) == 0) {
            $steps['r'] = array_pad($steps['r'], $step, $rStart);
        } else if (count($steps['r']) < $step) {
            $steps['r'] = array_pad($steps['r'], $step, $steps['r'][count($steps['r']) - 1]);
        } else if (count($steps['r']) > $step) {
            $steps['r'] = array_slice($steps['r'], 0, $step);
        }

        if ($color1[1] > $color2[1]) {
            $gDiff  = $color1[1] - $color2[1];
            if ($gDiff == 0) {
                $gDiff++;
            }
            $gStep  = round(($step / $gDiff));
            $gStart = $color1[1];
            $gEnd   = $color2[1];
        } else {
            $gDiff  = $color2[1] - $color1[1];
            if ($gDiff == 0) {
                $gDiff++;
            }
            $gStep  = round(($step / $gDiff));
            $gStart = $color2[1];
            $gEnd   = $color1[1];
        }

        while ($gStart > $gEnd) {
            $steps['g'][] = $gStart;
            $gStart -= $gStep;
        }

        if (count($steps['g']) == 0) {
            $steps['g'] = array_pad($steps['g'], $step, $gStart);
        } else if (count($steps['g']) < $step) {
            $steps['g'] = array_pad($steps['g'], $step, $steps['g'][count($steps['g']) - 1]);
        } else if (count($steps['g']) > $step) {
            $steps['g'] = array_slice($steps['g'], 0, $step);
        }

        if ($color1[2] > $color2[2]) {
            $bDiff  = $color1[2] - $color2[2];
            if ($bDiff == 0) {
                $bDiff++;
            }
            $bStep  = round(($step / $bDiff));
            $bStart = $color1[2];
            $bEnd   = $color2[2];
        } else {
            $bDiff  = $color2[2] - $color1[2];
            if ($bDiff == 0) {
                $bDiff++;
            }
            $bStep  = round(($step / $bDiff));
            $bStart = $color2[2];
            $bEnd   = $color1[2];
        }

        while ($bStart > $bEnd) {
            $steps['b'][] = $bStart;
            $bStart -= $bStep;
        }

        if (count($steps['b']) == 0) {
            $steps['b'] = array_pad($steps['b'], $step, $bStart);
        } else if (count($steps['b']) < $step) {
            $steps['b'] = array_pad($steps['b'], $step, $steps['b'][count($steps['b']) - 1]);
        } else if (count($steps['b']) > $step) {
            $steps['b'] = array_slice($steps['b'], 0, $step);
        }

        foreach ($steps['r'] as $i => $r) {
            $g = $steps['g'][$i];
            $b = $steps['b'][$i];
            $color = ($this->image->getMime() == 'image/gif') ? $this->image->getColor([$r, $g, $b], false) :
                $this->image->getColor([$r, $g, $b]);
            if ($vertical) {
                imageline($this->image->resource(), 0, $i, $this->image->getWidth(), $i, $color);
            } else {
                imageline($this->image->resource(), $i, 0, $i, $this->image->getHeight(), $color);
            }
        }

        return $this;
    }

}
