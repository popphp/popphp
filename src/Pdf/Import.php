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

/**
 * Pdf import class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Import
{

    /**
     * PDF imported objects
     * @var array
     */
    public $objects = [];

    /**
     * PDF imported page objects
     * @var array
     */
    public $pages = [];

    /**
     * PDF imported data
     * @var string
     */
    protected $data = null;

    /**
     * PDF imported kids indices
     * @var array
     */
    protected $kids = [];

    /**
     * PDF imported thumb objects
     * @var array
     */
    protected $thumbs = [];

    /**
     * Constructor
     *
     * Instantiate a PDF import object.
     *
     * @param  string $pdf
     * @param  int|string|array $pgs
     * @return \Pop\Pdf\Import
     */
    public function __construct($pdf, $pgs = null)
    {
        // Read the file data from the imported PDF.
        $this->data = file_get_contents($pdf);

        // Strip any and all XREF tables, as the structure of the PDF will change.
        while (strpos($this->data, 'xref') !== false) {
            $xref = substr($this->data, 0, (strpos($this->data, '%%EOF') + 5));
            $xref = substr($xref, strpos($xref, 'xref'));
            $this->data = str_replace($xref, '', $this->data);
        }

        // Get the PDF objects.
        $this->getObjects($this->data);
        $this->pages = $this->kids;

        // If the page argument was passed, parse out the desired page(s), removing any unwanted pages and their content.
        if (null !== $pgs) {
            if (is_array($pgs)) {
                foreach ($pgs as $value) {
                    $pAry[] = $this->pages[$value - 1];
                }
            } else {
                $pAry[] = $this->pages[$pgs - 1];
            }

            $rm = [];
            foreach ($this->pages as $value) {
                if (!in_array($value, $pAry)) {
                    $rm[] = $value;
                }
            }

            // Remove unwanted pages and their content from the imported data.
            if (count($rm) != 0) {
                foreach ($rm as $value) {
                    $content = substr($this->objects[$value]['data'], strpos($this->objects[$value]['data'], 'Contents'));
                    $content = substr($content, 0, strpos($content, '/'));
                    $content = str_replace('Contents', '', $content);
                    $content = str_replace('[', '', $content);
                    $content = str_replace(']', '', $content);
                    $content = str_replace(' 0 R', '|', $content);
                    $content = str_replace(' ', '', $content);
                    $content = substr($content, 0, -1);
                    $content_objs = explode('|', $content);

                    unset($this->objects[$value]);

                    if (in_array($value, $this->kids)) {
                        $k = array_search($value, $this->kids);
                        unset($this->kids[$k]);
                    }

                    foreach ($content_objs as $val) {
                        unset($this->objects[$val]);
                    }
                }

                $this->pages = $this->kids;
            }
        }
    }

    /**
     * Method to shift the objects' indices based on the array of indices passed to the method, to prevent duplication.
     *
     * @param  int $si
     * @return void
     */
    public function shiftObjects($si)
    {
        if ($this->firstIndex($this->objects) <= $si) {
            ksort($this->objects);
            $keyChanges = [];
            $newObjects = [];

            foreach ($this->objects as $key => $value) {
                $keyChanges[$key] = $si;
                $newObjects[$si] = $this->objects[$key];
                if (substr($newObjects[$si]['data'], 0, strlen($key . ' 0 obj')) === ($key . ' 0 obj')) {
                    $newObjects[$si]['data'] = str_replace($key . ' 0 obj', $si . ' 0 obj', $newObjects[$si]['data']);
                }
                $si++;
            }

            $keyChanges = array_reverse($keyChanges, true);
            foreach ($newObjects as $key => $obj) {
                if (count($obj['refs']) > 0) {
                    $matches = [];
                    preg_match_all('/\d+\s0\sR/mi', $newObjects[$key]['data'], $matches, PREG_OFFSET_CAPTURE);
                    if (isset($matches[0][0])) {
                        $start = count($matches[0]) - 1;
                        for ($i = $start; $i >= 0; $i--) {
                            $ref = $matches[0][$i][0];
                            $len = $matches[0][$i][1];
                            $k   = substr($ref, 0, strpos($ref, ' '));
                            if (isset($keyChanges[$k])) {
                                $newObjects[$key]['data'] = substr_replace($newObjects[$key]['data'], $keyChanges[$k] . ' 0 R', $len, strlen($ref));
                            }
                        }
                    }
                }
            }

            foreach ($this->pages as $k => $v) {
                if (isset($keyChanges[$v])) {
                    $this->kids[$k] = $keyChanges[$v];
                }
            }

            $this->objects = $newObjects;
            $this->pages = $this->kids;
        }
    }

    /**
     * Method to return the desired imported objects to the main PDF object.
     *
     * @param  int $par
     * @return array
     */
    public function returnObjects($par)
    {
        $objs = [];
        $keys = array_keys($this->objects);

        foreach ($keys as $key) {
            // Skip the root, parent and info objects, returning only page and content objects.
            if (($this->objects[$key]['type'] != 'root') && ($this->objects[$key]['type'] != 'parent') && ($this->objects[$key]['type'] != 'info')) {
                if ($this->objects[$key]['type'] == 'page') {
                    $parent = substr($this->objects[$key]['data'], strpos($this->objects[$key]['data'], 'Parent'));
                    $parent = substr($parent, 0, strpos($parent, '/'));
                    $parent = str_replace('Parent', '', $parent);
                    $parent = str_replace(' 0 R', '', $parent);
                    $parent = str_replace(' ', '', $parent);
                    $this->objects[$key]['data'] = str_replace('/Parent ' . $parent . ' 0 R', '/Parent ' . $par . ' 0 R', $this->objects[$key]['data']);
                    $this->objects[$key]['data'] = str_replace('/Parent [' . $parent . ' 0 R', '/Parent [' . $par . ' 0 R', $this->objects[$key]['data']);
                }
                $objs[$key] = $this->objects[$key];
            }

        }

        return $objs;
    }

    /**
     * Method to search and return the objects within in the imported data.
     *
     * @param  string $data
     * @return void
     */
    protected function getObjects($data)
    {
        // Grab object start points.
        preg_match_all('/\d*\s\d*\sobj/', $data, $matches, PREG_OFFSET_CAPTURE);

        if (isset($matches[0])) {
            $obj_start = $matches[0];

            // Start parsing through the object data.
            for ($i = 0; $i < count($obj_start); $i++) {
                $type = '';
                $j = $i + 1;
                $index = substr($obj_start[$i][0], 0, strpos($obj_start[$i][0], ' '));

                if (array_key_exists($j, $obj_start)) {
                    $obj_data = substr($data, $obj_start[$i][1], ($obj_start[$j][1] - $obj_start[$i][1]));
                } else {
                    $obj_data = substr($data, $obj_start[$i][1], (strrpos($data, 'endobj') - $obj_start[$i][1] + 6));
                }

                // Add all relevant objects, striping away any linearized code, hint codes or metadata, as the order and size of the PDF and its objects may change.
                if ((strpos($obj_data, '/Linearized') === false) && (strpos($obj_data, '/Type/Metadata') === false)) {
                    if ((strpos($obj_data, '/Catalog') !== false) && (strpos($obj_data, '/Pages') !== false)) {
                        // Strip away any metadata references.
                        $metadata = substr($obj_data, strpos($obj_data, 'Metadata'));
                        $metadata = '/' . substr($metadata, 0, strpos($metadata, '/'));
                        $obj_data = str_replace($metadata, '', $obj_data);
                        $type = 'root';
                    } else if ((strpos($obj_data, '/Creator') !== false) || (strpos($obj_data, '/Producer') !== false)) {
                        $type = 'info';
                    } else if ((strpos($obj_data, '/Count') !== false) && (strpos($obj_data, '/Kids') !== false)) {
                        $kids = substr($obj_data, strpos($obj_data, 'Kids'));
                        $kids = substr($kids, 0, strpos($kids, ']'));
                        $kids = str_replace('Kids', '', $kids);
                        $kids = str_replace('[', '', $kids);
                        $kids = str_replace(' 0 R', '|', $kids);
                        $kids = str_replace(' ', '', $kids);
                        $kids = substr($kids, 0, -1);
                        $kids_objs = explode('|', $kids);
                        $this->kids = $kids_objs;
                        $type = 'parent';
                    } else if ((strpos($obj_data, '/MediaBox') !== false) || (strpos($obj_data, '/Contents') !== false)) {
                        if (strpos($obj_data, '/Thumb') !== false) {
                            // Strip away any thumbnail references.
                            $thumbdata = substr($obj_data, strpos($obj_data, 'Thumb'));
                            $thumbdata = '/' . substr($thumbdata, 0, strpos($thumbdata, '/'));

                            $thumbindex = substr($thumbdata, strpos($thumbdata, ' '));
                            $thumbindex = str_replace(' 0 R', '', $thumbindex);
                            $thumbindex = str_replace(' ', '', $thumbindex);
                            $this->thumbs[] = $thumbindex;

                            $obj_data = str_replace($thumbdata, '', $obj_data);
                        }
                        $type = 'page';
                    } else {
                        $type = 'content';
                    }
                    $this->objects[$index] = ['type' => $type, 'data' => $obj_data, 'refs' => $this->getRefs($obj_data)];
                }
            }

            // Order the page objects correctly.
            $pageOrder = [];

            foreach ($this->objects as $key => $value) {
                if ($value['type'] == 'page') {
                    $pageOrder[$key] = $value;
                    unset($this->objects[$key]);
                }
            }

            foreach ($this->kids as $value) {
                if (isset($pageOrder[$value])) {
                    $this->objects[$value] = $pageOrder[$value];
                }
            }

            // Remove any thumbnail objects.
            if (count($this->thumbs) != 0) {
                foreach ($this->thumbs as $value) {
                    unset($this->objects[$value]);
                }
            }
        }
    }

    /**
     * Method to search and return the object references within in the data.
     *
     * @param  string $data
     * @return array
     */
    protected function getRefs($data)
    {
        $r    = [];
        $refs = [];

        // Grab reference start points.
        if (strpos($data, 'stream') !== false) {
            $data = substr($data, 0, strpos($data, 'stream'));
        }
        preg_match_all('/\d*\s0*\sR/', $data, $r, PREG_OFFSET_CAPTURE);
        foreach ($r[0] as $value) {
            $refs[] = str_replace(' 0 R', '', $value[0]);
        }

        sort($refs);
        return $refs;
    }

    /**
     * Method to return the last object index.
     *
     * @param  array $arr
     * @return int
     */
    protected function lastIndex(array $arr)
    {
        $objs = array_keys($arr);
        sort($objs);
        $last = null;

        foreach ($objs as $value) {
            $last = $value;
        }

        return $last;
    }

    /**
     * Method to return the first object index.
     *
     * @param  array $arr
     * @return int
     */
    protected function firstIndex(array $arr)
    {
        $objs = array_keys($arr);
        rsort($objs);
        $first = null;

        foreach ($objs as $value) {
            $first = $value;
        }

        return $first;
    }
}
