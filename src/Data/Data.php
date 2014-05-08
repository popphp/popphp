<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Data
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Data;

/**
 * Data class
 *
 * @category   Pop
 * @package    Pop_Data
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Data
{

    /**
     * Data file stream
     * @var string
     */
    protected $file = null;

    /**
     * Data file type
     * @var string
     */
    protected $type = null;

    /**
     * Data stream
     * @var string
     */
    protected $data = null;

    /**
     * Data table
     * @var string
     */
    protected $table = null;

    /**
     * Data identifier quote
     * @var string
     */
    protected $idQuote = null;

    /**
     * PMA compatible XML flag
     * @var boolean
     */
    protected $pma = false;

    /**
     * Constructor
     *
     * Instantiate the data object.
     *
     * @param  string $data
     * @return \Pop\Data\Data
     */
    public function __construct($data)
    {
        if ((is_string($data)) &&
            ((stripos($data, '.csv') !== false) ||
             (stripos($data, '.json') !== false) ||
             (stripos($data, '.sql') !== false) ||
             (stripos($data, '.xml') !== false) ||
             (stripos($data, '.yml') !== false) ||
             (stripos($data, '.yaml') !== false)) && file_exists($data)) {

            $file = new \Pop\File\File($data);
            $this->file = $file->read();
            $this->type = (strtolower($file->getExt()) == 'yml') ? 'Yaml' : ucfirst(strtolower($file->getExt()));
        } else {
            $this->data = $data;
        }
    }

    /**
     * Static method to instantiate the data object and return itself
     * to facilitate chaining methods together.
     *
     * @param  string $data
     * @return \Pop\Data\Data
     */
    public static function factory($data)
    {
        return new self($data);
    }

    /**
     * Get the file stream
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Get the data stream
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the table name
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get the ID quote
     *
     * @return string
     */
    public function getIdQuote()
    {
        return $this->idQuote;
    }

    /**
     * Get the PMA flag
     *
     * @return boolean
     */
    public function getPma()
    {
        return $this->pma;
    }

    /**
     * Set the table name
     *
     * @param  string $table
     * @return \Pop\Data\Data
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Set the identifier quote
     *
     * @param  string $quote
     * @return \Pop\Data\Data
     */
    public function setIdQuote($quote)
    {
        $this->idQuote = $quote;
        return $this;
    }

    /**
     * Set the PMA compatible XML flag
     *
     * @param  boolean $comp
     * @return \Pop\Data\Data
     */
    public function setPma($comp)
    {
        $this->pma = (boolean)$comp;
        return $this;
    }

    /**
     * Parse the data file stream and return a PHP data object.
     *
     * @return mixed
     */
    public function parseFile()
    {
        $class = 'Pop\\Data\\Type\\' . $this->type;
        $this->data = $class::decode($this->file);
        return $this->data;
    }

    /**
     * Parse the data stream and return a file data stream.
     *
     * @param  string $to
     * @param  array  $options
     * @throws Exception
     * @return mixed
     */
    public function parseData($to, array $options = null)
    {
        $to = strtolower($to);
        $types = array('csv', 'html', 'json', 'sql', 'xml', 'yaml');

        if (!in_array($to, $types)) {
            throw new Exception('That data type is not supported.');
        }

        $class = 'Pop\\Data\\Type\\' . ucfirst($to);

        if ($to == 'sql') {
            $this->file = $class::encode($this->data, $this->table, $this->idQuote);
        } else if ($to == 'xml') {
            $this->file = $class::encode($this->data, $this->table, $this->pma);
        } else if ($to == 'html') {
            $this->file = $class::encode($this->data, $options);
        } else {
            $this->file = $class::encode($this->data);
        }

        return $this->file;
    }

    /**
     * Write the data stream to a file and either save or output it
     *
     * @param  string  $toFile
     * @param  boolean $output
     * @param  boolean $download
     * @throws Exception
     * @return \Pop\Data\Data
     */
    public function writeData($toFile, $output = false, $download = false)
    {
        $file = new \Pop\File\File($toFile);

        $to = (strtolower($file->getExt()) == 'yml') ? 'yaml' : strtolower($file->getExt());
        $types = array('csv', 'json', 'sql', 'xml', 'yaml');

        if (!in_array($to, $types)) {
            throw new Exception('That data type is not supported.');
        }

        $class = 'Pop\\Data\\Type\\' . ucfirst($to);

        if ($to == 'sql') {
            $this->file = $class::encode($this->data, $this->table, $this->idQuote);
        } else if ($to == 'xml') {
            $this->file = $class::encode($this->data, $this->table, $this->pma);
        } else {
            $this->file = $class::encode($this->data);
        }

        $file->write($this->file);

        // Output or save file
        if ($output) {
            $file->output($download);
        } else {
            $file->save();
        }

        return $this;
    }

}
