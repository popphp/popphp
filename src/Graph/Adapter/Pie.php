<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Graph
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Graph\Adapter;

/**
 * Pie chart adapter class
 *
 * @category   Pop
 * @package    Pop_Graph
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Pie extends AbstractAdapter
{

    /**
     * Create a pie chart
     *
     * @param  array $pie
     * @param  array $percents
     * @param  int   $explode
     * @throws Exception
     * @return \Pop\Graph\Adapter\Pie
     */
    public function create(array $pie, array $percents, $explode = 0)
    {
        $total      = 0;
        $textMidPts = array();
        $textQuads  = array();
        $textValues = array();

        foreach ($percents as $value) {
            $total += (int)$value[0];
        }

        if ($total > 100) {
            throw new Exception('The percentages are greater than 100.');
        }

        $start = 0;
        $end = 0;
        foreach ($percents as $value) {
            $amt = round(($value[0] / 100) * 360);
            if ($start == 0) {
                $end = $amt;
            } else {
                $end = $start + $amt;
            }
            $this->graph->adapter()->setFillColor($value[1][0], $value[1][1], $value[1][2]);

            if ($explode != 0) {
                $center = array('x' => $pie['x'], 'y' => $pie['y']);
                $mid = (($end - $start) / 2) + $start;
                $midX = round($pie['x'] + ($pie['w'] * (cos($mid / 180 * pi()))));
                $midY = round($pie['y'] + ($pie['h'] * (sin($mid / 180 * pi()))));
                $midPt = array('x' => $midX, 'y' => $midY);

                $quad = $this->getQuadrant($midPt, $center);
                $triangle = $this->getTriangle($midPt, $center, $quad);

                $newHypot = $triangle['hypot'] - $explode;
                $newSide1 = round(sin(deg2rad($triangle['angle2'])) * $newHypot);
                $newSide2 = round(sin(deg2rad($triangle['angle1'])) * $newHypot);

                if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                    switch ($quad) {
                        case 1:
                            $x = $midX - $newSide1;
                            $y = $midY + $newSide2;
                            break;

                        case 2:
                            $x = $newSide1 + $midX;
                            $y = $midY + $newSide2;
                            break;

                        case 3:
                            $x = $newSide1 + $midX;
                            $y = $midY - $newSide2;
                            break;

                        case 4:
                            $x = $midX - $newSide1;
                            $y = $midY - $newSide2;
                            break;
                    }
                    $y = $pie['y'] + ($pie['y'] - $y);
                } else {
                    switch ($quad) {
                        case 1:
                            $x = $midX - $newSide1;
                            $y = $midY - $newSide2;
                            break;

                        case 2:
                            $x = $newSide1 + $midX;
                            $y = $midY - $newSide2;
                            break;

                        case 3:
                            $x = $newSide1 + $midX;
                            $y = $midY + $newSide2;
                            break;

                        case 4:
                            $x = $midX - $newSide1;
                            $y = $midY + $newSide2;
                            break;
                    }
                }
            } else {
                $x = $pie['x'];
                $y = $pie['y'];
            }

            $newMidX = round($x + ($pie['w'] * (cos($mid / 180 * pi()))));
            $newMidY = round($y + ($pie['h'] * (sin($mid / 180 * pi()))));
            $newMidPts = array('x' => $newMidX, 'y' => $newMidY);
            $quad = $this->getQuadrant($newMidPts, array('x' => $x, 'y' => $y));

            $textMidPts[] = $newMidPts;
            $textQuads[] = $quad;
            $textValues[] = $value;

            $this->graph->adapter()->drawArc($x, $y, $start, $end, $pie['w'], $pie['h']);
            $start = $end;
        }

        // Draw data point text.
        if ($this->graph->getShowText()) {
            $this->drawDataText($textValues, $textMidPts, $textQuads, 'pie');
        }

        return $this;
    }

}
