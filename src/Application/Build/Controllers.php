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
 * Controllers install class
 *
 * @category   Pop
 * @package    Pop_Application
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Controllers
{

    /**
     * Build the controller class files
     *
     * @param \Pop\Config $install
     * @param string     $installDir
     * @return void
     */
    public static function install($install, $installDir)
    {
        echo \Pop\I18n\I18n::factory()->__('Creating controller class files...') . PHP_EOL;

        // Make the controller folder
        $module = (substr($install->project->base, -1) == '/') ? 'module/' : '/module/';
        $ctrlDir = $install->project->base . $module . $install->project->name . '/src/' . $install->project->name . '/Controller';
        $viewDir = $install->project->base . $module . $install->project->name . '/view';

        if (!file_exists($ctrlDir)) {
            mkdir($ctrlDir);
        }

        if (!file_exists($viewDir)) {
            mkdir($viewDir);
        }

        // Create the controller class files
        if (isset($install->controllers)) {
            $controllers = $install->controllers->asArray();

            self::createControllers($controllers, array(
                'src'        => realpath($ctrlDir),
                'view'       => realpath($viewDir),
                'namespace'  => $install->project->name . '\Controller',
                'installDir' => $installDir
            ));
        }
    }

    /**
     * Create the controller class files
     *
     * @param array               $controllers
     * @param array               $base
     * @param string              $depth
     * @param \Pop\Code\Generator $controllerCls
     * @return void
     */
    public static function createControllers($controllers, $base = null, $depth = null, $controllerCls = null)
    {
        foreach ($controllers as $key => $value) {
            $level = (strpos($key, '/') !== false) ? $depth . $key : null;
            if (null !== $level) {
                if ($level != '/') {
                    $l = substr($level, 1);
                    if (strpos($l, '/') !== false) {
                        $l = substr($l, 0, strpos($l, '/'));
                    }
                    $ns = '\\' . ucfirst($l);
                    $l = '/' . ucfirst($l);
                    $lView = $level;
                } else {
                    $ns = null;
                    $l = null;
                    $lView = null;
                }

                // Check to make sure an 'index' method is defined for the top-level controller
                if ((substr_count($level, '/') == 1) && !array_key_exists('index', $value)) {
                    echo "The 'index' method of the top level controller '{$key}' is not defined." . PHP_EOL;
                    exit(0);
                }

                $viewPath = $base['view'] . (($level != '/') ? $level : null);
                $relativeViewPath = (strpos($base['src'] . $l, 'Controller/') !== false) ? '/../../../../view' . $lView : '/../../../view' . $lView;
                $srcPath = $base['src'] . $l;
                $namespace = $base['namespace'] . $ns;

                if (array_key_exists('index', $value) && ((null === $l) || (strtolower($key) == strtolower($l)))) {
                    $ctrlFile = $base['src'] . $l . '/IndexController.php';
                    $parent = 'C';
                } else if (array_key_exists('index', $value) && (strtolower($key) != strtolower($l))) {
                    $ctrlFile = $base['src'] . $l . '/' . ucfirst(substr($key, 1)) . 'Controller.php';
                    $parent = 'C';
                } else {
                    $ctrlFile = $base['src'] . $l . '/' . ucfirst(substr($key, 1)) . 'Controller.php';
                    $parent = 'IndexController';
                }

                if (!file_exists($viewPath)) {
                    mkdir($viewPath);
                }
                if (!file_exists($srcPath)) {
                    mkdir($srcPath);
                }

                if ((null === $controllerCls) || ($controllerCls->getFullpath() != $ctrlFile)) {
                    $controllerCls = new Generator($ctrlFile, Generator::CREATE_CLASS);

                    // Set namespace
                    $ns = new NamespaceGenerator($namespace);
                    $ns->setUses(array(
                        'Pop\Http\Response',
                        'Pop\Http\Request',
                        array('Pop\Mvc\Controller', 'C'),
                        'Pop\Mvc\View',
                        'Pop\Application\Application'
                    ));

                    // Create the constructor
                    $construct = new MethodGenerator('__construct');
                    $construct->setDesc('Constructor method to instantiate the controller object');
                    $construct->addArguments(array(
                        array('name' => 'request', 'value' => 'null', 'type' => 'Request'),
                        array('name' => 'response', 'value' => 'null', 'type' => 'Response'),
                        array('name' => 'project', 'value' => 'null', 'type' => 'Application'),
                        array('name' => 'viewPath', 'value' => 'null', 'type' => 'string')
                    ));

                    if ($parent == 'C') {
                        $construct->appendToBody("if (null === \$viewPath) {")
                                  ->appendToBody("    \$viewPath = __DIR__ . '{$relativeViewPath}';")
                                  ->appendToBody("}" . PHP_EOL);
                    }

                    if ($level != '/') {
                        $construct->appendToBody("if (null === \$request) {")
                                  ->appendToBody("    \$request = new Request(null, '{$level}');")
                                  ->appendToBody("}" . PHP_EOL);
                    }

                    $construct->appendToBody("parent::__construct(\$request, \$response, \$project, \$viewPath);", false);
                    $construct->getDocblock()->setReturn('self');

                    $controllerCls->setNamespace($ns);
                    $controllerCls->code()->setParent($parent)
                                          ->addMethod($construct);
                }
            }

            if (is_array($value)) {
                self::createControllers($value, $base, $level, $controllerCls);
            } else {
                // Copy view files over
                $viewPath = $base['view'] . (($depth != '/') ? $depth : null);
                if (!file_exists($viewPath)) {
                    mkdir($viewPath);
                }

                $viewFile = $base['installDir'] . '/view' . (($depth != '/') ? $depth : null) . '/' . $value;
                $viewFileCopy = $base['view'] . (($depth != '/') ? $depth : null) . '/' . $value;

                if (file_exists($viewFile)) {
                    copy($viewFile, $viewFileCopy);
                }

                // Create action methods
                $method = new MethodGenerator($key);
                $method->setDesc('The \'' . $key . '()\' method.');

                $code = ($key == 'error') ? '404' : null;

                if ($controllerCls->code()->getParent() != 'C') {
                    $vp = substr($depth, (strrpos($depth, '/') + 1)) . '/' . $value;
                } else {
                    $vp = $value;
                }
                $method->appendToBody("\$this->view = View::factory(\$this->viewPath . '/{$vp}');");
                $method->appendToBody("\$this->send(" . $code . ");", false);
                $method->getDocblock()->setReturn('void');

                $controllerCls->code()->addMethod($method);
            }
        }

        $controllerCls->save();
    }

}
