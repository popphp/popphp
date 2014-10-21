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
namespace Pop\Pdf\Object;

/**
 * Pdf page object class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Page extends \Pop\Pdf\AbstractObject implements ObjectInterface
{

    /**
     * Allowed properties
     * @var array
     */
    protected $allowed = [
        'index'      => 4,
        'parent'     => 2,
        'width'      => 612,
        'height'     => 792,
        'curContent' => null,
        'annots'     => [],
        'content'    => [],
        'xobjs'      => [],
        'fonts'      => [],
        'thumb'      => null
    ];

    /**
     * PDF page object data
     * @var string
     */
    protected $data = null;

    /**
     * Array of page sizes
     * @var array
     */
    protected $sizes = [
        '#10 Envelope' => ['width' => '297',  'height' => '684'],
        'C5 Envelope'  => ['width' => '461',  'height' => '648'],
        'DL Envelope'  => ['width' => '312',  'height' => '624'],
        'Folio'        => ['width' => '595',  'height' => '935'],
        'Executive'    => ['width' => '522',  'height' => '756'],
        'Letter'       => ['width' => '612',  'height' => '792'],
        'Legal'        => ['width' => '612',  'height' => '1008'],
        'Ledger'       => ['width' => '1224', 'height' => '792'],
        'Tabloid'      => ['width' => '792',  'height' => '1224'],
        'A0'           => ['width' => '2384', 'height' => '3370'],
        'A1'           => ['width' => '1684', 'height' => '2384'],
        'A2'           => ['width' => '1191', 'height' => '1684'],
        'A3'           => ['width' => '842',  'height' => '1191'],
        'A4'           => ['width' => '595',  'height' => '842'],
        'A5'           => ['width' => '420',  'height' => '595'],
        'A6'           => ['width' => '297',  'height' => '420'],
        'A7'           => ['width' => '210',  'height' => '297'],
        'A8'           => ['width' => '148',  'height' => '210'],
        'A9'           => ['width' => '105',  'height' => '148'],
        'B0'           => ['width' => '2920', 'height' => '4127'],
        'B1'           => ['width' => '2064', 'height' => '2920'],
        'B2'           => ['width' => '1460', 'height' => '2064'],
        'B3'           => ['width' => '1032', 'height' => '1460'],
        'B4'           => ['width' => '729',  'height' => '1032'],
        'B5'           => ['width' => '516',  'height' => '729'],
        'B6'           => ['width' => '363',  'height' => '516'],
        'B7'           => ['width' => '258',  'height' => '363'],
        'B8'           => ['width' => '181',  'height' => '258'],
        'B9'           => ['width' => '127',  'height' => '181'],
        'B10'          => ['width' => '91',   'height' => '127']
    ];

    /**
     * Constructor
     *
     * Instantiate a PDF page object.
     *
     * @param  string $str
     * @param  string $sz
     * @param  string $w
     * @param  string $h
     * @param  string $i
     * @throws Exception
     * @return Page
     */
    public function __construct($str = null, $sz = null, $w = null, $h = null, $i = null)
    {
        parent::__construct($this->allowed);

        // Use default settings for a new PDF page.
        if (null === $str) {
            // If no arguments are passed, default to the Letter size.
            if ((null === $sz) && (null === $w) && (null === $h)) {
                $this->width = $this->sizes['Letter']['width'];
                $this->height = $this->sizes['Letter']['height'];
            } else {
                // Check for a default size setting.
                if (null !== $sz) {
                    if (array_key_exists($sz, $this->sizes)) {
                        $this->width = $this->sizes[$sz]['width'];
                        $this->height = $this->sizes[$sz]['height'];
                    } else {
                        // Else, assign a custom width and height.
                        if (((null === $w) && (null !== $h)) || ((null !== $w) && (null === $h))) {
                            throw new Exception('Error: A width and height must be passed.');
                        }
                        $this->width = $w;
                        $this->height = $h;
                    }
                } else {
                    // Else, assign a custom width and height.
                    if (((null === $w) && (null !== $h)) || ((null !== $w) && (null === $h))) {
                        throw new Exception('Error: A width and height must be passed.');
                    }
                    $this->width = $w;
                    $this->height = $h;
                }
            }

            if (null === $i) {
                throw new Exception('Error: A page index must be passed.');
            }
            $this->index = $i;
            $this->data = "\n[{page_index}] 0 obj\n<</Type/Page/Parent [{parent}] 0 R[{annotations}]/MediaBox[0 0 {$this->width} {$this->height}]/Contents[[{content_objects}]]/Resources<</ProcSet[/PDF/Text/ImageB/ImageC/ImageI][{xobjects}][{fonts}]>>>>\nendobj\n";
        } else {
            // Else, determine the page object index.
            $this->index = substr($str, 0, strpos($str, ' '));

            // If present, record and record any thumb object index, as the contents of the page may change.
            if (strpos($str, '/Thumb') !== false) {
                $t = substr($str, strpos($str, 'Thumb'));
                $t = substr($t, 0, strpos($t, '/'));
                $t = str_replace('Thumb', '', $t);
                $t = str_replace('0 R', '', $t);
                $t = str_replace(' ', '', $t);
                $this->thumb = $t;
            }

            // Determine the page parent object index.
            $par = substr($str, strpos($str, 'Parent'));
            $par = substr($par, 0, strpos($par, '/'));
            $par = str_replace('Parent', '', $par);
            $par = str_replace('0 R', '', $par);
            $par = str_replace(' ', '', $par);
            $this->parent = $par;

            // Determine the page width and height.
            $wh = substr($str, strpos($str, 'MediaBox'));
            $wh = substr($wh, 0, (strpos($wh, ']') + 1));
            $wh = (strpos($wh, 'MediaBox [') !== false) ? str_replace('MediaBox [', '', $wh) : str_replace('MediaBox[', '', $wh);
            $wh = str_replace(']', '', $wh);
            $whAry = explode(' ', $wh);
            $this->width = $whAry[2];
            $this->height = $whAry[3];

            // Determine the page content objects.
            $cn = substr($str, strpos($str, 'Contents'));
            if (strpos($cn , '/') !== false) {
                $cn = substr($cn, 0, strpos($cn, '/'));
            } else if (strpos($cn , '>') !== false) {
                $cn = substr($cn, 0, strpos($cn, '>'));
            }
            $cn = str_replace('Contents', '', $cn);
            if (strpos($cn, '[') !== false) {
                $cn = substr($cn, 0, (strpos($cn, ']') + 1));
                $str = str_replace('/Contents' . $cn, '/Contents[{content_objects}]', $str);
                $cn = str_replace('[', '', $cn);
                $cn = str_replace(']', '', $cn);
                $cn = str_replace('0 R', '|', $cn);
                $cn = str_replace(' ', '', $cn);
                $cn = explode('|', $cn);
                foreach ($cn as $value) {
                    if ($value != '') {
                        $this->content[] = $value;
                    }
                }
            } else {
                $str = str_replace('/Contents' . $cn, '/Contents[{content_objects}]', $str);
                $cn = str_replace('0 R', '', $cn);
                $cn = str_replace(' ', '', $cn);
                $this->content[] = $cn;
            }

            // If they exist, determine the page annotation objects.
            if (strpos($str, '/Annots') !== false) {
                $an = substr($str, strpos($str, 'Annots'));
                $an = substr($an, 0, strpos($an, '/'));
                $an = str_replace('Annots', '', $an);
                if (strpos($an, '[') !== false) {
                    $an = substr($an, 0, (strpos($an, ']') + 1));
                    $str = str_replace('/Annots' . $an, '[{annotations}]', $str);
                    $an = str_replace('[', '', $an);
                    $an = str_replace(']', '', $an);
                    $an = str_replace('0 R', '|', $an);
                    $an = str_replace(' ', '', $an);
                    $an = explode('|', $an);
                    foreach ($an as $value) {
                        if ($value != '') {
                            $this->annots[] = $value;
                        }
                    }
                } else {
                    $an = substr($an, 0, strpos($an, '/'));
                    $str = str_replace('/Annots' . $an, '[{annotations}]', $str);
                    $an = str_replace('0 R', '', $an);
                    $an = str_replace(' ', '', $an);
                    $this->annots[] = $an;
                }
            }

            // If they exist, determine the page fonts.
            if (strpos($str, '/Font') !== false) {
                $ft = substr($str, strpos($str, 'Font'));
                $ft = substr($ft, 0, (strpos($ft, '>>') + 2));
                $str = str_replace('/' . $ft, '[{fonts}]', $str);
                $ft = str_replace('Font<<', '', $ft);
                $ft = str_replace('>>', '', $ft);
                $ft = explode('/', $ft);
                foreach ($ft as $value) {
                    if ($value != '') {
                        $this->fonts[] = '/' . $value;
                    }
                }
            }

            // If they exist, determine the page xobjects.
            if (strpos($str, '/XObject') !== false) {
                $xo = substr($str, strpos($str, 'XObject'));
                $xo = substr($xo, 0, (strpos($xo, '>>') + 2));
                $str = str_replace('/' . $xo, '[{xobjects}]', $str);
                $xo = str_replace('XObject<<', '', $xo);
                $xo = str_replace('>>', '', $xo);
                $xo = explode('/', $xo);
                foreach ($xo as $value) {
                    if ($value != '') {
                        $this->xobjs[] = '/' . $value;
                    }
                }
            }

            // If they exist, determine the page graphic states.
            if (strpos($str, '/ExtGState') !== false) {
                $gs = substr($str, strpos($str, 'ExtGState'));
                $gs = substr($gs, 0, (strpos($gs, '>>') + 2));
                //$str = str_replace('/' . $gs, '', $str);
                $gs = '/' . $gs;
            } else {
                $gs = '';
            }

            // If any groups exist
            if (strpos($str, '/Group') !== false) {
                $grp = substr($str, strpos($str, 'Group'));
                $grp = substr($grp, 0, (strpos($grp, '>>') + 2));
                $grp = '/' . $grp;
            } else {
                $grp = '';
            }

            // If resources exists
            if (strpos($str, '/Resources') !== false) {
                $res = substr($str, strpos($str, 'Resources'));
                if (strpos($res, '0 R') !== false) {
                    $res = substr($res, 0, (strpos($res, '0 R') + 3));
                    $res = '/' . $res;

                } else if (strpos($res, '>>') !== false) {
                    $res = substr($res, 0, (strpos($res, '>>') + 2));
                    $res = '/' . $res;
                } else {
                    $res = "/Resources<</ProcSet[/PDF/Text/ImageB/ImageC/ImageI][{xobjects}][{fonts}]{$gs}>>";
                }
            } else {
                $res = "/Resources<</ProcSet[/PDF/Text/ImageB/ImageC/ImageI][{xobjects}][{fonts}]{$gs}>>";
            }

            if (substr_count($res, '<<') > substr_count($res, '>>')) {
                $res .= str_repeat('>>', (substr_count($res, '<<') - substr_count($res, '>>')));
            }

            $this->data = "\n[{page_index}] 0 obj\n<</Type/Page/Parent [{parent}] 0 R[{annotations}]/MediaBox[0 0 {$this->width} {$this->height}]{$grp}/Contents[[{content_objects}]]{$res}>>\nendobj\n";
        }
    }

    /**
     * Method to print the parent object.
     *
     * @return string
     */
    public function __toString()
    {
        // Format the content objects.
        $contentObjs = implode(" 0 R ", $this->content);
        $contentObjs .= " 0 R";

        // Format the annotations.
        if (count($this->annots) > 0) {
            $annots = '/Annots[';
            $annots .= implode(" 0 R ", $this->annots);
            $annots .= " 0 R]";
        } else {
            $annots = '';
        }

        // Format the xobjects.
        if (count($this->xobjs) > 0) {
            $xobjects = '/XObject<<';
            $xobjects .= implode('', $this->xobjs);
            $xobjects .= '>>';
        } else {
            $xobjects = '';
        }

        // Format the fonts.
        if (count($this->fonts) > 0) {
            $fonts = '/Font<<';
            $fonts .= implode('', $this->fonts);
            $fonts .= '>>';
        } else {
            $fonts = '';
        }

        // Swap out the placeholders.
        $obj = str_replace('[{page_index}]', $this->index, $this->data);
        $obj = str_replace('[{parent}]', $this->parent, $obj);

        if (($annots != '') && (strpos($obj, '[{annotations}]') === false)) {
            $obj = str_replace('/MediaBox', $annots . '/MediaBox', $obj);
        } else {
            $obj = str_replace('[{annotations}]', $annots, $obj);
        }

        if (($xobjects != '') && (strpos($obj, '[{xobjects}]') === false)) {
            $obj = str_replace('/ProcSet', $xobjects . '/ProcSet', $obj);
        } else {
            $obj = str_replace('[{xobjects}]', $xobjects, $obj);
        }

        if (($fonts != '') && (strpos($obj, '[{fonts}]') === false)) {
            $obj = str_replace('/ProcSet', $fonts . '/ProcSet', $obj);
        } else {
            $obj = str_replace('[{fonts}]', $fonts, $obj);
        }

        $obj = str_replace('[{content_objects}]', $contentObjs, $obj);

        return $obj;
    }

}
