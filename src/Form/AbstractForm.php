<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Form;

use Pop\Dom\Child;

/**
 * Abstract form class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractForm extends Child
{

    /**
     * Form template
     * @var string
     */
    protected $template = null;

    /**
     * Form field values
     * @var array
     */
    protected $fields = [];

    /**
     * Form field groups
     * @var array
     */
    protected $groups = [];

    /**
     * Form field configuration values
     * @var array
     */
    protected $fieldConfig = [];

    /**
     * Global Form error display format
     * @var array
     */
    protected $errorDisplay = null;

    /**
     * Has file flag
     * @var boolean
     */
    protected $hasFile = false;

    /**
     * Set a form template for the render method to utilize
     *
     * @param  string $tmpl
     * @return Form
     */
    public function setTemplate($tmpl)
    {
        if (preg_match('/(.*)\.(x|ht|pht|xht)ml/i', $tmpl) ||
            (substr($tmpl, -4) == '.htm') ||
            (substr($tmpl, -4) == '.php') ||
            (substr($tmpl, -5) == '.php3') ||
            (substr($tmpl, -4) == '.txt')) {
            if (file_exists($tmpl)) {
                $this->template = ((stripos($tmpl, '.phtml') === false) && (stripos($tmpl, '.php') === false)) ?
                    file_get_contents($tmpl) :
                    $tmpl;
            } else {
                $this->template = $tmpl;
            }
        } else {
            $this->template = $tmpl;
        }
        return $this;
    }

    /**
     * Set the form action
     *
     * @param  string $action
     * @return Form
     */
    public function setAction($action)
    {
        $this->setAttribute('action', $action);
        return $this;
    }

    /**
     * Set the form method
     *
     * @param  string $method
     * @return Form
     */
    public function setMethod($method)
    {
        $this->setAttribute('method', $method);
        return $this;
    }

    /**
     * Get the form template for the render method to utilize.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Get the $hasFile property
     *
     * @return boolean
     */
    public function hasFile()
    {
        return $this->hasFile;
    }

    /**
     * Get the form action
     *
     * @return array
     */
    public function getAction()
    {
        return $this->getAttribute('action');
    }

    /**
     * Get the form method
     *
     * @return array
     */
    public function getMethod()
    {
        return $this->getAttribute('method');
    }

    /**
     * Get the form fields
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Method to clear any session data used with the form for
     * security tokens, captchas, etc.
     *
     * @return Form
     */
    public function clear()
    {
        // Start a session.
        if (session_id() == '') {
            session_start();
        }

        if (isset($_SESSION['pop_csrf'])) {
            unset($_SESSION['pop_csrf']);
        }

        if (isset($_SESSION['pop_captcha'])) {
            unset($_SESSION['pop_captcha']);
        }

        return $this;
    }

    /**
     * Method to filter the values with the applied
     * callbacks and their parameters
     *
     * @param  array $values
     * @param  array $filters
     * @return array
     */
    protected function filterValues(array $values, array $filters)
    {
        $filteredValues = [];

        foreach ($values as $key => $value) {
            foreach ($filters as $func => $params) {
                if (function_exists($func)) {
                    if ($value instanceof \ArrayObject) {
                        $value = (array)$value;
                    }
                    if (is_array($value)) {
                        $filteredAry = [];
                        foreach ($value as $k => $v) {
                            if (null !== $params) {
                                $pars = (!is_array($params)) ?
                                    [$v, $params] :
                                    array_merge([$v], $params);
                                $filteredAry[$k] = call_user_func_array($func, $pars);
                            } else {
                                $filteredAry[$k] = $func($v);
                            }
                        }
                        $filteredValues[$key] = $filteredAry;
                        $value = $filteredAry;
                    } else {
                        if (null !== $params) {
                            $pars = (!is_array($params)) ?
                                [$value, $params] :
                                array_merge([$value], $params);
                            $filteredValues[$key] = call_user_func_array($func, $pars);
                        } else {
                            $filteredValues[$key] = $func($value);
                        }
                        $value = $filteredValues[$key];
                    }
                } else {
                    $filteredValues[$key] = $value;
                }
            }
        }

        return $filteredValues;
    }

    /**
     * Set method to set the property to the value of fields[$name]
     *
     * @param  string $name
     * @param  mixed $value
     * @throws Exception
     * @return void
     */
    public function __set($name, $value)
    {
        $this->fields[$name] = $value;
    }

    /**
     * Get method to return the value of fields[$name]
     *
     * @param  string $name
     * @throws Exception
     * @return mixed
     */
    public function __get($name)
    {
        return (!array_key_exists($name, $this->fields)) ? null : $this->fields[$name];
    }

    /**
     * Return the isset value of fields[$name]
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->fields[$name]);
    }

    /**
     * Unset fields[$name]
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        $this->fields[$name] = null;
    }

}
