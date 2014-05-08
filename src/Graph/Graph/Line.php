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
namespace Pop\Graph\Graph;

/**
 * Line graph class
 *
 * @category   Pop
 * @package    Pop_Graph
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Line extends AbstractGraph
{

    /**
     * Create a line graph
     *
     * @param  array $dataPoints
     * @param  array $xAxis
     * @param  array $yAxis
     * @return \Pop\Graph\Graph\Line
     */
    public function create(array $dataPoints, array $xAxis, array $yAxis)
    {
        // Calculate the points.
        $points = $this->getPoints($xAxis, $yAxis);

        if ($this->graph->getShowX()) {
            $this->showXAxis($yAxis, $points);
        }
        if ($this->graph->getShowX()) {
            $this->showYAxis($xAxis, $points);
        }

        $skip = 1;

        // If the first data point does not equal the graph origin point.
        if (((float)$dataPoints[0][0] != (float)$xAxis[0]) && ((float)$dataPoints[0][1] != (float)$yAxis[0])) {
            $newData = array_merge(array(array((float)$xAxis[0], (float)$yAxis[0])), array(array((float)$dataPoints[0][0], (float)$yAxis[0])), $dataPoints);
            $dataPoints = $newData;
            $skip = 2;
        // Else, if the first data point X equals the graph origin point X.
        } else if (((float)$dataPoints[0][0] != (float)$xAxis[0])) {
            $newData = array_merge(array(array((float)$xAxis[0], (float)$yAxis[0])), array(array((float)$dataPoints[0][0], (float)$yAxis[0])), $dataPoints);
            $dataPoints = $newData;
            $skip = 3;
        // Else, if the first data point Y equals the graph origin point Y.
        } else if (((float)$dataPoints[0][1] != (float)$yAxis[0])) {
            $newData = array_merge(array(array((float)$xAxis[0], (float)$yAxis[0])), array(array((float)$xAxis[0], (float)$dataPoints[0][1])), $dataPoints);
            $dataPoints = $newData;
            $skip = 3;
        }

        // Draw graph data.
        if (null !== $this->graph->getFillColor()) {
            $this->graph->adapter()->setFillColor($this->graph->getFillColor());
            $this->graph->adapter()->setStrokeColor((null !== $this->graph->getStrokeColor()) ? $this->graph->getStrokeColor() : $this->graph->getFillColor());
            $this->graph->adapter()->setStrokeWidth($this->graph->getStrokeWidth());
            $formattedPoints = array();
            for ($i = 0; $i < count($dataPoints); $i++) {
                $x = ((($dataPoints[$i][0] - $dataPoints[0][0]) / $points->xRange) * $points->xLength) + $points->zeroPoint['x'];
                $y = $points->yOffset - ((($dataPoints[$i][1] - $dataPoints[0][1]) / $points->yRange) * $points->yLength);
                $formattedPoints[] = array('x' => $x, 'y' => $y);
                $lastX = $x;
            }
            $formattedPoints[] = array('x' => $lastX, 'y' => $points->zeroPoint['y']);
            $this->graph->adapter()->drawPolygon($formattedPoints);
        } else {
            $this->graph->adapter()->setStrokeWidth($this->graph->getStrokeWidth());
            $this->graph->adapter()->setStrokeColor((null !== $this->graph->getStrokeColor()) ? $this->graph->getStrokeColor() : new Rgb(0, 0, 0));

            for ($i = 1; $i < count($dataPoints); $i++) {
                $x1 = ((($dataPoints[$i - 1][0] - $dataPoints[0][0]) / $points->xRange) * $points->xLength) + $points->zeroPoint['x'];
                $y1 = $points->yOffset - ((($dataPoints[$i - 1][1] - $dataPoints[0][1]) / $points->yRange) * $points->yLength);
                $x2 = ((($dataPoints[$i][0] - $dataPoints[0][0]) / $points->xRange) * $points->xLength) + $points->zeroPoint['x'];
                $y2 = $points->yOffset - ((($dataPoints[$i][1] - $dataPoints[0][1]) / $points->yRange) * $points->yLength);
                $this->graph->adapter()->drawLine($x1, $y1, $x2, $y2);
            }

        }

        // Draw data point text.
        if ($this->graph->getShowText()) {
            $this->drawDataText($dataPoints, $xAxis, $yAxis, 'line', $points, $skip);
        }

        // Draw graph axes.
        $this->drawXAxis($xAxis, $points);
        $this->drawYAxis($yAxis, $points);

        return $this;
    }

}
