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
     * Directory with language files in it
     * @var string
     */
    protected $directory = null;

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
    protected $content = [
        'source' => [],
        'output' => []
    ];

    /**
     * Constructor
     *
     * Instantiate the I18n object.
     *
     * @param  string $lang
     * @param  string $dir
     * @return \Pop\I18n\I18n
     */
    public function __construct($lang = null, $dir = null)
    {
        if (null === $lang) {
            $lang = (defined('POP_LANG')) ? POP_LANG : 'en_US';
        }

        if (strpos($lang, '_') !== false) {
            $ary = explode('_', $lang);
            $this->language = $ary[0];
            $this->locale   = $ary[1];
        } else {
            $this->language = $lang;
            $this->locale   = strtoupper($lang);
        }

        $this->directory = ((null !== $dir) && file_exists($dir)) ? realpath($dir) . DIRECTORY_SEPARATOR
            : __DIR__ . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR;

        $this->loadCurrentLanguage();
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
        // If an XML file
        if (file_exists($langFile) && (stripos($langFile, '.xml') !== false)) {
            if (($xml =@ new \SimpleXMLElement($langFile, LIBXML_NOWARNING, true)) !== false) {
                $key    = 0;
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
        // Else if a JSON file
        } else if (file_exists($langFile) && (stripos($langFile, '.json') !== false)) {
            $json = json_decode(file_get_contents($langFile), true);

            $key    = 0;
            $length = count($json['language']['locale']);

            // Find the locale node key
            for ($i = 0; $i < $length; $i++) {
                if ($this->locale == $json['language']['locale'][$i]['region']) {
                    $key = $i;
                }
            }

            if ($this->locale == $json['language']['locale'][$key]['region']) {
                foreach ($json['language']['locale'][$key]['text'] as $text) {
                    if (isset($text['source']) && isset($text['output'])) {
                        $this->content['source'][] = (string)$text['source'];
                        $this->content['output'][] = (string)$text['output'];
                    }
                }
            }
        } else {
            throw new Exception('Error: The language file ' . $langFile . ' does not exist or is not valid.');
        }
    }

    /**
     * Return the translated string
     *
     * @param  string $str
     * @param  string|array $params
     * @return string
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
    public static function getLanguages($dir)
    {
        $langsAry      = [];
        $langDirectory = $dir;

        if (file_exists($langDirectory)) {
            $files = scandir($langDirectory);
            foreach ($files as $file) {
                if (stripos($file, '.xml')) {
                    if (($xml =@ new \SimpleXMLElement($langDirectory . DIRECTORY_SEPARATOR . $file, LIBXML_NOWARNING, true)) !== false) {
                        $lang       = (string)$xml->attributes()->output;
                        $langName   = (string)$xml->attributes()->name;
                        $langNative = (string)$xml->attributes()->native;

                        foreach ($xml->locale as $locale) {
                            $region = (string)$locale->attributes()->region;
                            $name   = (string)$locale->attributes()->name;
                            $native = (string)$locale->attributes()->native;
                            $native .= ' (' . $langName . ', ' . $name . ')';
                            $langsAry[$lang . '_' . $region] = $langNative . ', ' . $native;
                        }
                    }
                } else if (stripos($file, '.json')) {
                    $json = json_decode(file_get_contents($langDirectory . DIRECTORY_SEPARATOR . $file), true);
                    $lang       = $json['language']['output'];
                    $langName   = $json['language']['name'];
                    $langNative = $json['language']['native'];

                    foreach ($json['language']['locale'] as $locale) {
                        $region = $locale['region'];
                        $name   = $locale['name'];
                        $native = $locale['native'];
                        $native .= ' (' . $langName . ', ' . $name . ')';
                        $langsAry[$lang . '_' . $region] = $langNative . ', ' . $native;
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
     * $lang = [
     *     'src'    => 'en',
     *     'output' => 'de',
     *     'name'   => 'German',
     *     'native' => 'Deutsch'
     * ];
     *
     * $locales = [
     *     [
     *         'region' => 'DE',
     *         'name'   => 'Germany',
     *         'native' => 'Deutschland',
     *         'text' => [
     *             [
     *                 'source' => 'This field is required.',
     *                 'output' => 'Dieses Feld ist erforderlich.'
     *             ], ...
     *         ]
     *     ], ...
     * ];
     *
     * @param  array  $lang
     * @param  array  $locales
     * @param  string $file
     * @throws Exception
     * @return void
     */
    public static function createLanguageFile(array $lang, array $locales, $file)
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
        if (stripos($file, '.xml') !== false) {
            $xmlHeader = file_get_contents(__DIR__ . '/Data/__.xml');
            $xmlHeader = substr($xmlHeader, 0, (strpos($xmlHeader, 'native="">') + 10));
            $xmlHeader = str_replace(
                ['src=""', 'output=""'],
                ['src="' . $lang['src'] . '"', 'output="' . $lang['output'] . '"'],
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
        } else if (stripos($file, '.json') !== false) {
            $lang['locale'] = $locales;

            // Save JSON file
            file_put_contents($file, json_encode(['language' => $lang], JSON_PRETTY_PRINT));
        }
    }

    /**
     * Create an language file fragment from a source file and an output file,
     * each entry separated by a new line
     *
     * @param  string $source
     * @param  string $output
     * @param  string $target
     * @throws Exception
     * @return void
     */
    public static function createFromText($source, $output, $target = null)
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

        $xml  = null;
        $json = '            "text"   : [' . PHP_EOL;

        foreach ($outputLines as $key => $value) {
            if (!empty($value) && !empty($sourceLines[$key])) {
                $xml .= '        <text>' . PHP_EOL . '            <source>' . $sourceLines[$key] . '</source>' . PHP_EOL .
                    '            <output>' . $value . '</output>' . PHP_EOL .
                    '        </text>' . PHP_EOL;

                $json .= '                {' . PHP_EOL . '                    "source" : "' . $sourceLines[$key] . '",' . PHP_EOL .
                    '                    "output" : "' . $value . '"' . PHP_EOL . '                },' . PHP_EOL;
            }
        }

        $json .= '            ]' . PHP_EOL;

        file_put_contents($targetDir . DIRECTORY_SEPARATOR . $lang . '.json', $json);
        file_put_contents($targetDir . DIRECTORY_SEPARATOR . $lang . '.xml', $xml);
    }

    /**
     * Translate and return the string.
     *
     * @param  string $str
     * @param  string|array $params
     * @return string
     */
    protected function translate($str, $params = null)
    {
        $key   = array_search($str, $this->content['source']);
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
        if (file_exists($this->directory . $this->language . '.xml')) {
            $this->loadFile($this->directory . $this->language . '.xml');
        } else if (file_exists($this->directory . $this->language . '.json')) {
            $this->loadFile($this->directory . $this->language . '.json');
        }
    }

}
