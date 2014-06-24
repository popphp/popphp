<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
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

use Pop\Color\Space;

/**
 * Vertical graph adapter class
 *
 * @category   Pop
 * @package    Pop_Graph
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class VBar extends AbstractAdapter
{

    /**
     * Create a vertical bar graph
     *
     * @param  array $dataPoints
     * @param  array $xAxis
     * @param  array $yAxis
     * @return \Pop\Graph\Adapter\VBar
     */
    public function create(array $dataPoints, array $xAxis, array $yAxis)
    {
        // Calculate the points.
        $points = $this->getPoints($xAxis, $yAxis);

        if ($this->graph->getShowX()) {
            $this->showXAxis($yAxis, $points, $this->graph->getBarWidth());
        }
        if ($this->graph->getShowY()) {
            $this->showYAxis($xAxis, $points);
        }

        // Draw graph data.
        $realXDiv = ($points->xLength - ($this->graph->getBarWidth() * 2)) / (count($xAxis) - 1);

        if ((null !== $this->graph->getFillColor()) || is_array($dataPoints[0])) {
            $this->graph->adapter()->setStrokeWidth($this->graph->getStrokeWidth());
            for ($i = 0; $i < count($dataPoints); $i++) {
                if (is_array($dataPoints[$i])) {
                    $pt = $dataPoints[$i][0];
                    $this->graph->adapter()->setStrokeColor((null !== $this->graph->getStrokeColor()) ? $this->graph->getStrokeColor() : $dataPoints[$i][1]);
                    $this->graph->adapter()->setFillColor($dataPoints[$i][1]);
                } else {
                    $pt = $dataPoints[$i];
                    $this->graph->adapter()->setStrokeColor((null !== $this->graph->getStrokeColor()) ? $this->graph->getStrokeColor() : $this->graph->getFillColor());
                    $this->graph->adapter()->setFillColor($this->graph->getFillColor());
                }
                $x = ($realXDiv * ($i + 1)) - ($this->graph->getBarWidth() / 1.75);
                $y = $points->yOffset - ((($pt) / $points->yRange) * $points->yLength);
                $w = $this->graph->getBarWidth();
                $h = $points->zeroPoint['y'] - $y;
                $this->graph->adapter()->drawRectangle($x, $y, $w, $h);
            }
        } else {
            $this->graph->adapter()->setStrokeWidth($this->graph->getStrokeWidth());
            $this->graph->adapter()->setStrokeColor((null !== $this->graph->getStrokeColor()) ? $this->graph->getStrokeColor() : new Space\Rgb(0, 0, 0));
            for ($i = 0; $i < count($dataPoints); $i++) {
                $x = ($realXDiv * ($i + 1)) - ($this->graph->getBarWidth() / 1.75);
                $y = $points->yOffset - ((($dataPoints[$i]) / $points->yRange) * $points->yLength);
                $w = $this->graph->getBarWidth();
                $h = $points->zeroPoint['y'] - $y;
                $this->graph->adapter()->drawLine($x, $y, $x, ($y + $h));
                $this->graph->adapter()->drawLine($x, $y, ($x + $w), $y);
                $this->graph->adapter()->drawLine(($x + $w), $y, ($x + $w), ($y + $h));
            }
        }

        // Draw data point text.
        if ($this->graph->getShowText()) {
            if (is_array($dataPoints[0])) {
                $dPts = array();
                foreach ($dataPoints as $value) {
                    $dPts[] = $value[0];
                }
            } else {
                $dPts = $dataPoints;
            }
            $this->drawDataText($dPts, $xAxis, $yAxis, 'vBar', $points);
        }

        // Draw graph axes.
        $this->drawXAxis($xAxis, $points, $this->graph->getBarWidth());
        $this->drawYAxis($yAxis, $points);

        return $this;
    }

}
