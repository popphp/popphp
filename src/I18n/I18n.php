<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_I18n
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\I18n;

/**
 * I18n and l10n class
 *
 * @category   Pop
 * @package    Pop_I18n
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class I18n
{

    /**
     * Default system language
     * @var string
     */
    protected $language = null;

    /**
     * Default system locale
     * @var string
     */
    protected $locale = null;

    /**
     * Language content
     * @var array
     */
    protected $content = array(
        'source' => array(),
        'output' => array()
    );

    /**
     * Constructor
     *
     * Instantiate the I18n object.
     *
     * @param  string $lang
     * @return \Pop\I18n\I18n
     */
    public function __construct($lang = null)
    {
        if (null === $lang) {
            $lang = (defined('POP_LANG')) ? POP_LANG : 'en_US';
        }

        if (strpos($lang, '_') !== false) {
            $ary  = explode('_', $lang);
            $this->language = $ary[0];
            $this->locale = $ary[1];
        } else {
            $this->language = $lang;
            $this->locale = strtoupper($lang);
        }

        $this->loadCurrentLanguage();
    }

    /**
     * Static method to load the I18n object.
     *
     * @param  string $lang
     * @return \Pop\I18n\I18n
     */
    public static function factory($lang = null)
    {
        return new self($lang);
    }

    /**
     * Get current language setting.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Get current locale setting.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Load language content from an XML file.
     *
     * @param  string $langFile
     * @throws Exception
     * @return void
     */
    public function loadFile($langFile)
    {
        if (file_exists($langFile)) {
            if (($xml =@ new \SimpleXMLElement($langFile, LIBXML_NOWARNING, true)) !== false) {
                $key = 0;
                $length = count($xml->locale);

                // Find the locale node key
                for ($i = 0; $i < $length; $i++) {
                    if ($this->locale == (string)$xml->locale[$i]->attributes()->region) {
                        $key = $i;
                    }
                }

                // If the locale node matches the current locale
                if ($this->locale == (string)$xml->locale[$key]->attributes()->region) {
                    foreach ($xml->locale[$key]->text as $text) {
                        if (isset($text->source) && isset($text->output)) {
                            $this->content['source'][] = (string)$text->source;
                            $this->content['output'][] = (string)$text->output;
                        }
                    }
                }
            } else {
                throw new Exception('Error: There was an error processing that XML file.');
            }
        } else {
            throw new Exception('Error: The language file ' . $langFile . ' does not exist.');
        }
    }

    /**
     * Return the translated string
     *
     * @param  string $str
     * @param  string|array $params
     * @return $str
     */
    public function __($str, $params = null)
    {
        return $this->translate($str, $params);
    }

    /**
     * Echo the translated string.
     *
     * @param  string $str
     * @param  string|array $params
     * @return void
     */
    public function _e($str, $params = null)
    {
        echo $this->translate($str, $params);
    }

    /**
     * Get languages from the XML files.
     *
     * @param  string $dir
     * @return array
     */
    public static function getLanguages($dir = null)
    {
        $langsAry = array();
        $langDirectory = (null !== $dir) ? $dir : __DIR__ . '/Data';

        if (file_exists($langDirectory)) {
            $langDir = new \Pop\File\Dir($langDirectory);
            $files = $langDir->getFiles();
            foreach ($files as $file) {
                if ($file != '__.xml') {
                    if (($xml =@ new \SimpleXMLElement($langDirectory . '/' . $file, LIBXML_NOWARNING, true)) !== false) {
                        $lang = (string)$xml->attributes()->output;
                        $langName = (string)$xml->attributes()->name;
                        $langNative = (string)$xml->attributes()->native;

                        foreach ($xml->locale as $locale) {
                            $region = (string)$locale->attributes()->region;
                            $name   = (string)$locale->attributes()->name;
                            $native = (string)$locale->attributes()->native;
                            $native .= ' (' . $langName . ', ' . $name . ')';
                            $langsAry[$lang . '_' . $region] = $langNative . ', ' . $native;
                        }
                    }
                }
            }
        }

        ksort($langsAry);
        return $langsAry;
    }

    /**
     * Create an XML language file from an array of data.
     * The format of the parameters should be as follows:
     *
     * $lang = array(
     *     'src'    => 'en',
     *     'output' => 'de',
     *     'name'   => 'German',
     *     'native' => 'Deutsch'
     * );
     *
     * $locales = array(
     *     array(
     *         'region' => 'DE',
     *         'name'   => 'Germany',
     *         'native' => 'Deutschland',
     *         'text' => array(
     *             array(
     *                 'source' => 'This field is required.',
     *                 'output' => 'Dieses Feld ist erforderlich.'
     *             ), ...
     *         )
     *     ), ...
     * );
     *
     * @param  array  $lang
     * @param  array  $locales
     * @param  string $file
     * @throws Exception
     * @return void
     */
    public static function createXmlFile(array $lang, array $locales, $file)
    {
        // Validate the $lang parameter
        if (!isset($lang['src']) || !isset($lang['output'])) {
            throw new Exception("Error: The language parameter must have at least the 'src' and 'output' keys defined.");
        }

        // Validate the $locales parameter
        foreach ($locales as $locale) {
            if (!isset($locale['region'])) {
                throw new Exception("Error: The locales parameter must have at least the 'region' key defined in each locale.");
            }
            if (!isset($locale['text'])) {
                throw new Exception("Error: The locales parameter must have at least the 'text' key defined in each locale.");
            }
            if (!is_array($locale['text'])) {
                throw new Exception("Error: The parameter key 'text' in each locale must be an array.");
            }
        }

        // Get the XML file header
        $xmlHeader = file_get_contents(__DIR__ . '/Data/__.xml');
        $xmlHeader = substr($xmlHeader, 0, (strpos($xmlHeader, 'native="">') + 10));
        $xmlHeader = str_replace(
            array('src="en"', 'output=""'),
            array('src="' . $lang['src'] . '"', 'output="' . $lang['output'] . '"'),
            $xmlHeader
        );

        if (isset($lang['name'])) {
            $xmlHeader = str_replace('name=""', 'name="' . $lang['name'] . '"', $xmlHeader);
        }

        if (isset($lang['native'])) {
            $xmlHeader = str_replace('native=""', 'native="' . $lang['native'] . '"', $xmlHeader);
        }

        // Format the Locales
        $xmlLocales = null;

        foreach ($locales as $locale) {
            $name = (isset($locale['name'])) ? $locale['name'] : null;
            $native = (isset($locale['native'])) ? $locale['native'] : null;
            $xmlLocales .= '    <locale region="' . $locale['region'] . '" name="' . $name . '" native="' . $native . '">' . PHP_EOL;
            foreach ($locale['text'] as $text) {
                if (!isset($text['source']) || !isset($text['output'])) {
                    throw new Exception("Error: The 'source' and 'output' keys must be defined in each 'text' array.");
                }
                $xmlLocales .= '        <text>' . PHP_EOL;
                $xmlLocales .= '            <source>' . $text['source'] . '</source>' . PHP_EOL;
                $xmlLocales .= '            <output>' . $text['output'] . '</output>' . PHP_EOL;
                $xmlLocales .= '        </text>' . PHP_EOL;
            }
            $xmlLocales .= '    </locale>' . PHP_EOL;
        }

        // Save XML file
        file_put_contents($file, $xmlHeader . PHP_EOL . $xmlLocales . '</language>');
    }

    /**
     * Create an XML document fragment from a source file and an output file,
     * each entry separated by a new line
     *
     * @param  string $source
     * @param  string $output
     * @param  string $target
     * @throws Exception
     * @return void
     */
    public static function createXmlFromText($source, $output, $target = null)
    {
        if (!file_exists($source)) {
            throw new Exception('Error: The source file does not exist.');
        }
        if (!file_exists($output)) {
            throw new Exception('Error: The output file does not exist.');
        }

        $sourceLines = explode(PHP_EOL, file_get_contents($source));
        $outputLines = explode(PHP_EOL, file_get_contents($output));

        $targetDir = (null !== $target) ? $target : dirname($output);

        if (!file_exists($targetDir)) {
            throw new Exception('Error: The target directory does not exist.');
        }

        if (strpos($output, '/') !== false) {
            $lang = substr($output, (strrpos($output, '/') + 1));
            $lang = substr($lang, 0, strpos($lang, '.'));
        } else if (strpos($output, "\\") !== false) {
            $lang = substr($output, (strrpos($output, "\\") + 1));
            $lang = substr($lang, 0, strpos($lang, '.'));
        } else {
            $lang = substr($output, 0, strpos($output, '.'));
        }

        $xml = null;

        foreach ($outputLines as $key => $value) {
            if (!empty($value) && !empty($sourceLines[$key])) {
                $xml .= '        <text>' . PHP_EOL . '            <source>' . $sourceLines[$key] . '</source>' . PHP_EOL .
                    '            <output>' . $value . '</output>' . PHP_EOL .
                    '        </text>' . PHP_EOL;
            }
        }

        file_put_contents($targetDir . DIRECTORY_SEPARATOR . $lang . '.xml', $xml);
    }

    /**
     * Translate and return the string.
     *
     * @param  string $str
     * @param  string|array $params
     * @return mixed
     */
    protected function translate($str, $params = null)
    {
        $key = array_search($str, $this->content['source']);
        $trans = ($key !== false) ? $this->content['output'][$key] : $str;

        if (null !== $params) {
            if (is_array($params)) {
                foreach ($params as $key => $value) {
                    $trans = str_replace('%' . ($key + 1), $value, $trans);
                }
            } else {
                $trans = str_replace('%1', $params, $trans);
            }
        }

        return $trans;
    }

    /**
     * Get language content from the XML file.
     *
     * @return void
     */
    protected function loadCurrentLanguage()
    {
        $this->loadFile(__DIR__ . '/Data/' . $this->language . '.xml');
    }

}
