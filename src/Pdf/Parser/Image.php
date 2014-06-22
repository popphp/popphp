<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Pdf\Parser;

use Pop\Image\Gd;
use Pop\Image\Imagick;
use Pop\Pdf\Object\Object;

/**
 * Image parser class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Image
{

    /**
     * Image object
     * @var mixed
     */
    protected $img = 0;

    /**
     * Image X Coordinate
     * @var array
     */
    protected $x = 0;

    /**
     * Image Y Coordinate
     * @var array
     */
    protected $y = 0;

    /**
     * PDF next index
     * @var array
     */
    protected $index = 0;

    /**
     * Image objects
     * @var array
     */
    protected $objects = array();

    /**
     * XObject string
     * @var string
     */
    protected $xobject = null;

    /**
     * Stream string
     * @var string
     */
    protected $stream = null;

    /**
     * Image data
     * @var string
     */
    protected $imageData = null;

    /**
     * Image original width
     * @var int
     */
    protected $origW = null;

    /**
     * Image original height
     * @var int
     */
    protected $origH = null;

    /**
     * Image data length
     * @var int
     */
    protected $imageDataLength = null;

    /**
     * Scaled image path
     * @var string
     */
    protected $scaledImage = null;

    /**
     * Converted image path
     * @var string
     */
    protected $convertedImage = null;

    /**
     * Constructor
     *
     * Instantiate a image parser object to be used by Pop_Pdf.
     *
     * @param  string  $img
     * @param  int     $x
     * @param  int     $y
     * @param  int     $i
     * @param  mixed   $scl
     * @param  boolean $preserveRes
     * @throws Exception
     * @return \Pop\Pdf\Parser\Image
     */
    public function __construct($img, $x, $y, $i, $scl = null, $preserveRes = false)
    {
        $this->x = $x;
        $this->y = $y;
        $this->index = $i;

        $allowed = array(
            'gif'  => 'image/gif',
            'jpe'  => 'image/jpeg',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png'
        );

        $this->img = new Gd($img);

        // If a scale value is passed, scale the image.
        if (null !== $scl) {
            if ($preserveRes) {
                $dims = self::getScaledDimensions($scl, $this->img->getWidth(), $this->img->getHeight());
                $imgWidth = $dims['w'];
                $imgHeight = $dims['h'];
            } else {
                $this->scaleImage($scl);
                $imgWidth = $this->img->getWidth();
                $imgHeight = $this->img->getHeight();
            }
        } else {
            $imgWidth = $this->img->getWidth();
            $imgHeight = $this->img->getHeight();
        }

        // Set the initial image data and data length.
        $this->imageData = $this->img->read();
        $this->imageDataLength = strlen($this->imageData);

        // If a JPEG, parse the JPEG
        if ($this->img->getMime() == 'image/jpeg') {
            $this->parseJpeg();
        // Else parse the PNG or GIF.
        } else if (($this->img->getMime() == 'image/png') || ($this->img->getMime() == 'image/gif')) {
            // If the image is a GIF, convert to a PNG and re-read image data.
            if ($this->img->getMime() == 'image/gif') {
                $this->convertImage();
            }
            $this->parsePng();
        } else {
            throw new Exception('Error: That image type is not supported. Only GIF, JPG and PNG image types are supported.');
        }

        // Get the image original dimensions
        $this->origW = $this->img->getWidth();
        $this->origH = $this->img->getHeight();

        // Define the xobject object and stream.
        $this->xobject = "/I{$this->index} {$this->index} 0 R";
        $this->stream = "\nq\n" . $imgWidth . " 0 0 " . $imgHeight. " {$this->x} {$this->y} cm\n/I{$this->index} Do\nQ\n";

        // Image clean-up.
        if ((null !== $this->scaledImage) && file_exists($this->scaledImage)) {
            unlink($this->scaledImage);
        }
        if ((null !== $this->convertedImage) && file_exists($this->convertedImage)) {
            unlink($this->convertedImage);
        }
    }

    /**
     * Method to get the image objects.
     *
     * @return array
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * Method to get the xobject string.
     *
     * @return string
     */
    public function getXObject()
    {
        return $this->xobject;
    }

    /**
     * Method to get the stream string.
     *
     * @return string
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Method to get the original image width.
     *
     * @return string
     */
    public function getOrigW()
    {
        return $this->origW;
    }

    /**
     * Method to get the original image height.
     *
     * @return string
     */
    public function getOrigH()
    {
        return $this->origH;
    }

    /**
     * Method to get scaled dimensions of the image, while preserving the resolution.
     *
     * @param  mixed $scl
     * @param  int   $origW
     * @param  int   $origH
     * @throws Exception
     * @return array
     */
    public static function getScaledDimensions($scl, $origW, $origH)
    {
        // Scale or resize the image
        if (is_array($scl) && (isset($scl['w']) || isset($scl['h']))) {
            if (isset($scl['w'])) {
                $wid = $scl['w'];
                $scale = $wid / $origW;
                $hgt = round($origH * $scale);
            } else if (isset($scl['h'])) {
                $hgt = $scl['h'];
                $scale = $hgt / $origH;
                $wid = round($origW * $scale);
            }
        } else if (is_float($scl)) {
            $wid = round($origW * $scl);
            $hgt = round($origH * $scl);
        } else if (is_int($scl)) {
            $scale = ($origW > $origH) ? ($scl / $origW) : ($scl / $origH);
            $wid = round($origW * $scale);
            $hgt = round($origH * $scale);
        } else {
            throw new Exception('Error: The image scale value is not valid.');
        }

        $dims = array('w' => $wid, 'h' => $hgt);

        return $dims;
    }

    /**
     * Method to scale or resize the image.
     *
     * @param mixed $scl
     * @throws Exception
     * @return void
     */
    protected function scaleImage($scl)
    {
        // Define the temp scaled image.
        $this->scaledImage = \Pop\File\Dir::getUploadTemp() . DIRECTORY_SEPARATOR . $this->img->getFilename() . '_' . time() . '.' . $this->img->getExt();

        // Scale or resize the image
        if (is_array($scl) && (isset($scl['w']) || isset($scl['h']))) {
            if (isset($scl['w'])) {
                $this->img->resizeToWidth($scl['w']);
            } else if (isset($scl['h'])) {
                $this->img->resizeToHeight($scl['h']);
            }
        } else if (is_float($scl)) {
            $this->img->scale($scl);
        } else if (is_int($scl)) {
            $this->img->resize($scl);
        } else {
            throw new Exception('Error: The image scale value is not valid.');
        }

        // Save and clear the output buffer.
        if (($this->img->getMime() == 'image/jpeg') || (($this->img->getMime() == 'image/png') && ($this->img->getColorMode() == 'RGB'))) {
            $this->img->setQuality(90);
        }
        $this->img->save($this->scaledImage);

        // Re-instantiate the newly scaled image object.
        $this->img = new Gd($this->scaledImage);
    }

    /**
     * Method to convert the image.
     *
     * @return void
     */
    protected function convertImage()
    {
        // Define the temp converted image.
        $this->convertedImage = \Pop\File\Dir::getUploadTemp() . DIRECTORY_SEPARATOR . $this->img->getFilename() . '_' . time() . '.png';

        // Convert the GIF to PNG, save and clear the output buffer.
        $this->img->convert('png')->save($this->convertedImage);

        // Re-instantiate the newly converted image object and re-read the image data.
        $this->img = new Gd($this->convertedImage);
        $this->imageData = $this->img->read();
    }

    /**
     * Method to parse the JPEG image.
     *
     * @return void
     */
    protected function parseJpeg()
    {
        // Add the image to the _objects array.
        $colorMode  = (strtolower($this->img->getColorMode()) == 'srgb') ? 'RGB' : $this->img->getColorMode();
        $colorspace = ($this->img->getColorMode() == 'CMYK') ? "/DeviceCMYK\n    /Decode [1 0 1 0 1 0 1 0]" : "/Device" . $colorMode;
        $this->objects[$this->index] = new Object("{$this->index} 0 obj\n<<\n    /Type /XObject\n    /Subtype /Image\n    /Width " . $this->img->getWidth() . "\n    /Height " . $this->img->getHeight() . "\n    /ColorSpace {$colorspace}\n    /BitsPerComponent 8\n    /Filter /DCTDecode\n    /Length {$this->imageDataLength}\n>>\nstream\n{$this->imageData}\nendstream\nendobj\n");
    }

    /**
     * Method to parse the PNG image.
     *
     * @throws Exception
     * @return void
     */
    protected function parsePng()
    {
        // Define some PNG image-specific variables.
        $PLTE = null;
        $TRNS = null;
        $maskIndex = null;
        $mask = null;

        // Determine the PNG colorspace.
        if ($this->img->getColorMode() == 'Gray') {
            $colorspace = '/DeviceGray';
            $numOfColors = 1;
        } else if (stripos($this->img->getColorMode(), 'RGB') !== false) {
            $colorspace = '/DeviceRGB';
            $numOfColors = 3;
        } else if ($this->img->getColorMode() == 'Indexed') {
            $colorspace = '/Indexed';
            $numOfColors = 1;

            // If the PNG is indexed, parse and read the palette and any transparencies that might exist.
            if (strpos($this->imageData, 'PLTE') !== false) {
                $lenByte = substr($this->imageData, (strpos($this->imageData, "PLTE") - 4), 4);
                $palLength = $this->readInt($lenByte);
                $PLTE = substr($this->imageData, (strpos($this->imageData, "PLTE") + 4), $palLength);
                $mask = null;

                // If a transparency exists, parse it and set the mask accordindly, along with the palette.
                if (strpos($this->imageData, 'tRNS') !== false) {
                    $lenByte = substr($this->imageData, (strpos($this->imageData, "tRNS") - 4), 4);
                    $TRNS = substr($this->imageData, (strpos($this->imageData, "tRNS") + 4), $this->readInt($lenByte));
                    $maskIndex = strpos($TRNS, chr(0));
                    $mask = "    /Mask [" . $maskIndex . " " . $maskIndex . "]\n";
                }
            }

            $colorspace = "[/Indexed /DeviceRGB " . ($this->img->colorTotal() - 1) . " " . ($this->index + 1) . " 0 R]";
        }

        // Parse header data, bits and color type
        $lenByte = substr($this->imageData, (strpos($this->imageData, "IHDR") - 4), 4);
        $header = substr($this->imageData, (strpos($this->imageData, "IHDR") + 4), $this->readInt($lenByte));
        $bits = ord(substr($header, 8, 1));
        $colorType = ord(substr($header, 9, 1));

        // Make sure the PNG does not contain a true alpha channel.
        if (($colorType >= 4) && ((bits == 8) || ($bits == 16))) {
            throw new Exception('Error: PNG alpha channels are not supported. Only 8-bit transparent PNG images are supported.');
        }

        // Parse and set the PNG image data and data length.
        $lenByte = substr($this->imageData, (strpos($this->imageData, "IDAT") - 4), 4);
        $this->imageDataLength = $this->readInt($lenByte);
        $IDAT = substr($this->imageData, (strpos($this->imageData, "IDAT") + 4), $this->imageDataLength);

        // Add the image to the _objects array.
        $this->objects[$this->index] = new Object("{$this->index} 0 obj\n<<\n    /Type /XObject\n    /Subtype /Image\n    /Width " . $this->img->getWidth() . "\n    /Height " . $this->img->getHeight() . "\n    /ColorSpace {$colorspace}\n    /BitsPerComponent " . $bits . "\n    /Filter /FlateDecode\n    /DecodeParms <</Predictor 15 /Colors {$numOfColors} /BitsPerComponent " . $bits . " /Columns " . $this->img->getWidth() . ">>\n{$mask}    /Length {$this->imageDataLength}\n>>\nstream\n{$IDAT}\nendstream\nendobj\n");

        // If it exists, add the image palette to the _objects array.
        if ($PLTE != '') {
            $j = $this->index + 1;
            $this->objects[$j] = new Object("{$j} 0 obj\n<<\n    /Length " . $palLength . "\n>>\nstream\n{$PLTE}\nendstream\nendobj\n");
            $this->objects[$j]->setPalette(true);
        }
    }

    /**
     * Method to read an unsigned integer.
     *
     * @param  string $data
     * @return int
     */
    protected function readInt($data)
    {
        $ary = unpack('Nlength', $data);
        return $ary['length'];
    }

}