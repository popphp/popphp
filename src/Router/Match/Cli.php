<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Cli extends AbstractMatch
{

    /**
     * Constructor
     *
     * Instantiate the CLI match object
     *
     * @return Cli
     */
    public function __construct()
    {
        $this->setSegments();
    }

    /**
     * Set the route segments
     *
     * @return Cli
     */
    public function setSegments()
    {
        global $argv;
        array_shift($argv);
        $this->segments = $argv;
        return $this;
    }

    /**
     * Match the route to the controller class
     *
     * @param  array $controllers
     * @return string
     */
    public function match($controllers)
    {
        global $argv;
        $match = $this->traverseControllers($controllers);

        $this->action = 'index';

        // Get the action if present
        if (null !== $this->segmentMatch) {
            $index = array_search($this->segmentMatch, $this->segments);
            if (($index !== false) && isset($this->segments[$index + 1]) && !empty($this->segments[$index + 1])) {
                if (method_exists($match, $this->segments[$index + 1])) {
                    $this->action = $this->segments[$index + 1];
                } else {
                    $index++;
                    $argv = [];
                    // Clean up the arguments
                    for ($i = $index; $i < count($this->segments); $i++) {
                        $argv[] = $this->segments[$i];
                    }
                }
            }
        } else if (isset($this->segments[0]) && ($this->action == 'index')) {
            $this->action = $this->segments[0];
            if (count($this->segments) > 1) {
                $index = 1;
                $argv = [];
                // Clean up the arguments
                for ($i = $index; $i < count($this->segments); $i++) {
                    $argv[] = $this->segments[$i];
                }
            }
        }

        return $match;
    }

}