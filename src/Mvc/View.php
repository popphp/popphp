<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Mvc
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mvc;

/**
 * Mvc view class
 *
 * @category   Pop
 * @package    Pop_Mvc
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class View
{

    /**
     * View template file
     * @var string
     */
    protected $templateFile = null;

    /**
     * View template string
     * @var string
     */
    protected $templateString = null;

    /**
     * Model data
     * @var array
     */
    protected $data = [];

    /**
     * View output string
     * @var string
     */
    protected $output = null;

    /**
     * Constructor
     *
     * Instantiate the view object.
     *
     * @param  string $template
     * @param  array  $data
     * @return View
     */
    public function __construct($template = null, array $data = [])
    {
        if (null !== $template) {
            $this->setTemplate($template);
        }

        $this->data = $data;
    }

    /**
     * Get model data
     *
     * @param  string $key
     * @return mixed
     */
    public function get($key)
    {
        return (isset($this->data[$key])) ? $this->data[$key] : null;
    }

    /**
     * Get all model data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get view template
     *
     * @return string
     */
    public function getTemplate()
    {
        $tmpl = null;
        if (null !== $this->templateFile) {
            $tmpl = $this->templateFile;
        } else if (null !== $this->templateString) {
            $tmpl = $this->templateString;
        }

        return $tmpl;
    }

    /**
     * Get view template file
     *
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->templateFile;
    }

    /**
     * Get view template string
     *
     * @return string
     */
    public function getTemplateString()
    {
        return $this->templateString;
    }

    /**
     * Determine whether the view uses a template file
     *
     * @return boolean
     */
    public function hasTemplateFile()
    {
        return (null !== $this->templateFile);
    }

    /**
     * Determine whether the view uses a template string
     *
     * @return boolean
     */
    public function hasTemplateString()
    {
        return (null !== $this->templateString);
    }

    /**
     * Set view template with auto-detect
     *
     * @param  string $template
     * @return View
     */
    public function setTemplate($template)
    {
        if (((substr($template, -6) == '.phtml') ||
             (substr($template, -5) == '.php3') ||
             (substr($template, -4) == '.php')) && (file_exists($template))) {
            $this->templateFile   = $template;
            $this->templateString = null;
        } else {
            $this->templateString = $template;
            $this->templateFile   = null;
        }

        return $this;
    }

    /**
     * Set view template file
     *
     * @param  string $template
     * @throws Exception
     * @return View
     */
    public function setTemplateFile($template)
    {
        if (((substr($template, -6) == '.phtml') ||
             (substr($template, -5) == '.php3') ||
             (substr($template, -4) == '.php')) && (file_exists($template))) {
            $this->templateFile   = $template;
            $this->templateString = null;
        } else {
            throw new Exception('That template file either does not exist or is not the correct format.');
        }

        return $this;
    }

    /**
     * Set view template string
     *
     * @param  string $template
     * @return View
     */
    public function setTemplateString($template)
    {
        $this->templateString = $template;
        $this->templateFile   = null;
        return $this;
    }

    /**
     * Set model data
     *
     * @param  string $name
     * @param  mixed  $value
     * @return View
     */
    public function set($name, $value)
    {
        $this->data[$name] = $value;
        return $this;
    }

    /**
     * Merge new model data
     *
     * @param  array $data
     * @return View
     */
    public function merge(array $data)
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Set all model data
     *
     * @param  array $data
     * @return View
     */
    public function setData(array $data = [])
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Render the view.
     *
     * @param  boolean $ret
     * @throws Exception
     * @return mixed
     */
    public function render($ret = false)
    {
        if ((null === $this->templateFile) && (null === $this->templateString)) {
            throw new Exception('A template asset has not been assigned.');
        }

        if (null !== $this->templateFile) {
            $this->renderTemplateFile();
        } else {
            $this->renderTemplateString();
        }

        if ($ret) {
            return $this->output;
        } else {
            echo $this->output;
        }
    }

    /**
     * Render view template file
     *
     * @return void
     */
    protected function renderTemplateFile()
    {
        if (null !== $this->data) {
            foreach ($this->data as $key => $value) {
                ${$key} = $value;
            }
        }

        ob_start();
        include $this->templateFile;
        $this->output = ob_get_clean();
    }

    /**
     * Render view template string
     *
     * @return void
     */
    protected function renderTemplateString()
    {
        $this->output = $this->templateString;

        if (null !== $this->data) {
            // Parse conditionals
            $this->parseConditionals();

            // Parse array values
            $this->parseArrays();

            // Parse scalar values
            $this->parseScalars();
        }
    }

    /**
     * Parse conditionals in the template string
     *
     * @return void
     */
    protected function parseConditionals()
    {
        $matches = [];
        preg_match_all('/\[{if/mi', $this->templateString, $matches, PREG_OFFSET_CAPTURE);
        if (isset($matches[0]) && isset($matches[0][0])) {
            foreach ($matches[0] as $match) {
                $cond = substr($this->templateString, $match[1]);
                $cond = substr($cond, 0, strpos($cond, '[{/if}]') + 7);
                $var  = substr($cond, strpos($cond, '(') + 1);
                $var  = substr($var, 0, strpos($var, ')'));
                // If var is an array
                if (strpos($var, '[') !== false) {
                    $index  = substr($var, (strpos($var, '[') + 1));
                    $index  = substr($index, 0, strpos($index, ']'));
                    $var    = substr($var, 0, strpos($var, '['));
                    $varSet = (isset($this->data[$var][$index]));
                } else {
                    $index = null;
                    $varSet = (isset($this->data[$var]));
                }
                if (strpos($cond, '[{else}]') !== false) {
                    if ($varSet) {
                        $code = substr($cond, (strpos($cond, ')}]') + 3));
                        $code = substr($code, 0, strpos($code, '[{else}]'));
                        $code = (null !== $index) ?
                            str_replace('[{' . $var . '[' . $index . ']}]', $this->data[$var][$index], $code) :
                            str_replace('[{' . $var . '}]', $this->data[$var], $code);
                        $this->output = str_replace($cond, $code, $this->output);
                    } else {
                        $code = substr($cond, (strpos($cond, '[{else}]') + 8));
                        $code = substr($code, 0, strpos($code, '[{/if}]'));
                        $this->output = str_replace($cond, $code, $this->output);
                    }
                } else {
                    if ($varSet) {
                        $code = substr($cond, (strpos($cond, ')}]') + 3));
                        $code = substr($code, 0, strpos($code, '[{/if}]'));
                        $code = (null !== $index) ?
                            str_replace('[{' . $var . '[' . $index . ']}]', $this->data[$var][$index], $code) :
                            str_replace('[{' . $var . '}]', $this->data[$var], $code);
                        $this->output = str_replace($cond, $code, $this->output);
                    } else {
                        $this->output = str_replace($cond, '', $this->output);
                    }
                }
            }
        }
    }

    /**
     * Parse arrays in the template string
     *
     * @return void
     */
    protected function parseArrays()
    {
        foreach ($this->data as $key => $value) {
            if (is_array($value) || ($value instanceof \ArrayObject)) {
                $start = '[{' . $key . '}]';
                $end   = '[{/' . $key . '}]';
                if ((strpos($this->templateString, $start) !== false) && (strpos($this->templateString, $end) !== false)) {
                    $loopCode = substr($this->templateString, strpos($this->templateString, $start));
                    $loopCode = substr($loopCode, 0, (strpos($loopCode, $end) + strlen($end)));

                    $loop = str_replace($start, '', $loopCode);
                    $loop = str_replace($end, '', $loop);
                    $outputLoop = '';
                    $i = 0;
                    foreach ($value as $ky => $val) {
                        if (is_array($val) || ($val instanceof \ArrayObject)) {
                            $l = $loop;
                            foreach ($val as $k => $v) {
                                // Check is value is stringable
                                if ((is_object($v) && method_exists($v, '__toString')) || (!is_object($v) && !is_array($v))) {
                                    $l = str_replace('[{' . $k . '}]', $v, $l);
                                }
                            }
                            $outputLoop .= $l;
                        } else {
                            // Check is value is stringable
                            if ((is_object($val) && method_exists($val, '__toString')) || (!is_object($val) && !is_array($val))) {
                                $replace = (!is_numeric($ky)) ? '[{' . $ky . '}]' : '[{value}]';
                                $outputLoop .= str_replace($replace, $val, $loop);
                            }
                        }
                        $i++;
                        if ($i < count($value)) {
                            $outputLoop .= PHP_EOL;
                        }
                    }
                    $this->output = str_replace($loopCode, $outputLoop, $this->output);
                }
            }
        }
    }

    /**
     * Parse scalar values in the template string
     *
     * @return void
     */
    protected function parseScalars()
    {
        foreach ($this->data as $key => $value) {
            if (!is_array($value) && !($value instanceof \ArrayObject)) {
                // Check is value is stringable
                if ((is_object($value) && method_exists($value, '__toString')) || (!is_object($value) && !is_array($value))) {
                    $this->output = str_replace('[{' . $key . '}]', $value, $this->output);
                }
            }
        }
    }

    /**
     * Return rendered view as string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render(true);
    }

    /**
     * Get method to return the value of data[$name].
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Set method to set the property to the value of data[$name].
     *
     * @param  string $name
     * @param  mixed $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * Return the isset value of data[$name].
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Unset data[$name].
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }

}
