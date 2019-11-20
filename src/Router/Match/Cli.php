<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.3.3
 */
class Cli extends AbstractMatch
{

    /**
     * Route commands
     * @var array
     */
    protected $commands = [];

    /**
     * Allowed route options
     * @var array
     */
    protected $options = [
        'options' => [], // [-v|--verbose]
        'values'  => [], // [-n|--name=]
        'arrays'  => []  // [-i|--id=*]
    ];

    /**
     * Allowed route parameters
     * @var array
     */
    protected $parameters = [];

    /**
     * Flag for all required parameters
     * @var boolean
     */
    protected $hasAllRequired = true;

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

        $this->segments    = $argv;
        $this->routeString = implode(' ', $argv);

        return $this;
    }

    /**
     * Prepare the routes
     *
     * @return Cli
     */
    public function prepare()
    {
        $this->flattenRoutes($this->routes);
        return $this;
    }

    /**
     * Match the route
     *
     * @param  string $forceRoute
     * @return boolean
     */
    public function match($forceRoute = null)
    {
        if (count($this->preparedRoutes) == 0) {
            $this->prepare();
        }

        $routeToMatch = (null !== $forceRoute) ? $forceRoute : $this->routeString;

        foreach ($this->preparedRoutes as $regex => $controller) {
            if (preg_match($regex, $routeToMatch) != 0) {
                $this->route = $regex;
                break;
            }
        }

        if ((null !== $this->route) || (null !== $this->dynamicRoute)) {
            $this->parseRouteParams();
        }

        return $this->hasRoute();
    }

    /**
     * Determine if the route has been matched
     *
     * @return boolean
     */
    public function hasRoute()
    {
        return (((null !== $this->route) && ($this->hasAllRequired)) || (null !== $this->dynamicRoute) || (null !== $this->defaultRoute));
    }

    /**
     * Get the route commands
     *
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Get the command parameters
     *
     * @return array
     */
    public function getCommandParameters()
    {
        return $this->parameters;
    }

    /**
     * Get the command options
     *
     * @return array
     */
    public function getCommandOptions()
    {
        return $this->options;
    }

    /**
     * Get the parsed route params
     *
     * @return array
     */
    public function getParameters()
    {
        $params = $this->routeParams;
        unset($params['options']);
        return $params;
    }

    /**
     * Get a parsed route param
     *
     * @param  string $name
     * @return mixed
     */
    public function getParameter($name)
    {
        return (isset($this->routeParams[$name])) ? $this->routeParams[$name] : null;
    }

    /**
     * Get the parsed route options
     *
     * @return array
     */
    public function getOptions()
    {
        return (isset($this->routeParams['options'])) ? $this->routeParams['options'] : null;
    }

    /**
     * Get a parsed route option
     *
     * @param  string $name
     * @return mixed
     */
    public function getOption($name)
    {
        return (isset($this->routeParams['options']) && isset($this->routeParams['options'][$name])) ?
            $this->routeParams['options'][$name] : null;
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

    /**
     * Flatten the nested routes
     *
     * @param  array|string $route
     * @param  mixed        $controller
     * @return void
     */
    protected function flattenRoutes($route, $controller = null)
    {
        if (is_array($route)) {
            foreach ($route as $r => $c) {
                $this->flattenRoutes($r, $c);
            }
        } else if (null !== $controller) {
            if (!isset($controller['controller'])) {
                foreach ($controller as $r => $c) {
                    $this->flattenRoutes($route . $r, $c);
                }
            } else {
                $routeRegex = $this->getRouteRegex($route);
                if (isset($controller['default']) && ($controller['default'])) {
                    $this->defaultRoute = $controller;
                }
                $this->preparedRoutes[$routeRegex['regex']] = array_merge($controller, [
                    'route' => $route
                ]);
            }
        }
    }

    /**
     * Get the REGEX pattern for the route string
     *
     * @param  string $route
     * @return array
     */
    protected function getRouteRegex($route)
    {
        $routeRegex         = '^';
        $commands           = [];
        $options            = [];
        $optionValues       = [];
        $optionValueArray   = [];
        $requiredParameters = [];
        $optionalParameters = [];

        if (!isset($this->commands[$route])) {
            $this->commands[$route] = [];
        }

        if ((strpos($route, '<') !== false) || (strpos($route, '[') !== false)) {
            $regexCommands = [];
            preg_match_all('/[a-zA-Z0-9-_:|]*(?=\s)/', $route, $commands, PREG_OFFSET_CAPTURE);
            foreach ($commands[0] as $i => $command) {
                if (!empty($command[0])) {
                    $regexCommands[] = $command[0];
                    $this->commands[$route][] = $command[0];
                }
            }
            if (count($regexCommands) > 0) {
                $routeRegex .= implode(' ', $regexCommands);
            }
        } else {
            $this->commands[$route] = explode(' ', $route);
            $routeRegex            .= $route . '$';
        }

        preg_match_all('/\[\-[a-zA-Z0-9-_:|]*\]/', $route, $options, PREG_OFFSET_CAPTURE);
        preg_match_all('/\[\-[a-zA-Z0-9-_:|=]*=\]/', $route, $optionValues, PREG_OFFSET_CAPTURE);
        preg_match_all('/\[\-[a-zA-Z0-9-_:|=]*=\*\]/', $route, $optionValueArray, PREG_OFFSET_CAPTURE);
        preg_match_all('/(?<!\[)<[a-zA-Z0-9-_:|]*>/', $route, $requiredParameters, PREG_OFFSET_CAPTURE);
        preg_match_all('/\[<[a-zA-Z0-9-_:|]*>\]/', $route, $optionalParameters, PREG_OFFSET_CAPTURE);

        $routeRegex .= (isset($requiredParameters[0]) && isset($requiredParameters[0][0])) ? ' (.*)$' : '(.*)$';

        foreach ($options[0] as $option) {
            if (strpos($option[0], '--') !== false) {
                $name = substr($option[0], (strpos($option[0], '--') + 2));
                $name = substr($name, 0, strpos($name, ']'));
            } else {
                $name = substr($option[0], (strpos($option[0], '-') + 1));
                $name = (strpos($name, '|') !== false) ? substr($name, 0, strpos($name, '|')) : substr($name, 0, strpos($name, ']'));
            }
            if (!isset($this->options['options'][$route])) {
                $this->options['options'][$route] = [];
            }
            $this->options['options'][$route][$name] = '/' . str_replace(['[', ']'], ['(', ')'], $option[0]) . '/';
        }

        foreach ($optionValues[0] as $option) {
            $opt = str_replace(['[', ']'], ['', ''], $option[0]);
            if (strpos($option[0], '--') !== false) {
                $name = substr($option[0], (strpos($option[0], '--') + 2));
                $name = substr($name, 0, strpos($name, '='));
            } else {
                $name = substr($option[0], (strpos($option[0], '-') + 1));
                $name = substr($name, 0, strpos($name, '='));
            }
            if (strpos($opt, '|') !== false) {
                [$opt1, $opt2] = explode('|', $opt);
                $optionRegex   = '(' . $opt1 . '[a-zA-Z0-9-_:|.@,\/]+|' . $opt1 . '"(.*)"|' . $opt2 .
                    '[a-zA-Z0-9-_:|.@,\/]+|' . $opt2 . '"(.*)")';
            } else {
                $optionRegex = '(' . $opt . '[a-zA-Z0-9-_:|.@,\/]+|' . $opt . '"(.*)")';
            }
            if (!isset($this->options['values'][$route])) {
                $this->options['values'][$route] = [];
            }
            $this->options['values'][$route][$name] = '/' . $optionRegex . '/';
        }

        foreach ($optionValueArray[0] as $option) {
            $opt = str_replace(['[', ']', '*'], ['', '', ''], $option[0]);
            if (strpos($option[0], '--') !== false) {
                $name = substr($option[0], (strpos($option[0], '--') + 2));
                $name = substr($name, 0, strpos($name, '='));
            } else {
                $name = substr($option[0], (strpos($option[0], '-') + 1));
                $name = substr($name, 0, strpos($name, '='));
            }
            if (strpos($opt, '|') !== false) {
                [$opt1, $opt2] = explode('|', $opt);
                $optionRegex   = '(' . $opt1 . '[a-zA-Z0-9-_:|.@,\/]+|' . $opt1 . '"(.*)"|' . $opt2 .
                    '[a-zA-Z0-9-_:|.@,\/]+|' . $opt2 . '"(.*)")';
            } else {
                $optionRegex = '(' . $opt . '[a-zA-Z0-9-_:|.@,\/]+|' . $opt . '"(.*)")';
            }
            if (!isset($this->options['arrays'][$route])) {
                $this->options['arrays'][$route] = [];
            }
            $this->options['arrays'][$route][$name] = '/' . $optionRegex . '/';
        }

        foreach ($requiredParameters[0] as $i => $parameter) {
            if (!isset($this->parameters[$route])) {
                $this->parameters[$route] = [];
            }
            $this->parameters[$route][substr($parameter[0], 1, -1)] = [
                'position' => ($i + 1),
                'required' => true
            ];
        }

        $cur = (isset($this->parameters[$route])) ? count($this->parameters[$route]) : 0;

        foreach ($optionalParameters[0] as $j => $parameter) {
            if (!isset($this->parameters[$route])) {
                $this->parameters[$route] = [];
            }
            $this->parameters[$route][substr($parameter[0], 2, -2)] = [
                'position' => ($j + 1 + $cur),
                'required' => false
            ];
        }

        return [
            'regex' => '/' . $routeRegex . '/'
        ];
    }

    /**
     * Parse route dispatch parameters
     *
     * @return void
     */
    protected function parseRouteParams()
    {
        if ((null !== $this->dynamicRoute) && (count($this->segments) >= 3)) {
            $this->routeParams = (strpos($this->dynamicRoute, 'param*') !==  false) ?
                [array_slice($this->segments, 2)] : array_slice($this->segments, 2);
        } else {
            $options = [];
            $start   = 0;
            $route   = $this->preparedRoutes[$this->route]['route'];
            if (isset($this->options['options'][$route])) {
                foreach ($this->options['options'][$route] as $option => $regex) {
                    $match = [];
                    preg_match($regex, $this->routeString, $match);
                    if (isset($match[0]) && !empty($match[0])) {
                        $options[$option] = true;
                        if (array_search($match[0], $this->segments) > $start) {
                            $start = array_search($match[0], $this->segments);
                        }
                    }
                }
            }

            if (isset($this->options['values'][$route])) {
                foreach ($this->options['values'][$route] as $option => $regex) {
                    $match = [];
                    preg_match($regex, $this->routeString, $match);
                    if (isset($match[0]) && !empty($match[0])) {
                        if (strpos($match[0], '=') !== false) {
                            $value = substr($match[0], (strpos($match[0], '=') + 1));
                        } else {
                            $value = substr($match[0], 2);
                        }
                        $options[$option] = $value;
                        if (array_search($match[0], $this->segments) > $start) {
                            $start = array_search($match[0], $this->segments);
                        }
                    }
                }
            }

            if (isset($this->options['arrays'][$route])) {
                foreach ($this->options['arrays'][$route] as $option => $regex) {
                    $matches = [];
                    $values  = [];
                    preg_match_all($regex, $this->routeString, $matches);
                    if (isset($matches[0]) && !empty($matches[0])) {
                        foreach ($matches[0] as $match) {
                            if (strpos($match, '=') !== false) {
                                $value = substr($match, (strpos($match, '=') + 1));
                            } else {
                                $value = substr($match, 2);
                            }
                            $values[] = $value;
                            if (array_search($match, $this->segments) > $start) {
                                $start = array_search($match, $this->segments);
                            }
                        }
                    }
                    if (count($values) > 0) {
                        $options[$option] = $values;
                    }
                }
            }

            $i = (count($options) > 0) ? $start + 1 : $start + count($this->commands[$route]);

            if (isset($this->parameters[$route])) {
                foreach ($this->parameters[$route] as $name => $parameter) {
                    if ($parameter['required']) {
                        $required[$name] = null;
                    }
                    if (isset($this->segments[$i])) {
                        $this->routeParams[$name] = $this->segments[$i];
                        $required[$name] = true;
                        $i++;
                    } else {
                        $this->routeParams[$name] = null;
                    }

                    if (($parameter['required']) && (null === $this->routeParams[$name])) {
                        $this->hasAllRequired = false;
                    }
                }
            }

            $this->routeParams['options'] = $options;
        }
    }

}
