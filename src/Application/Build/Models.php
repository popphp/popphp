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
use Pop\Code\Generator\NamespaceGenerator;

/**
 * Model install class
 *
 * @category   Pop
 * @package    Pop_Application
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Models
{

    /**
     * Build the model class files
     *
     * @param \Pop\Config $install
     * @return void
     */
    public static function install($install)
    {
        echo 'Creating model class files...' . PHP_EOL;

        // Create model class folder
        $modelDir = $install->project->base . '/module/' . $install->project->name . '/src/' . $install->project->name . '/Model';
        if (!file_exists($modelDir)) {
            mkdir($modelDir);
        }

        $models = $install->models->asArray();
        foreach ($models as $model) {
            $modelName = ucfirst(\Pop\Application\Build::underscoreToCamelcase($model));

            // Define namespace
            $ns = new NamespaceGenerator($install->project->name . '\Model');

            // Create and save model class file
            $modelCls = new Generator($modelDir . '/' . $modelName . '.php', Generator::CREATE_CLASS);
            $modelCls->setNamespace($ns);
            $modelCls->save();
        }
    }

}
