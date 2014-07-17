<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Code
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Code;

/**
 * Generator code class
 *
 * @category   Pop
 * @package    Pop_Code
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Generator
{

    /**
     * Constant to not use a class or interface
     * @var int
     */
    const CREATE_NONE = 0;

    /**
     * Constant to use a class
     * @var int
     */
    const CREATE_CLASS = 1;

    /**
     * Constant to use an interface
     * @var int
     */
    const CREATE_INTERFACE = 2;

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
     * File output data.
     * @var string
     */
    protected $output = null;

    /**
     * Code object
     * @var Generator\ClassGenerator|Generator\InterfaceGenerator
     */
    protected $code = null;

    /**
     * Docblock generator object
     * @var Generator\DocblockGenerator
     */
    protected $docblock = null;

    /**
     * Namespace generator object
     * @var Generator\NamespaceGenerator
     */
    protected $namespace = null;

    /**
     * Code body
     * @var string
     */
    protected $body = null;

    /**
     * Code indent
     * @var string
     */
    protected $indent = null;

    /**
     * Flag to close the code file with ?>
     * @var boolean
     */
    protected $close = false;

    /**
     * Array of allowed file types.
     * @var array
     */
    protected $allowed = [
        'php'   => 'text/plain',
        'php3'  => 'text/plain',
        'phtml' => 'text/plain'
    ];

    /**
     * Constructor
     *
     * Instantiate the code generator object
     *
     * @param  string $file
     * @param  int    $type
     * @return Generator
     */
    public function __construct($file, $type = Generator::CREATE_NONE)
    {
        $fileInfo = pathinfo($file);

        $this->fullpath  = $file;
        $this->basename  = $fileInfo['basename'];
        $this->filename  = $fileInfo['filename'];
        $this->extension = (isset($fileInfo['extension'])) ? $fileInfo['extension'] : null;

        if ($type == self::CREATE_CLASS) {
            $this->createClass();
        } else if ($type == self::CREATE_INTERFACE) {
            $this->createInterface();
        } else if (($type == self::CREATE_NONE) && file_exists($file)) {
            $this->body = str_replace('<?php', '', file_get_contents($file));
            $this->body = trim(str_replace('?>', '', $this->body)) . PHP_EOL . PHP_EOL;
        }
    }

    /**
     * Create a class generator object
     *
     * @return Generator
     */
    public function createInterface()
    {
        $this->code = new Generator\InterfaceGenerator($this->filename);
        return $this;
    }

    /**
     * Create a class generator object
     *
     * @return Generator
     */
    public function createClass()
    {
        $this->code = new Generator\ClassGenerator($this->filename);
        return $this;
    }

    /**
     * Access the code generator object
     *
     * @return Generator\ClassGenerator|Generator\InterfaceGenerator
     */
    public function code()
    {
        return $this->code;
    }

    /**
     * Set the code close flag
     *
     * @param  boolean $close
     * @return Generator
     */
    public function setClose($close = false)
    {
        $this->close = (boolean)$close;
        return $this;
    }

    /**
     * Set the code indent
     *
     * @param  string $indent
     * @return Generator
     */
    public function setIndent($indent = null)
    {
        $this->indent = $indent;
        return $this;
    }

    /**
     * Get the code indent
     *
     * @return string
     */
    public function getIndent()
    {
        return $this->indent;
    }

    /**
     * Set the namespace generator object
     *
     * @param  Generator\NamespaceGenerator $namespace
     * @return Generator
     */
    public function setNamespace(Generator\NamespaceGenerator $namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Access the namespace generator object
     *
     * @return Generator\NamespaceGenerator
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Set the docblock generator object
     *
     * @param  Generator\DocblockGenerator $docblock
     * @return Generator
     */
    public function setDocblock(Generator\DocblockGenerator $docblock)
    {
        $this->docblock = $docblock;
        return $this;
    }

    /**
     * Access the docblock generator object
     *
     * @return Generator\DocblockGenerator
     */
    public function getDocblock()
    {
        return $this->docblock;
    }

    /**
     * Set the code body
     *
     * @param  string $body
     * @param  boolean $newline
     * @return Generator
     */
    public function setBody($body, $newline = true)
    {
        $this->body = $this->indent . str_replace(PHP_EOL, PHP_EOL . $this->indent, $body);
        if ($newline) {
            $this->body .= PHP_EOL;
        }

        return $this;
    }

    /**
     * Append to the code body
     *
     * @param  string $body
     * @param  boolean $newline
     * @return Generator
     */
    public function appendToBody($body, $newline = true)
    {
        $body = str_replace(PHP_EOL, PHP_EOL . $this->indent, $body);
        $this->body .= $this->indent . $body;
        if ($newline) {
            $this->body .= PHP_EOL;
        }
        return $this;
    }

    /**
     * Get the method body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get the fullpath
     *
     * @return string
     */
    public function getFullpath()
    {
        return $this->fullpath;
    }

    /**
     * Read data from the code file.
     *
     * @param  int|string $off
     * @param  int|string $len
     * @return string
     */
    public function read($off = null, $len = null)
    {
        $data = null;

        // Read from the output buffer
        if (null !== $this->output) {
            if (null !== $off) {
                $data = (null !== $len) ? substr($this->output, $off, $len) : substr($this->output, $off);
            } else {
                $data = $this->output;
            }
            // Else, if the file exists, then read the data from the actual file
        } else if (file_exists($this->fullpath)) {
            if (null !== $off) {
                $data = (null !== $len) ?
                    file_get_contents($this->fullpath, null, null, $off, $len) :
                    $this->output = file_get_contents($this->fullpath, null, null, $off);
            } else {
                $data = file_get_contents($this->fullpath);
            }
        }

        return $data;
    }

    /**
     * Render method
     *
     * @param  boolean $ret
     * @return mixed
     */
    public function render($ret = false)
    {
        $this->output = '<?php' . PHP_EOL;
        $this->output .= (null !== $this->docblock) ? $this->docblock->render(true) . PHP_EOL : null;

        if (null !== $this->namespace) {
            $this->output .= $this->namespace->render(true) . PHP_EOL;
        }

        if (null !== $this->code) {
            $this->output .= $this->code->render(true) . PHP_EOL;
        }

        if (null !== $this->body) {
            $this->output .= PHP_EOL . $this->body . PHP_EOL . PHP_EOL;
        }

        if ($this->close) {
            $this->output .= '?>' . PHP_EOL;
        }

        if ($ret) {
            return $this->output;
        } else {
            echo $this->output;
        }
    }

    /**
     * Output the code object directly.
     *
     * @param  boolean $download
     * @return Generator
     */
    public function output($download = false)
    {
        $this->render(true);

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
     * Save the code object to disk.
     *
     * @param  string $to
     * @param  boolean $append
     * @return Generator
     */
    public function save($to = null, $append = false)
    {
        $this->render(true);

        $file = (null === $to) ? $this->fullpath : $to;

        if ($append) {
            file_put_contents($file, $this->output, FILE_APPEND);
        } else {
            file_put_contents($file, $this->output);
        }

        return $this;
    }

    /**
     * Print code
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render(true);
    }

}
