<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
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
 * Base install class
 *
 * @category   Pop
 * @package    Pop_Application
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Base
{

    /**
     * Build the base folder and file structure
     *
     * @param \Pop\Config $build
     * @return void
     */
    public static function build($build)
    {
        echo PHP_EOL . '    Creating base folder and file structure...' . PHP_EOL;

        // Define folders to create
        $folders = [
            $build->base,
            $build->base . DIRECTORY_SEPARATOR . 'config',
            $build->base . DIRECTORY_SEPARATOR . 'data',
            $build->base . DIRECTORY_SEPARATOR . 'src',
            $build->base . DIRECTORY_SEPARATOR . 'view',
            $build->docroot,
        ];

        // Create the folders
        foreach ($folders as $folder) {
            if (!file_exists($folder)) {
                mkdir($folder);
            }
        }

        // Make the '/data' folder writable
        chmod($build->base . DIRECTORY_SEPARATOR . 'data', 0777);

        // If configuration is for a module
        if (isset($build->module) && ($build->module)) {
            // Create module.php file
            $moduleCfg = new \Pop\Code\Generator(
                $build->base . DIRECTORY_SEPARATOR .
                'config' . DIRECTORY_SEPARATOR . 'module.php'
            );
            $moduleCfg->appendToBody('return new Pop\Config([', true)
                      ->appendToBody("    '" . $build->name . "' => [")
                      ->appendToBody("        'base' => realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR)")
                      ->appendToBody('    ]')
                      ->appendToBody(']);', false)
                      ->save();
        // Else, it's for an application
        } else {
            if (!file_exists($build->base . DIRECTORY_SEPARATOR . 'modules')) {
                mkdir($build->base . DIRECTORY_SEPARATOR . 'modules');
            }

            // Create application.php file
            $applicationCfg = new \Pop\Code\Generator(
                $build->base . DIRECTORY_SEPARATOR .
                'config' . DIRECTORY_SEPARATOR . 'application.php'
            );

            $applicationCfg->appendToBody('return new Pop\Config([')
                           ->appendToBody("    'name'      => '" . $build->name . "',")
                           ->appendToBody("    'base'      => realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR)", false);

            // Add the database config to it
            if (isset($build->databases)) {
                $applicationCfg->appendToBody(",")
                               ->appendToBody("    'databases' => [");
                $databases = $build->databases->toArray();
                $default   = null;
                $i = 0;
                foreach ($databases as $dbname => $db) {
                    $isPdo    = (stripos($db['type'], 'pdo') !== false) ? true : false;
                    $isSqlite = (stripos($db['type'], 'sqlite') !== false) ? true : false;

                    if ($isPdo) {
                        $pdoType = strtolower(substr($db['type'], (strpos($db['type'], '_') + 1)));
                        $realDbType = 'Pdo';
                    } else {
                        $pdoType = null;
                        $realDbType = $db['type'];
                    }

                    $applicationCfg->appendToBody("        '" . $dbname . "' => new Pop\\Db\\Adapter\\" . $realDbType . "([");
                    $j = 0;
                    $default = ($db['default']) ? $dbname : null;
                    $dbCreds = $db;
                    unset($dbCreds['type']);
                    unset($dbCreds['prefix']);
                    unset($dbCreds['default']);
                    foreach ($dbCreds as $key => $value) {
                        $j++;
                        if ($isSqlite) {
                            $dbFile = "realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . '" . basename($value) . "')";
                            $ary = "            '{$key}' => {$dbFile}";
                        } else {
                            $ary = "            '{$key}' => '{$value}'";
                        }
                        if ($isPdo) {
                            $ary .= "," . PHP_EOL . "            'type' => '{$pdoType}'";
                        }
                        if ($j < count($dbCreds)) {
                            $ary .= ',';
                        }
                        $applicationCfg->appendToBody($ary);
                    }
                    $i++;
                    $end = ($i < count($databases)) ? '        ]),' : '        ])';
                    $applicationCfg->appendToBody($end);
                }
                $applicationCfg->appendToBody('    ]', false);

                if (null !== $default) {
                    $applicationCfg->appendToBody("," . PHP_EOL . "    'defaultDb' => '" . $default . "'");
                }
            }

            // Save application config
            $applicationCfg->appendToBody(']);', false);
            $applicationCfg->save();
        }
    }

}
