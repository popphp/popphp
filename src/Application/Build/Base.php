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
            $build->application->base . $build->application->name,
            $build->application->base . $build->application->name . '/data',
            $build->application->base . $build->application->name . '/src',
            $build->application->base . $build->application->name . '/view',
            $build->application->docroot
        ];

        // Create the folders
        foreach ($folders as $folder) {
            if (!file_exists($folder)) {
                mkdir($folder);
            }
        }

        // Make the '/data' folder writable
        chmod($build->application->base . $build->application->name . '/data', 0777);

        // Figure out the relative base and docroot
        $base    = str_replace("\\", '/', realpath($build->application->base));
        $docroot = str_replace("\\", '/', realpath($build->application->docroot));
        $base    = (substr($base, -1) == '/') ? substr($base, 0, -1) : $base;
        $docroot = (substr($docroot, -1) == '/') ? substr($docroot, 0, -1) : $docroot;

        // If the base and docroot are the same
        if (strlen($base) == strlen($docroot)) {
            $base    = "__DIR__ . '/../'";
            $docroot = "__DIR__ . '/../'";
        // If the docroot is under the base
        } else if (strlen($base) < strlen($docroot)) {
            $relDocroot = str_replace($base, '', $docroot);
            $base       = "__DIR__ . '/../'";
            $docroot    = "__DIR__ . '/.." . $relDocroot . "'";
        // If the base is under the docroot
        } else if (strlen($base) > strlen($docroot)) {
            // Calculate how many levels up the docroot is from the base
            $diff = str_replace($docroot, '/', $base);
            $levels = substr_count($diff, '/');
            $dirs = null;
            for ($i = 0; $i < $levels; $i++) {
                $dirs .= '../';
            }
            $base    = "__DIR__ . '/../'";
            $docroot = "__DIR__ . '/" . $dirs . "'";
        }

        // Create application.php file
        $applicationCfg = new \Pop\Code\Generator($build->application->base . $build->application->name . '/config.php');
        $applicationCfg->appendToBody('return new Pop\Config([', true)
                       ->appendToBody("    '" . $build->application->name . "' => [")
                       ->appendToBody("        'base'      => " . $base . ",")
                       ->appendToBody("        'docroot'   => " . $docroot . ",")
                       ->appendToBody("        'data'      => __DIR__ . '/data',")
                       ->appendToBody("        'src'       => __DIR__ . '/src',")
                       ->appendToBody("        'view'      => __DIR__ . '/view'", false);


        // Add the database config to it
        if (isset($build->databases)) {
            $applicationCfg->appendToBody(",")
                           ->appendToBody("        'databases' => [");
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

                $applicationCfg->appendToBody("            '" . $dbname . "' => new Pop\\Db\\Adapter\\" . $realDbType . "([");
                $j = 0;
                $default = ($db['default']) ? $dbname : null;
                $dbCreds = $db;
                unset($dbCreds['type']);
                unset($dbCreds['prefix']);
                unset($dbCreds['default']);
                foreach ($dbCreds as $key => $value) {
                    $j++;
                    if ($isSqlite) {
                        $dbFile = "__DIR__ . '/../" . $build->application->name . "/data/" . basename($value) . "'";
                        $ary = "                '{$key}' => {$dbFile}";
                    } else {
                        $ary = "                '{$key}' => '{$value}'";
                    }
                    if ($isPdo) {
                        $ary .= "," . PHP_EOL . "                'type' => '{$pdoType}'";
                    }
                    if ($j < count($dbCreds)) {
                       $ary .= ',';
                    }
                    $applicationCfg->appendToBody($ary);
                }
                $i++;
                $end = ($i < count($databases)) ? '            ]),' : '            ])';
                $applicationCfg->appendToBody($end);
            }
            $applicationCfg->appendToBody('        ]', false);

            if (null !== $default) {
                $applicationCfg->appendToBody("," . PHP_EOL . "        'defaultDb' => '" . $default . "'");
            }
        }

        // Save application config
        $applicationCfg->appendToBody('    ]');
        $applicationCfg->appendToBody(']);', false);
        $applicationCfg->save();
    }

}
