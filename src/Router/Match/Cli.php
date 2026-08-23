<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
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
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
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
     * Specificity score per prepared route, keyed the same as preparedRoutes
     * @var array
     */
    protected array $routeSpecificity = [];

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

        $this->seed($argv);
    }

    /**
     * Seed the parsing inputs (segments and route string) from either
     * pre-split argv-style segments or a raw command string
     *
     * @param  string|array $input
     * @return void
     */
    protected function seed(string|array $input): void
    {
        $segments = is_array($input)
            ? array_values($input)
            : preg_split('/\s+/', trim($input), -1, PREG_SPLIT_NO_EMPTY);

        $this->segments    = $segments;
        $this->routeString = implode(' ', $segments);
    }

    /**
     * Prepare the routes
     *
     * @return static
     */
    public function prepare(): static
    {
        $this->flattenRoutes($this->routes);

        uksort($this->preparedRoutes, function($keyA, $keyB) {
            $scoreA = $this->routeSpecificity[$keyA] ?? 0;
            $scoreB = $this->routeSpecificity[$keyB] ?? 0;
            return $scoreB <=> $scoreA;
        });

        return $this;
    }

    /**
     * Match the route
     *
     * @param  string|array|null $forceRoute
     * @return bool
     */
    public function match(mixed $forceRoute = null): bool
    {
        if (count($this->preparedRoutes) == 0) {
            $this->prepare();
        }

        $this->route                = null;
        $this->hasAllRequired       = true;
        $this->routeParams          = [];
        $this->dispatchableResolved = false;

        if ($forceRoute !== null) {
            $this->seed($forceRoute);
        }

        $routeToMatch = $this->routeString;

        foreach (array_keys($this->preparedRoutes) as $regex) {
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

        return (($this->route !== null) || $this->matchesDynamicRoute() || ($this->defaultRoute !== null));
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

                $requiredCount = 0;
                $optionalCount = 0;
                foreach ($this->parameters[$route] ?? [] as $parameter) {
                    $parameter['required'] ? $requiredCount++ : $optionalCount++;
                }
                $this->routeSpecificity[$routeRegex['regex']] = 1000 - ($requiredCount * 5) - ($optionalCount * 10);

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
        if (!isset($this->commands[$route])) {
            $this->commands[$route] = [];
        }

        $routeRegex = '^' . $this->extractRouteCommandsRegex($route);

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

        $routeRegex .= (isset($requiredParameters[0][0])) ? ' (.*)$' : '(.*)$';

        $this->indexRouteOptions($route, $options[0]);
        $this->indexRouteOptionValues($route, $optionValues[0]);
        $this->indexRouteOptionValueArrays($route, $optionValueArray[0]);
        $this->indexRouteParameters($route, $requiredParameters[0], $optionalParameters[0]);

        return [
            'regex' => '/' . $routeRegex . '/'
        ];
    }

    /**
     * Extract the leading command-segment regex fragment from a route string,
     * seeding $this->commands[$route] along the way
     *
     * @param  string $route
     * @return string
     */
    protected function extractRouteCommandsRegex(string $route): string
    {
        if (str_contains($route, '<') || str_contains($route, '[')) {
            $regexCommands = [];
            preg_match_all('/[a-zA-Z0-9-_:|\p{L}]*(?=\s)/u', $route, $commands, PREG_OFFSET_CAPTURE);
            foreach ($commands[0] as $command) {
                if (!empty($command[0])) {
                    $regexCommands[] = $command[0];
                    $this->commands[$route][] = $command[0];
                }
            }

            return (count($regexCommands) > 0) ? implode(' ', $regexCommands) : '';
        }

        $this->commands[$route] = explode(' ', $route);

        return $route . '$';
    }

    /**
     * Index the [-o]/[--option] flag matches for a route
     *
     * @param  string $route
     * @param  array  $options
     * @return void
     */
    protected function indexRouteOptions(string $route, array $options): void
    {
        foreach ($options as $option) {
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
    }

    /**
     * Index the [--option=]-style value-option matches for a route
     *
     * @param  string $route
     * @param  array  $optionValues
     * @return void
     */
    protected function indexRouteOptionValues(string $route, array $optionValues): void
    {
        foreach ($optionValues as $option) {
            $opt = str_replace(['[', ']'], ['', ''], $option[0]);
            if (str_contains($option[0], '--')) {
                $name = substr($option[0], (strpos($option[0], '--') + 2));
                $name = substr($name, 0, strpos($name, '='));
            } else {
                $name = substr($option[0], (strpos($option[0], '-') + 1));
                $name = substr($name, 0, 1);
            }
            if (!isset($this->options['values'][$route])) {
                $this->options['values'][$route] = [];
            }
            $this->options['values'][$route][$name] = '/' . $this->buildOptionValueRegex($opt) . '/';
        }
    }

    /**
     * Index the [--option=*]-style array-value-option matches for a route
     *
     * @param  string $route
     * @param  array  $optionValueArray
     * @return void
     */
    protected function indexRouteOptionValueArrays(string $route, array $optionValueArray): void
    {
        foreach ($optionValueArray as $option) {
            $opt = str_replace(['[', ']', '*'], ['', '', ''], $option[0]);
            if (str_contains($option[0], '--')) {
                $name = substr($option[0], (strpos($option[0], '--') + 2));
                $name = substr($name, 0, strpos($name, '='));
            } else {
                $name = substr($option[0], (strpos($option[0], '-') + 1));
                $name = substr($name, 0, 1);
            }
            if (!isset($this->options['arrays'][$route])) {
                $this->options['arrays'][$route] = [];
            }
            $this->options['arrays'][$route][$name] = '/' . $this->buildOptionValueRegex($opt) . '/';
        }
    }

    /**
     * Build the value-matching regex fragment shared by option-value and
     * option-value-array matches
     *
     * @param  string $opt
     * @return string
     */
    protected function buildOptionValueRegex(string $opt): string
    {
        if (str_contains($opt, '|')) {
            [$opt1, $opt2] = explode('|', $opt);
            return '(' . $opt1 . '[a-zA-Z0-9-_:|.@,\/]+|' . $opt1 . '"(.*)"|' . $opt2 .
                '[a-zA-Z0-9-_:|.@,\/]+|' . $opt2 . '"(.*)")';
        }

        return '(' . $opt . '[a-zA-Z0-9-_:|.@,\/]+|' . $opt . '"(.*)")';
    }

    /**
     * Index the <param>/[<param>] required and optional parameter positions for a route
     *
     * @param  string $route
     * @param  array  $requiredParameters
     * @param  array  $optionalParameters
     * @return void
     */
    protected function indexRouteParameters(string $route, array $requiredParameters, array $optionalParameters): void
    {
        foreach ($requiredParameters as $i => $parameter) {
            if (!isset($this->parameters[$route])) {
                $this->parameters[$route] = [];
            }
            $this->parameters[$route][substr($parameter[0], 1, -1)] = [
                'position' => ($i + 1),
                'required' => true
            ];
        }

        $cur = (isset($this->parameters[$route])) ? count($this->parameters[$route]) : 0;

        foreach ($optionalParameters as $j => $parameter) {
            if (!isset($this->parameters[$route])) {
                $this->parameters[$route] = [];
            }
            $this->parameters[$route][substr($parameter[0], 2, -2)] = [
                'position' => ($j + 1 + $cur),
                'required' => false
            ];
        }
    }

    /**
     * Parse route dispatch parameters
     *
     * @return void
     */
    protected function parseRouteParams(): void
    {
        if ($this->matchesDynamicRoute()) {
            $offset = $this->getDynamicRouteParamOffset();
            if (count($this->segments) > $offset) {
                $this->routeParams = (str_contains((string)$this->dynamicRoute, 'param*')) ?
                    [array_slice($this->segments, $offset)] : array_slice($this->segments, $offset);
            }
            return;
        }

        if ($this->route === null) {
            return;
        }

        $route = $this->preparedRoutes[$this->route]['route'];

        // Later categories win on a name collision, matching the original
        // sequential options -> values -> arrays overwrite order.
        $options = array_merge(
            $this->parseOptionFlags($route),
            $this->parseOptionValues($route),
            $this->parseOptionValueArrays($route)
        );

        $this->parsePositionalParameters($route);

        if (!empty($options)) {
            $this->routeParams['options'] = $options;
        }
    }

    /**
     * Parse [-o]/[--option] boolean flags present in the route string
     *
     * @param  string $route
     * @return array
     */
    protected function parseOptionFlags(string $route): array
    {
        $options = [];

        if (isset($this->options['options'][$route])) {
            foreach ($this->options['options'][$route] as $option => $regex) {
                $match = [];
                preg_match($regex, $this->routeString, $match);
                if (isset($match[0]) && !empty($match[0])) {
                    $options[$option] = true;
                }
            }
        }

        return $options;
    }

    /**
     * Parse [--option=]-style value options present in the route string
     *
     * @param  string $route
     * @return array
     */
    protected function parseOptionValues(string $route): array
    {
        $options = [];

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
                }
            }
        }

        return $options;
    }

    /**
     * Parse [--option=*]-style array-value options present in the route string
     *
     * @param  string $route
     * @return array
     */
    protected function parseOptionValueArrays(string $route): array
    {
        $options = [];

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
                    }
                }
                if (count($values) > 0) {
                    $options[$option] = $values;
                }
            }
        }

        return $options;
    }

    /**
     * Match remaining route segments (after commands/options are filtered
     * out) positionally against the route's required/optional parameters
     *
     * @param  string $route
     * @return void
     */
    protected function parsePositionalParameters(string $route): void
    {
        if (!isset($this->parameters[$route])) {
            return;
        }

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

}
