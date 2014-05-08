<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Image;

use Pop\Color\Space\Rgb;

/**
 * Image CAPTCHA class
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Captcha
{

    /**
     * Constant to force using the GD component
     * @var int
     */
    const FORCE_GD = true;

    /**
     * CAPTCHA image resource
     * @var mixed
     */
    protected $image = null;

    /**
     * CAPTCHA token object
     * @var array
     */
    protected $token = null;

    /**
     * CAPTCHA string length
     * @var int
     */
    protected $length = 4;

    /**
     * CAPTCHA grid spacing
     * @var int
     */
    protected $grid = 5;

    /**
     * CAPTCHA border width
     * @var mixed
     */
    protected $border = 0.5;

    /**
     * CAPTCHA font size (if using system fonts, it will be capped at 5)
     * @var int
     */
    protected $size = 20;

    /**
     * CAPTCHA token expiration in seconds
     * @var int
     */
    protected $expire = 300;

    /**
     * CAPTCHA font
     * @var string
     */
    protected $font = null;

    /**
     * CAPTCHA rotate
     * @var int
     */
    protected $rotate = 0;

    /**
     * CAPTCHA x
     * @var int
     */
    protected $x = null;

    /**
     * CAPTCHA y
     * @var int
     */
    protected $y = null;

    /**
     * CAPTCHA swirl
     * @var int
     */
    protected $swirl = null;

    /**
     * CAPTCHA colors
     * @var int
     */
    protected $colors = array(
        'background' => array('r' => 255, 'g' => 255,'b' => 255),
        'text'       => array('r' =>   0, 'g' =>   0,'b' =>   0),
        'grid'       => array('r' => 180, 'g' => 180,'b' => 180),
        'border'     => array('r' =>   0, 'g' =>   0,'b' =>   0)
    );

    /**
     * Constructor
     *
     * Instantiate a CAPTCHA image object. Valid options are:
     *
     *     $options = array(
     *         'width'      => 75,
     *         'height'     => 25,
     *         'background' => array(200, 200, 200) // R, G, B values for the background color
     *     );
     *
     *     $options = array(
     *         'image'  => 'some-image-background,gif'
     *     );
     *
     * This $forceGd flag forces the object to use the Gd extension. If both are
     * installed, it will default to Imagick unless this flag is set to true.
     *
     * @param array   $options
     * @param boolean $forceGd
     * @throws Exception
     * @return \Pop\Image\Captcha
     */
    public function __construct(array $options, $forceGd = false)
    {
        // Check that at least one of the image extensions is installed
        if ((!Gd::isInstalled()) && (!Imagick::isInstalled())) {
            throw new Exception('Error: At least either the GD or Imagick extension must be installed.');
        }

        if ($forceGd) {
            $class = 'Pop\Image\Gd';
        } else {
            $class = (Imagick::isInstalled()) ? 'Pop\Image\Imagick' : 'Pop\Image\Gd';
        }

        // Parse through the options
        if (isset($options['image']) && file_exists($options['image'])) {
            $image = $options['image'];
            $w = null;
            $h = null;
        } else if (isset($options['width']) && isset($options['height']) && is_numeric($options['width']) && is_numeric($options['height'])) {
            $image = 'pop-captcha.gif';
            $w = $options['width'];
            $h = $options['height'];
        } else  {
            throw new Exception('Error: You must either pass a valid width and height or a valid image in the $options parameter.');
        }

        if (isset($options['background']) && is_array($options['background']) && (count($options['background']) == 3)) {
            $background = new Rgb($options['background'][0], $options['background'][1], $options['background'][2]);
        } else {
            $background = null;
        }

        // Create new image object
        $this->image = new $class($image, $w, $h, $background);
    }

    /**
     * Static method to instantiate the CAPTCHA image object
     * and return itself to facilitate chaining methods together.
     *
     * @param array   $options
     * @param boolean $forceGd
     * @return \Pop\Image\Captcha
     */
    public static function factory(array $options, $forceGd = false)
    {
        return new self($options, $forceGd);
    }

    /**
     * Method to get the image object
     *
     * @return mixed
     */
    public function image()
    {
        return $this->image;
    }

    /**
     * Method to get the string length
     *
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Method to get the grid spacing
     *
     * @return int
     */
    public function getGridWidth()
    {
        return $this->grid;
    }

    /**
     * Method to get the border width
     *
     * @return mixed
     */
    public function getBorderWidth()
    {
        return $this->border;
    }

    /**
     * Method to get the font size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Method to get the expiration length in seconds
     *
     * @return int
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * Method to get the font
     *
     * @return string
     */
    public function getFont()
    {
        return $this->font;
    }

    /**
     * Method to get the rotation
     *
     * @return int
     */
    public function getRotate()
    {
        return $this->rotate;
    }

    /**
     * Method to get the X position
     *
     * @return int
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * Method to get the Y position
     *
     * @return int
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Method to get the swirl
     *
     * @return int
     */
    public function getSwirl()
    {
        return $this->swirl;
    }

    /**
     * Method to get the background color
     *
     * @return array
     */
    public function getBackgroundColor()
    {
        return $this->colors['background'];
    }

    /**
     * Method to get the text color
     *
     * @return array
     */
    public function getTextColor()
    {
        return $this->colors['text'];
    }

    /**
     * Method to get the grid color
     *
     * @return array
     */
    public function getGridColor()
    {
        return $this->colors['grid'];
    }

    /**
     * Method to get the border color
     *
     * @return array
     */
    public function getBorderColor()
    {
        return $this->colors['border'];
    }

    /**
     * Method to set the string length
     *
     * @param int $length
     * @return \Pop\Image\Captcha
     */
    public function setLength($length = 4)
    {
        $this->length = $length;
        return $this;
    }

    /**
     * Method to set the grid spacing
     *
     * @param int $grid
     * @return \Pop\Image\Captcha
     */
    public function setGridWidth($grid = 5)
    {
        $this->grid = $grid;
        return $this;
    }

    /**
     * Method to set the border width
     *
     * @param mixed $border
     * @return \Pop\Image\Captcha
     */
    public function setBorderWidth($border = 0.55)
    {
        $this->border = $border;
        return $this;
    }

    /**
     * Method to set the font size
     *
     * @param int $size
     * @return \Pop\Image\Captcha
     */
    public function setSize($size = 20)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Method to set the expiration length in seconds
     *
     * @param int $expire
     * @return \Pop\Image\Captcha
     */
    public function setExpire($expire = 300)
    {
        $this->expire = $expire;
        return $this;
    }

    /**
     * Method to set the font
     *
     * @param string $font
     * @return \Pop\Image\Captcha
     */
    public function setFont($font = null)
    {
        $this->font = $font;
        return $this;
    }

    /**
     * Method to set the rotation
     *
     * @param int $rotate
     * @return \Pop\Image\Captcha
     */
    public function setRotate($rotate = null)
    {
        $this->rotate = $rotate;
        return $this;
    }

    /**
     * Method to set the X position
     *
     * @param int $x
     * @return \Pop\Image\Captcha
     */
    public function setX($x = null)
    {
        $this->x = $x;
        return $this;
    }

    /**
     * Method to set the Y position
     *
     * @param int $y
     * @return \Pop\Image\Captcha
     */
    public function setY($y = null)
    {
        $this->y = $y;
        return $this;
    }

    /**
     * Method to set the X, Y position
     *
     * @param int $x
     * @param int $y
     * @return \Pop\Image\Captcha
     */
    public function setXY($x = null, $y = null)
    {
        $this->x = $x;
        $this->y = $y;
        return $this;
    }

    /**
     * Method to set the swirl
     *
     * @param int $swirl
     * @return \Pop\Image\Captcha
     */
    public function setSwirl($swirl = null)
    {
        $this->swirl = $swirl;
        return $this;
    }

    /**
     * Method to set the background color
     *
     * @param int $r
     * @param int $g
     * @param int $b
     * @return \Pop\Image\Captcha
     */
    public function setBackgroundColor($r, $g, $b)
    {
        $this->colors['background']['r'] = (int)$r;
        $this->colors['background']['g'] = (int)$g;
        $this->colors['background']['b'] = (int)$b;

        return $this;
    }

    /**
     * Method to set the text color
     *
     * @param int $r
     * @param int $g
     * @param int $b
     * @return \Pop\Image\Captcha
     */
    public function setTextColor($r, $g, $b)
    {
        $this->colors['text']['r'] = (int)$r;
        $this->colors['text']['g'] = (int)$g;
        $this->colors['text']['b'] = (int)$b;

        return $this;
    }

    /**
     * Method to set the grid color
     *
     * @param int $r
     * @param int $g
     * @param int $b
     * @return \Pop\Image\Captcha
     */
    public function setGridColor($r = null, $g = null, $b = null)
    {
        if (null === $r) {
            $this->colors['grid']['r'] = null;
            $this->colors['grid']['g'] = null;
            $this->colors['grid']['b'] = null;
        } else {
            $this->colors['grid']['r'] = (int)$r;
            $this->colors['grid']['g'] = (int)$g;
            $this->colors['grid']['b'] = (int)$b;
        }

        return $this;
    }

    /**
     * Method to set the border color
     *
     * @param int $r
     * @param int $g
     * @param int $b
     * @return \Pop\Image\Captcha
     */
    public function setBorderColor($r = null, $g = null, $b = null)
    {
        if (null === $r) {
            $this->colors['border']['r'] = (int)$r;
            $this->colors['border']['g'] = (int)$g;
            $this->colors['border']['b'] = (int)$b;
        } else {
            $this->colors['border']['r'] = null;
            $this->colors['border']['g'] = null;
            $this->colors['border']['b'] = null;
        }

        return $this;
    }

    /**
     * Method to output the image object
     *
     * @return void
     */
    public function output()
    {
        $width = $this->image->getWidth();
        $height = $this->image->getHeight();

        // If grid is set, draw grid
        if (null !== $this->colors['grid']['r']) {
            $this->image->setStrokeColor(new Rgb(
                $this->colors['grid']['r'],
                $this->colors['grid']['g'],
                $this->colors['grid']['b']
            ));

            // Draw horizontal lines
            for ($i = $this->grid; $i < $height; $i += $this->grid) {
                $this->image->drawLine(0, $i, $width, $i);
            }

            // Draw vertical lines
            for ($i = $this->grid; $i < $width; $i += $this->grid) {
                $this->image->drawLine($i, 0, $i, $height);
            }
        }

        // If border is set, draw border
        if (null !== $this->colors['border']['r']) {
            $this->image->setStrokeColor(new Rgb(
                $this->colors['border']['r'],
                $this->colors['border']['g'],
                $this->colors['border']['b']
            ))->border($this->border);
        }

        // Generate the token
        $this->generateToken();

        // Set text color
        $this->image->setFillColor(new Rgb(
            $this->colors['text']['r'],
            $this->colors['text']['g'],
            $this->colors['text']['b']
        ));

        $this->image->setStrokeColor(new Rgb(
            $this->colors['text']['r'],
            $this->colors['text']['g'],
            $this->colors['text']['b']
        ));

        // Draw text using a font
        if (null !== $this->font) {
            // If not defined, calculate the x, y start-point for the text
            if ((null === $this->x) || (null === $this->y)) {
                if ($this->image instanceof Imagick) {
                    $this->x = round(($width - ($this->length * ($this->size / 1.5))) / 2);
                    $this->y = ($height - 2) - round(($height - $this->size) / 2);
                } else {
                    $this->x = round(($width - ($this->length * $this->size)) / 2);
                    $this->y = $height - round(($height - $this->size) / 2);
                }
            }
            $this->image->text($this->token['value'], $this->size, $this->x, $this->y, $this->font, $this->rotate);
        // Else, draw text using a system font
        } else {
            // If not defined, calculate the x, y start-point for the text
            if ((null === $this->x) || (null === $this->y)) {
                if ($this->image instanceof Imagick) {
                    $this->x = round(($width - ($this->length * ($this->size / 2))) / 2);
                    $this->y = ($height - 2) - round(($height - $this->size) / 2);
                } else {
                    $this->x = round(($width - ($this->length * 8.5)) / 2);
                    $this->y = round(($height - 17) / 2);
                }
            }
            $this->image->text($this->token['value'], $this->size, $this->x, $this->y);
        }

        if (null !== $this->swirl) {
            $this->image->swirl($this->swirl);
        }

        // Output the image and destroy the resource to prevent any caching issues.
        $this->image->output();
        $this->image->destroy();
    }

    /**
     * Method to get the token object, generating a new one if necessary
     *
     * @return array
     */
    public function getToken()
    {
        if (null === $this->token) {
            $this->generateToken();
        }

        return $this->token;
    }

    /**
     * Method to generate the token object
     *
     * @return void
     */
    public function generateToken()
    {
        $str = null;
        $chars = str_split('ABCDEFGHJKLMNPQRSTUVWXYZ23456789');

        for ($i = 0; $i < $this->length; $i++) {
            $index = rand(0, (count($chars) - 1));
            $str .= $chars[$index];
        }

        // Start a session.
        if (session_id() == '') {
            session_start();
        }

        // If no captcha token has been created, create one
        if (!isset($_SESSION['pop_captcha'])) {
            $this->token = array(
                'captcha' => '<img id="pop-captcha-image" src="' . $_SERVER['PHP_SELF'] . '" alt="POP Captcha Image" /><br />(<a class="reload" href="#" onclick="document.getElementById(\'pop-captcha-image\').src = document.getElementById(\'pop-captcha-image\').src + \'?reload=1\'; return false;">Reload</a>)',
                'value'   => $str,
                'expire'  => (int)$this->expire,
                'start'   => time()
            );
            $_SESSION['pop_captcha'] = serialize($this->token);
        // Else, retrieve the existing one
        } else {
            $this->token = unserialize($_SESSION['pop_captcha']);
            // If the token is has no value, is expired or a reload has been requested, generate a new value
            if ((null === $this->token['value']) || (($this->token['expire'] + $this->token['start']) < time()) || isset($_GET['reload'])) {
                $this->token['value'] = $str;
                $this->token['start'] = time();
                $_SESSION['pop_captcha'] = serialize($this->token);
            }
        }
    }

}