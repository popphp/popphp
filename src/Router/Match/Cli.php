<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.2.0
 */
class Cli extends AbstractMatch
{

    /**
     * Route commands
     * @var array
     */
    protected array $commands = [];

    /**
     * Allowed route options
     * @var array
     */
    protected array $options = [
        'options' => [], // [-v|--verbose]
        'values'  => [], // [-n|--name=]
        'arrays'  => []  // [-i|--id=*]
    ];

    /**
     * Allowed route parameters
     * @var array
     */
    protected array $parameters = [];

    /**
     * Flag for all required parameters
     * @var bool
     */
    protected bool $hasAllRequired = true;

    /**
     * Constructor
     *
     *   cmd              required command
     *   [cmd]            optional command
     *   cmd1|cmd2        required command with alternate options
     *   [cmd1|cmd2]      optional command with alternate options
     *
     *   [-o|--option]    option flag
     *   [-o|--option=]   option value
     *   [-o|--option=*]  multiple option values
     *
     *   <param>          required parameter
     *   [<param>]        optional parameter
     *
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
     * @return static
     */
    public function prepare(): static
    {
        $this->flattenRoutes($this->routes);
        return $this;
    }

    /**
     * Match the route
     *
     * @param  mixed $forceRoute
     * @return bool
     */
    public function match(mixed $forceRoute = null): bool
    {
        if (count($this->preparedRoutes) == 0) {
            $this->prepare();
        }

        $routeToMatch = ($forceRoute !== null) ? $forceRoute : $this->routeString;

        foreach ($this->preparedRoutes as $regex => $controller) {
            if (preg_match($regex, $routeToMatch) != 0) {
                $this->route = $regex;
                break;
            }
        }

        if (($this->route !== null) || ($this->dynamicRoute !== null)) {
            $this->parseRouteParams();
        }

        return $this->hasRoute();
    }

    /**
     * Determine if the route has been matched
     *
     * @return bool
     */
    public function hasRoute(): bool
    {
        if (($this->route !== null) && !($this->hasAllRequired)) {
            return false;
        }

        return ((($this->route !== null) && ($this->hasAllRequired)) || (($this->dynamicRoute !== null) || ($this->defaultRoute !== null)));
    }

    /**
     * Get the route commands
     *
     * @return array
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * Get the command parameters
     *
     * @return array
     */
    public function getCommandParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get the command options
     *
     * @return array
     */
    public function getCommandOptions(): array
    {
        return $this->options;
    }

    /**
     * Get the parsed route params
     *
     * @return array
     */
    public function getParameters(): array
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
    public function getParameter(string $name): mixed
    {
        return $this->routeParams[$name] ?? null;
    }

    /**
     * Get the parsed route options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->routeParams['options'] ?? [];
    }

    /**
     * Get a parsed route option
     *
     * @param  string $name
     * @return mixed
     */
    public function getOption(string $name): mixed
    {
        return $this->routeParams['options'][$name] ?? null;
    }

