<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Data
 * @author     Nick Sagona, III <dev@nolainteractive.com>
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
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Data
{

    /**
     * Data file type
     * @var string
     */
    protected $type = null;

    /**
     * Data file output stream
     * @var string
     */
    protected $output = null;

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
     * Full path and name of the file, i.e. '/some/dir/file.ext'
     * @var string
     */
    protected $fullpath = null;

    /**
     * Full basename of file, i.e. 'file.ext'
     * @var string
     */
    protected $basename = null;

    /**
     * Full filename of file, i.e. 'file'
     * @var string
     */
    protected $filename = null;

    /**
     * File extension, i.e. 'ext'
     * @var string
     */
    protected $extension = null;

    /**
     * Constructor
     *
     * Instantiate the data object.
     *
     * @param  string $data
     * @return Data
     */
    public function __construct($data)
    {
        // If data is a file
        if ((is_string($data)) &&
            ((stripos($data, '.csv') !== false) ||
             (stripos($data, '.json') !== false) ||
             (stripos($data, '.sql') !== false) ||
             (stripos($data, '.xml') !== false) ||
             (stripos($data, '.yml') !== false) ||
             (stripos($data, '.yaml') !== false)) && file_exists($data)) {


            $fileInfo = pathinfo($data);

            $this->fullpath  = $data;
            $this->basename  = $fileInfo['basename'];
            $this->filename  = $fileInfo['filename'];
            $this->extension = (isset($fileInfo['extension'])) ? $fileInfo['extension'] : null;

            $this->output    = file_get_contents($data);
            $this->type      = (strtolower($this->extension) == 'yml') ? 'Yaml' : ucfirst(strtolower($this->extension));
        // Else, if it's just data
        } else {
            $this->data = $data;
        }
    }

    /**
     * Get the file stream
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
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
     * @return Data
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
     * @return Data
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
     * @return Data
     */
    public function setPma($comp)
    {
        $this->pma = (boolean)$comp;
        return $this;
    }

    /**
     * Parse the data from the file.
     *
     * @return Data
     */
    public function parseFile()
    {
        $class = 'Pop\\Data\\Type\\' . $this->type;
        $this->data = $class::decode($this->output);
        return $this;
    }

    /**
     * Parse the data.
     *
     * @param  string $to
     * @param  array  $options
     * @throws Exception
     * @return Data
     */
    public function parseData($to, array $options = null)
    {
        $to    = strtolower($to);
        $types = ['csv', 'html', 'json', 'sql', 'xml', 'yaml'];

        if (!in_array($to, $types)) {
            throw new Exception('That data type is not supported.');
        }

        $class = 'Pop\\Data\\Type\\' . ucfirst($to);

        if ($to == 'sql') {
            $this->output = $class::encode($this->data, $this->table, $this->idQuote);
        } else if ($to == 'xml') {
            $this->output = $class::encode($this->data, $this->table, $this->pma);
        } else if ($to == 'html') {
            $this->output = $class::encode($this->data, $options);
        } else {
            $this->output = $class::encode($this->data);
        }

        return $this;
    }

    /**
     * Output the data file directly.
     *
     * @param  string $to
     * @param  boolean $download
     * @return Data
     */
    public function output($to, $download = false)
    {
        $fileInfo        = pathinfo($to);
        $this->fullpath  = $to;
        $this->basename  = $fileInfo['basename'];
        $this->filename  = $fileInfo['filename'];
        $this->extension = (isset($fileInfo['extension'])) ? $fileInfo['extension'] : null;

        // Determine if the force download argument has been passed.
        $attach = ($download) ? 'attachment; ' : null;

        header('Content-type: text/plain');
        header('Content-disposition: ' . $attach . 'filename=' . $this->basename);

        if (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443)) {
            header('Expires: 0');
            header('Cache-Control: private, must-revalidate');
            header('Pragma: cache');
        }

        echo $this->output;

        return $this;
    }

    /**
     * Save the data file to disk.
     *
     * @param  string $to
     * @param  boolean $append
     * @return Data
     */
    public function save($to, $append = false)
    {
        $fileInfo        = pathinfo($to);
        $this->fullpath  = $to;
        $this->basename  = $fileInfo['basename'];
        $this->filename  = $fileInfo['filename'];
        $this->extension = (isset($fileInfo['extension'])) ? $fileInfo['extension'] : null;

        if ($append) {
            file_put_contents($to, $this->output, FILE_APPEND);
        } else {
            file_put_contents($to, $this->output);
        }

        return $this;
    }

}
