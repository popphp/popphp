<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Font
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Font;

/**
 * Type1 font class
 *
 * @category   Pop
 * @package    Pop_Font
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Type1 extends AbstractFont
{

    /**
     * Type1 dictionary
     * @var string
     */
    public $dict = null;

    /**
     * Type1 data
     * @var string
     */
    public $data = null;

    /**
     * Type1 data in hex format
     * @var string
     */
    public $hex = null;

    /**
     * Type1 encoding
     * @var string
     */
    public $encoding = null;

    /**
     * Type1 length1
     * @var int
     */
    public $length1 = null;

    /**
     * Type1 length2
     * @var int
     */
    public $length2 = null;

    /**
     * Type1 font data
     * @var string
     */
    public $fontData = null;

    /**
     * Type1 PFB file path
     * @var string
     */
    public $pfbPath = null;

    /**
     * Type1 AFM file path
     * @var string
     */
    public $afmPath = null;

    /**
     * Constructor
     *
     * Instantiate a Type1 font file object based on a pre-existing font file on disk.
     *
     * @param  string $font
     * @return \Pop\Font\Type1
     */
    public function __construct($font)
    {
        parent::__construct($font);

        $dir = realpath($this->dir);

        if (strtolower($this->extension) == 'pfb') {
            $this->pfbPath = $this->fullpath;
            $this->parsePfb($this->fullpath);
            if (file_exists($dir . DIRECTORY_SEPARATOR . $this->filename . '.afm')) {
                $this->afmPath = $dir . DIRECTORY_SEPARATOR . $this->filename . '.afm';
            } else if (file_exists($dir . DIRECTORY_SEPARATOR . $this->filename . '.AFM')) {
                $this->afmPath = $dir . DIRECTORY_SEPARATOR . $this->filename . '.AFM';
            }
            if (null !== $this->afmPath) {
                $this->parseAfm($this->afmPath);
            }
        } else if (strtolower($this->extension) == 'afm') {
            $this->afmPath = $this->fullpath;
            $this->parseAfm($this->afmPath);
            if (file_exists($dir . DIRECTORY_SEPARATOR . $this->filename . '.pfb')) {
                $this->pfbPath = $dir . DIRECTORY_SEPARATOR . $this->filename . '.pfb';
            } else if (file_exists($dir . DIRECTORY_SEPARATOR . $this->filename . '.PFB')) {
                $this->pfbPath = $dir . DIRECTORY_SEPARATOR . $this->filename . '.PFB';
            }
            if (null !== $this->pfbPath) {
                $this->parsePfb($this->pfbPath);
            }
        }
    }

    /**
     * Method to parse the Type1 PFB file.
     *
     * @param  string $pfb
     * @return void
     */
    protected function parsePfb($pfb)
    {
        $data = file_get_contents($pfb);

        // Get lengths and data
        $f = fopen($pfb, 'rb');
        $a = unpack('Cmarker/Ctype/Vsize', fread($f,6));
        $this->length1 = $a['size'];
        $this->fontData = fread($f, $this->length1);
        $a = unpack('Cmarker/Ctype/Vsize', fread($f,6));
        $this->length2 = $a['size'];
        $this->fontData .= fread($f, $this->length2);

        $info = [];
        $this->dict = substr($data, stripos($data, 'FontDirectory'));
        $this->dict = substr($this->dict, 0, stripos($this->dict, 'currentdict end'));

        $this->data = substr($data, (stripos($data, 'currentfile eexec') + 18));
        $this->data = substr($this->data, 0, (stripos($this->data, '0000000000000000000000000000000000000000000000000000000000000000') - 1));

        $this->convertToHex();

        if (stripos($this->dict, '/FullName') !== false) {
            $name = substr($this->dict, (stripos($this->dict, '/FullName ') + 10));
            $name = trim(substr($name, 0, stripos($name, 'readonly def')));
            $info['fullName'] = $this->strip($name);
        }

        if (stripos($this->dict, '/FamilyName') !== false) {
            $family = substr($this->dict, (stripos($this->dict, '/FamilyName ') + 12));
            $family = trim(substr($family, 0, stripos($family, 'readonly def')));
            $info['fontFamily'] = $this->strip($family);
        }

        if (stripos($this->dict, '/FontName') !== false) {
            $font = substr($this->dict, (stripos($this->dict, '/FontName ') + 10));
            $font = trim(substr($font, 0, stripos($font, 'def')));
            $info['postscriptName'] = $this->strip($font);
        }

        if (stripos($this->dict, '/version') !== false) {
            $version = substr($this->dict, (stripos($this->dict, '/version ') + 9));
            $version = trim(substr($version, 0, stripos($version, 'readonly def')));
            $info['version'] = $this->strip($version);
        }

        if (stripos($this->dict, '/UniqueId') !== false) {
            $matches = [];
            preg_match('/UniqueID\s\d/', $this->dict, $matches, PREG_OFFSET_CAPTURE);
            $id = substr($this->dict, ($matches[0][1] + 9));
            $id = trim(substr($id, 0, stripos($id, 'def')));
            $info['uniqueId'] = $this->strip($id);
        }

        if (stripos($this->dict, '/Notice') !== false) {
            $copyright = substr($this->dict, (stripos($this->dict, '/Notice ') + 8));
            $copyright = substr($copyright, 0, stripos($copyright, 'readonly def'));
            $copyright = str_replace('\\(', '(', $copyright);
            $copyright = trim(str_replace('\\)', ')', $copyright));
            $info['copyright'] = $this->strip($copyright);
        }

        $this->info = new \ArrayObject($info, \ArrayObject::ARRAY_AS_PROPS);

        if (stripos($this->dict, '/FontBBox') !== false) {
            $bbox = substr($this->dict, (stripos($this->dict, '/FontBBox') + 9));
            $bbox = substr($bbox, 0, stripos($bbox, 'readonly def'));
            $bbox = trim($this->strip($bbox));
            $bboxAry = explode(' ', $bbox);
            $this->bBox = new \ArrayObject([
                'xMin' => str_replace('{', '', $bboxAry[0]),
                'yMin' => $bboxAry[1],
                'xMax' => $bboxAry[2],
                'yMax' => str_replace('}', '', $bboxAry[3])
            ], \ArrayObject::ARRAY_AS_PROPS);
        }

        if (stripos($this->dict, '/Ascent') !== false) {
            $ascent = substr($this->dict, (stripos($this->dict, '/ascent ') + 8));
            $this->ascent = trim(substr($ascent, 0, stripos($ascent, 'def')));
        }

        if (stripos($this->dict, '/Descent') !== false) {
            $descent = substr($this->dict, (stripos($this->dict, '/descent ') + 9));
            $this->descent = trim(substr($descent, 0, stripos($descent, 'def')));
        }

        if (stripos($this->dict, '/ItalicAngle') !== false) {
            $italic = substr($this->dict, (stripos($this->dict, '/ItalicAngle ') + 13));
            $this->italicAngle = trim(substr($italic, 0, stripos($italic, 'def')));
            if ($this->italicAngle != 0) {
                $this->flags->isItalic = true;
            }
        }

        if (stripos($this->dict, '/em') !== false) {
            $units = substr($this->dict, (stripos($this->dict, '/em ') + 4));
            $this->unitsPerEm = trim(substr($units, 0, stripos($units, 'def')));
        }

        if (stripos($this->dict, '/isFixedPitch') !== false) {
            $fixed = substr($this->dict, (stripos($this->dict, '/isFixedPitch ') + 14));
            $fixed = trim(substr($fixed, 0, stripos($fixed, 'def')));
            $this->flags->isFixedPitch = ($fixed == 'true') ? true : false;
        }

        if (stripos($this->dict, '/ForceBold') !== false) {
            $force = substr($this->dict, (stripos($this->dict, '/ForceBold ') + 11));
            $force = trim(substr($force, 0, stripos($force, 'def')));
            $this->flags->isForceBold = ($force == 'true') ? true : false;
        }

        if (stripos($this->dict, '/Encoding') !== false) {
            $enc = substr($this->dict, (stripos($this->dict, '/Encoding ') + 10));
            $this->encoding = trim(substr($enc, 0, stripos($enc, 'def')));
        }
    }

    /**
     * Method to parse the Type1 Adobe Font Metrics file
     *
     * @param  string $afm
     * @return void
     */
    protected function parseAfm($afm)
    {
        $data = file_get_contents($afm);

        if (stripos($data, 'FontBBox') !== false) {
            $bbox = substr($data, (stripos($data, 'FontBBox') + 8));
            $bbox = substr($bbox, 0, stripos($bbox, "\n"));
            $bbox = trim($bbox);
            $bboxAry = explode(' ', $bbox);
            $this->bBox = new \ArrayObject([
                'xMin' => $bboxAry[0],
                'yMin' => $bboxAry[1],
                'xMax' => $bboxAry[2],
                'yMax' => $bboxAry[3]
            ], \ArrayObject::ARRAY_AS_PROPS);
        }

        if (stripos($data, 'ItalicAngle') !== false) {
            $ital = substr($data, (stripos($data, 'ItalicAngle ') + 11));
            $this->italicAngle = trim(substr($ital, 0, stripos($ital, "\n")));
            if ($this->italicAngle != 0) {
                $this->flags->isItalic = true;
            }
        }

        if (stripos($data, 'IsFixedPitch') !== false) {
            $fixed = substr($data, (stripos($data, 'IsFixedPitch ') + 13));
            $fixed = strtolower(trim(substr($fixed, 0, stripos($fixed, "\n"))));
            if ($fixed == 'true') {
                $this->flags->isFixedPitch = true;
            }
        }

        if (stripos($data, 'CapHeight') !== false) {
            $cap = substr($data, (stripos($data, 'CapHeight ') + 10));
            $this->capHeight = trim(substr($cap, 0, stripos($cap, "\n")));
        }

        if (stripos($data, 'Ascender') !== false) {
            $asc = substr($data, (stripos($data, 'Ascender ') + 9));
            $this->ascent = trim(substr($asc, 0, stripos($asc, "\n")));
        }

        if (stripos($data, 'Descender') !== false) {
            $desc = substr($data, (stripos($data, 'Descender ') + 10));
            $this->descent = trim(substr($desc, 0, stripos($desc, "\n")));
        }

        if (stripos($data, 'StartCharMetrics') !== false) {
            $num = substr($data, (stripos($data, 'StartCharMetrics ') + 17));
            $this->numberOfGlyphs = trim(substr($num, 0, stripos($num, "\n")));
            $chars = substr($data, (stripos($data, 'StartCharMetrics ') + 17 + strlen($this->numberOfGlyphs)));
            $chars = trim(substr($chars, 0, stripos($chars, 'EndCharMetrics')));
            $glyphs = explode("\n", $chars);
            $widths = [];
            foreach ($glyphs as $glyph) {
                $w = substr($glyph, (stripos($glyph, 'WX ') + 3));
                $w = substr($w, 0, strpos($w, ' ;'));
                $widths[] = $w;
            }
            $this->glyphWidths = $widths;
        }
    }

    /**
     * Method to convert the data string to hex.
     *
     * @return void
     */
    protected function convertToHex()
    {
        $ary = str_split($this->data);
        $length = count($ary);

        for ($i = 0; $i < $length; $i++) {
            $this->hex .= bin2hex($ary[$i]);
        }
    }

    /**
     * Method to strip parentheses et al from a string.
     *
     * @param  string $str
     * @return string
     */
    protected function strip($str)
    {
        // Strip parentheses
        if (substr($str, 0, 1) == '(') {
            $str = substr($str, 1);
        }
        if (substr($str, -1) == ')') {
            $str = substr($str, 0, -1);
        }
        // Strip curly brackets
        if (substr($str, 0, 1) == '{') {
            $str = substr($str, 1);
        }
        if (substr($str, -1) == '}') {
            $str = substr($str, 0, -1);
        }
        // Strip leading slash
        if (substr($str, 0, 1) == '/') {
            $str = substr($str, 1);
        }

        return $str;
    }

}
