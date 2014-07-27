<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_I18n
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\I18n\Format;

/**
 * I18n XML format class
 *
 * @category   Pop
 * @package    Pop_I18n
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Xml
{

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
    public static function createFile(array $lang, array $locales, $file)
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

        $xmlHeader = file_get_contents(__DIR__ . '/../Data/__.xml');
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
    }

    /**
     * Create an language file fragment from a source file and an output file,
     * each entry separated by a new line
     *
     * @param  string $source
     * @param  string $output
     * @param  string $dir
     * @throws Exception
     * @return void
     */
    public static function createFragment($source, $output, $dir = null)
    {
        if (!file_exists($source)) {
            throw new Exception('Error: The source file does not exist.');
        }
        if (!file_exists($output)) {
            throw new Exception('Error: The output file does not exist.');
        }

        $sourceLines = explode(PHP_EOL, file_get_contents($source));
        $outputLines = explode(PHP_EOL, file_get_contents($output));

        $targetDir = (null !== $dir) ? $dir : dirname($output);

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

        foreach ($outputLines as $key => $value) {
            if (!empty($value) && !empty($sourceLines[$key])) {
                $xml .= '        <text>' . PHP_EOL . '            <source>' . $sourceLines[$key] . '</source>' . PHP_EOL .
                    '            <output>' . $value . '</output>' . PHP_EOL .
                    '        </text>' . PHP_EOL;
            }
        }

        file_put_contents($targetDir . DIRECTORY_SEPARATOR . $lang . '.xml', $xml);
    }

}
