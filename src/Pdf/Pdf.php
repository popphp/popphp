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
namespace Pop\Pdf;

use Pop\Color\Space;
use Pop\Pdf\Object;

/**
 * Pdf class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Pdf
{

    /**
     * PDF root index.
     * @var int
     */
    protected $root = 1;

    /**
     * PDF parent index.
     * @var int
     */
    protected $parent = 2;

    /**
     * PDF info index.
     * @var int
     */
    protected $info = 3;

    /**
     * Array of PDF page object indices.
     * @var array
     */
    protected $pages = [];

    /**
     * Array of PDF objects.
     * @var array
     */
    protected $objects = [];

    /**
     * PDF trailer.
     * @var string
     */
    protected $trailer = null;

    /**
     * Current PDF page.
     * @var int
     */
    protected $curPage = null;

    /**
     * PDF text parameters.
     * @var array
     */
    protected $textParams = ['c' => 0, 'w' => 0, 'h' => 100, 'v' => 100, 'rot' => 0, 'rend' => 0];

    /**
     * PDF bytelength
     * @var int
     */
    protected $bytelength = null;

    /**
     * Standard PDF fonts with their approximate character width and height factors.
     * @var array
     */
    protected $standardFonts = [
        'Arial'                    => ['width_factor' => 0.5, 'height_factor' => 1],
        'Arial,Italic'             => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'Arial,Bold'               => ['width_factor' => 0.55, 'height_factor' => 1.12],
        'Arial,BoldItalic'         => ['width_factor' => 0.55, 'height_factor' => 1.12],
        'Courier'                  => ['width_factor' => 0.65, 'height_factor' => 1],
        'CourierNew'               => ['width_factor' => 0.65, 'height_factor' => 1],
        'Courier-Oblique'          => ['width_factor' => 0.65, 'height_factor' => 1],
        'CourierNew,Italic'        => ['width_factor' => 0.65, 'height_factor' => 1],
        'Courier-Bold'             => ['width_factor' => 0.65, 'height_factor' => 1],
        'CourierNew,Bold'          => ['width_factor' => 0.65, 'height_factor' => 1],
        'Courier-BoldOblique'      => ['width_factor' => 0.65, 'height_factor' => 1],
        'CourierNew,BoldItalic'    => ['width_factor' => 0.65, 'height_factor' => 1],
        'Helvetica'                => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'Helvetica-Oblique'        => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'Helvetica-Bold'           => ['width_factor' => 0.55, 'height_factor' => 1.12],
        'Helvetica-BoldOblique'    => ['width_factor' => 0.55, 'height_factor' => 1.12],
        'Symbol'                   => ['width_factor' => 0.85, 'height_factor' => 1.12],
        'Times-Roman'              => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'Times-Bold'               => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'Times-Italic'             => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'Times-BoldItalic'         => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'TimesNewRoman'            => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'TimesNewRoman,Italic'     => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'TimesNewRoman,Bold'       => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'TimesNewRoman,BoldItalic' => ['width_factor' => 0.5, 'height_factor' => 1.12],
        'ZapfDingbats'             => ['width_factor' => 0.75, 'height_factor' => 1.12]
    ];

    /**
     * Current fonts added to the PDF
     * @var array
     */
    protected $fonts = [];

    /**
     * Last font name
     * @var string
     */
    protected $lastFontName = null;

    /**
     * Array of images added to the PDF
     * @var string
     */
    protected $images = [];

    /**
     * Stroke ON or OFF flag
     * @var boolean
     */
    protected $stroke = false;

    /**
     * Stroke width
     * @var int
     */
    protected $strokeWidth = null;

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
     * Stroke color of the document
     * @var mixed
     */
    protected $strokeColor = null;

    /**
     * Fill color of the document
     * @var mixed
     */
    protected $fillColor = null;

    /**
     * Background color of the document
     * @var mixed
     */
    protected $backgroundColor = null;

    /**
     * Compression property
     * @var boolean
     */
    protected $compress = true;

    /**
     * Full path of pdf file, i.e. '/path/to/pdffile.ext'
     * @var string
     */
    protected $fullpath = null;

    /**
     * Full, absolute directory of the pdf file, i.e. '/some/dir/'
     * @var string
     */
    protected $dir = null;

    /**
     * Full basename of pdf file, i.e. 'pdffile.ext'
     * @var string
     */
    protected $basename = null;

    /**
     * Full filename of pdf file, i.e. 'pdffile'
     * @var string
     */
    protected $filename = null;

    /**
     * PDF file extension, i.e. 'ext'
     * @var string
     */
    protected $extension = 'pdf';

    /**
     * PDF file size in bytes
     * @var int
     */
    protected $size = 0;

    /**
     * PDF file mime type
     * @var string
     */
    protected $mime = 'application/pdf';

    /**
     * PDF output buffer
     * @var string
     */
    protected $output = null;

    /**
     * Constructor
     *
     * Instantiate a PDF file object based on either a pre-existing PDF file on disk,
     * or a new PDF file. Arguments may be passed to add a page upon instantiation.
     * The PDF file exists, it and all of its assets will be imported.
     *
     * @param  string $pdf
     * @param  string $sz
     * @param  int    $w
     * @param  int    $h
     * @throws Exception
     * @return Pdf
     */
    public function __construct($pdf, $sz = null, $w = null, $h = null)
    {
        $this->fillColor       = new Space\Rgb(0, 0, 0);
        $this->backgroundColor = new Space\Rgb(255, 255, 255);

        $this->fullpath  = $pdf;
        $parts           = pathinfo($pdf);
        $this->size      = (file_exists($pdf) ? filesize($pdf) : 0);
        $this->dir       = realpath($parts['dirname']);
        $this->basename  = $parts['basename'];
        $this->filename  = $parts['filename'];
        $this->extension = (isset($parts['extension']) && ($parts['extension'] != '')) ? $parts['extension'] : null;

        if ((null === $this->extension) || ($this->extension != 'pdf')) {
            throw new Exception('Error: That pdf file does not have the correct extension.');
        }

        $this->objects[1] = new Object\Root();
        $this->objects[2] = new Object\ParentObject();
        $this->objects[3] = new Object\Info();

        // If the PDF file already exists, import it.
        if ($this->size != 0) {
            $this->import($this->fullpath);
        }

        // If page parameters were passed, add a new page.
        if ((null !== $sz) || ((null !== $w) && (null !== $h))) {
            $this->addPage($sz, $w, $h);
        }
    }

    /**
     * Method to import either an entire PDF, or a page of a PDF, and the related data.
     *
     * @param  string           $pdf
     * @param  int|string|array $pg
     * @return Pdf
     */
    public function import($pdf, $pg = null)
    {
        // Create a new PDF Import object.
        $pdfi = new Import($pdf, $pg);

        // Shift the imported objects indices based on existing indices in this PDF.
        $pdfi->shiftObjects(($this->lastIndex($this->objects) + 1));

        // Fetch the imported objects.
        $importedObjs = $pdfi->returnObjects($this->parent);

        // Loop through the imported objects, adding the pages or objects as applicable.
        foreach($importedObjs as $key => $value) {
            if ($value['type'] == 'page') {
                // Add the page object.
                $this->objects[$key] = new Object\Page($value['data']);

                // Finalize related page variables and objects.
                $this->curPage = (null === $this->curPage) ? 0 : ($this->lastIndex($this->pages) + 1);
                $this->pages[$this->curPage] = $key;
                $this->objects[$this->parent]->count += 1;
            } else {
                // Else, add the content object.
                $this->objects[$key] = new Object\Object($value['data']);
            }
        }

        foreach ($pdfi->pages as $value) {
            $this->objects[$this->parent]->kids[] = $value;
        }

        return $this;
    }

    /**
     * Method to add a page to the PDF of a determined size.
     *
     * @param  string $sz
     * @param  int    $w
     * @param  int    $h
     * @return Pdf
     */
    public function addPage($sz = null, $w = null, $h = null)
    {
        // Define the next page and content object indices.
        $pi = $this->lastIndex($this->objects) + 1;
        $ci = $this->lastIndex($this->objects) + 2;

        // Create the page object.
        $this->objects[$pi] = new Object\Page(null, $sz, $w, $h, $pi);
        $this->objects[$pi]->content[] = $ci;
        $this->objects[$pi]->curContent = $ci;
        $this->objects[$pi]->parent = $this->parent;

        // Create the content object.
        $this->objects[$ci] = new Object\Object($ci);

        // Finalize related page variables and objects.
        $this->curPage = (null === $this->curPage) ? 0 : ($this->lastIndex($this->pages) + 1);
        $this->pages[$this->curPage] = $pi;
        $this->objects[$this->parent]->count += 1;
        $this->objects[$this->parent]->kids[] = $pi;

        return $this;
    }

    /**
     * Method to copy a page of the PDF.
     *
     * @param  int $pg
     * @throws Exception
     * @return Pdf
     */
    public function copyPage($pg)
    {
        $key = $pg - 1;

        // Check if the page exists.
        if (!array_key_exists($key, $this->pages)) {
            throw new Exception('Error: That page does not exist.');
        }

        $pi = $this->lastIndex($this->objects) + 1;
        $ci = $this->lastIndex($this->objects) + 2;
        $this->objects[$pi] = new Object\Page($this->objects[$this->pages[$key]]);
        $this->objects[$pi]->index = $pi;

        // Duplicate the page's content objects.
        $oldContent = $this->objects[$pi]->content;
        unset($this->objects[$pi]->content);
        foreach ($oldContent as $key => $value) {
            $this->objects[$ci] = new Object\Object((string)$this->objects[$value]);
            $this->objects[$ci]->index = $ci;
            $this->objects[$pi]->content[] = $ci;
            $ci += 1;
        }

        // Finalize related page variables and objects.
        $this->curPage = (null === $this->curPage) ? 0 : ($this->lastIndex($this->pages) + 1);
        $this->pages[$this->curPage] = $pi;
        $this->objects[$this->parent]->count += 1;
        $this->objects[$this->parent]->kids[] = $pi;


        return $this;
    }

    /**
     * Method to delete the page of the PDF and its content objects.
     *
     * @param  int $pg
     * @throws Exception
     * @return Pdf
     */
    public function deletePage($pg)
    {
        $key = $pg - 1;

        // Check if the page exists.
        if (!array_key_exists($key, $this->pages)) {
            throw new Exception('Error: That page does not exist.');
        }

        // Determine the page index and related data.
        $pi = $this->pages[$key];
        $ki =  array_search($pi, $this->objects[$this->parent]->kids);
        $content_objs = $this->objects[$pi]->content;

        // Remove the page's content objects.
        if (count($content_objs) != 0) {
            foreach ($content_objs as $value) {
                unset($this->objects[$value]);
            }
        }

        // Subtract the page from the parent's count property.
        $this->objects[$this->parent]->count -= 1;

        // Remove the page from the kids and pages arrays, and remove the page object.
        unset($this->objects[$this->parent]->kids[$ki]);
        unset($this->pages[$key]);
        unset($this->objects[$pi]);

        // Reset the kids array.
        $tmpAry = $this->objects[$this->parent]->kids;
        $this->objects[$this->parent]->kids = [];
        foreach ($tmpAry as $value) {
            $this->objects[$this->parent]->kids[] = $value;
        }

        // Reset the pages array.
        $tmpAry = $this->pages;
        $this->pages = [];
        foreach ($tmpAry as $value) {
            $this->pages[] = $value;
        }

        return $this;
    }

    /**
     * Method to order the pages of the PDF.
     *
     * @param  array $pgs
     * @throws Exception
     * @return Pdf
     */
    public function orderPages($pgs)
    {
        $newOrder = [];

        // Check if the PDF has more than one page.
        if (count($this->pages) <= 1) {
            throw new Exception('Error: The PDF does not have enough pages in which to order.');
        // Else, check if the numbers of pages passed equals the number of pages in the PDF.
        } else if (count($pgs) != count($this->pages)) {
            throw new Exception('Error: The pages array passed does not contain the same number of pages as the PDF.');
        }

        // Make sure each page passed is within the PDF and not out of range.
        foreach ($pgs as $value) {
            if (!array_key_exists(($value - 1), $this->pages)) {
                throw new Exception('Error: The pages array passed contains a page that does not exist.');
            }
        }

        // Set the new order of the page objects.
        foreach ($pgs as $value) {
            $newOrder[] = $this->pages[$value - 1];
        }

        // Set the kids and pages arrays to the new order.
        $this->objects[$this->parent]->kids = $newOrder;
        $this->pages = $newOrder;

        return $this;
    }

    /**
     * Method to return the current page number of the current page of the PDF.
     *
     * @return int
     */
    public function curPage()
    {
        return ($this->curPage + 1);
    }

    /**
     * Method to return the current number of pages in the PDF.
     *
     * @return int
     */
    public function numPages()
    {
        return count($this->pages);
    }

    /**
     * Method to return the name of the last font added.
     *
     * @return string
     */
    public function getFonts()
    {
        return $this->fonts;
    }

    /**
     * Method to return the name of the last font added.
     *
     * @return string
     */
    public function getLastFontName()
    {
        return $this->lastFontName;
    }

    /**
     * Method to get the compression of the PDF.
     *
     * @return boolean
     */
    public function getCompression()
    {
        return $this->compress;
    }

    /**
     * Method to set the compression of the PDF.
     *
     * @param  boolean $comp
     * @return Pdf
     */
    public function setCompression($comp = false)
    {
        $this->compress = $comp;
        return $this;
    }

    /**
     * Method to set the current page of the PDF in which to edit.
     *
     * @param  int $pg
     * @throws Exception
     * @return Pdf
     */
    public function setPage($pg)
    {
        $key = $pg - 1;

        // Check if the page exists.
        if (!array_key_exists($key, $this->pages)) {
            throw new Exception('Error: That page does not exist.');
        }
        $this->curPage = $pg - 1;

        return $this;
    }

    /**
     * Method to set the PDF version.
     *
     * @param  string $ver
     * @return Pdf
     */
    public function setVersion($ver)
    {
        $this->objects[$this->root]->version = $ver;
        return $this;
    }

    /**
     * Method to set the PDF info title.
     *
     * @param  string $tle
     * @return Pdf
     */
    public function setTitle($tle)
    {
        $this->objects[$this->info]->title = $tle;
        return $this;
    }

    /**
     * Method to set the PDF info author.
     *
     * @param  string $auth
     * @return Pdf
     */
    public function setAuthor($auth)
    {
        $this->objects[$this->info]->author = $auth;
        return $this;
    }

    /**
     * Method to set the PDF info subject.
     *
     * @param  string $subj
     * @return Pdf
     */
    public function setSubject($subj)
    {
        $this->objects[$this->info]->subject = $subj;
        return $this;
    }

    /**
     * Method to set the PDF info creation date.
     *
     * @param  string $dt
     * @return Pdf
     */
    public function setCreateDate($dt)
    {
        $this->objects[$this->info]->create_date = $dt;
        return $this;
    }

    /**
     * Method to set the PDF info modification date.
     *
     * @param  string $dt
     * @return Pdf
     */
    public function setModDate($dt)
    {
        $this->objects[$this->info]->mod_date = $dt;
        return $this;
    }

    /**
     * Method to set the background of the document.
     *
     * @param  Space\ColorInterface $color
     * @return Pdf
     */
    public function setBackgroundColor(Space\ColorInterface $color)
    {
        $this->backgroundColor = $color;
        return $this;
    }

    /**
     * Method to set the fill color of objects and text in the PDF.
     *
     * @param  Space\ColorInterface $color
     * @return Pdf
     */
    public function setFillColor(Space\ColorInterface $color)
    {
        $this->fillColor = $color;

        $co_index = $this->getContentObject();
        $this->objects[$co_index]->setStream("\n" . $this->convertColor($color->getRed()) . " " . $this->convertColor($color->getGreen()) . " " . $this->convertColor($color->getBlue()) . " rg\n");

        return $this;
    }

    /**
     * Method to set the stroke color of paths in the PDF.
     *
     * @param  Space\ColorInterface $color
     * @return Pdf
     */
    public function setStrokeColor(Space\ColorInterface $color)
    {
        $this->strokeColor = $color;

        $co_index = $this->getContentObject();
        $this->objects[$co_index]->setStream("\n" . $this->convertColor($color->getRed()) . " " . $this->convertColor($color->getGreen()) . " " . $this->convertColor($color->getBlue()) . " RG\n");

        return $this;
    }

    /**
     * Method to set the width and dash properties of paths in the PDF.
     *
     * @param  int $w
     * @param  int $dash_len
     * @param  int $dash_gap
     * @return Pdf
     */
    public function setStrokeWidth($w = null, $dash_len = null, $dash_gap = null)
    {
        if ((null === $w) || ($w == false) || ($w == 0)) {
            $this->stroke = false;
            $this->strokeWidth = null;
            $this->strokeDashLength = null;
            $this->strokeDashGap = null;
        } else {
            $this->stroke = true;
            $this->strokeWidth = $w;
            $this->strokeDashLength = $dash_len;
            $this->strokeDashGap = $dash_gap;

            // Set stroke to the $w argument, or else default it to 1pt.
            $new_str = "\n{$w} w\n";

            // Set the dash properties of the stroke, or else default it to a solid line.
            $new_str .= ((null !== $dash_len) && (null !== $dash_gap)) ? "[{$dash_len} {$dash_gap}] 0 d\n" : "[] 0 d\n";

            $co_index = $this->getContentObject();
            $this->objects[$co_index]->setStream($new_str);
        }

        return $this;
    }

    /**
     * Method to set the text parameters for rendering text content.
     *
     * @param  int $c    (character spacing)
     * @param  int $w    (word spacing)
     * @param  int $h    (horz stretch)
     * @param  int $v    (vert stretch)
     * @param  int $rot  (rotation)
     * @param  int $rend (render flag, 0 - 7)
     * @throws Exception
     * @return Pdf
     */
    public function setTextParams($c = 0, $w = 0, $h = 100, $v = 100, $rot = 0, $rend = 0)
    {
        // Check the rotation parameter.
        if (abs($rot) > 90) {
            throw new Exception('Error: The rotation parameter must be between -90 and 90 degrees.');
        }

        // Check the render parameter.
        if ((!is_int($rend)) || (($rend > 7) || ($rend < 0))) {
            throw new Exception('Error: The render parameter must be an integer between 0 and 7.');
        }

        // Set the text parameters.
        $this->textParams['c'] = $c;
        $this->textParams['w'] = $w;
        $this->textParams['h'] = $h;
        $this->textParams['v'] = $v;
        $this->textParams['rot'] = $rot;
        $this->textParams['rend'] = $rend;

        return $this;
    }

    /**
     * Method to add a font to the PDF.
     *
     * @param  string  $font
     * @param  boolean $embedOverride
     * @throws Exception
     * @return Pdf
     */
    public function addFont($font, $embedOverride = false)
    {
        // Embed the font file.
        if (file_exists($font)) {
            $fontIndex = (count($this->objects[$this->objects[$this->pages[$this->curPage]]->index]->fonts) == 0) ? 1 : count($this->objects[$this->objects[$this->pages[$this->curPage]]->index]->fonts) + 1;
            $objectIndex = $this->lastIndex($this->objects) + 1;

            $fontParser = new Parser\Font($font, $fontIndex, $objectIndex, $this->compress);

            if (!$fontParser->isEmbeddable() && !$embedOverride) {
                throw new Exception('Error: The font license does not allow for it to be embedded.');
            }

            $this->objects[$this->objects[$this->pages[$this->curPage]]->index]->fonts[$fontParser->getFontName()] = $fontParser->getFontRef();
            $fontObjects = $fontParser->getObjects();

            foreach ($fontObjects as $key => $value) {
                $this->objects[$key] = $value;
            }

            $this->lastFontName = $fontParser->getFontName();
        // Else, use a standard font.
        } else {
            // Check to make sure the font is a standard PDF font.
            if (!array_key_exists($font, $this->standardFonts)) {
                throw new Exception('Error: That font is not contained within the standard PDF fonts.');
            }
            // Set the font index.
            $ft_index = (count($this->objects[$this->objects[$this->pages[$this->curPage]]->index]->fonts) == 0) ? 1 : count($this->objects[$this->objects[$this->pages[$this->curPage]]->index]->fonts) + 1;

            // Set the font name and the next object index.
            $f = 'MF' . $ft_index;
            $i = $this->lastIndex($this->objects) + 1;

            // Add the font to the current page's fonts and add the font to _objects array.
            $this->objects[$this->objects[$this->pages[$this->curPage]]->index]->fonts[$font] = "/{$f} {$i} 0 R";
            $this->objects[$i] = new Object\Object("{$i} 0 obj\n<<\n    /Type /Font\n    /Subtype /Type1\n    /Name /{$f}\n    /BaseFont /{$font}\n    /Encoding /WinAnsiEncoding\n>>\nendobj\n\n");

            $this->lastFontName = $font;
        }

        if (!in_array($this->lastFontName, $this->fonts)) {
            $this->fonts[] = $this->lastFontName;
        }

        return $this;
    }

    /**
     * Method to add text to the PDF.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $size
     * @param  string $str
     * @param  string $font
     * @throws Exception
     * @return Pdf
     */
    public function addText($x, $y, $size, $str, $font = null)
    {
        // Check to see if the font already exists on another page.
        $fontExists = false;

        if (null === $font) {
            $font = $this->getLastFontName();
        }

        if (function_exists('mb_strlen')) {
            if (mb_strlen($str, 'UTF-8') < strlen($str)) {
                $str = utf8_decode($str);
            }
        }

        foreach ($this->pages as $value) {
            if (array_key_exists($font, $this->objects[$value]->fonts)) {
                $this->objects[$this->objects[$this->pages[$this->curPage]]->index]->fonts[$font] = $this->objects[$value]->fonts[$font];
                $fontObj = substr($this->objects[$this->objects[$this->pages[$this->curPage]]->index]->fonts[$font], 1, (strpos(' ', $this->objects[$this->objects[$this->pages[$this->curPage]]->index]->fonts[$font]) + 3));
                $fontExists = true;
            }
        }

        // If the font does not already exist, add it.
        if (!$fontExists) {
            if (isset($this->pages[$this->curPage]) &&
                isset($this->objects[$this->pages[$this->curPage]]) &&
                isset($this->objects[$this->objects[$this->pages[$this->curPage]]->index]) &&
                (array_key_exists($font, $this->objects[$this->objects[$this->pages[$this->curPage]]->index]->fonts))) {
                $fontObj = substr($this->objects[$this->objects[$this->pages[$this->curPage]]->index]->fonts[$font], 1, (strpos(' ', $this->objects[$this->objects[$this->pages[$this->curPage]]->index]->fonts[$font]) + 3));
            } else {
                throw new Exception('Error: The font \'' . $font . '\' has not been added to the PDF.');
            }
        }

        // Add the text to the current page's content stream.
        $co_index = $this->getContentObject();
        $this->objects[$co_index]->setStream("\nBT\n    /{$fontObj} {$size} Tf\n    " . $this->calcTextMatrix() . " {$x} {$y} Tm\n    " . $this->textParams['c'] . " Tc " . $this->textParams['w'] . " Tw " . $this->textParams['rend'] . " Tr\n    ({$str})Tj\nET\n");

        return $this;
    }

    /**
     * Method to get the width and height of a string in a certain font. It returns
     * an array with the approximate width, height and offset baseline values.
     *
     * @param  string $str
     * @param  string $font
     * @param  int    $sz
     * @throws Exception
     * @return array
     */
    public function getStringSize($str, $font, $sz)
    {
        if (!array_key_exists($font, $this->standardFonts)) {
            throw new Exception('Error: That font is not contained within the standard PDF fonts.');
        }

        // Calculate the approximate width, height and offset baseline values of the string at the certain font.
        $size = [];

        $size['width']    = round(($sz * $this->standardFonts[$font]['width_factor']) * strlen($str));
        $size['height']   = round($sz * $this->standardFonts[$font]['height_factor']);
        $size['baseline'] = round($sz / 3);

        return $size;
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
    public function drawLine($x1, $y1, $x2, $y2)
    {
        $co_index = $this->getContentObject();
        $this->objects[$co_index]->setStream("\n{$x1} {$y1} m\n{$x2} {$y2} l\nS\n");

        return $this;
    }

    /**
     * Method to add a rectangle to the PDF.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @param  boolean $fill
     * @return Pdf
     */
    public function drawRectangle($x, $y, $w, $h = null, $fill = true)
    {
        if (null === $h) {
            $h = $w;
        }

        $co_index = $this->getContentObject();
        $this->objects[$co_index]->setStream("\n{$x} {$y} {$w} {$h} re\n" . $this->setStyle($fill) . "\n");

        return $this;
    }

    /**
     * Method to add a square to the PDF.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @param  boolean $fill
     * @return Pdf
     */
    public function drawSquare($x, $y, $w, $fill = true)
    {
        $this->drawRectangle($x, $y, $w, $w, $fill);
        return $this;
    }

    /**
     * Method to add an ellipse to the PDF.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @param  int     $h
     * @param  boolean $fill
     * @return Pdf
     */
    public function drawEllipse($x, $y, $w, $h = null, $fill = true)
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
        $coor1_bez1_x = $x1;
        $coor1_bez1_y = (round(0.55 * ($y2 - $y1))) + $y1;
        $coor1_bez2_x = $x1;
        $coor1_bez2_y = (round(0.45 * ($y1 - $y4))) + $y4;

        // Calculate coordinate number two's 2 bezier points.
        $coor2_bez1_x = (round(0.45 * ($x2 - $x1))) + $x1;
        $coor2_bez1_y = $y2;
        $coor2_bez2_x = (round(0.55 * ($x3 - $x2))) + $x2;
        $coor2_bez2_y = $y2;

        // Calculate coordinate number three's 2 bezier points.
        $coor3_bez1_x = $x3;
        $coor3_bez1_y = (round(0.55 * ($y2 - $y3))) + $y3;
        $coor3_bez2_x = $x3;
        $coor3_bez2_y = (round(0.45 * ($y3 - $y4))) + $y4;

        // Calculate coordinate number four's 2 bezier points.
        $coor4_bez1_x = (round(0.55 * ($x3 - $x4))) + $x4;
        $coor4_bez1_y = $y4;
        $coor4_bez2_x = (round(0.45 * ($x4 - $x1))) + $x1;
        $coor4_bez2_y = $y4;

        $co_index = $this->getContentObject();
        $this->objects[$co_index]->setStream("\n{$x1} {$y1} m\n{$coor1_bez1_x} {$coor1_bez1_y} {$coor2_bez1_x} {$coor2_bez1_y} {$x2} {$y2} c\n{$coor2_bez2_x} {$coor2_bez2_y} {$coor3_bez1_x} {$coor3_bez1_y} {$x3} {$y3} c\n{$coor3_bez2_x} {$coor3_bez2_y} {$coor4_bez1_x} {$coor4_bez1_y} {$x4} {$y4} c\n{$coor4_bez2_x} {$coor4_bez2_y} {$coor1_bez2_x} {$coor1_bez2_y} {$x1} {$y1} c\n" . $this->setStyle($fill) . "\n");

        return $this;
    }

    /**
     * Method to add a circle to the PDF.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @param  boolean $fill
     * @return Pdf
     */
    public function drawCircle($x, $y, $w, $fill = true)
    {
        $this->drawEllipse($x, $y, $w, $w, $fill);
        return $this;
    }

    /**
     * Method to add an arc to the PDF.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $start
     * @param  int $end
     * @param  int $w
     * @param  int $h
     * @param  boolean $fill
     * @return Pdf
     */
    public function drawArc($x, $y, $start, $end, $w, $h = null, $fill = true)
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
        $endQuad = $this->getQuadrant($endPoint, $centerPoint);

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

        $this->drawEllipse($x, $y, $w, $h, $fill);
        $this->drawClippingPolygon($polyPoints, true);

        return $this;
    }

    /**
     * Method to add a polygon to the image.
     *
     * @param  array $points
     * @param  boolean $fill
     * @return Pdf
     */
    public function drawPolygon($points, $fill = true)
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

        $co_index = $this->getContentObject();
        $this->objects[$co_index]->setStream("\n{$polygon}\n" . $this->setStyle($fill) . "\n");

        return $this;
    }

    /**
     * Method to open a new graphics state layer within the PDF.
     * Must be used in conjunction with the closeLayer() method.
     *
     * @return Pdf
     */
    public function openLayer()
    {
        $co_index = $this->getContentObject();
        $this->objects[$co_index]->setStream("\nq\n");

        return $this;
    }

    /**
     * Method to close a new graphics state layer within the PDF.
     * Must be used in conjunction with the openLayer() method.
     *
     * @return Pdf
     */
    public function closeLayer()
    {
        $co_index = $this->getContentObject();
        $this->objects[$co_index]->setStream("\nQ\n");

        return $this;
    }

    /**
     * Method to add a clipping rectangle to the PDF.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @return Pdf
     */
    public function drawClippingRectangle($x, $y, $w, $h = null)
    {
        $oldFillColor = $this->fillColor;
        $oldStrokeColor = $this->strokeColor;
        $oldStrokeWidth = $this->strokeWidth;
        $oldStrokeDashLength = $this->strokeDashLength;
        $oldStrokeDashGap = $this->strokeDashGap;

        $this->setFillColor($this->backgroundColor);
        $this->setStrokeWidth(false);

        $h = (null === $h) ? $w : $h;
        $co_index = $this->getContentObject();
        $this->objects[$co_index]->setStream("\n{$x} {$y} {$w} {$h} re\nW\nF\n");

        $this->setFillColor($oldFillColor);
        if (null !== $oldStrokeColor) {
            $this->setStrokeColor($oldStrokeColor);
            $this->setStrokeWidth($oldStrokeWidth, $oldStrokeDashLength, $oldStrokeDashGap);
        }

        return $this;
    }

    /**
     * Method to add a clipping square to the PDF.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @return Pdf
     */
    public function drawClippingSquare($x, $y, $w)
    {
        $this->drawClippingRectangle($x, $y, $w, $w);
        return $this;
    }

    /**
     * Method to add a clipping ellipse to the PDF.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @param  int     $h
     * @return Pdf
     */
    public function drawClippingEllipse($x, $y, $w, $h = null)
    {
        $oldFillColor = $this->fillColor;
        $oldStrokeColor = $this->strokeColor;
        $oldStrokeWidth = $this->strokeWidth;
        $oldStrokeDashLength = $this->strokeDashLength;
        $oldStrokeDashGap = $this->strokeDashGap;

        $this->setFillColor($this->backgroundColor);
        $this->setStrokeWidth(false);

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
        $coor1_bez1_x = $x1;
        $coor1_bez1_y = (round(0.55 * ($y2 - $y1))) + $y1;
        $coor1_bez2_x = $x1;
        $coor1_bez2_y = (round(0.45 * ($y1 - $y4))) + $y4;

        // Calculate coordinate number two's 2 bezier points.
        $coor2_bez1_x = (round(0.45 * ($x2 - $x1))) + $x1;
        $coor2_bez1_y = $y2;
        $coor2_bez2_x = (round(0.55 * ($x3 - $x2))) + $x2;
        $coor2_bez2_y = $y2;

        // Calculate coordinate number three's 2 bezier points.
        $coor3_bez1_x = $x3;
        $coor3_bez1_y = (round(0.55 * ($y2 - $y3))) + $y3;
        $coor3_bez2_x = $x3;
        $coor3_bez2_y = (round(0.45 * ($y3 - $y4))) + $y4;

        // Calculate coordinate number four's 2 bezier points.
        $coor4_bez1_x = (round(0.55 * ($x3 - $x4))) + $x4;
        $coor4_bez1_y = $y4;
        $coor4_bez2_x = (round(0.45 * ($x4 - $x1))) + $x1;
        $coor4_bez2_y = $y4;

        $co_index = $this->getContentObject();
        $this->objects[$co_index]->setStream("\n{$x1} {$y1} m\n{$coor1_bez1_x} {$coor1_bez1_y} {$coor2_bez1_x} {$coor2_bez1_y} {$x2} {$y2} c\n{$coor2_bez2_x} {$coor2_bez2_y} {$coor3_bez1_x} {$coor3_bez1_y} {$x3} {$y3} c\n{$coor3_bez2_x} {$coor3_bez2_y} {$coor4_bez1_x} {$coor4_bez1_y} {$x4} {$y4} c\n{$coor4_bez2_x} {$coor4_bez2_y} {$coor1_bez2_x} {$coor1_bez2_y} {$x1} {$y1} c\nW\nF\n");

        $this->setFillColor($oldFillColor);
        if (null !== $oldStrokeColor) {
            $this->setStrokeColor($oldStrokeColor);
            $this->setStrokeWidth($oldStrokeWidth, $oldStrokeDashLength, $oldStrokeDashGap);
        }

        return $this;
    }

    /**
     * Method to add a clipping circle to the PDF.
     *
     * @param  int     $x
     * @param  int     $y
     * @param  int     $w
     * @return Pdf
     */
    public function drawClippingCircle($x, $y, $w)
    {
        $this->drawClippingEllipse($x, $y, $w, $w);
        return $this;
    }

    /**
     * Method to add a clipping polygon to the PDF.
     *
     * @param  array $points
     * @return Pdf
     */
    public function drawClippingPolygon($points)
    {
        $oldFillColor = $this->fillColor;
        $oldStrokeColor = $this->strokeColor;
        $oldStrokeWidth = $this->strokeWidth;
        $oldStrokeDashLength = $this->strokeDashLength;
        $oldStrokeDashGap = $this->strokeDashGap;

        $this->setFillColor($this->backgroundColor);
        $this->setStrokeWidth(false);

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
        $polygon .= "W\n";

        $co_index = $this->getContentObject();
        $this->objects[$co_index]->setStream("\n{$polygon}\nF\n");

        $this->setFillColor($oldFillColor);
        if (null !== $oldStrokeColor) {
            $this->setStrokeColor($oldStrokeColor);
            $this->setStrokeWidth($oldStrokeWidth, $oldStrokeDashLength, $oldStrokeDashGap);
        }

        return $this;
    }

    /**
     * Method to add a URL link to the PDF.
     *
     * @param  int    $x
     * @param  int    $y
     * @param  int    $w
     * @param  int    $h
     * @param  string $url
     * @return Pdf
     */
    public function addUrl($x, $y, $w, $h, $url)
    {
        $x2 = $x + $w;
        $y2 = $y + $h;

        $i = $this->lastIndex($this->objects) + 1;

        // Add the annotation index to the current page's annotations and add the annotation to _objects array.
        $this->objects[$this->objects[$this->pages[$this->curPage]]->index]->annots[] = $i;
        $this->objects[$i] = new Object\Object("{$i} 0 obj\n<<\n    /Type /Annot\n    /Subtype /Link\n    /Rect [{$x} {$y} {$x2} {$y2}]\n    /Border [0 0 0]\n    /A <</S /URI /URI ({$url})>>\n>>\nendobj\n\n");

        return $this;
    }

    /**
     * Method to add an internal link to the PDF.
     *
     * @param  int $x
     * @param  int $y
     * @param  int $w
     * @param  int $h
     * @param  int $X
     * @param  int $Y
     * @param  int $Z
     * @param  int $dest
     * @throws Exception
     * @return Pdf
     */
    public function addLink($x, $y, $w, $h, $X, $Y, $Z, $dest = null)
    {
        $x2 = $x + $w;
        $y2 = $y + $h;

        $i = $this->lastIndex($this->objects) + 1;

        // Set the destination of the internal link, or default to the current page.
        if (null !== $dest) {
            if (!isset($this->pages[$dest - 1])) {
                throw new Exception('Error: That page has not been defined.');
            }
            $d = $this->objects[$this->pages[$dest - 1]]->index;
        // Else, set the destination to the current page.
        } else {
            $d = $this->objects[$this->pages[$this->curPage]]->index;
        }

        // Add the annotation index to the current page's annotations and add the annotation to _objects array.
        $this->objects[$this->objects[$this->pages[$this->curPage]]->index]->annots[] = $i;
        $this->objects[$i] = new Object\Object("{$i} 0 obj\n<<\n    /Type /Annot\n    /Subtype /Link\n    /Rect [{$x} {$y} {$x2} {$y2}]\n    /Border [0 0 0]\n    /Dest [{$d} 0 R /XYZ {$X} {$Y} {$Z}]\n>>\nendobj\n\n");

        return $this;
    }

    /**
     * Method to add an image to the PDF.
     *
     * @param  string  $image
     * @param  int     $x
     * @param  int     $y
     * @param  mixed   $scl
     * @param  boolean $preserveRes
     * @throws Exception
     * @return Pdf
     */
    public function addImage($image, $x, $y, $scl = null, $preserveRes = true)
    {
        if (array_key_exists($image, $this->images) && ($preserveRes)) {
            $i = $this->lastIndex($this->objects) + 1;
            $co_index = $this->images[$image]['index'];
            if (null !== $scl) {
                $dims = Parser\Image::getScaledDimensions($scl, $this->images[$image]['origW'], $this->images[$image]['origH']);
                $imgWidth = $dims['w'];
                $imgHeight = $dims['h'];
            } else {
                $imgWidth = $this->images[$image]['origW'];
                $imgHeight = $this->images[$image]['origH'];
            }
            $this->objects[$this->objects[$this->pages[$this->curPage]]->curContent]->setStream("\nq\n" . $imgWidth . " 0 0 " . $imgHeight. " {$x} {$y} cm\n/I{$co_index} Do\nQ\n");
            $this->objects[$this->objects[$this->pages[$this->curPage]]->index]->xobjs[] = $this->images[$image]['xobj'];
        } else {
            // Create image parser object
            $i = $this->lastIndex($this->objects) + 1;
            $imageParser = new Parser\Image($image, $x, $y, $i, $scl, $preserveRes);

            $imageObjects = $imageParser->getObjects();

            foreach ($imageObjects as $key => $value) {
                $this->objects[$key] = $value;
            }

            // Add the image to the current page's xobject array and content stream.
            $this->objects[$this->objects[$this->pages[$this->curPage]]->index]->xobjs[] = $imageParser->getXObject();

            $co_index = $this->getContentObject();
            $this->objects[$co_index]->setStream($imageParser->getStream());
            if ($preserveRes) {
                $this->images[$image] = [
                    'index' => $i,
                    'origW' => $imageParser->getOrigW(),
                    'origH' => $imageParser->getOrigH(),
                    'xobj'  => $imageParser->getXObject()
                ];
            }
        }

        return $this;
    }

    /**
     * Output the PDF directly to the browser.
     *
     * @param  boolean $download
     * @return Pdf
     */
    public function output($download = false)
    {
        // Format and finalize the PDF.
        $this->finalize();

        // Determine if the force download argument has been passed.
        $attach = ($download) ? 'attachment; ' : null;
        $headers = array(
            'Content-type'        => $this->mime,
            'Content-disposition' => $attach . 'filename=' . $this->basename
        );

        if (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443)) {
            $headers['Expires']       = 0;
            $headers['Cache-Control'] = 'private, must-revalidate';
            $headers['Pragma']        = 'cache';
        }

        header('HTTP/1.1 200 OK');
        foreach ($headers as $name => $value) {
            header($name . ": " . $value);
        }

        echo $this->output;
        return $this;
    }

    /**
     * Save the PDF directly to the server.
     *
     * @param  string  $to
     * @param  boolean $append
     * @throws Exception
     * @return Pdf
     */
    public function save($to = null, $append = false)
    {
        // Format and finalize the PDF.
        $this->finalize();

        $file = (null === $to) ? $this->fullpath : $to;

        if ($append) {
            file_put_contents($file, $this->output, FILE_APPEND);
        } else {
            file_put_contents($file, $this->output);
        }

        return $this;
    }

    /**
     * Method to finalize the PDF.
     *
     * @return Pdf
     */
    public function finalize()
    {
        $this->output = null;

        // Define some variables and initialize the trailer.
        $numObjs = count($this->objects) + 1;
        $this->trailer = "xref\n0 {$numObjs}\n0000000000 65535 f \n";

        // Calculate the root object lead off.
        $byteLength = $this->calcByteLength($this->objects[$this->root]);
        $this->bytelength += $byteLength;
        $this->trailer .= $this->formatByteLength($this->bytelength) . " 00000 n \n";
        $this->output .= $this->objects[$this->root];

        // Loop through the rest of the objects, calculate their size and length for the xref table and add their data to the output.
        foreach ($this->objects as $obj) {
            if ($obj->index != $this->root) {
                if (($obj instanceof Object\Object) && ($this->compress) && (!$obj->isPalette()) && (!$obj->isCompressed())) {
                    $obj->compress();
                }
                $byteLength = $this->calcByteLength($obj);
                $this->bytelength += $byteLength;
                $this->trailer .= $this->formatByteLength($this->bytelength) . " 00000 n \n";
                $this->output .= $obj;
            }
        }

        // Finalize the trailer.
        $this->trailer .= "trailer\n<</Size {$numObjs}/Root {$this->root} 0 R/Info {$this->info} 0 R>>\nstartxref\n" . ($this->bytelength + 68) . "\n%%EOF";

        // Append the trailer to the final output.
        $this->output .= $this->trailer;

        return $this;
    }

    /**
     * Method to return the current page's content object, or create one if necessary.
     *
     * @return int
     */
    protected function getContentObject()
    {
        // If the page's current content object index is not set, create one.
        if (null === $this->objects[$this->objects[$this->pages[$this->curPage]]->index]->curContent) {
            $coi = $this->lastIndex($this->objects) + 1;
            $this->objects[$this->objects[$this->pages[$this->curPage]]->index]->content[] = $coi;
            $this->objects[$this->objects[$this->pages[$this->curPage]]->index]->curContent = $coi;
            $this->objects[$coi] = new Object\Object($coi);
        // Else, set and return the page's current content object index.
        } else {
            $coi = $this->objects[$this->objects[$this->pages[$this->curPage]]->index]->curContent;
        }

        return $coi;
    }

    /**
     * Method to calculate text matrix.
     *
     * @return string
     */
    protected function calcTextMatrix()
    {
        // Define some variables.
        $a   = '';
        $b   = '';
        $c   = '';
        $d   = '';
        $neg = null;

        // Determine is the rotate parameter is negative or not.
        $neg = ($this->textParams['rot'] < 0) ? true : false;

        // Calculate the text matrix parameters.
        $rot = abs($this->textParams['rot']);

        if (($rot >= 0) && ($rot <= 45)) {
            $factor = round(($rot / 45), 2);
            if ($neg) {
                $a = 1;
                $b = '-' . $factor;
                $c = $factor;
                $d = 1;
            } else {
                $a = 1;
                $b = $factor;
                $c = '-' . $factor;
                $d = 1;
            }
        } else if (($rot > 45) && ($rot <= 90)) {
            $factor = round(((90 - $rot) / 45), 2);
            if ($neg) {
                $a = $factor;
                $b = -1;
                $c = 1;
                $d = $factor;
            } else {
                $a = $factor;
                $b = 1;
                $c = -1;
                $d = $factor;
            }
        }

        // Adjust the text matrix parameters according to the horizontal and vertical scale parameters.
        if ($this->textParams['h'] != 100) {
            $a = round(($a * ($this->textParams['h'] / 100)), 2);
            $b = round(($b * ($this->textParams['h'] / 100)), 2);
        }

        if ($this->textParams['v'] != 100) {
            $c = round(($c * ($this->textParams['v'] / 100)), 2);
            $d = round(($d * ($this->textParams['v'] / 100)), 2);
        }

        // Set the text matrix and return it.
        $tm = "{$a} {$b} {$c} {$d}";

        return $tm;
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
        if ($point['x'] >= $center['x']) {
            $quad = ($point['y'] >= $center['y']) ? 4 : 1;
        } else {
            $quad = ($point['y'] >= $center['y']) ? 3 : 2;
        }

        return $quad;
    }

    /**
     * Method to calculate byte length.
     *
     * @param  string $str
     * @return int
     */
    protected function calcByteLength($str)
    {
        $bytes = str_replace("\n", "", $str);
        return strlen($bytes);
    }

    /**
     * Method to format byte length.
     *
     * @param  int|string $num
     * @return string
     */
    protected function formatByteLength($num)
    {
        return sprintf('%010d', $num);
    }

    /**
     * Method to convert color.
     *
     * @param  int|string $color
     * @return float
     */
    protected function convertColor($color)
    {
        $c = round(($color / 256), 2);
        return $c;
    }

    /**
     * Method to set the fill/stroke style.
     *
     * @param  boolean $fill
     * @return string
     */
    protected function setStyle($fill)
    {
        $style = null;

        if (($fill) && ($this->stroke)) {
            $style = 'B';
        } else if ($fill) {
            $style = 'F';
        } else {
            $style = 'S';
        }

        return $style;
    }

    /**
     * Method to return the last object index.
     *
     * @param  array $arr
     * @return int
     */
    protected function lastIndex(array $arr)
    {
        $last = null;
        $objs = array_keys($arr);
        sort($objs);

        foreach ($objs as $obj) {
            $last = $obj;
        }

        return $last;
    }

}
