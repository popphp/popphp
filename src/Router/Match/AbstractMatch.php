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
 * Pop router match abstract class
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractMatch
{

    /**
     * Array of route segments
     * @var array
     */
    protected $segments = [];

    /**
     * Action
     * @var string
     */
    protected $action = null;

    /**
     * Segment match
     * @var string
     */
    protected $segmentMatch = null;

    /**
     * Get the route segments
     *
     * @return array
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * Get the action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Traverse the controllers
     *
     * @param  array $controllers
     * @param  int $depth
     * @return string
     */
    protected function traverseControllers($controllers, $depth = 0)
    {
        $next = $depth + 1;
        // If the path stem exists in the controllers, the traverse it
        if (isset($this->segments[$depth]) &&
            (array_key_exists($this->segments[$depth], $controllers))) {
            // If the next level is an array, traverse it
            if (is_array($controllers[$this->segments[$depth]])) {
                $this->segmentMatch = $this->segments[$depth];
                return $this->traverseControllers($controllers[$this->segments[$depth]], $next);
            // Else, return the controller class name
            } else {
                $this->segmentMatch = $this->segments[$depth];
                return (isset($controllers[$this->segments[$depth]])) ?
                    $controllers[$this->segments[$depth]] : null;
            }
        // Else check for the root 'index' path
        } else if (array_key_exists('index', $controllers)) {
            // If the next level is an array, traverse it
            if (is_array($controllers['index'])) {
                return $this->traverseControllers($controllers['index'], $next);
            // Else, return the controller class name
            } else {
                return (isset($controllers['index'])) ? $controllers['index'] : null;
            }
        }
    }

    /**
     * Constructor
     *
     * Instantiate the match object
     *
     * @return AbstractMatch
     */
    abstract public function __construct();

    /**
     * Set the route segments
     *
     * @return AbstractMatch
     */
    abstract public function setSegments();

    /**
     * Match the route to the controller class
     *
     * @param  array $controllers
     * @return string
     */
    abstract public function match($controllers);

}