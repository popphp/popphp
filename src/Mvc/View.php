<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Mvc
 * @author     Nick Sagona, III <info@popphp.org>
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
 * @author     Nick Sagona, III <info@popphp.org>
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
            // Render nested arrays first
            foreach ($this->data as $key => $value) {
                if (is_array($value) || ($value instanceof \ArrayObject)) {
                    $start = '[{' . $key . '}]';
                    $end = '[{/' . $key . '}]';
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

            // Render scalar values
            foreach ($this->data as $key => $value) {
                if (!is_array($value) && !($value instanceof \ArrayObject)) {
                    // Check is value is stringable
                    if ((is_object($value) && method_exists($value, '__toString')) || (!is_object($value) && !is_array($value))) {
                        $this->output = str_replace('[{' . $key . '}]', $value, $this->output);
                    }
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
