<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Config
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop;

/**
 * Config class
 *
 * @category   Pop
 * @package    Pop_Config
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Config
{
    /**
     * Flag for whether or not changes are allowed after object instantiation
     * @var boolean
     */
    protected $allowChanges = false;

    /**
     * Config values as config objects
     * @var array
     */
    protected $config = [];

    /**
     * Config values as an array
     * @var array
     */
    protected $array = [];

    /**
     * Constructor
     *
     * Instantiate a config object
     *
     * @param  array   $config
     * @param  boolean $changes
     * @return \Pop\Config
     */
    public function __construct(array $config = [], $changes = false)
    {
        $this->allowChanges = $changes;
        $this->setConfig($config);
    }

    /**
     * Method to merge the values of another config object into this one
     *
     * @param  mixed $config
     * @throws \Exception
     * @return \Pop\Config
     */
    public function merge($config)
    {
        if (!is_array($config) && !($config instanceof Config)) {
            throw new \Exception('The config passed must be an array or an instance of Pop\Config.');
        }

        $orig = $this->toArray();
        $merge = ($config instanceof \Pop\Config) ? $config->toArray() : $config;

        $this->setConfig(array_merge_recursive($orig, $merge));
        $this->array = [];

        return $this;
    }

    /**
     * Method to get the config values as an array or ArrayObject
     *
     * @param  boolean $arrayObject
     * @return array
     */
    public function toArray($arrayObject = false)
    {
        $this->array = ($arrayObject) ? new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS) : [];
        $this->getConfig($arrayObject);
        return $this->array;
    }

    /**
     * Method to return if changes to the config are allowed.
     *
     * @return boolean
     */
    public function changesAllowed()
    {
        return $this->allowChanges;
    }

    /**
     * Magic get method to return the value of config[$name].
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return (array_key_exists($name, $this->config)) ? $this->config[$name] : null;
    }

    /**
     * Set method to set the property to the value of config[$name].
     *
     * @param  string $name
     * @param  mixed $value
     * @throws \Exception
     * @return void
     */
    public function __set($name, $value)
    {
        if ($this->allowChanges) {
            $this->config[$name] = (is_array($value) ? new Config($value, $this->allowChanges) : $value);
        } else {
            throw new \Exception('Real-time configuration changes are not allowed.');
        }
    }

    /**
     * Return the isset value of config[$name].
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->config[$name]);
    }

    /**
     * Unset config[$name].
     *
     * @param  string $name
     * @throws \Exception
     * @return void
     */
    public function __unset($name)
    {
        if ($this->allowChanges) {
            unset($this->config[$name]);
        } else {
            throw new \Exception('Real-time configuration changes are not allowed.');
        }
    }

    /**
     * Method to set the config values
     *
     * @param  array $config
     * @return void
     */
    protected function setConfig($config)
    {
        foreach ($config as $key => $value) {
            $this->config[$key] = (is_array($value) ? new Config($value, $this->allowChanges) : $value);
        }
    }

    /**
     * Method to get the config values as array
     *
     * @param  boolean $arrayObject
     * @return void
     */
    protected function getConfig($arrayObject = false)
    {
        foreach ($this->config as $key => $value) {
            $this->array[$key] = ($value instanceof Config) ? $value->toArray($arrayObject) : $value;
        }
    }

}
