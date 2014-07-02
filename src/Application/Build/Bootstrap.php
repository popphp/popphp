<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Application
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Application\Build;

/**
 * Bootstrap install class
 *
 * @category   Pop
 * @package    Pop_Application
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Bootstrap
{

    /**
     * Create the bootstrap file
     *
     * @param \Pop\Config $build
     * @return void
     */
    public static function build($build)
    {
        // Define full paths of the autoloader and config files
        $autoload       = realpath(__DIR__ . '/../../../vendor/autoload.php');
        $applicationSrc = realpath($build->application->base . $build->application->name . '/src');
        $applicationCfg = realpath($build->application->base . '/config.php');

        // Figure out the relative base and docroot
        $base    = str_replace("\\", '/', realpath($build->application->base));
        $docroot = str_replace("\\", '/', realpath($build->application->docroot));
        $base    = (substr($base, -1) == '/') ? substr($base, 0, -1) : $base;
        $docroot = (substr($docroot, -1) == '/') ? substr($docroot, 0, -1) : $docroot;

        // If the base and docroot are the same
        if (strlen($base) == strlen($docroot)) {
            $autoload       = "__DIR__ . '/vendor/autoload.php'";
            $applicationSrc = "__DIR__ . '/app/src'";
            $applicationCfg = "__DIR__ . '/app/config/application.php'";
        // If the docroot is under the base
        } else if (strlen($base) < strlen($docroot)) {
            // Calculate how many levels up the base is from the docroot
            $diff   = str_replace($base, '', $docroot);
            $levels = substr_count($diff, '/');
            $dirs = '/';
            for ($i = 0; $i < $levels; $i++) {
                $dirs .= '../';
            }
            $autoload       = "__DIR__ . '" . $dirs . "vendor/autoload.php'";
            $applicationSrc = "__DIR__ . '" . $dirs . "app/src'";
            $applicationCfg = "__DIR__ . '" . $dirs . "app/config/application.php'";
        // If the base is under the docroot
        } else if (strlen($base) > strlen($docroot)) {
            $dir = str_replace($docroot, '', $base);
            $autoload       = "__DIR__ . '" . $dir . "/vendor/autoload.php'";
            $applicationSrc = "__DIR__ . '" . $dir . "/app/src'";
            $applicationCfg = "__DIR__ . '" . $dir . "/app/config/application.php'";
        }

        // Create new Code file object
        $bootstrap = new \Pop\Code\Generator($build->application->docroot . '/bootstrap.php');

        // Create new bootstrap file
        if (!file_exists($build->application->docroot . '/bootstrap.php')) {
            $bootstrap->appendToBody("// Require the autoload file" . PHP_EOL . "\$autoloader = require {$autoload};" . PHP_EOL);
        }

        // Else, just append to the existing bootstrap file
        $bootstrap->appendToBody("\$autoloader->addPsr4('{$build->application->name}\\\\', {$applicationSrc});" . PHP_EOL)
                  ->appendToBody("// Create a application object")
                  ->appendToBody("\$application = new {$build->application->name}\\Application(")
                  ->appendToBody("    include {$applicationCfg},");

        // Set up any controllers via a router object
        if (isset($build->controllers)) {
            $controllers = $build->controllers->toArray();
            $ctrls = array();
            foreach ($controllers as $key => $value) {
                $subs = array();
                foreach ($value as $k => $v) {
                    if (is_array($v)) {
                        $subs[] = $k;
                    }
                }
                if (count($subs) > 0) {
                    $ctls = "'{$key}' => [" . PHP_EOL;
                    if (array_key_exists('index', $value)) {
                        $ctls .= "            '/' => '{$build->application->name}\\Controller\\" . ucfirst(\Pop\Application\Build::underscoreToCamelcase(substr($key, 1))) . "\\IndexController'," . PHP_EOL;
                    }
                    foreach ($subs as $sub) {
                        $ctls .= "            '{$sub}' => '{$build->application->name}\\Controller\\" . ucfirst(\Pop\Application\Build::underscoreToCamelcase(substr($key, 1))) . "\\" . ucfirst(\Pop\Application\Build::underscoreToCamelcase(substr($sub, 1))) . "Controller'," . PHP_EOL;
                    }
                    $ctls .= '        ]';
                    $ctrls[] = $ctls;
                } else {
                    if ($key == '/') {
                        $ctrls[] = "'{$key}' => '{$build->application->name}\\Controller\\IndexController'";
                    } else {
                        $controllerName = substr($key, 1);
                        if (array_key_exists('index', $value)) {
                            $ctrls[] = "'{$key}' => '{$build->application->name}\\Controller\\" . ucfirst(\Pop\Application\Build::underscoreToCamelcase($controllerName)) . "\\IndexController'";
                        } else {
                            $ctrls[] = "'{$key}' => '{$build->application->name}\\Controller\\" . ucfirst(\Pop\Application\Build::underscoreToCamelcase($controllerName)) . "Controller'";
                        }
                    }
                }
            }
            $bootstrap->appendToBody("    new Pop\\Mvc\\Router([");
            $i = 1;
            foreach ($ctrls as $c) {
                $end = ($i < count($ctrls)) ? ',' : null;
                $bootstrap->appendToBody("        " . $c . $end);
                $i++;
            }
            $bootstrap->appendToBody("    ])");
        }

        // Finalize and save the bootstrap file
        $bootstrap->appendToBody(");")
                  ->save();
    }

}
