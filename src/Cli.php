<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Cli
 * @author     Nick Sagona, III <dev@nolainteractive.com>
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
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Cli
{

    /**
     * CLI arguments
     * @var array
     */
    protected $args = [];

    /**
     * Available CLI commands
     * @var array
     */
    protected $commands = [
        'check',
        'help',
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
                case 'version':
                    $this->version();
                    break;
            }
        }
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
        echo '   ./pop check             ' . 'Check the current environment for dependencies' . PHP_EOL;
        echo '   ./pop help              ' . 'Display this help' . PHP_EOL;
        echo '   ./pop version           ' . 'Display version of Pop PHP Framework and latest available' . PHP_EOL . PHP_EOL;
        exit();
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
