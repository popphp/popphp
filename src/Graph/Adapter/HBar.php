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
 * Horizontal graph adapter class
 *
 * @category   Pop
 * @package    Pop_Graph
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class HBar extends AbstractAdapter
{

    /**
     * Create a horizontal bar graph
     *
     * @param  array $dataPoints
     * @param  array $xAxis
     * @param  array $yAxis
     * @return \Pop\Graph\Adapter\HBar
     */
    public function create(array $dataPoints, array $xAxis, array $yAxis)
    {
        // Calculate the points.
        $points = $this->getPoints($xAxis, $yAxis);

        if ($this->graph->getShowX()) {
            $this->showXAxis($yAxis, $points);
        }
        if ($this->graph->getShowX()) {
            $this->showYAxis($xAxis, $points, $this->graph->getBarWidth());
        }

        // Draw graph data.
        if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
            $realYDiv = ($points->yLength + ($this->graph->getBarWidth() * 2)) / (count($yAxis) - 1);
        } else {
            $realYDiv = ($points->yLength - ($this->graph->getBarWidth() * 2)) / (count($yAxis) - 1);
        }

        if ((null !== $this->graph->getFillColor()) || is_array($dataPoints[0])) {
            $this->graph->adapter()->setFillColor($this->graph->getFillColor());
            $this->graph->adapter()->setStrokeColor((null !== $this->graph->getStrokeColor()) ? $this->graph->getStrokeColor() : $this->graph->getFillColor());
            $this->graph->adapter()->setStrokeWidth($this->graph->getStrokeWidth());
            $len = count($dataPoints);
            for ($i = 0; $i < $len; $i++) {
                if (is_array($dataPoints[$i])) {
                    $pt = $dataPoints[$i][0];
                    $this->graph->adapter()->setStrokeColor((null !== $this->graph->getStrokeColor()) ? $this->graph->getStrokeColor() : $dataPoints[$i][1]);
                    $this->graph->adapter()->setFillColor($dataPoints[$i][1]);
                } else {
                    $pt = $dataPoints[$i];
                    $this->graph->adapter()->setStrokeColor((null !== $this->graph->getStrokeColor()) ? $this->graph->getStrokeColor() : $this->graph->getFillColor());
                    $this->graph->adapter()->setFillColor($this->graph->getFillColor());
                }
                if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                    $y = ($points->zeroPoint['y'] - ($realYDiv * $i)) + ($this->graph->getBarWidth() / 5);
                } else {
                    $y = ($points->yLength - ($realYDiv * ($i + 1))) + ($this->graph->getBarWidth() * 1.1);
                }
                $x = $points->zeroPoint['x'];
                $h = $this->graph->getBarWidth();
                $w = (($pt / $points->xRange) * $points->xLength);
                $this->graph->adapter()->drawRectangle($x, $y, $w, $h);
            }
        } else {
            $strokeColor = (null !== $this->graph->getStrokeColor()) ? $this->graph->getStrokeColor() : [0, 0, 0];
            $this->graph->adapter()->setStrokeWidth($this->graph->getStrokeWidth());
            $this->graph->adapter()->setStrokeColor($strokeColor[0], $strokeColor[1], $strokeColor[2]);
            for ($i = 0; $i < count($dataPoints); $i++) {
                if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                    $y = ($points->zeroPoint['y'] - ($realYDiv * $i)) + ($this->graph->getBarWidth() / 5);
                } else {
                    $y = ($points->yLength - ($realYDiv * ($i + 1))) + ($this->graph->getBarWidth() * 1.1);
                }
                $x = $points->zeroPoint['x'];
                $h = $this->graph->getBarWidth();
                $w = (($dataPoints[$i] / $points->xRange) * $points->xLength);
                $this->graph->adapter()->drawLine($x, $y, ($x + $w), $y);
                $this->graph->adapter()->drawLine(($x + $w), $y, ($x + $w), ($y + $h));
                $this->graph->adapter()->drawLine(($x + $w), ($y + $h), $x, ($y + $h));
            }
        }

        // Draw data point text.
        if ($this->graph->getShowText()) {
            if (is_array($dataPoints[0])) {
                $dPts = [];
                foreach ($dataPoints as $value) {
                    $dPts[] = $value[0];
                }
            } else {
                $dPts = $dataPoints;
            }
            $this->drawDataText($dPts, $xAxis, $yAxis, 'hBar', $points);
        }

        // Draw graph axes.
        $this->drawXAxis($xAxis, $points);
        $this->drawYAxis($yAxis, $points, $this->graph->getBarWidth());

        return $this;
    }

}
