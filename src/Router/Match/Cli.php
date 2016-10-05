<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Router\Match;

/**
 * Pop router CLI match class
 *
 * @category   Pop
 * @package    Pop\Router
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class Cli extends AbstractMatch
{

    /**
     * CLI arguments
     * @var array
     */
    protected $arguments = [];

    /**
     * CLI Argument string
     * @var string
     */
    protected $argumentString = null;

    /**
     * Constructor
     *
     * Instantiate the CLI match object
     */
    public function __construct()
    {
        $argv = $_SERVER['argv'];

        // Trim the script name out of the arguments array
        array_shift($argv);

        $this->arguments      = $argv;
        $this->argumentString = implode(' ', $argv);

        return $this;
    }

    /**
     * Get the CLI route arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Get the CLI route argument string
     *
     * @return string
     */
    public function getArgumentString()
    {
        return $this->argumentString;
    }

    /**
     * Match the route
     *
     * @param  array $routes
     * @return boolean
     */
    public function match($routes)
    {
        $matched = false;

        return $matched;
    }

    /**
     * Method to process if a route was not found
     *
     * @param  boolean $exit
     * @return void
     */
    public function noRouteFound($exit = true)
    {
        if (stripos(PHP_OS, 'win') === false) {
            $string  = "    \x1b[1;37m\x1b[41m                          \x1b[0m" . PHP_EOL;
            $string .= "    \x1b[1;37m\x1b[41m    Command not found.    \x1b[0m" . PHP_EOL;
            $string .= "    \x1b[1;37m\x1b[41m                          \x1b[0m";
        } else {
            $string = 'Command Not Found.';
        }
        echo PHP_EOL . $string . PHP_EOL . PHP_EOL;
        if ($exit) {
            exit(127);
        }
    }

}
