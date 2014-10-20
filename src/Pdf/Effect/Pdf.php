<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Pdf\Effect;

/**
 * Pdf draw class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Pdf extends \Pop\Pdf\AbstractPdfEffect
{

    /**
     * Draw a border around the current PDF page.
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @param  int $w
     * @param  int $dashLength
     * @param  int $dashGap
     * @return Pdf
     */
    public function border($r, $g, $b, $w = 4, $dashLength = null, $dashGap = null)
    {
        $this->setStrokeColor($r, $g, $b);
        $this->setStrokeWidth($w, $dashLength, $dashGap);

        $x       = round(0 + ($this->strokeWidth / 2), PHP_ROUND_HALF_DOWN);
        $y       = round(0 + ($this->strokeWidth / 2), PHP_ROUND_HALF_DOWN);
        $w       = round($this->pdf->getCurrentPageObject()->width - ($this->strokeWidth), PHP_ROUND_HALF_DOWN);
        $h       = round($this->pdf->getCurrentPageObject()->height - ($this->strokeWidth), PHP_ROUND_HALF_DOWN);
        $coIndex = $this->pdf->getContentObjectIndex();
        $this->pdf->getObject($coIndex)->setStream("\n{$x} {$y} {$w} {$h} re\n" . $this->setStyle() . "\n");

        return $this;
    }

    /**
     * Flood the current PDF page with a color fill.
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return Pdf
     */
    public function fill($r, $g, $b)
    {
        $this->setFillColor($r, $g, $b);

        $w       = $this->pdf->getCurrentPageObject()->width;
        $h       = $this->pdf->getCurrentPageObject()->height;
        $coIndex = $this->pdf->getContentObjectIndex();
        $this->pdf->getObject($coIndex)->setStream("\n0 0 {$w} {$h} re\n" . $this->setStyle() . "\n");

        return $this;
    }

}