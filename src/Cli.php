<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Cli
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop;

/**
 * Cli class
 *
 * @category   Pop
 * @package    Pop_Cli
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Cli
{

    /**
     * CLI error codes & messages
     * @var array
     */
    protected static $errorCodes = array(
        0 => 'Unknown error.',
        2 => 'You must pass an build file to build the project.',
        3 => 'That file does not exist.',
    );

    /**
     * CLI arguments
     * @var array
     */
    protected $args = null;

    /**
     * Available CLI commands
     * @var array
     */
    protected $commands = [
        'check',
        'help',
        'build',
        'show',
        'version'
    ];

    /**
     * Constructor method to instantiate the CLI object
     *
     * @param  array $args
     * @return self
     */
    public function __construct(array $args = [])
    {
        // Write header
        echo PHP_EOL;
        echo '  Pop PHP 2' . PHP_EOL;
        echo '  =========' . PHP_EOL . PHP_EOL;

        $this->args = $args;

        if (!isset($this->args[1])) {
            echo '  You must pass at least one argument. Use ./pop help for help.' . PHP_EOL . PHP_EOL;
            exit();
        } else if (isset($this->args[1]) && !in_array($this->args[1], $this->commands)) {
            echo '  The argument \'' . $this->args[1] . '\' was not recognized. Use ./pop help for help.' . PHP_EOL . PHP_EOL;
            exit();
        } else {
            switch ($this->args[1]) {
                case 'check':
                    $this->check();
                    break;
                case 'help':
                    $this->help();
                    break;
                case 'build':
                    $this->build();
                    break;
                case 'show':
                    self::instructions();
                    exit();
                    break;
                case 'version':
                    $this->version();
                    break;
            }
        }
    }

    /**
     * Show build instructions
     *
     * @return void
     */
    public static function instructions()
    {
        $msg1 = "You can construct the scaffolding of your application using the build command. This process will create and " .
            "build the base foundation of your application under the folder specified in the build file. Minimally, the " .
            "build file should return a Pop\\Config object containing your application build settings, such as application" .
            "name, folders, forms, controllers, views and any database credentials.";

        $msg2 = "Besides creating the base folders and files for you, one of the main benefits is ability to test and " .
            "install the database and the corresponding configuration and class files. You can take advantage of this " .
            "by having the database SQL files in the same folder as your build file, like so:";

        echo '    ' . wordwrap($msg1, 70, PHP_EOL . '    ') . PHP_EOL . PHP_EOL;
        echo '    ' . wordwrap($msg2, 70, PHP_EOL . '    ') . PHP_EOL . PHP_EOL;
        echo '    project' . DIRECTORY_SEPARATOR . 'build.php' . PHP_EOL;
        echo '    project' . DIRECTORY_SEPARATOR . 'db.sql' . PHP_EOL . PHP_EOL;
    }

    /**
     * Check environment
     *
     * @return void
     */
    protected function check()
    {
        $system = Version::check();

        $green    = (!$system['windows']) ? "\033[1;32m" : null;
        $yellow   = (!$system['windows']) ? "\033[1;33m" : null;
        $red      = (!$system['windows']) ? "\033[1;31m" : null;
        $blue     = (!$system['windows']) ? "\033[1;34m" : null;
        $endColor = (!$system['windows']) ? "\033[0m" : null;

        echo '    System Check:' . PHP_EOL;
        echo '    -------------' . PHP_EOL;

        // PHP
        echo '      ' . $blue . 'PHP Required' . $endColor . ':  ' . $system['php']['required'] . PHP_EOL;
        echo '      ' . $blue . 'PHP Installed' . $endColor . ': ' . (($system['php']['compare'] >= 0) ? $green : $red) .
            $system['php']['installed'] . $endColor . PHP_EOL . PHP_EOL;

        // Pop
        echo '      ' . $blue . 'Pop Latest' . $endColor . ':    ' . $system['pop']['latest'] . PHP_EOL;
        echo '      ' . $blue . 'Pop Installed' . $endColor . ': ' . (($system['pop']['compare'] >= 0) ? $green : $yellow) .
            $system['pop']['installed'] . $endColor . PHP_EOL . PHP_EOL;

        // Environment
        echo '    Environment:' . PHP_EOL;
        echo '    ------------' . PHP_EOL;

        $count     = 0;
        $available = 0;
        foreach ($system['environment'] as $key => $value) {
            if (is_array($value)) {
                echo '      ' . $blue . ucwords(str_replace('_', ' ', $key)) . $endColor . ':' . PHP_EOL;
                foreach ($value as $k => $v) {
                    $count++;
                    if ($v) {
                        $available++;
                    }
                    echo '        - ' . $blue . ucwords(str_replace('_', ' ', $k)) . $endColor . ': ' .
                        (($v) ? $green . 'Yes' : $red . 'No') . $endColor . PHP_EOL;
                }
            } else {
                $count++;
                if ($value) {
                    $available++;
                }
                echo '      ' . $blue . ucwords(str_replace('_', ' ', $key)) . $endColor . ': ' .
                    (($value) ? $green . 'Yes' : $red . 'No') . $endColor . PHP_EOL;
            }
        }

        echo '      ------------------' . PHP_EOL;
        echo '      ' . (($available == $count) ? $green : $yellow) . $available . $endColor . ' of ' . $green . $count . $endColor .
            ' Available' . PHP_EOL . PHP_EOL;

        exit();
    }

    /**
     * Show help
     *
     * @return void
     */
    protected function help()
    {
        echo '   ./pop check             ' . 'Check the current configuration for required dependencies' . PHP_EOL;
        echo '   ./pop help              ' . 'Display this help' . PHP_EOL;
        echo '   ./pop build file.php    ' . 'Build an application based on the build file specified' . PHP_EOL;
        echo '   ./pop show              ' . 'Show project build instructions' . PHP_EOL;
        echo '   ./pop version           ' . 'Display version of Pop PHP Framework and latest available' . PHP_EOL . PHP_EOL;
        exit();
    }

    /**
     * Build application
     *
     * @return void
     */
    protected function build()
    {
        if (!isset($this->args[2])) {
            echo '  You must pass an build file to build the project.'. PHP_EOL . PHP_EOL;
            exit();
        } else if (!file_exists($this->args[2])) {
            echo '  The build file does not exist.' . PHP_EOL . PHP_EOL;
            exit();
        } else {
            \Pop\Application\Build::build($this->args[2]);
            exit();
        }
    }

    /**
     * Show version
     *
     * @return void
     */
    protected function version()
    {
        $latest = 'N/A';
        $handle = fopen('http://www.popphp.org/version', 'r');
        if ($handle !== false) {
            $latest = trim(stream_get_contents($handle));
            fclose($handle);
        }

        echo '    Version' . PHP_EOL;
        echo '    -------' . PHP_EOL;
        echo '      Current Installed: ' . Version::VERSION . PHP_EOL;
        echo '      Latest Available:  ' . $latest . PHP_EOL;
        echo PHP_EOL;
        echo (version_compare(Version::VERSION, $latest) >= 0) ?
            '      The latest version is installed.' :
            '      A newer version is available.';
        echo PHP_EOL . PHP_EOL;
        exit();
    }

}
