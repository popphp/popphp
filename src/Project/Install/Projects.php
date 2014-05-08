<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Project
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Project\Install;

use Pop\Code\Generator;
use Pop\Code\Generator\MethodGenerator;
use Pop\Code\Generator\NamespaceGenerator;

/**
 * Project install class
 *
 * @category   Pop
 * @package    Pop_Project
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Projects
{

    /**
     * Install the project class files
     *
     * @param \Pop\Config $install
     * @param string     $installDir
     * @return void
     */
    public static function install($install, $installDir)
    {
        // Create the project class file
        $projectCls = new Generator(
            $install->project->base . '/module/' . $install->project->name . '/src/' . $install->project->name . '/Project.php',
            Generator::CREATE_CLASS
        );

        // Set namespace
        $ns = new NamespaceGenerator($install->project->name);
        $ns->setUse('Pop\Project\Project', 'P');

        // Create 'run' method
        $run = new MethodGenerator('run');
        $run->setDesc('Add any project specific code to this method for run-time use here.');
        $run->appendToBody('parent::run();', false);
        $run->getDocblock()->setReturn('void');

        // Finalize the project config file and save it
        $projectCls->setNamespace($ns);
        $projectCls->code()->setParent('P')
                           ->addMethod($run);
        $projectCls->save();

        $input = self::installWeb();

        // Install any web config and controller files
        if ($input != 'n') {
            if (file_exists(__DIR__ . '/Web/index.php')) {
                $index = new Generator(__DIR__ . '/Web/index.php');
                $contents = $index->read() .
                    '// Run the project' . PHP_EOL .
                    'try {' . PHP_EOL .
                    '    $project->run();' . PHP_EOL .
                    '} catch (\Exception $e) {' . PHP_EOL .
                    '    echo $e->getMessage();' . PHP_EOL .
                    '}' . PHP_EOL;
                file_put_contents($install->project->docroot . '/index.php', $contents);
            }
            if ($input == 'a') {
                if (file_exists(__DIR__ . '/Web/ht.access')) {
                    copy(__DIR__ . '/Web/ht.access', $install->project->docroot . '/.htaccess');
                }
            } else if ($input == 'i') {
                if (file_exists(__DIR__ . '/Web/web.config')) {
                    copy(__DIR__ . '/Web/web.config', $install->project->docroot . '/web.config');
                }
            } else {
                echo \Pop\I18n\I18n::factory()->__('You will have to install your web server rewrite configuration manually.') . PHP_EOL;
            }
        }
    }

    /**
     * Install index controller and web config files prompt
     *
     * @return string
     */
    public static function installWeb()
    {
        $msg = \Pop\I18n\I18n::factory()->__('Install index controller and web configuration files?') . ' ([A]pache/[I]IS/[O]ther/[N]o) ';
        echo $msg;
        $input = null;

        while (($input != 'a') && ($input != 'i') && ($input != 'o') && ($input != 'n')) {
            if (null !== $input) {
                echo $msg;
            }
            $prompt = fopen("php://stdin", "r");
            $input = fgets($prompt, 32);
            $input = substr(strtolower(rtrim($input)), 0, 1);
            fclose ($prompt);
        }

        return $input;
    }

}
