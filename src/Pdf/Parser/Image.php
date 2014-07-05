<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
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
     * @var array
     */
    protected $img = [
        'width'      => 0,
        'height'     => 0,
        'mime'       => null,
        'colormode'  => null,
        'channels'   => 0,
        'depth'      => 0,
        'filename'   => null,
        'extension'  => null,
        'fullpath'   => null,
        'colortotal' => 0,
        'alpha'      => false
    ];

    /**
     * Image resource
     * @var resource
     */
    protected $resource = null;

    /**
     * Image file output buffer
     * @var mixed
     */
    protected $output = null;

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
    protected $objects = [];

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
        $this->x     = $x;
        $this->y     = $y;
        $this->index = $i;

        $this->setImage($img);

        // If a scale value is passed, scale the image.
        if (null !== $scl) {
            if ($preserveRes) {
                $dims      = self::getScaledDimensions($scl, $this->img['width'], $this->img['height']);
                $imgWidth  = $dims['w'];
                $imgHeight = $dims['h'];
            } else {
                $this->scaleImage($scl);
                $imgWidth  = $this->img['width'];
                $imgHeight = $this->img['height'];
            }
        } else {
            $imgWidth  = $this->img['width'];
            $imgHeight = $this->img['height'];
        }

        // Reset the initial image data and data length.
        $this->imageData       = file_get_contents($this->img['fullpath']);
        $this->imageDataLength = strlen($this->imageData);

        // If a JPEG, parse the JPEG
        if ($this->img['mime'] == 'image/jpeg') {
            $this->parseJpeg();
        // Else parse the PNG or GIF.
        } else if (($this->img['mime'] == 'image/png') || ($this->img['mime'] == 'image/gif')) {
            // If the image is a GIF, convert to a PNG and re-read image data.
            if ($this->img['mime'] == 'image/gif') {
                $this->convertImage();
            }
            $this->parsePng();
        } else {
            throw new Exception('Error: That image type is not supported. Only GIF, JPG and PNG image types are supported.');
        }

        // Get the image original dimensions
        $this->origW = $this->img['width'];
        $this->origH = $this->img['height'];

        // Define the xobject object and stream.
        $this->xobject = "/I{$this->index} {$this->index} 0 R";
        $this->stream  = "\nq\n" . $imgWidth . " 0 0 " . $imgHeight. " {$this->x} {$this->y} cm\n/I{$this->index} Do\nQ\n";

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

        $dims = ['w' => $wid, 'h' => $hgt];

        return $dims;
    }

    /**
     * Method to parse and set initial image properties and settings
     *
     * @param  string $img
     * @return void
     */
    protected function setImage($img)
    {
        $parts                  = pathinfo($img);
        $this->img['fullpath']  = realpath($img);
        $this->img['filename']  = $parts['filename'];
        $this->img['extension'] = (isset($parts['extension']) && ($parts['extension'] != '')) ? $parts['extension'] : null;

        $imgSize = getimagesize($this->img['fullpath']);

        // Set image properties.
        $this->img['width']      = $imgSize[0];
        $this->img['height']     = $imgSize[1];
        $this->img['channels']   = (isset($imgSize['channels'])) ? $imgSize['channels'] : null;
        $this->img['depth']      = (isset($imgSize['bits'])) ? $imgSize['bits'] : null;

        $this->imageData       = file_get_contents($this->img['fullpath']);
        $this->imageDataLength = strlen($this->imageData);

        if (stripos($this->img['extension'], 'jp') !== false) {
            $this->img['mime'] = 'image/jpeg';
            switch ($this->img['channels']) {
                case 1:
                    $this->img['colormode'] = 'Gray';
                    break;
                case 3:
                    $this->img['colormode'] = 'RGB';
                    break;
                case 4:
                    $this->img['colormode'] = 'CMYK';
                    break;
            }
        } else if (strtolower($this->img['extension']) == 'gif') {
            $this->img['mime']     = 'image/gif';
            $this->img['colormode'] = 'Indexed';
        } else if (strtolower($this->img['extension']) == 'png') {
            $this->img['mime'] = 'image/png';
            $colorType = ord($this->imageData[25]);
            switch ($colorType) {
                case 0:
                    $this->img['channels']  = 1;
                    $this->img['colormode'] = 'Gray';
                    break;
                case 2:
                    $this->img['channels']  = 3;
                    $this->img['colormode'] = 'RGB';
                    break;
                case 3:
                    $this->img['channels']  = 3;
                    $this->img['colormode'] = 'Indexed';
                    break;
                case 4:
                    $this->img['channels']  = 1;
                    $this->img['colormode'] = 'Gray';
                    $this->img['alpha']     = true;
                    break;
                case 6:
                    $this->img['channels']  = 3;
                    $this->img['colormode'] = 'RGB';
                    $this->img['alpha']     = true;
                    break;
            }
        }
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
        $this->scaledImage = realpath(sys_get_temp_dir()) . DIRECTORY_SEPARATOR . $this->img['filename'] . '_' . time() . '.' . $this->img->getExtension();

        // Scale or resize the image
        if (is_array($scl) && (isset($scl['w']) || isset($scl['h']))) {
            if (isset($scl['w'])) {
                $this->resizeToWidth($scl['w']);
            } else if (isset($scl['h'])) {
                $this->resizeToHeight($scl['h']);
            }
        } else if (is_float($scl)) {
            $this->scale($scl);
        } else if (is_int($scl)) {
            $this->resize($scl);
        } else {
            throw new Exception('Error: The image scale value is not valid.');
        }

        $this->save($this->scaledImage);

        // Re-initialize the newly scaled image.
        $this->setImage($this->scaledImage);
        $this->imageData = file_get_contents($this->img['fullpath']);
    }

    /**
     * Method to convert the image.
     *
     * @return void
     */
    protected function convertImage()
    {
        // Define the temp converted image.
        $this->convertedImage = realpath(sys_get_temp_dir()) . DIRECTORY_SEPARATOR . $this->img['filename'] . '_' . time() . '.png';

        // Convert the GIF to PNG, save and clear the output buffer.
        $this->createResource();
        imageinterlace($this->resource, 0);

        // Change the type of the image object to the new,
        // requested image type.
        $this->img['extension'] = 'png';
        $this->img['mime']      = 'image/png';

        // Redefine the image object properties with the new values.
        $this->img['fullpath'] = $this->convertedImage;
        $this->img['filename'] = basename($this->convertedImage, '.png');


        $this->save($this->convertedImage);

        // Re-initialize the newly converted image.
        $this->setImage($this->convertedImage);
        $this->imageData = file_get_contents($this->img['fullpath']);
    }

    /**
     * Method to parse the JPEG image.
     *
     * @return void
     */
    protected function parseJpeg()
    {
        // Add the image to the _objects array.
        $colorMode  = (strtolower($this->img['colormode']) == 'srgb') ? 'RGB' : $this->img['colormode'];
        $colorspace = ($this->img['colormode'] == 'CMYK') ? "/DeviceCMYK\n    /Decode [1 0 1 0 1 0 1 0]" : "/Device" . $colorMode;
        $this->objects[$this->index] = new Object("{$this->index} 0 obj\n<<\n    /Type /XObject\n    /Subtype /Image\n    /Width " . $this->img['width'] . "\n    /Height " . $this->img['height'] . "\n    /ColorSpace {$colorspace}\n    /BitsPerComponent 8\n    /Filter /DCTDecode\n    /Length {$this->imageDataLength}\n>>\nstream\n{$this->imageData}\nendstream\nendobj\n");
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
        $PLTE      = null;
        $TRNS      = null;
        $maskIndex = null;
        $mask      = null;

        // Determine the PNG colorspace.
        if ($this->img['colormode'] == 'Gray') {
            $colorspace = '/DeviceGray';
            $numOfColors = 1;
        } else if (stripos($this->img['colormode'], 'RGB') !== false) {
            $colorspace = '/DeviceRGB';
            $numOfColors = 3;
        } else if ($this->img['colormode'] == 'Indexed') {
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

            $this->img['colortotal'] = imagecolorstotal($this->resource);
            $colorspace = "[/Indexed /DeviceRGB " . ($this->img['colortotal'] - 1) . " " . ($this->index + 1) . " 0 R]";
        }

        // Parse header data, bits and color type
        $lenByte   = substr($this->imageData, (strpos($this->imageData, "IHDR") - 4), 4);
        $header    = substr($this->imageData, (strpos($this->imageData, "IHDR") + 4), $this->readInt($lenByte));
        $bits      = ord(substr($header, 8, 1));
        $colorType = ord(substr($header, 9, 1));

        // Make sure the PNG does not contain a true alpha channel.
        if (($colorType >= 4) && (($bits == 8) || ($bits == 16))) {
            throw new Exception('Error: PNG alpha channels are not supported. Only 8-bit transparent PNG images are supported.');
        }

        // Parse and set the PNG image data and data length.
        $lenByte = substr($this->imageData, (strpos($this->imageData, "IDAT") - 4), 4);
        $this->imageDataLength = $this->readInt($lenByte);
        $IDAT = substr($this->imageData, (strpos($this->imageData, "IDAT") + 4), $this->imageDataLength);

        // Add the image to the _objects array.
        $this->objects[$this->index] = new Object("{$this->index} 0 obj\n<<\n    /Type /XObject\n    /Subtype /Image\n    /Width " . $this->img['width'] . "\n    /Height " . $this->img['height'] . "\n    /ColorSpace {$colorspace}\n    /BitsPerComponent " . $bits . "\n    /Filter /FlateDecode\n    /DecodeParms <</Predictor 15 /Colors {$numOfColors} /BitsPerComponent " . $bits . " /Columns " . $this->img['width'] . ">>\n{$mask}    /Length {$this->imageDataLength}\n>>\nstream\n{$IDAT}\nendstream\nendobj\n");

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


    /**
     * Resize the image object to the width parameter passed.
     *
     * @param  int $wid
     * @return Image
     */
    public function resizeToWidth($wid)
    {
        $scale = $wid / $this->img['width'];
        $hgt = round($this->img['height'] * $scale);

        // Create a new image output resource.
        $this->createResource();
        $this->output = imagecreatetruecolor($wid, $hgt);

        // Copy newly sized image to the output resource.
        $this->copyImage($wid, $hgt);

        return $this;
    }

    /**
     * Resize the image object to the height parameter passed.
     *
     * @param  int $hgt
     * @return Image
     */
    protected function resizeToHeight($hgt)
    {
        $scale = $hgt / $this->img['height'];
        $wid = round($this->img['width'] * $scale);

        // Create a new image output resource.
        $this->createResource();
        $this->output = imagecreatetruecolor($wid, $hgt);

        // Copy newly sized image to the output resource.
        $this->copyImage($wid, $hgt);

        return $this;
    }

    /**
     * Resize the image object to the largest dimension
     *
     * @param  int $px
     * @return Image
     */
    protected function resize($px)
    {
        $scale = ($this->img['width'] > $this->img['height']) ? ($px / $this->img['width']) : ($px / $this->img['height']);

        $wid = round($this->img['width'] * $scale);
        $hgt = round($this->img['height'] * $scale);

        // Create a new image output resource.
        $this->createResource();
        $this->output = imagecreatetruecolor($wid, $hgt);

        // Copy newly sized image to the output resource.
        $this->copyImage($wid, $hgt);

        return $this;
    }

    /**
     * Scale the image object
     *
     * @param  float|string $scl
     * @return Image
     */
    protected function scale($scl)
    {
        $wid = round($this->img['width'] * $scl);
        $hgt = round($this->img['height'] * $scl);

        // Create a new image output resource.
        $this->createResource();
        $this->output = imagecreatetruecolor($wid, $hgt);

        // Copy newly sized image to the output resource.
        $this->copyImage($wid, $hgt);

        return $this;
    }

    /**
     * Create a new image resource based on the current image type
     * of the image object.
     *
     * @return void
     */
    protected function createResource()
    {
        if (file_exists($this->img['fullpath'])) {
            switch ($this->img['mime']) {
                case 'image/gif':
                    $this->resource = imagecreatefromgif($this->img['fullpath']);
                    break;
                case 'image/png':
                    $this->resource = imagecreatefrompng($this->img['fullpath']);
                    break;
                case 'image/jpeg':
                    $this->resource = imagecreatefromjpeg($this->img['fullpath']);
                    break;
            }
        }
    }

    /**
     * Copy the image resource to the image output resource with the set parameters.
     *
     * @param  int $w
     * @param  int $h
     * @param  int $x
     * @param  int $y
     * @return void
     */
    protected function copyImage($w, $h, $x = 0, $y = 0)
    {
        imagecopyresampled($this->output, $this->resource, 0, 0, $x, $y, $w, $h, $this->img['width'], $this->img['height']);
        $this->img['width']  = imagesx($this->output);
        $this->img['height'] = imagesy($this->output);
    }

    /**
     * Save the image object to disk.
     *
     * @param  string  $to
     * @return Image
     */
    public function save($to = null)
    {
        if (null === $this->resource) {
            $this->createResource();
        }

        if (null === $this->output) {
            $this->output = $this->resource;
        }

        $img = ((null === $to) ? $this->img['fullpath'] : $to);

        switch ($this->img['mime']) {
            case 'image/png':
                if ($this->img['colormode'] != 'Indexed') {
                    imagepng($this->output, $img, 1);
                } else {
                    imagepng($this->output, $img);
                }
                break;
            case 'image/jpeg':
                imagejpeg($this->output, $img, 90);
                break;
        }

        clearstatcache();

        $this->setImage($img);

        return $this;
    }

}