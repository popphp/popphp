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
namespace Pop\Pdf;

use Pop\Pdf\Object;

/**
 * Pdf class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
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
    protected $currentPage = null;

    /**
     * PDF byte length
     * @var int
     */
    protected $byteLength = null;

    /**
     * Array of images added to the PDF
     * @var string
     */
    protected $images = [];

    /**
     * PDF draw object
     * @var Draw\Draw
     */
    protected $draw = null;

    /**
     * PDF type object
     * @var Type\Type
     */
    protected $type = null;

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
        $this->fullpath  = $pdf;
        $parts           = pathinfo($pdf);
        $this->size      = (file_exists($pdf) ? filesize($pdf) : 0);
        $this->dir       = realpath($parts['dirname']);
        $this->basename  = $parts['basename'];
        $this->filename  = $parts['filename'];
        $this->extension = (isset($parts['extension']) && ($parts['extension'] != '')) ? $parts['extension'] : null;

        if ((null === $this->extension) || (strtolower($this->extension) != 'pdf')) {
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
        $import = new Import($pdf, $pg);

        // Shift the imported objects indices based on existing indices in this PDF.
        $import->shiftObjects(($this->lastIndex($this->objects) + 1));

        // Fetch the imported objects.
        $importedObjs = $import->returnObjects($this->parent);

        // Loop through the imported objects, adding the pages or objects as applicable.
        foreach($importedObjs as $key => $value) {
            if ($value['type'] == 'page') {
                // Add the page object.
                $this->objects[$key] = new Object\Page($value['data']);

                // Finalize related page variables and objects.
                $this->currentPage = (null === $this->currentPage) ? 0 : ($this->lastIndex($this->pages) + 1);
                $this->pages[$this->currentPage] = $key;
                $this->objects[$this->parent]->count += 1;
            } else {
                // Else, add the content object.
                $this->objects[$key] = new Object\Object($value['data']);
            }
        }

        foreach ($import->getPages() as $value) {
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
        $this->currentPage = (null === $this->currentPage) ? 0 : ($this->lastIndex($this->pages) + 1);
        $this->pages[$this->currentPage] = $pi;
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
        foreach ($oldContent as $value) {
            $this->objects[$ci] = new Object\Object((string)$this->objects[$value]);
            $this->objects[$ci]->index = $ci;
            $this->objects[$pi]->content[] = $ci;
            $ci += 1;
        }

        // Finalize related page variables and objects.
        $this->currentPage = (null === $this->currentPage) ? 0 : ($this->lastIndex($this->pages) + 1);
        $this->pages[$this->currentPage] = $pi;
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
        $contentObjs = $this->objects[$pi]->content;

        // Remove the page's content objects.
        if (count($contentObjs) != 0) {
            foreach ($contentObjs as $value) {
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
     * Get the Pdf draw object
     *
     * @return Draw\Draw
     */
    public function draw()
    {
        if (null === $this->draw) {
            $this->draw = new Draw\Draw($this);
        }
        if (null === $this->draw->getPdf()) {
            $this->draw->setPdf($this);
        }
        return $this->draw;
    }

    /**
     * Get the Pdf type object
     *
     * @return Type\Type
     */
    public function type()
    {
        if (null === $this->type) {
            $this->type = new Type\Type($this);
        }
        if (null === $this->type->getPdf()) {
            $this->type->setPdf($this);
        }
        return $this->type;
    }

    /**
     * Method to get a page object
     *
     * @param int $i
     * @return int
     */
    public function getPage($i)
    {
        return (isset($this->pages[$i]) ? $this->pages[$i] : null);
    }

    /**
     * Method to get pages array
     *
     * @return array
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Method to get a content object
     *
     * @param int $i
     * @return Object\ObjectInterface
     */
    public function getObject($i)
    {
        return (isset($this->objects[$i]) ? $this->objects[$i] : null);
    }

    /**
     * Method to get content objects array
     *
     * @return array
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * Method to return the current page number of the current page of the PDF.
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return ($this->currentPage + 1);
    }

    /**
     * Method to return the current page number of the current page of the PDF.
     *
     * @return int
     */
    public function getCurrentPageIndex()
    {
        return $this->currentPage;
    }

    /**
     * Method to return the current number of pages in the PDF.
     *
     * @return int
     */
    public function getNumberOfPages()
    {
        return count($this->pages);
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
    public function setCompression($comp)
    {
        $this->compress = (bool)$comp;
        return $this;
    }

    /**
     * Method to set a content object
     *
     * @param  mixed $object
     * @param  int   $i
     * @return Pdf
     */
    public function setObject($object, $i)
    {
        $this->objects[$i] = $object;
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
        $this->currentPage = $pg - 1;

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
        $this->objects[$this->info]->createDate = $dt;
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
        $this->objects[$this->info]->modDate = $dt;
        return $this;
    }

    /**
     * Method to set the background of the document.
     *
     * @param  int $r
     * @param  int $g
     * @param  int $b
     * @return Pdf
     */
    public function setBackgroundColor($r = 0, $g = 0, $b = 0)
    {
        // Create a back fill object, $pdf->draw()->...
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
        $coIndex = $this->getContentObjectIndex();
        $this->objects[$coIndex]->setStream("\nq\n");

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
        $coIndex = $this->getContentObjectIndex();
        $this->objects[$coIndex]->setStream("\nQ\n");

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

        // Add the annotation index to the current page's annotations and add the annotation to objects array.
        $this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->annots[] = $i;
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
            $d = $this->objects[$this->pages[$this->currentPage]]->index;
        }

        // Add the annotation index to the current page's annotations and add the annotation to objects array.
        $this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->annots[] = $i;
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
            $coIndex = $this->images[$image]['index'];
            if (null !== $scl) {
                $dims = Parser\Image::getScaledDimensions($scl, $this->images[$image]['origW'], $this->images[$image]['origH']);
                $imgWidth = $dims['w'];
                $imgHeight = $dims['h'];
            } else {
                $imgWidth = $this->images[$image]['origW'];
                $imgHeight = $this->images[$image]['origH'];
            }
            $this->objects[$this->objects[$this->pages[$this->currentPage]]->curContent]->setStream("\nq\n" . $imgWidth . " 0 0 " . $imgHeight. " {$x} {$y} cm\n/I{$coIndex} Do\nQ\n");
            $this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->xobjs[] = $this->images[$image]['xobj'];
        } else {
            // Create image parser object
            $i = $this->lastIndex($this->objects) + 1;
            $imageParser = new Parser\Image($image, $x, $y, $i, $scl, $preserveRes);

            $imageObjects = $imageParser->getObjects();

            foreach ($imageObjects as $key => $value) {
                $this->objects[$key] = $value;
            }

            // Add the image to the current page's xobject array and content stream.
            $this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->xobjs[] = $imageParser->getXObject();

            $coIndex = $this->getContentObjectIndex();
            $this->objects[$coIndex]->setStream($imageParser->getStream());
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
     * @param  boolean $sendHeaders
     * @return void
     */
    public function output($download = false, $sendHeaders = true)
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

        // Send the headers and output the PDF
        if (!headers_sent() && ($sendHeaders)) {
            header('HTTP/1.1 200 OK');
            foreach ($headers as $name => $value) {
                header($name . ": " . $value);
            }
        }

        echo $this->output;
    }

    /**
     * Save the PDF directly to the server.
     *
     * @param  string  $to
     * @throws Exception
     * @return void
     */
    public function save($to = null)
    {
        $this->finalize();
        file_put_contents(((null === $to) ? $this->fullpath : $to), $this->output);
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
        $this->byteLength += $byteLength;
        $this->trailer .= $this->formatByteLength($this->byteLength) . " 00000 n \n";
        $this->output .= $this->objects[$this->root];

        // Loop through the rest of the objects, calculate their size and length for the xref table and add their data to the output.
        foreach ($this->objects as $obj) {
            if ($obj->index != $this->root) {
                if (($obj instanceof Object\Object) && ($this->compress) && (!$obj->isPalette()) && (!$obj->isCompressed())) {
                    $obj->compress();
                }
                $byteLength = $this->calcByteLength($obj);
                $this->byteLength += $byteLength;
                $this->trailer .= $this->formatByteLength($this->byteLength) . " 00000 n \n";
                $this->output .= $obj;
            }
        }

        // Finalize the trailer.
        $this->trailer .= "trailer\n<</Size {$numObjs}/Root {$this->root} 0 R/Info {$this->info} 0 R>>\nstartxref\n" . ($this->byteLength + 68) . "\n%%EOF";

        // Append the trailer to the final output.
        $this->output .= $this->trailer;

        return $this;
    }

    /**
     * Method to return the current page's content object index, or create one if necessary.
     *
     * @return int
     */
    public function getContentObjectIndex()
    {
        // If the page's current content object index is not set, create one.
        if (null === $this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->curContent) {
            $coIndex = $this->lastIndex($this->objects) + 1;
            $this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->content[] = $coIndex;
            $this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->curContent = $coIndex;
            $this->objects[$coIndex] = new Object\Object($coIndex);
        // Else, set and return the page's current content object index.
        } else {
            $coIndex = $this->objects[$this->objects[$this->pages[$this->currentPage]]->index]->curContent;
        }

        return $coIndex;
    }

    /**
     * Method to return the last object index.
     *
     * @param  array $arr
     * @return int
     */
    public function lastIndex(array $arr)
    {
        $last = null;
        $objs = array_keys($arr);
        sort($objs);

        foreach ($objs as $obj) {
            $last = $obj;
        }

        return $last;
    }

    /**
     * Method to print the PDF.
     *
     * @return string
     */
    public function __toString()
    {
        $this->output();
        return '';
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

}
