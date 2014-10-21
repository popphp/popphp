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
namespace Pop\Pdf\Draw;

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
     * Current clipping state.
     * @var boolean
     */
    protected $clipping = false;

    /**
     * Add a clipping path
     *
     * @param  boolean $clip
     * @return Pdf
     */
    public function clipping($clip = true)
    {
        $this->clipping = (bool)$clip;
        return $this;
    }

    /**
     * Method to add a line to the PDF.
     *
     * @param  int $x1
     * @param  int $y1
     * @param  int $x2
     * @param  int $y2
     * @return Pdf
     */
    public function line($x1, $y1, $x2, $y2)
    {
        $coIndex = $this->pdf->getContentObjectIndex();
        $this->pdf->getObject($coIndex)->appendStream("\n{$x1} {$y1} m\n{$x2} {$y2} l\nS\n");

        return $this;
    }

    /**
     * Method to add a rectangle to the PDF.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return Pdf
     */
    public function rectangle($x, $y, $w, $h = null)
    {
        if (null === $h) {
            $h = $w;
        }

        $coIndex = $this->pdf->getContentObjectIndex();

        if ($this->clipping) {
            $bgColor = $this->pdf->getBackgroundColor();
            $this->setStrokeWidth(0);
            $this->setFillColor($bgColor[0], $bgColor[1], $bgColor[2]);
            $this->pdf->getObject($coIndex)->appendStream("\n{$x} {$y} {$w} {$h} re\nW\nF\n");
        } else {
            $this->pdf->getObject($coIndex)->appendStream("\n{$x} {$y} {$w} {$h} re\n" . $this->setStyle() . "\n");
        }

        return $this;
    }

    /**
     * Method to add a rounded rectangle to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @param  int $rx
     * @param  int $ry
     * @return Pdf
     */
    public function roundedRectangle($x, $y, $w, $h = null, $rx = 10, $ry = null)
    {
        if (null === $h) {
            $h = $w;
        }

        if (null === $ry) {
            $ry = $rx;
        }

        $rectangle = null;

        $bez1X = $x;
        $bez1Y = $y;
        $bez2X = $x + $w;
        $bez2Y = $y;
        $bez3X = $x + $w;
        $bez3Y = $y + $h;
        $bez4X = $x;
        $bez4Y = $y + $h;

        $points = [
            ['x' => $x, 'y' => $y + $ry],
            ['x' => $x + $rx, 'y' => $y],
            ['x' => $x + $w - $rx, 'y' => $y],
            ['x' => $x + $w, 'y' => $y + $ry],
            ['x' => $x + $w, 'y' => $y + $h - $ry],
            ['x' => $x + $w - $rx, 'y' => $y + $h],
            ['x' => $x + $rx, 'y' => $y + $h],
            ['x' => $x, 'y' => $y + $h - $ry]
        ];

        $rectangle .= $points[7]['x'] . " " . $points[7]['y'] . " m\n";
        $rectangle .= $points[0]['x'] . " " . $points[0]['y'] . " l\n";
        $rectangle .= $bez1X . " " . $bez1Y . " " . $bez1X . " " . $bez1Y . " " . $points[1]['x'] . " " . $points[1]['y'] . " c\n";
        $rectangle .= $points[2]['x'] . " " . $points[2]['y'] . " l\n";
        $rectangle .= $bez2X . " " . $bez2Y . " " . $bez2X . " " . $bez2Y . " " . $points[3]['x'] . " " . $points[3]['y'] . " c\n";
        $rectangle .= $points[4]['x'] . " " . $points[4]['y'] . " l\n";
        $rectangle .= $bez3X . " " . $bez3Y . " " . $bez3X . " " . $bez3Y . " " . $points[5]['x'] . " " . $points[5]['y'] . " c\n";
        $rectangle .= $points[6]['x'] . " " . $points[6]['y'] . " l\n";
        $rectangle .= $bez4X . " " . $bez4Y . " " . $bez4X . " " . $bez4Y . " " . $points[7]['x'] . " " . $points[7]['y'] . " c\n";

        $rectangle .= "h\n";

        $coIndex = $this->pdf->getContentObjectIndex();

        if ($this->clipping) {
            $bgColor = $this->pdf->getBackgroundColor();
            $this->setStrokeWidth(0);
            $this->setFillColor($bgColor[0], $bgColor[1], $bgColor[2]);
            $this->pdf->getObject($coIndex)->appendStream("\n{$rectangle}\nW\nF\n");
        } else {
            $this->pdf->getObject($coIndex)->appendStream("\n{$rectangle}\n" . $this->setStyle() . "\n");
        }

        return $this;
    }

    /**
     * Method to add a square to the PDF.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @return Pdf
     */
    public function square($x, $y, $w)
    {
        $this->rectangle($x, $y, $w, $w);
        return $this;
    }

    /**
     * Draw a rounded square on the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $rx
     * @param  int $ry
     * @return Pdf
     */
    public function roundedSquare($x, $y, $w, $rx = 10, $ry = null)
    {
        return $this->roundedRectangle($x, $y, $w, $w, $rx, $ry);
    }

    /**
     * Method to draw an ellipse to the PDF.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @param  int     $h
     * @return Pdf
     */
    public function ellipse($x, $y, $w, $h = null)
    {
        if (null === $h) {
            $h = $w;
        }

        $x1 = $x + $w;
        $y1 = $y;

        $x2 = $x;
        $y2 = $y - $h;

        $x3 = $x - $w;
        $y3 = $y;

        $x4 = $x;
        $y4 = $y + $h;

        // Calculate coordinate number one's 2 bezier points.
        $coor1Bez1X = $x1;
        $coor1Bez1Y = (round(0.55 * ($y2 - $y1))) + $y1;
        $coor1Bez2X = $x1;
        $coor1Bez2Y = (round(0.45 * ($y1 - $y4))) + $y4;

        // Calculate coordinate number two's 2 bezier points.
        $coor2Bez1X = (round(0.45 * ($x2 - $x1))) + $x1;
        $coor2Bez1Y = $y2;
        $coor2Bez2X = (round(0.55 * ($x3 - $x2))) + $x2;
        $coor2Bez2Y = $y2;

        // Calculate coordinate number three's 2 bezier points.
        $coor3Bez1X = $x3;
        $coor3Bez1Y = (round(0.55 * ($y2 - $y3))) + $y3;
        $coor3Bez2X = $x3;
        $coor3Bez2Y = (round(0.45 * ($y3 - $y4))) + $y4;

        // Calculate coordinate number four's 2 bezier points.
        $coor4Bez1X = (round(0.55 * ($x3 - $x4))) + $x4;
        $coor4Bez1Y = $y4;
        $coor4Bez2X = (round(0.45 * ($x4 - $x1))) + $x1;
        $coor4Bez2Y = $y4;

        $coIndex = $this->pdf->getContentObjectIndex();

        if ($this->clipping) {
            $bgColor = $this->pdf->getBackgroundColor();
            $this->setStrokeWidth(0);
            $this->setFillColor($bgColor[0], $bgColor[1], $bgColor[2]);
            $this->pdf->getObject($coIndex)->appendStream("\n{$x1} {$y1} m\n{$coor1Bez1X} {$coor1Bez1Y} {$coor2Bez1X} {$coor2Bez1Y} {$x2} {$y2} c\n{$coor2Bez2X} {$coor2Bez2Y} {$coor3Bez1X} {$coor3Bez1Y} {$x3} {$y3} c\n{$coor3Bez2X} {$coor3Bez2Y} {$coor4Bez1X} {$coor4Bez1Y} {$x4} {$y4} c\n{$coor4Bez2X} {$coor4Bez2Y} {$coor1Bez2X} {$coor1Bez2Y} {$x1} {$y1} c\nW\nF\n");
        } else {
            $this->pdf->getObject($coIndex)->appendStream("\n{$x1} {$y1} m\n{$coor1Bez1X} {$coor1Bez1Y} {$coor2Bez1X} {$coor2Bez1Y} {$x2} {$y2} c\n{$coor2Bez2X} {$coor2Bez2Y} {$coor3Bez1X} {$coor3Bez1Y} {$x3} {$y3} c\n{$coor3Bez2X} {$coor3Bez2Y} {$coor4Bez1X} {$coor4Bez1Y} {$x4} {$y4} c\n{$coor4Bez2X} {$coor4Bez2Y} {$coor1Bez2X} {$coor1Bez2Y} {$x1} {$y1} c\n" . $this->setStyle() . "\n");
        }

        return $this;
    }

    /**
     * Method to draw a circle to the PDF.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @return Pdf
     */
    public function circle($x, $y, $w)
    {
        $this->ellipse($x, $y, $w, $w);
        return $this;
    }

    /**
     * Method to draw an arc to the PDF.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $start
     * @param  int $end
     * @param  int $w
     * @param  int $h
     * @return Pdf
     */
    public function arc($x, $y, $start, $end, $w, $h = null)
    {
        if (null === $h) {
            $h = $w;
        }

        $sX = round($w * cos($start / 180 * pi()));
        $sY = round($h * sin($start / 180 * pi()));
        $eX = round($w * cos($end / 180 * pi()));
        $eY = round($h * sin($end / 180 * pi()));

        $centerPoint = ['x' => $x, 'y' => $y];
        $startPoint  = ['x' => $x + $sX, 'y' => $y - $sY];
        $endPoint    = ['x' => $x + $eX, 'y' => $y - $eY];

        $startQuad = $this->getQuadrant($startPoint, $centerPoint);
        $endQuad   = $this->getQuadrant($endPoint, $centerPoint);

        $maskPoint1 = ['x' => ($x + $w + 50), 'y' => ($y - $h - 50)];
        $maskPoint2 = ['x' => ($x - $w - 50), 'y' => ($y - $h - 50)];
        $maskPoint3 = ['x' => ($x - $w - 50), 'y' => ($y + $h + 50)];
        $maskPoint4 = ['x' => ($x + $w + 50), 'y' => ($y + $h + 50)];

        $polyPoints = [$centerPoint, $startPoint];

        switch ($startQuad) {
            case 1:
                $polyPoints[] = $maskPoint1;
                if ($endQuad == 1) {
                    $polyPoints[] = $maskPoint4;
                    $polyPoints[] = $maskPoint3;
                    $polyPoints[] = $maskPoint2;
                    $polyPoints[] = ['x' => $endPoint['x'], 'y' => $maskPoint2['y']];
                } else if ($endQuad == 2) {
                    $polyPoints[] = $maskPoint4;
                    $polyPoints[] = $maskPoint3;
                    $polyPoints[] = $maskPoint2;
                } else if ($endQuad == 3) {
                    $polyPoints[] = $maskPoint4;
                    $polyPoints[] = $maskPoint3;
                } else if ($endQuad == 4) {
                    $polyPoints[] = $maskPoint4;
                }
                break;

            case 2:
                $polyPoints[] = $maskPoint2;
                if ($endQuad == 2) {
                    $polyPoints[] = $maskPoint1;
                    $polyPoints[] = $maskPoint4;
                    $polyPoints[] = $maskPoint3;
                    $polyPoints[] = ['x' => $maskPoint3['x'], 'y' => $endPoint['y']];
                } else if ($endQuad == 3) {
                    $polyPoints[] = $maskPoint1;
                    $polyPoints[] = $maskPoint4;
                    $polyPoints[] = $maskPoint3;
                } else if ($endQuad == 4) {
                    $polyPoints[] = $maskPoint1;
                    $polyPoints[] = $maskPoint4;
                } else if ($endQuad == 1) {
                    $polyPoints[] = $maskPoint1;
                }

                break;
            case 3:
                $polyPoints[] = $maskPoint3;
                if ($endQuad == 3) {
                    $polyPoints[] = $maskPoint2;
                    $polyPoints[] = $maskPoint1;
                    $polyPoints[] = $maskPoint4;
                    $polyPoints[] = ['x' => $endPoint['x'], 'y' => $maskPoint4['y']];
                } else if ($endQuad == 4) {
                    $polyPoints[] = $maskPoint2;
                    $polyPoints[] = $maskPoint1;
                    $polyPoints[] = $maskPoint4;
                } else if ($endQuad == 1) {
                    $polyPoints[] = $maskPoint2;
                    $polyPoints[] = $maskPoint1;
                } else if ($endQuad == 2) {
                    $polyPoints[] = $maskPoint2;
                }

                break;
            case 4:
                $polyPoints[] = $maskPoint4;
                if ($endQuad == 4) {
                    $polyPoints[] = $maskPoint3;
                    $polyPoints[] = $maskPoint2;
                    $polyPoints[] = $maskPoint1;
                    $polyPoints[] = ['x' => $maskPoint1['x'], 'y' => $endPoint['y']];
                } else if ($endQuad == 1) {
                    $polyPoints[] = $maskPoint3;
                    $polyPoints[] = $maskPoint2;
                    $polyPoints[] = $maskPoint1;
                } else if ($endQuad == 2) {
                    $polyPoints[] = $maskPoint3;
                    $polyPoints[] = $maskPoint2;
                } else if ($endQuad == 3) {
                    $polyPoints[] = $maskPoint3;
                }

                break;
        }

        $polyPoints[] = $endPoint;

        $this->ellipse($x, $y, $w, $h);
        $this->clipping(true)->polygon($polyPoints);

        return $this;
    }

    /**
     * Method to draw a polygon to the image.
     *
     * @param  array $points
     * @return Pdf
     */
    public function polygon($points)
    {
        $i = 1;
        $polygon = null;

        foreach ($points as $coord) {
            if ($i == 1) {
                $polygon .= $coord['x'] . " " . $coord['y'] . " m\n";
            } else if ($i <= count($points)) {
                $polygon .= $coord['x'] . " " . $coord['y'] . " l\n";
            }
            $i++;
        }
        $polygon .= "h\n";

        $coIndex = $this->pdf->getContentObjectIndex();

        if ($this->clipping) {
            $bgColor = $this->pdf->getBackgroundColor();
            $this->setStrokeWidth(0);
            $this->setFillColor($bgColor[0], $bgColor[1], $bgColor[2]);
            $this->pdf->getObject($coIndex)->appendStream("\n{$polygon}\nW\nF\n");
        } else {
            $this->pdf->getObject($coIndex)->appendStream("\n{$polygon}\n" . $this->setStyle() . "\n");
        }

        return $this;
    }

}