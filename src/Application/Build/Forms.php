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
 * Form install class
 *
 * @category   Pop
 * @package    Pop_Application
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Forms
{

    /**
     * Build the form class files
     *
     * @param \Pop\Config $build
     * @return void
     */
    public static function build($build)
    {
        echo PHP_EOL . '    Creating form class files...' . PHP_EOL;

        // Create form class folder
        $formDir = $build->application->base . $build->application->name . '/src/Form';
        if (!file_exists($formDir)) {
            mkdir($formDir);
        }

        $forms = $build->forms->toArray();
        foreach ($forms as $name => $form) {
            $formName = ucfirst(\Pop\Application\Build::underscoreToCamelcase($name));

            // Define namespace
            $ns = new NamespaceGenerator($build->application->name . '\Form');
            $ns->setUse('Pop\Form\Form')
               ->setUse('Pop\Form\Element')
               ->setUse('Pop\Validator');

            // Create the constructor
            $construct = new MethodGenerator('__construct');
            $construct->setDesc('Constructor method to instantiate the form object');
            $construct->getDocblock()->setReturn('self');
            $construct->addArguments(
                array(
                    array('name' => 'action', 'value' => 'null',   'type' => 'string'),
                    array('name' => 'method', 'value' => "'post'", 'type' => 'string'),
                    array('name' => 'fields', 'value' => 'null',   'type' => 'array'),
                    array('name' => 'indent', 'value' => 'null',   'type' => 'string')
                )
            );

            // Create the init values array within the constructor
            if (is_array($form) && (count($form) > 0)) {
                $construct->appendToBody("\$this->initFieldsValues = [");
                $i = 0;
                foreach ($form as $nm => $field) {
                    $i++;
                    $construct->appendToBody("    '" . $nm . "' => [");
                    $j = 0;
                    foreach ($field as $key => $value) {
                        $j++;
                        $comma = ($j < count($field)) ? ',' : null;
                        if ($key == 'validators') {
                            $val = null;
                            if (is_array($value)) {
                                $val = '[' . PHP_EOL;
                                foreach ($value as $v) {
                                    $val .= '            new Validator\\' . $v . ',' . PHP_EOL;
                                }
                                $val .= '        ]';
                            } else {
                                $val = 'new Validator\\' . $value;
                            }
                            $construct->appendToBody("        '{$key}' => {$val}{$comma}");
                        } else if (($key == 'value') || ($key == 'marked') || ($key == 'attributes') || ($key == 'error')) {
                            $val = var_export($value, true);
                            $val = str_replace(PHP_EOL, PHP_EOL . '        ', $val);
                            if (strpos($val, 'Select::') !== false) {
                                $val = 'Element\\' . str_replace("'", '', $val);
                            }
                            $construct->appendToBody("        '{$key}' => {$val}{$comma}");
                        } else {
                            if (is_bool($value)) {
                                $val = ($value) ? 'true' : 'false';
                            } else {
                                $val = "'" . $value . "'";
                            }
                            $construct->appendToBody("        '{$key}' => {$val}{$comma}");
                        }
                    }
                    $end = ($i < count($form)) ? '    ],' : '    ]';
                    $construct->appendToBody($end);
                }
                $construct->appendToBody("];");
            }

            $construct->appendToBody("parent::__construct(\$action, \$method, \$fields, \$indent);");

            // Create and save form class file
            $formCls = new Generator($formDir . '/' . $formName . '.php', Generator::CREATE_CLASS);
            $formCls->setNamespace($ns);
            $formCls->code()->setParent('Form')
                            ->addMethod($construct);

            $formCls->save();
        }
    }

}
