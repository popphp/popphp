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
 * Graph adapter abstract class
 *
 * @category   Pop
 * @package    Pop_Graph
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractAdapter
{
    /**
     * Show X-axis color
     * @var \Pop\Graph\Graph
     */
    protected $graph = null;

    /**
     * Constructor
     *
     * Instantiate the graph adapter object.
     *
     * @param  \Pop\Graph\Graph
     * @return AbstractAdapter
     */
    public function __construct(\Pop\Graph\Graph $graph)
    {
        $this->graph = $graph;
    }

    /**
     * Get points
     *
     * @param  array $xAxis
     * @param  array $yAxis
     * @return \ArrayObject
     */
    protected function getPoints($xAxis, $yAxis)
    {
        if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
            $zeroPoint = ['x' => $this->graph->getPadding(), 'y' => $this->graph->getPadding()];
            $endX      = ['x' => ($this->graph->getWidth() - $this->graph->getPadding()), 'y' => $zeroPoint['y']];
            $endY      = ['x' => $zeroPoint['x'], 'y' => ($this->graph->getHeight() - $this->graph->getPadding())];
            $xOffset   = $this->graph->getPadding();
            $yOffset   = $this->graph->getPadding();
        } else {
            $zeroPoint = ['x' => $this->graph->getPadding(), 'y' => ($this->graph->getHeight() - $this->graph->getPadding())];
            $endX      = ['x' => ($this->graph->getWidth() - $this->graph->getPadding()), 'y' => $zeroPoint['y']];
            $endY      = ['x' => $zeroPoint['x'], 'y' => $this->graph->getPadding()];
            $xOffset   = $this->graph->getPadding();
            $yOffset   = $this->graph->getHeight()- $this->graph->getPadding();
        }

        $xLength = $endX['x'] - $zeroPoint['x'];
        $yLength = $zeroPoint['y'] - $endY['y'];
        $xRange = (float)$xAxis[count($xAxis) - 1] - (float)$xAxis[0];
        $yRange = (float)$yAxis[count($yAxis) - 1] - (float)$yAxis[0];

        $xDiv = $xLength / (count($xAxis) - 1);
        $yDiv = $yLength / (count($yAxis) - 1);

        $points = new \ArrayObject([
            'zeroPoint' => $zeroPoint,
            'endX'      => $endX,
            'endY'      => $endY,
            'xOffset'   => $xOffset,
            'yOffset'   => $yOffset,
            'xLength'   => $xLength,
            'yLength'   => $yLength,
            'xRange'    => $xRange,
            'yRange'    => $yRange,
            'xDiv'      => $xDiv,
            'yDiv'      => $yDiv
        ], \ArrayObject::ARRAY_AS_PROPS);

        return $points;
    }

    /**
     * Draw the X Axis increments
     *
     * @param  array        $xAxis
     * @param  \ArrayObject $points
     * @param  int          $offset
     * @return void
     */
    protected function showXAxis($xAxis, $points, $offset = 0)
    {
        $xColor    = $this->graph->getXColor();
        $fontColor = $this->graph->getFontColor();
        $this->graph->adapter()->setStrokeWidth(1);
        $this->graph->adapter()->setStrokeColor($xColor[0], $xColor[1], $xColor[2]);
        $this->graph->adapter()->drawLine($points->zeroPoint['x'], $points->zeroPoint['y'], $points->endX['x'], $points->endX['y']);
        $this->graph->adapter()->setFillColor($fontColor[0], $fontColor[1], $fontColor[2]);

        $i = 0;

        if ($offset != 0) {
            $realXDiv  = ($points->xLength - ($offset * 2)) / (count($xAxis) - 1);
            $realZeroX = $points->zeroPoint['x'] + ($realXDiv / 2);
        } else {
            $realXDiv  = $points->xDiv;
            $realZeroX = $points->zeroPoint['x'];
        }

        foreach ($xAxis as $x) {
            if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                $this->graph->adapter()->drawLine($realZeroX + ($realXDiv * $i), $points->zeroPoint['y'], $realZeroX + ($realXDiv * $i), ($this->graph->getHeight() - $this->graph->getPadding()));
            } else {
                $this->graph->adapter()->drawLine($realZeroX + ($realXDiv * $i), $points->zeroPoint['y'] - $points->yLength, $realZeroX + ($realXDiv * $i), $points->zeroPoint['y']);
            }
            $i++;
        }
    }

    /**
     * Draw the Y Axis increments
     *
     * @param  array        $yAxis
     * @param  \ArrayObject $points
     * @param  int          $offset
     * @return void
     */
    protected function showYAxis($yAxis, $points, $offset = 0)
    {
        $yColor    = $this->graph->getYColor();
        $fontColor = $this->graph->getFontColor();
        $this->graph->adapter()->setStrokeWidth(1);
        $this->graph->adapter()->setStrokeColor($yColor[0], $yColor[1], $yColor[2]);
        $this->graph->adapter()->setFillColor($fontColor[0], $fontColor[1], $fontColor[2]);

        $i = 0;

        if ($offset != 0) {
            if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                $realYDiv  = ($points->yLength + ($offset * 2)) / (count($yAxis) - 1);
                $realZeroY = $points->zeroPoint['y'] - ($realYDiv / 2);
            } else {
                $realYDiv  = ($points->yLength - ($offset * 2)) / (count($yAxis) - 1);
                $realZeroY = $points->zeroPoint['y'] - ($realYDiv / 2);
            }
        } else {
            $realYDiv  = $points->yDiv;
            $realZeroY = $points->zeroPoint['y'];
        }

        foreach ($yAxis as $y) {
            $this->graph->adapter()->drawLine($points->zeroPoint['x'], $realZeroY - ($realYDiv * $i), ($this->graph->getWidth() - $this->graph->getPadding()), $realZeroY - ($realYDiv * $i));
            $i++;
        }
    }

    /**
     * Draw the X Axis
     *
     * @param  array        $xAxis
     * @param  \ArrayObject $points
     * @param  int          $offset
     * @return void
     */
    protected function drawXAxis($xAxis, $points, $offset = 0)
    {
        $axisColor = $this->graph->getAxisColor();
        $fontColor = $this->graph->getFontColor();
        $this->graph->adapter()->setStrokeWidth($this->graph->getAxisWidth());
        $this->graph->adapter()->setStrokeColor($axisColor[0], $axisColor[1], $axisColor[2]);
        $this->graph->adapter()->drawLine($points->zeroPoint['x'], $points->zeroPoint['y'], $points->endX['x'], $points->endX['y']);
        $this->graph->adapter()->setFillColor($fontColor[0], $fontColor[1], $fontColor[2]);

        $i = 0;

        if ($offset != 0) {
            $realXDiv  = ($points->xLength - ($offset * 2)) / (count($xAxis) - 1);
            $realZeroX = $points->zeroPoint['x'] + ($realXDiv / 2);
        } else {
            $realXDiv  = $points->xDiv;
            $realZeroX = $points->zeroPoint['x'];
        }

        foreach ($xAxis as $x) {
            $xFontOffset = ($this->graph->getFontSize() * strlen($x)) / 3;
            $yFontOffset = $this->graph->getFontSize() + 10;
            if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                $this->graph->adapter()->drawLine($realZeroX + ($realXDiv * $i), $points->zeroPoint['y'], $realZeroX + ($realXDiv * $i), $points->zeroPoint['y'] - 5);
            } else {
                $this->graph->adapter()->drawLine($realZeroX + ($realXDiv * $i), $points->zeroPoint['y'], $realZeroX + ($realXDiv * $i), $points->zeroPoint['y'] + 5);
            }

            if (null !== $this->graph->getFont()) {
                if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                    $this->graph->adapter()->addText($realZeroX + ($realXDiv * $i) - $xFontOffset, $points->zeroPoint['y'] - $yFontOffset, $this->graph->getFontSize(), $x, $this->graph->getFonts($this->graph->getFont()));
                } else {
                    $this->graph->adapter()->text($x, $this->graph->getFontSize(), $realZeroX + ($realXDiv * $i) - $xFontOffset, $points->zeroPoint['y'] + $yFontOffset, $this->graph->getFonts($this->graph->getFont()));
                }
            } else {
                if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                    $this->graph->adapter()->addFont('Arial');
                    $this->graph->adapter()->addText($realZeroX + ($realXDiv * $i) - $xFontOffset, $points->zeroPoint['y'] - $yFontOffset, $this->graph->getFontSize(), $x, 'Arial');
                } else {
                    $this->graph->adapter()->text($x, $this->graph->getFontSize(), $realZeroX + ($realXDiv * $i) - $xFontOffset, $points->zeroPoint['y'] + $yFontOffset);
                }
            }
            $i++;
        }
    }

    /**
     * Draw the Y Axis
     *
     * @param  array        $yAxis
     * @param  \ArrayObject $points
     * @param  int          $offset
     * @return void
     */
    protected function drawYAxis($yAxis, $points, $offset = 0)
    {
        $axisColor = $this->graph->getAxisColor();
        $fontColor = $this->graph->getFontColor();
        $this->graph->adapter()->setStrokeWidth($this->graph->getAxisWidth());
        $this->graph->adapter()->setStrokeColor($axisColor[0], $axisColor[1], $axisColor[2]);
        $this->graph->adapter()->drawLine($points->zeroPoint['x'], $points->zeroPoint['y'], $points->endY['x'], $points->endY['y']);
        $this->graph->adapter()->setFillColor($fontColor[0], $fontColor[1], $fontColor[2]);

        $i = 0;

        if ($offset != 0) {
            if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                $realYDiv  = ($points->yLength + ($offset * 2)) / (count($yAxis) - 1);
                $realZeroY = $points->zeroPoint['y'] - ($realYDiv / 2);
            } else {
                $realYDiv  = ($points->yLength - ($offset * 2)) / (count($yAxis) - 1);
                $realZeroY = $points->zeroPoint['y'] - ($realYDiv / 2);
            }
        } else {
            $realYDiv = $points->yDiv;
            $realZeroY = $points->zeroPoint['y'];
        }

        foreach ($yAxis as $y) {
            $xFontOffset = (($this->graph->getFontSize() * strlen($y)) / 1.5) + 15;
            $yFontOffset = $this->graph->getFontSize() / 2;
            $this->graph->adapter()->drawLine($points->zeroPoint['x'], $realZeroY - ($realYDiv * $i), $points->zeroPoint['x'] - 5, $realZeroY - ($realYDiv * $i));
            if (null !== $this->graph->getFont()) {
                if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                    $this->graph->adapter()->addText($points->zeroPoint['x'] - $xFontOffset, $realZeroY - ($realYDiv * $i) - $yFontOffset, $this->graph->getFontSize(), $y, $this->graph->getFonts($this->graph->getFont()));
                } else {
                    $this->graph->adapter()->text($y, $this->graph->getFontSize(), $points->zeroPoint['x'] - $xFontOffset, $realZeroY - ($realYDiv * $i) + $yFontOffset, $this->graph->getFonts($this->graph->getFont()));
                }
            } else {
                    if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                    $this->graph->adapter()->addFont('Arial');
                    $this->graph->adapter()->addText($points->zeroPoint['x'] - $xFontOffset, $realZeroY - ($realYDiv * $i) - $yFontOffset, $this->graph->getFontSize(), $y, 'Arial');
                } else {
                    $this->graph->adapter()->text($y, $this->graph->getFontSize(), $points->zeroPoint['x'] - $xFontOffset, $realZeroY - ($realYDiv * $i) + $yFontOffset);
                }
            }
            $i++;
        }
    }

    /**
     * Draw the data point text on the graph
     *
     * @param  array  $dataPoints
     * @param  array  $xAxis
     * @param  array  $yAxis
     * @param  string $type
     * @param  array  $points
     * @param  int    $skip
     * @return void
     */
    protected function drawDataText($dataPoints, $xAxis, $yAxis, $type, $points = null, $skip = 1)
    {
        $font             = $this->graph->getFont();
        $fontSize         = $this->graph->getFontSize();
        $fontColor        = $this->graph->getFontColor();
        $reverseFontColor = $this->graph->getReverseFontColor();
        $fillColor        = $this->graph->getFillColor();

        switch ($type) {
            // Draw data point text on a line graph.
            case 'line':
                $this->graph->adapter()->setFillColor($fontColor[0], $fontColor[1], $fontColor[2]);
                $prevY = null;
                $nextY = null;
                $start = $skip;

                for ($i = $start; $i < count($dataPoints); $i++) {
                    $strSize = (strlen($dataPoints[$i][0]) * $fontSize) / 8;
                    $x = ((($dataPoints[$i][0] - $dataPoints[0][0]) / $points->xRange) * $points->xLength) + $points->zeroPoint['x'] - $strSize;
                    $y = $points->yOffset - ((($dataPoints[$i][1] - $dataPoints[0][1]) / $points->yRange) * $points->yLength);
                    if ($i < (count($dataPoints) - 1)) {
                        if (null !== $prevY) {
                            $nextY = $points->yOffset - ((($dataPoints[$i + 1][1] - $dataPoints[0][1]) / $points->yRange) * $points->yLength);
                        }
                        if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                            if ((null !== $prevY) && ($y < $nextY) && ($y < $prevY)) {
                                $y -= $fontSize * 2;
                                if (null !== $fillColor) {
                                    $revColor = (null !== $reverseFontColor) ? $reverseFontColor : [255, 255, 255];
                                    $this->graph->adapter()->setFillColor($revColor[0], $revColor[1], $revColor[2]);
                                }
                            } else if (((null !== $prevY) && ($y < $nextY) && ($y > $prevY)) || ((null === $prevY) && ($y > $nextY))) {
                                $x -= $strSize * 2;
                            } else if (((null !== $prevY) && ($y > $nextY) && ($y < $prevY)) || ((null === $prevY) && ($y < $nextY))) {
                                $x += $strSize * 2;
                            }
                        } else {
                            if ((null !== $prevY) && ($y > $nextY) && ($y > $prevY)) {
                                $y += $fontSize * 2;
                                if (null !== $fillColor) {
                                    $revColor = (null !== $reverseFontColor) ? $reverseFontColor : [255, 255, 255];
                                    $this->graph->adapter()->setFillColor($revColor[0], $revColor[1], $revColor[2]);
                                }
                            } else if (((null !== $prevY) && ($y > $nextY) && ($y < $prevY)) || ((null === $prevY) && ($y > $nextY))) {
                                $x -= $strSize * 2;
                            } else if (((null !== $prevY) && ($y < $nextY) && ($y > $prevY)) || ((null === $prevY) && ($y < $nextY))) {
                                $x += $strSize * 2;
                            }
                        }
                    }

                    if (null !== $font) {
                        if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                            $this->graph->adapter()->addText($x, ($y + ($fontSize / 2)), $fontSize, $dataPoints[$i][1], $this->graph->getFonts($this->graph->getFont()));
                        } else {
                            $this->graph->adapter()->text($dataPoints[$i][1], $fontSize, $x, ($y - ($fontSize / 2)), $this->graph->getFonts($this->graph->getFont()));
                        }
                    } else {
                        if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                            $this->graph->adapter()->addFont('Arial');
                            $this->graph->adapter()->addText($x, ($y + ($fontSize / 2)), $fontSize, $dataPoints[$i][1], 'Arial');
                        } else {
                            $this->graph->adapter()->text($dataPoints[$i][1], $fontSize, $x, ($y - ($fontSize / 2)));
                        }
                    }
                    $prevY = $y;
                    $this->graph->adapter()->setFillColor($fontColor[0], $fontColor[1], $fontColor[2]);
                }
                break;

            // Draw data point text on a vertical bar graph.
            case 'vBar':
                $this->graph->adapter()->setFillColor($fontColor[0], $fontColor[1], $fontColor[2]);
                $realXDiv = ($points->xLength - ($this->graph->getBarWidth() * 2)) / (count($xAxis) - 1);
                for ($i = 0; $i < count($dataPoints); $i++) {
                    $strSize = (strlen($dataPoints[$i]) * $fontSize) / 4;
                    $x = ($realXDiv * ($i + 1)) - ($this->graph->getBarWidth() / 1.75) + ($this->graph->getBarWidth() / 2) - $strSize;
                    $y = $points->yOffset - ((($dataPoints[$i]) / $points->yRange) * $points->yLength);
                    if (null !== $font) {
                        if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                            $this->graph->adapter()->addText($x, ($y + ($fontSize / 2)), $fontSize, $dataPoints[$i], $this->graph->getFonts($this->graph->getFont()), $fontSize);
                        } else {
                            $this->graph->adapter()->text($dataPoints[$i], $fontSize, $x, ($y - ($fontSize / 2)), $this->graph->getFonts($this->graph->getFont()));
                        }
                    } else {
                        if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                            $this->graph->adapter()->addFont('Arial');
                            $this->graph->adapter()->addText($x, ($y + ($fontSize / 2)), $fontSize, $dataPoints[$i], 'Arial');
                        } else {
                            $this->graph->adapter()->text($dataPoints[$i], $fontSize, $x, ($y - ($fontSize / 2)));
                        }
                    }
                }
                break;

            // Draw data point text on a horizontal bar graph.
            case 'hBar':
                $this->graph->adapter()->setFillColor($fontColor[0], $fontColor[1], $fontColor[2]);
                if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                    $realYDiv = ($points->yLength + ($this->graph->getBarWidth() * 2)) / (count($yAxis) - 1);
                } else {
                    $realYDiv = ($points->yLength - ($this->graph->getBarWidth() * 2)) / (count($yAxis) - 1);
                }

                $len = count($dataPoints);
                for ($i = 0; $i < $len; $i++) {
                    if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                        $y = ($points->zeroPoint['y'] - ($realYDiv * $i)) + ($this->graph->getBarWidth() / 5) + ($this->graph->getBarWidth() / 2) - ($fontSize / 2);
                    } else {
                        $y = ($points->yLength - ($realYDiv * ($i + 1))) + ($this->graph->getBarWidth() * 1.1) + ($this->graph->getBarWidth() / 2) + ($fontSize / 2);
                    }
                    $x = (($dataPoints[$i] / $points->xRange) * $points->xLength) + $points->zeroPoint['x'] +  ($fontSize / 2);
                    if (null !== $font) {
                        if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                            $this->graph->adapter()->addText($x, ($y + ($fontSize / 2)), $fontSize, $dataPoints[$i], $this->graph->getFonts($this->graph->getFont()));
                        } else {
                            $this->graph->adapter()->text($dataPoints[$i], $fontSize, $x, ($y - ($fontSize / 2)), $this->graph->getFonts($this->graph->getFont()));
                        }
                    } else {
                        if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                            $this->graph->adapter()->addFont('Arial');
                            $this->graph->adapter()->addText($x, ($y + ($fontSize / 2)), $fontSize, $dataPoints[$i], 'Arial');
                        } else {
                            $this->graph->adapter()->text($dataPoints[$i], $fontSize, $x, ($y - ($fontSize / 2)));
                        }
                    }
                }
                break;

            // Draw data point text on a pie chart.
            case 'pie':
                for ($i = 0; $i < count($dataPoints); $i++) {
                    $newMidX = $xAxis[$i]['x'];
                    $newMidY = $xAxis[$i]['y'];
                    $this->graph->adapter()->setFillColor($fontColor[0], $fontColor[1], $fontColor[2]);
                    if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
                        // Text not supported on PDF pie charts yet due to clipping path issues.
                    } else {
                        switch ($yAxis[$i]) {
                            case 1:
                                $textX = $newMidX + ($fontSize * 1.5);
                                $textY = $newMidY + ($fontSize * 1.5);
                                break;
                            case 2:
                                $textX = $newMidX - ($fontSize * 1.5);
                                $textY = $newMidY + ($fontSize * 1.5);
                                break;
                            case 3:
                                $textX = $newMidX - ($fontSize * 1.5);
                                $textY = $newMidY - ($fontSize * 1.5);
                                break;
                            case 4:
                                $textX = $newMidX + ($fontSize * 1.5);
                                $textY = $newMidY - ($fontSize * 1.5);
                                break;
                        }
                        $this->graph->adapter()->text($dataPoints[$i][0] . '%', $fontSize, $textX, $textY, $this->graph->getFonts($this->graph->getFont()));
                    }
                    $this->graph->adapter()->setFillColor($dataPoints[$i][1]);
                }
                break;
        }
    }

    /**
     * Method to calculate which quadrant a point is in.
     *
     * @param  array $point
     * @param  array $center
     * @return int
     */
    protected function getQuadrant($point, $center)
    {
        if ($this->graph->adapter() instanceof \Pop\Pdf\Pdf) {
            if ($point['x'] >= $center['x']) {
                $quad = ($point['y'] >= $center['y']) ? 4 : 1;
            } else {
                $quad = ($point['y'] >= $center['y']) ? 3 : 2;
            }
        } else {
            if ($point['x'] >= $center['x']) {
                $quad = ($point['y'] <= $center['y']) ? 4 : 1;
            } else {
                $quad = ($point['y'] <= $center['y']) ? 3 : 2;
            }
        }

        return $quad;
    }

    /**
     * Method to calculate the points and data of a triangle.
     *
     * @param  array $point
     * @param  array $center
     * @param  int   $quad
     * @return array
     */
    protected function getTriangle($point, $center, $quad)
    {
        $tri = [];

        switch ($quad) {
            case 1:
                $tri['side1'] = $point['x'] - $center['x'];
                $tri['side2'] = abs($point['y'] - $center['y']);
                break;

            case 2:
                $tri['side1'] = $center['x'] - $point['x'];
                $tri['side2'] = abs($point['y'] - $center['y']);
                break;

            case 3:
                $tri['side1'] = $center['x'] - $point['x'];
                $tri['side2'] = abs($center['y'] - $point['y']);
                break;

            case 4:
                $tri['side1'] = $point['x'] - $center['x'];
                $tri['side2'] = abs($center['y'] - $point['y']);
                break;
        }

        $tri['hypot']  = round(hypot($tri['side1'], $tri['side2']));
        $tri['angle1'] = round(rad2deg(asin($tri['side2'] / $tri['hypot'])));
        $tri['angle2'] = round(rad2deg(asin($tri['side1'] / $tri['hypot'])));

        return $tri;
    }

}
