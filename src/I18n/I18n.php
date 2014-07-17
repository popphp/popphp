<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
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
     * @return I18n
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
