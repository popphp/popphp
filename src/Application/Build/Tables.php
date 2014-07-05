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

use Pop\Code\Generator;
use Pop\Code\Generator\PropertyGenerator;
use Pop\Code\Generator\NamespaceGenerator;

/**
 * Table install class
 *
 * @category   Pop
 * @package    Pop_Application
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Tables
{

    /**
     * Build the table class files
     *
     * @param \Pop\Config $build
     * @param array       $dbTables
     * @return void
     */
    public static function build($build, $dbTables)
    {
        echo PHP_EOL . '    Creating database table class files...' . PHP_EOL;

        // Create table class folder
        $tableDir = $build->base . DIRECTORY_SEPARATOR . 'src/Table';
        if (!file_exists($tableDir)) {
            mkdir($tableDir);
        }

        // Loop through the tables, creating the classes
        foreach ($dbTables as $table => $value) {
            $prefix = (isset($value['prefix'])) ? $value['prefix'] : null;
            $tableName = ucfirst(\Pop\Application\Build::underscoreToCamelcase(str_replace($prefix, '', $table)));

            $ns = new NamespaceGenerator($build->name . '\Table');
            $ns->setUse('Pop\Db\Record');

            if (strpos($value['primaryId'], '|') !== false) {
                $pKeys = explode('|', $value['primaryId']);
            } else {
                $pKeys = [$value['primaryId']];
            }

            if (null !== $prefix) {
                $prefix = new PropertyGenerator('prefix', 'string', $prefix, 'protected');
                $prefix->setStatic(true);
            }
            $propId = new PropertyGenerator('primaryKeys', 'array', $pKeys, 'protected');

            // Create and save table class file
            $tableCls = new Generator($tableDir . '/' . $tableName . '.php', Generator::CREATE_CLASS);
            $tableCls->setNamespace($ns);
            $tableCls->code()->setParent('Record')
                             ->addProperty($propId);

            if (null !== $prefix) {
                $tableCls->code()->addProperty($prefix);
            }

            $tableCls->save();
        }
    }

}
