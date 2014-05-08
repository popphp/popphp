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
     * @param \Pop\Config $install
     * @return void
     */
    public static function install($install)
    {
        echo \Pop\I18n\I18n::factory()->__('Creating base folder and file structure...') . PHP_EOL;

        // Define folders to create
        $folders = array(
            $install->project->base,
            $install->project->base . '/config',
            $install->project->base . '/module',
            $install->project->base . '/module/' . $install->project->name,
            $install->project->base . '/module/' . $install->project->name . '/config',
            $install->project->base . '/module/' . $install->project->name . '/data',
            $install->project->base . '/module/' . $install->project->name . '/src',
            $install->project->base . '/module/' . $install->project->name . '/src/' . $install->project->name,
            $install->project->base . '/module/' . $install->project->name . '/view',
            $install->project->docroot
        );

        // Create the folders
        foreach ($folders as $folder) {
            if (!file_exists($folder)) {
                mkdir($folder);
            }
        }

        // Make the '/data' folder writable
        chmod($install->project->base . '/module/' . $install->project->name . '/data', 0777);

        // Figure out the relative base and docroot
        $base = str_replace("\\", '/', realpath($install->project->base));
        $docroot = str_replace("\\", '/', realpath($install->project->docroot));
        $base = (substr($base, -1) == '/') ? substr($base, 0, -1) : $base;
        $docroot = (substr($docroot, -1) == '/') ? substr($docroot, 0, -1) : $docroot;

        // If the base and docroot are the same
        if (strlen($base) == strlen($docroot)) {
            $base = "__DIR__ . '/../'";
            $docroot = "__DIR__ . '/../'";
        // If the docroot is under the base
        } else if (strlen($base) < strlen($docroot)) {
            $relDocroot = str_replace($base, '', $docroot);
            $base = "__DIR__ . '/../'";
            $docroot = "__DIR__ . '/.." . $relDocroot . "'";
        // If the base is under the docroot
        } else if (strlen($base) > strlen($docroot)) {
            // Calculate how many levels up the docroot is from the base
            $diff = str_replace($docroot, '/', $base);
            $levels = substr_count($diff, '/');
            $dirs = null;
            for ($i = 0; $i < $levels; $i++) {
                $dirs .= '../';
            }
            $base = "__DIR__ . '/../'";
            $docroot = "__DIR__ . '/" . $dirs . "'";
        }

        // Create project.php file
        $projectCfg = new \Pop\Code\Generator($install->project->base . '/config/project.php');
        $projectCfg->appendToBody('return new Pop\Config(array(', true)
                   ->appendToBody("    'base'      => " . $base . ",")
                   ->appendToBody("    'docroot'   => " . $docroot, false);

        // Add the database config to it
        if (isset($install->databases)) {
            $projectCfg->appendToBody(",")
                       ->appendToBody("    'databases' => array(");
            $databases = $install->databases->asArray();
            $default = null;
            $i = 0;
            foreach ($databases as $dbname => $db) {
                $isPdo = (stripos($db['type'], 'pdo') !== false) ? true : false;
                $isSqlite = (stripos($db['type'], 'sqlite') !== false) ? true : false;

                if ($isPdo) {
                    $pdoType = strtolower(substr($db['type'], (strpos($db['type'], '_') + 1)));
                    $realDbType = 'Pdo';
                } else {
                    $pdoType = null;
                    $realDbType = $db['type'];
                }

                $projectCfg->appendToBody("        '" . $dbname . "' => Pop\\Db\\Db::factory('" . $realDbType . "', array (");
                $j = 0;
                $default = ($db['default']) ? $dbname : null;
                $dbCreds = $db;
                unset($dbCreds['type']);
                unset($dbCreds['prefix']);
                unset($dbCreds['default']);
                foreach ($dbCreds as $key => $value) {
                    $j++;
                    if ($isSqlite) {
                        $dbFile = "__DIR__ . '/../module/" . $install->project->name . "/data/" . basename($value) . "'";
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
                    $projectCfg->appendToBody($ary);
                }
                $i++;
                $end = ($i < count($databases)) ? '        )),' : '        ))';
                $projectCfg->appendToBody($end);
            }
            $projectCfg->appendToBody('    )', false);

            if (null !== $default) {
                $projectCfg->appendToBody("," . PHP_EOL . "    'defaultDb' => '" . $default . "'", false);
            }
        }

        // Save project config
        $projectCfg->appendToBody(PHP_EOL . '));', false);
        $projectCfg->save();

        // Create the module config file
        $moduleCfg = new \Pop\Code\Generator($install->project->base . '/module/' . $install->project->name . '/config/module.php');
        $moduleCfg->appendToBody('return array(')
                  ->appendToBody("    '{$install->project->name}' => new Pop\Config(array(")
                  ->appendToBody("        'base'   => __DIR__ . '/../',")
                  ->appendToBody("        'config' => __DIR__ . '/../config',")
                  ->appendToBody("        'data'   => __DIR__ . '/../data',")
                  ->appendToBody("        'src'    => __DIR__ . '/../src',")
                  ->appendToBody("        'view'   => __DIR__ . '/../view'")
                  ->appendToBody("    ))")
                  ->appendToBody(");", false)
                  ->save();
    }

}