    /**
     * Method to process if a route was not found
     *
     * @param  bool $exit
     * @return void
     */
    public function noRouteFound(bool $exit = true): void
    {
        if ((stripos(PHP_OS, 'darwin') === false) && (stripos(PHP_OS, 'win') !== false)) {
            $string = 'Command Not Found.';
        } else {
            $string  = "    \x1b[1;37m\x1b[41m                          \x1b[0m" . PHP_EOL;
            $string .= "    \x1b[1;37m\x1b[41m    Command Not Found.    \x1b[0m" . PHP_EOL;
            $string .= "    \x1b[1;37m\x1b[41m                          \x1b[0m";
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
    protected function flattenRoutes(array|string $route, mixed $controller = null): void
    {
        if (is_array($route)) {
            foreach ($route as $r => $c) {
                $this->flattenRoutes($r, $c);
            }
        } else if ($controller !== null) {
            if (!isset($controller['controller'])) {
                foreach ($controller as $r => $c) {
                    $this->flattenRoutes($route . $r, $c);
                }
            } else {
                $routeRegex = $this->getRouteRegex($route);
                if (isset($controller['default']) && ($controller['default'])) {
                    $this->defaultRoute['*'] = $controller;
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
    protected function getRouteRegex(string $route): array
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

        // Get route commands
        if (str_contains($route, '<') || str_contains($route, '[')) {
            $regexCommands = [];
            preg_match_all('/[a-zA-Z0-9-_:|\p{L}]*(?=\s)/u', $route, $commands, PREG_OFFSET_CAPTURE);
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

        // Get route options
        //   [-o]
        //   [--option]
        //   [-o|--option]
        //   [--option|-o]
        preg_match_all('/\[(\-[a-zA-Z0-9]|\-\-[a-zA-Z0-9:\-_]*)(\|(\-\-[a-zA-Z0-9:\-_]*|\-[a-zA-Z0-9]))*\]/', $route, $options, PREG_OFFSET_CAPTURE);

        // Get route option values
        //   [-o|--option=]
        //   [--option=|-o]
        //   [--option=]
        preg_match_all('/\[(\-[a-zA-z0-9]\|)*\-\-[a-zA-Z0-9:\-_]*=(\|\-[a-zA-z0-9])*\]/', $route, $optionValues, PREG_OFFSET_CAPTURE);

        // Get route option value arrays
        //   [-o|--option=*]
        //   [--option=*|-o]
        //   [--option=*]
        preg_match_all('/\[(\-[a-zA-z0-9]\|)*\-\-[a-zA-Z0-9:\-_]*=\*(\|\-[a-zA-z0-9])*\]/', $route, $optionValueArray, PREG_OFFSET_CAPTURE);

        // Get route required parameters <param>
        preg_match_all('/(?<!\[)<[a-zA-Z0-9-_:|]*>/', $route, $requiredParameters, PREG_OFFSET_CAPTURE);

        // Get route optional parameters [<param>]
        preg_match_all('/\[<[a-zA-Z0-9-_:|]*>\]/', $route, $optionalParameters, PREG_OFFSET_CAPTURE);

        $routeRegex .= (isset($requiredParameters[0]) && isset($requiredParameters[0][0])) ? ' (.*)$' : '(.*)$';

        foreach ($options[0] as $option) {
            if (str_contains($option[0], '--')) {
                $name = substr($option[0], (strpos($option[0], '--') + 2));
                $name = substr($name, 0, strpos($name, ']'));
                if (str_contains($name, '|')) {
                    $name = substr($name, 0, strpos($name, '|'));
                }
            } else {
                $name = substr($option[0], (strpos($option[0], '-') + 1));
                $name = (str_contains($name, '|')) ? substr($name, 0, strpos($name, '|')) : substr($name, 0, strpos($name, ']'));
            }
            if (!isset($this->options['options'][$route])) {
                $this->options['options'][$route] = [];
            }
            $this->options['options'][$route][$name] = '/' . str_replace(['[', ']'], ['(', ')'], $option[0]) . '/';
        }

        foreach ($optionValues[0] as $option) {
            $opt = str_replace(['[', ']'], ['', ''], $option[0]);
            if (str_contains($option[0], '--')) {
                $name = substr($option[0], (strpos($option[0], '--') + 2));
                $name = substr($name, 0, strpos($name, '='));
            } else {
                $name = substr($option[0], (strpos($option[0], '-') + 1));
                $name = substr($name, 0, 1);
            }
            if (str_contains($opt, '|')) {
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
            if (str_contains($option[0], '--')) {
                $name = substr($option[0], (strpos($option[0], '--') + 2));
                $name = substr($name, 0, strpos($name, '='));
            } else {
                $name = substr($option[0], (strpos($option[0], '-') + 1));
                $name = substr($name, 0, 1);
            }
            if (str_contains($opt, '|')) {
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
    protected function parseRouteParams(): void
    {
        if (($this->dynamicRoute !== null) && (count($this->segments) >= 3)) {
            $this->routeParams = (str_contains($this->dynamicRoute, 'param*')) ?
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
                    $value = null;
                    preg_match($regex, $this->routeString, $match);
                    if (isset($match[0]) && !empty($match[0])) {
                        if (str_contains($match[0], '=')) {
                            $value = substr($match[0], (strpos($match[0], '=') + 1));
                        } else if ((str_starts_with($match[0], '-')) && (substr($match[0], 1, 1) != '-') && !str_contains($match[0], $option)) {
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
                            $value = null;
                            if (str_contains($match, '=')) {
                                $value = substr($match, (strpos($match, '=') + 1));
                            } else if ((str_starts_with($match, '-')) && (substr($match, 1, 1) != '-') && !str_contains($match, $option)) {
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

            if (isset($this->parameters[$route])) {
                // Filter out commands and options from route segments, leaving only potential parameters
                $paramSegments = $this->segments;
                if (isset($this->commands[$route]) && is_array($this->commands[$route])) {
                    foreach ($this->commands[$route] as $command) {
                        if (in_array($command, $paramSegments)) {
                            unset($paramSegments[array_search($command, $paramSegments)]);
                        }
                    }
                }
                $paramSegments = array_values(array_filter($paramSegments, function($value) {
                    return !str_starts_with($value, '-');
                }));

                $i = 0;

                foreach ($this->parameters[$route] as $name => $parameter) {
                    if (isset($paramSegments[$i])) {
                        $this->routeParams[$name] = $paramSegments[$i];
                        $i++;
                    } else {
                        $this->routeParams[$name] = null;
                    }

                    if (($parameter['required']) && ($this->routeParams[$name] === null)) {
                        $this->hasAllRequired = false;
                    }
                }
            }

            if (!empty($options)) {
                $this->routeParams['options'] = $options;
            }
        }
    }

}
