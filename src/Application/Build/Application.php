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

use Pop\Code\Generator;
use Pop\Code\Generator\MethodGenerator;
use Pop\Code\Generator\NamespaceGenerator;

/**
 * Application install class
 *
 * @category   Pop
 * @package    Pop_Application
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Application
{

    /**
     * Install the application class files
     *
     * @param \Pop\Config $build
     * @return void
     */
    public static function build($build)
    {
        // Create the application class file
        $applicationCls = new Generator(
            $build->application->base . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Application.php',
            Generator::CREATE_CLASS
        );

        // Set namespace
        $ns = new NamespaceGenerator($build->application->name);
        $ns->setUse('Pop\Application\Application', 'App');

        // Create 'run' method
        $run = new MethodGenerator('run');
        $run->setDesc('Add any application specific code to this method for run-time use here.');
        $run->appendToBody('parent::run();', false);
        $run->getDocblock()->setReturn('void');

        // Finalize the application config file and save it
        $applicationCls->setNamespace($ns);
        $applicationCls->code()->setParent('App')
                               ->addMethod($run);
        $applicationCls->save();

        $input = self::installWeb();

        // Install any web config and controller files
        if ($input != 'n') {
            if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'Web' . DIRECTORY_SEPARATOR . 'index.php')) {
                $index = new Generator(__DIR__ . DIRECTORY_SEPARATOR . 'Web' . DIRECTORY_SEPARATOR . 'index.php');
                $contents = $index->read() .
                    '// Run the application' . PHP_EOL .
                    'try {' . PHP_EOL .
                    '    $application->run();' . PHP_EOL .
                    '} catch (\Exception $e) {' . PHP_EOL .
                    '    echo $e->getMessage();' . PHP_EOL .
                    '}' . PHP_EOL;
                file_put_contents($build->application->docroot . DIRECTORY_SEPARATOR . 'index.php', $contents);
            }
            if ($input == 'a') {
                if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'Web' . DIRECTORY_SEPARATOR . 'ht.access')) {
                    copy(
                        __DIR__ . DIRECTORY_SEPARATOR . 'Web' . DIRECTORY_SEPARATOR . 'ht.access',
                        $build->application->docroot . DIRECTORY_SEPARATOR . '.htaccess'
                    );
                }
            } else if ($input == 'i') {
                if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'Web' . DIRECTORY_SEPARATOR . 'web.config')) {
                    copy(
                        __DIR__ . DIRECTORY_SEPARATOR . 'Web' . DIRECTORY_SEPARATOR . 'web.config',
                        $build->application->docroot . DIRECTORY_SEPARATOR . 'web.config'
                    );
                }
            } else {
                echo PHP_EOL . '    You will have to install your web server rewrite configuration manually.' . PHP_EOL;
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
        $msg = '    Install index controller and web configuration files?' . ' ([A]pache/[I]IS/[O]ther/[N]o) ';
        echo $msg;
        $input = null;

        while (($input != 'a') && ($input != 'i') && ($input != 'o') && ($input != 'n')) {
            if (null !== $input) {
                echo $msg;
            }
            $prompt = fopen("php://stdin", "r");
            $input  = fgets($prompt, 32);
            $input  = substr(strtolower(rtrim($input)), 0, 1);
            fclose($prompt);
        }

        return $input;
    }

}
