<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Image\Draw;

/**
 * Image class
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Svg extends AbstractDraw
{

    /**
     * Opacity
     * @var float
     */
    protected $opacity = 1.0;

    /**
     * Stroke dash length
     * @var int
     */
    protected $strokeDashLength = null;

    /**
     * Stroke dash gap
     * @var int
     */
    protected $strokeDashGap = null;

    /**
     * SVG image available clipping paths
     * @var array
     */
    protected $clippingPaths = [];

    /**
     * Current clipping path to use.
     * @var int
     */
    protected $curClippingPath = null;

    /**
     * Current clipping object.
     * @var int
     */
    protected $clippingObject = null;

    /**
     * Set the opacity
     *
     * @param  float $opacity
     * @return Svg
     */
    public function setOpacity($opacity)
    {
        $this->opacity = $opacity;
        return $this;
    }

    /**
     * Set fill color
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return Svg
     */
    public function setFillColor($r, $g, $b)
    {
        $this->fillColor = [(int)$r, (int)$g, (int)$b];
        return $this;
    }

    /**
     * Set stroke color
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return Svg
     */
    public function setStrokeColor($r, $g, $b)
    {
        $this->strokeColor = [(int)$r, (int)$g, (int)$b];
        return $this;
    }

    /**
     * Get stroke width
     *
     * @param int $w
     * @param int $dashLength
     * @param int $dashGap
     * @return Svg
     */
    public function setStrokeWidth($w, $dashLength = null, $dashGap = null)
    {
        parent::setStrokeWidth($w);
        $this->strokeDashLength = $dashLength;
        $this->strokeDashGap    = $dashGap;
        return $this;
    }

    /**
     * Add a clipping path
     *
     * @param  int $path
     * @return Svg
     */
    public function addClippingPath($path)
    {
        $this->clippingPaths[] = $path;
        return $this;
    }

    /**
     * Get the clipping paths
     *
     * @return array
     */
    public function getClippingPaths()
    {
        return $this->clippingPaths;
    }

    /**
     * Get the number of clipping paths
     *
     * @return int
     */
    public function getNumberOfClippingPaths()
    {
        return count($this->clippingPaths);
    }

    /**
     * Get the current clipping path index
     *
     * @return mixed
     */
    public function getCurClippingPath()
    {
        return $this->curClippingPath;
    }

    /**
     * Get the current clipping path index
     *
     * @param  mixed $path
     * @return Svg
     */
    public function setCurClippingPath($path)
    {
        if (in_array($path, $this->clippingPaths) || (null === $path)) {
            $this->curClippingPath = $path;
        }
        return $this;
    }

    /**
     * Add a clipping path
     *
     * @return Svg
     */
    public function clipping()
    {
        $this->curClippingPath = count($this->clippingPaths);
        $this->clippingPaths[] = $this->curClippingPath;
        $defs = $this->image->resource()->addChild('defs');

        $this->clippingObject = $defs->addChild('clipPath');
        $this->clippingObject->addAttribute('id', 'clip' . $this->curClippingPath);

        return $this;
    }

    /**
     * Add a clipping path
     *
     * @return Svg
     */
    public function clearClipping()
    {
        $this->curClippingPath = null;
        return $this;
    }

    /**
     * Method to draw a line to the image.
     *
     * @param  int $x1
     * @param  int $y1
     * @param  int $x2
     * @param  int $y2
     * @return Svg
     */
    public function line($x1, $y1, $x2, $y2)
    {
        $line = $this->image->resource()->addChild('line');
        $line->addAttribute('x1', $x1 . $this->image->getUnits());
        $line->addAttribute('y1', $y1 . $this->image->getUnits());
        $line->addAttribute('x2', $x2 . $this->image->getUnits());
        $line->addAttribute('y2', $y2 . $this->image->getUnits());

        $line = $this->setStyles($line);

        return $this;
    }

    /**
     * Method to draw a rectangle to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return Svg
     */
    public function rectangle($x, $y, $w, $h = null)
    {
        if (null !== $this->clippingObject) {
            $rect = $this->clippingObject->addChild('rect');
        } else {
            $rect = $this->image->resource()->addChild('rect');
        }

        $rect->addAttribute('x', $x . $this->image->getUnits());
        $rect->addAttribute('y', $y . $this->image->getUnits());
        $rect->addAttribute('width', $w . $this->image->getUnits());
        $rect->addAttribute('height', ((null === $h) ? $w : $h) . $this->image->getUnits());

        if (null === $this->clippingObject) {
            $rect = $this->setStyles($rect);
        } else {
            $this->clippingObject = null;
        }

        return $this;
    }

    /**
     * Method to draw a rectangle to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @return Svg
     */
    public function square($x, $y, $w)
    {
        return $this->rectangle($x, $y, $w, $w);
    }

    /**
     * Method to draw a rectangle to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @param  int $rx
     * @param  int $ry
     * @return Svg
     */
    public function roundRectangle($x, $y, $w, $h = null, $rx = 10, $ry = null)
    {
        if (null !== $this->clippingObject) {
            $rect = $this->clippingObject->addChild('rect');
        } else {
            $rect = $this->image->resource()->addChild('rect');
        }

        $rect->addAttribute('x', $x . $this->image->getUnits());
        $rect->addAttribute('y', $y . $this->image->getUnits());
        $rect->addAttribute('rx', $rx . $this->image->getUnits());
        $rect->addAttribute('ry', ((null === $ry) ? $rx : $ry) . $this->image->getUnits());
        $rect->addAttribute('width', $w . $this->image->getUnits());
        $rect->addAttribute('height', ((null === $h) ? $w : $h) . $this->image->getUnits());

        if (null === $this->clippingObject) {
            $rect = $this->setStyles($rect);
        } else {
            $this->clippingObject = null;
        }

        return $this;
    }

    /**
     * Method to draw a rectangle to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $rx
     * @param  int $ry
     * @return Svg
     */
    public function roundSquare($x, $y, $w, $rx = 10, $ry = null)
    {
        return $this->roundRectangle($x, $y, $w, $w, $rx, $ry);
    }

    /**
     * Method to add an ellipse to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return Svg
     */
    public function ellipse($x, $y, $w, $h = null)
    {
        if (null !== $this->clippingObject) {
            $ellipse = $this->clippingObject->addChild('ellipse');
        } else {
            $ellipse = $this->image->resource()->addChild('ellipse');
        }
        $ellipse->addAttribute('cx', $x . $this->image->getUnits());
        $ellipse->addAttribute('cy', $y . $this->image->getUnits());
        $ellipse->addAttribute('rx', $w . $this->image->getUnits());
        $ellipse->addAttribute('ry', ((null === $h) ? $w : $h) . $this->image->getUnits());

        if (null === $this->clippingObject) {
            $ellipse = $this->setStyles($ellipse);
        } else {
            $this->clippingObject = null;
        }

        return $this;
    }

    /**
     * Method to add a circle to the image.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @return Svg
     */
    public function circle($x, $y, $w)
    {
        if (null !== $this->clippingObject) {
            $circle = $this->clippingObject->addChild('circle');
        } else {
            $circle = $this->image->resource()->addChild('circle');
        }

        $circle->addAttribute('cx', $x . $this->image->getUnits());
        $circle->addAttribute('cy', $y . $this->image->getUnits());
        $circle->addAttribute('r', $w . $this->image->getUnits());

        if (null === $this->clippingObject) {
            $circle = $this->setStyles($circle);
        } else {
            $this->clippingObject = null;
        }

        return $this;
    }


    /**
     * Method to add an arc to the image.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $start
     * @param  int $end
     * @param  int $w
     * @param  int $h
     * @return Svg
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
        $startPoint = ['x' => $x + $sX, 'y' => $y + $sY];
        $endPoint = ['x' => $x + $eX, 'y' => $y + $eY];

        $startQuad = $this->getQuadrant($startPoint, $centerPoint);
        $endQuad = $this->getQuadrant($endPoint, $centerPoint);

        $corner1 = ['x' => $this->image->getWidth(), 'y' => $this->image->getHeight()];
        $corner2 = ['x' => 0, 'y' => $this->image->getHeight()];
        $corner3 = ['x' => 0, 'y' => 0];
        $corner4 = ['x' => $this->image->getWidth(), 'y' => 0];

        $polyPoints = [$centerPoint, $startPoint];

        switch ($startQuad) {
            case 1:
                if ($endQuad == 1) {
                    $polyPoints[] = $corner1;
                    $polyPoints[] = ['x' => $endPoint['x'], 'y' => $this->image->getHeight()];
                } else if ($endQuad == 2) {
                    $polyPoints[] = $corner1;
                    $polyPoints[] = $corner2;
                } else if ($endQuad == 3) {
                    $polyPoints[] = $corner1;
                    $polyPoints[] = $corner2;
                    $polyPoints[] = $corner3;
                } else if ($endQuad == 4) {
                    $polyPoints[] = $corner1;
                    $polyPoints[] = $corner2;
                    $polyPoints[] = $corner3;
                    $polyPoints[] = $corner4;
                }
                break;
            case 2:
                if ($endQuad == 1) {
                    $polyPoints[] = $corner2;
                    $polyPoints[] = $corner3;
                    $polyPoints[] = $corner4;
                    $polyPoints[] = $corner1;
                } else if ($endQuad == 2) {
                    $polyPoints[] = $corner2;
                    $polyPoints[] = ['x' => 0, 'y' => $endPoint['y']];
                } else if ($endQuad == 3) {
                    $polyPoints[] = $corner2;
                    $polyPoints[] = $corner3;
                } else if ($endQuad == 4) {
                    $polyPoints[] = $corner2;
                    $polyPoints[] = $corner3;
                    $polyPoints[] = $corner4;
                }
                break;
            case 3:
                if ($endQuad == 1) {
                    $polyPoints[] = $corner3;
                    $polyPoints[] = $corner4;
                    $polyPoints[] = $corner1;
                } else if ($endQuad == 2) {
                    $polyPoints[] = $corner3;
                    $polyPoints[] = $corner4;
                    $polyPoints[] = $corner1;
                    $polyPoints[] = $corner2;
                } else if ($endQuad == 3) {
                    $polyPoints[] = $corner3;
                    $polyPoints[] = ['x' => $endPoint['x'], 'y' => 0];
                } else if ($endQuad == 4) {
                    $polyPoints[] = $corner3;
                    $polyPoints[] = $corner4;
                }
                break;
            case 4:
                if ($endQuad == 1) {
                    $polyPoints[] = $corner4;
                    $polyPoints[] = $corner1;
                } else if ($endQuad == 2) {
                    $polyPoints[] = $corner4;
                    $polyPoints[] = $corner1;
                    $polyPoints[] = $corner2;
                } else if ($endQuad == 3) {
                    $polyPoints[] = $corner4;
                    $polyPoints[] = $corner1;
                    $polyPoints[] = $corner2;
                    $polyPoints[] = $corner3;
                } else if ($endQuad == 4) {
                    $polyPoints[] = $corner4;
                    $polyPoints[] = ['x' => $this->image->getWidth(), 'y' => $endPoint['y']];
                }
                break;
        }

        $polyPoints[] = $endPoint;

        $stamp = rand();

        $defs = $this->image->resource()->addChild('defs');

        $clip = $defs->addChild('clipPath');
        $clip->addAttribute('id', 'polyClip' . $stamp);

        $formattedPoints = [];
        foreach ($polyPoints as $point) {
            $formattedPoints[] = $point['x'] . ',' . $point['y'];
        }
        $poly = $clip->addChild('polygon');
        $poly->addAttribute('points', implode(' ', $formattedPoints));

        $ellipse = $this->image->resource()->addChild('ellipse');
        $ellipse->addAttribute('style', 'clip-path: url(#polyClip' . $stamp .');');
        $ellipse->addAttribute('cx', $x . $this->image->getUnits());
        $ellipse->addAttribute('cy', $y . $this->image->getUnits());
        $ellipse->addAttribute('rx', $w . $this->image->getUnits());
        $ellipse->addAttribute('ry', ((null === $h) ? $w : $h) . $this->image->getUnits());

        $ellipse = $this->setStyles($ellipse);

        return $this;
    }

    /**
     * Method to draw a polygon to the image.
     *
     * @param  array $points
     * @return Svg
     */
    public function polygon($points)
    {
        $formattedPoints = [];
        foreach ($points as $point) {
            $formattedPoints[] = $point['x'] . ',' . $point['y'];
        }

        if (null !== $this->clippingObject) {
            $poly = $this->clippingObject->addChild('polygon');
        } else {
            $poly = $this->image->resource()->addChild('polygon');
        }
        $poly->addAttribute('points', implode(' ', $formattedPoints));

        if (null === $this->clippingObject) {
            $poly = $this->setStyles($poly);
        } else {
            $this->clippingObject = null;
        }

        return $this;
    }

    /**
     * Method to set the styles.
     *
     * @param  \SimpleXMLElement $obj
     * @return \SimpleXMLElement
     */
    protected function setStyles(\SimpleXMLElement $obj)
    {
        if (null !== $this->curClippingPath) {
            $obj->addAttribute('style', 'clip-path: url(#clip' . $this->curClippingPath .');');
        }

        if (null !== $this->image->getCurGradient()) {
            $obj->addAttribute('fill', 'url(#grad' . $this->image->getCurGradient() . ')');
        } else if (null !== $this->fillColor) {
            $obj->addAttribute('fill', 'rgb(' . $this->fillColor[0] . ',' . $this->fillColor[1] . ',' . $this->fillColor[2] . ')');
            if ($this->opacity < 1.0) {
                $obj->addAttribute('fill-opacity', $this->opacity);
            }
        } else {
            $obj->addAttribute('fill', 'none');
        }
        if ($this->strokeWidth > 0) {
            $obj->addAttribute('stroke', 'rgb(' . $this->strokeColor[0] . ',' . $this->strokeColor[1] . ',' . $this->strokeColor[2] . ')');
            $obj->addAttribute('stroke-width', $this->strokeWidth . $this->image->getUnits());
            if ((null !== $this->strokeDashLength) && (null !== $this->strokeDashGap)) {
                $obj->addAttribute('stroke-dasharray', $this->strokeDashLength . $this->image->getUnits() . ',' . $this->strokeDashGap . $this->image->getUnits());
            }
        }

        return $obj;
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
        $quad = 0;

        if ($point['x'] >= $center['x']) {
            $quad = ($point['y'] <= $center['y']) ? 4 : 1;
        } else {
            $quad = ($point['y'] <= $center['y']) ? 3 : 2;
        }

        return $quad;
    }

}
