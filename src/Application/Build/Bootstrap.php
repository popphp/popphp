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

use Pop\Filter\String;

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
     * @param \Pop\Config $install
     * @return void
     */
    public static function install($install)
    {
        // Define full paths of the autoloader and config files
        $autoload = realpath(__DIR__ . '/../../Loader/Autoloader.php');
        $moduleSrc = realpath($install->project->base . '/module/' . $install->project->name . '/src');
        $projectCfg = realpath($install->project->base . '/config/project.php');
        $moduleCfg = realpath($install->project->base . '/module/' . $install->project->name . '/config/module.php');

        // Figure out the relative base and docroot
        $base = str_replace("\\", '/', realpath($install->project->base));
        $docroot = str_replace("\\", '/', realpath($install->project->docroot));
        $base = (substr($base, -1) == '/') ? substr($base, 0, -1) : $base;
        $docroot = (substr($docroot, -1) == '/') ? substr($docroot, 0, -1) : $docroot;

        // If the base and docroot are the same
        if (strlen($base) == strlen($docroot)) {
            $autoload = "__DIR__ . '/vendor/PopPHPFramework/src/Pop/Loader/Autoloader.php'";
            $moduleSrc = "__DIR__ . '/module/" . $install->project->name . "/src'";
            $projectCfg = "__DIR__ . '/config/project.php'";
            $moduleCfg = "__DIR__ . '/module/" . $install->project->name . "/config/module.php'";
        // If the docroot is under the base
        } else if (strlen($base) < strlen($docroot)) {
            // Calculate how many levels up the base is from the docroot
            $diff = str_replace($base, '', $docroot);
            $levels = substr_count($diff, '/');
            $dirs = '/';
            for ($i = 0; $i < $levels; $i++) {
                $dirs .= '../';
            }
            $autoload = "__DIR__ . '" . $dirs . "vendor/PopPHPFramework/src/Pop/Loader/Autoloader.php'";
            $moduleSrc = "__DIR__ . '" . $dirs . "module/" . $install->project->name . "/src'";
            $projectCfg = "__DIR__ . '" . $dirs . "config/project.php'";
            $moduleCfg = "__DIR__ . '" . $dirs . "module/" . $install->project->name . "/config/module.php'";
        // If the base is under the docroot
        } else if (strlen($base) > strlen($docroot)) {
            $dir = str_replace($docroot, '', $base);
            $autoload = "__DIR__ . '" . $dir . "/vendor/PopPHPFramework/src/Pop/Loader/Autoloader.php'";
            $moduleSrc = "__DIR__ . '" . $dir . "/module/" . $install->project->name . "/src'";
            $projectCfg = "__DIR__ . '" . $dir . "/config/project.php'";
            $moduleCfg = "__DIR__ . '" . $dir . "/module/" . $install->project->name . "/config/module.php'";
        }

        // Create new Code file object
        $bootstrap = new \Pop\Code\Generator($install->project->docroot . '/bootstrap.php');

        // Create new bootstrap file
        if (!file_exists($install->project->docroot . '/bootstrap.php')) {
            $bootstrap->appendToBody("// Require the Autoloader class file" . PHP_EOL . "require_once {$autoload};" . PHP_EOL)
                      ->appendToBody("// Instantiate the autoloader object" . PHP_EOL . "\$autoloader = Pop\\Loader\\Autoloader::factory();" . PHP_EOL . "\$autoloader->splAutoloadRegister();");
        }

        // Else, just append to the existing bootstrap file
        $bootstrap->appendToBody("\$autoloader->register('{$install->project->name}', {$moduleSrc});" . PHP_EOL)
                  ->appendToBody("// Create a project object")
                  ->appendToBody("\$project = {$install->project->name}\\Application::factory(")
                  ->appendToBody("    include {$projectCfg},")
                  ->appendToBody("    include {$moduleCfg},");

        // Set up any controllers via a router object
        if (isset($install->controllers)) {
            $controllers = $install->controllers->asArray();
            $ctrls = array();
            foreach ($controllers as $key => $value) {
                $subs = array();
                foreach ($value as $k => $v) {
                    if (is_array($v)) {
                        $subs[] = $k;
                    }
                }
                if (count($subs) > 0) {
                    $ctls = "'{$key}' => array(" . PHP_EOL;
                    if (array_key_exists('index', $value)) {
                        $ctls .= "            '/' => '{$install->project->name}\\Controller\\" . ucfirst(String::underscoreToCamelcase(substr($key, 1))) . "\\IndexController'," . PHP_EOL;
                    }
                    foreach ($subs as $sub) {
                        $ctls .= "            '{$sub}' => '{$install->project->name}\\Controller\\" . ucfirst(String::underscoreToCamelcase(substr($key, 1))) . "\\" . ucfirst(String::underscoreToCamelcase(substr($sub, 1))) . "Controller'," . PHP_EOL;
                    }
                    $ctls .= '        )';
                    $ctrls[] = $ctls;
                } else {
                    if ($key == '/') {
                        $ctrls[] = "'{$key}' => '{$install->project->name}\\Controller\\IndexController'";
                    } else {
                        $controllerName = substr($key, 1);
                        if (array_key_exists('index', $value)) {
                            $ctrls[] = "'{$key}' => '{$install->project->name}\\Controller\\" . ucfirst(String::underscoreToCamelcase($controllerName)) . "\\IndexController'";
                        } else {
                            $ctrls[] = "'{$key}' => '{$install->project->name}\\Controller\\" . ucfirst(String::underscoreToCamelcase($controllerName)) . "Controller'";
                        }
                    }
                }
            }
            $bootstrap->appendToBody("    new Pop\\Mvc\\Router(array(");
            $i = 1;
            foreach ($ctrls as $c) {
                $end = ($i < count($ctrls)) ? ',' : null;
                $bootstrap->appendToBody("        " . $c . $end);
                $i++;
            }
            $bootstrap->appendToBody("    ))");
        }

        // Finalize and save the bootstrap file
        $bootstrap->appendToBody(");")
                  ->save();
    }

}
