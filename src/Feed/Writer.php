<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Feed
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Feed;

/**
 * Feed writer class
 *
 * @category   Pop
 * @package    Pop_Feed
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Writer
{

    /**
     * Feed headers
     * @var array
     */
    protected $headers = [];

    /**
     * Feed items
     * @var array
     */
    protected $items = [];

    /**
     * Feed date format
     * @var string
     */
    protected $dateFormat = 'D, j M Y H:i:s O';

    /**
     * Feed type
     * @var boolean
     */
    protected $atom = false;

    /**
     * Feed output
     * @var string
     */
    protected $output = null;

    /**
     * Document charset
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * Constructor
     *
     * Instantiate the feed object.
     *
     * @param  array   $headers
     * @param  array   $items
     * @return Writer
     */
    public function __construct($headers, $items)
    {
        $this->setHeaders($headers);
        $this->setItems($items);
    }

    /**
     * Set a header
     *
     * @param  string $header
     * @param  string $value
     * @return Writer
     */
    public function setHeader($header, $value)
    {
        $this->headers[$header] = $value;
        return $this;
    }

    /**
     * Set the headers
     *
     * @param  array $headers
     * @return Writer
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Set all items
     *
     * @param  array $items
     * @throws Exception
     * @return Writer
     */
    public function setItems(array $items)
    {
        if (count($items) == 0) {
            throw new Exception('Error: The items array must not be empty.');
        }
        if (!is_array(array_values($items)[0])) {
            throw new Exception('Error: The items array must contain arrays of scalar values.');
        }

        $this->items = $items;
        return $this;
    }

    /**
     * Add an item
     *
     * @param  array $item
     * @throws Exception
     * @return Writer
     */
    public function addItem(array $item)
    {
        if (count($item) == 0) {
            throw new Exception('Error: The item array must not be empty.');
        }

        if (is_array(array_values($item)[0])) {
            throw new Exception('Error: The item array must not contain scalar values.');
        }

        $this->items = array_merge($this->items, $item);
        return $this;
    }

    /**
     * Add items
     *
     * @param  array $items
     * @throws Exception
     * @return Writer
     */
    public function addItems(array $items)
    {
        foreach ($items as $item) {
            if (!is_array($item)) {
                throw new Exception('Error: The item array must contain arrays of scalar values.');
            }
            $this->addItem($item);
        }

        return $this;
    }

    /**
     * Set the date format
     *
     * @param  string $date
     * @return Writer
     */
    public function setDateFormat($date)
    {
        $this->dateFormat = $date;
        return $this;
    }

    /**
     * Method to set the  charset.
     *
     * @param  string $char
     * @return Writer
     */
    public function setCharset($char)
    {
        $this->charset = $char;
        return $this;
    }

    /**
     * Set the feed to an ATOM feed
     *
     * @return Writer
     */
    public function setAtom()
    {
        $this->atom = true;
        return $this;
    }

    /**
     * Set the feed to an RSS feed
     *
     * @return Writer
     */
    public function setRss()
    {
        $this->atom = false;
        return $this;
    }

    /**
     * Get a header
     *
     * @param  string $header
     * @return mixed
     */
    public function getHeader($header)
    {
        return (isset($this->headers[$header]) ? $this->headers[$header] : null);
    }

    /**
     * Get the headers
     *
     * @return string
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get the items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get the date format
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * Method to return the charset.
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Determine if the feed is an ATOM feed
     *
     * @return boolean
     */
    public function isAtom()
    {
        return $this->atom;
    }

    /**
     * Determine if the feed is an RSS feed
     *
     * @return boolean
     */
    public function isRss()
    {
        return (!$this->atom);
    }

    /**
     * Method to render the document and its child elements.
     *
     * @param  boolean $ret
     * @return mixed
     */
    public function render($ret = false)
    {
        $this->init();

        // If the return flag is passed, return output.
        if ($ret) {
            return $this->output;
            // Else, print output.
        } else {
            if (!headers_sent()) {
                header("HTTP/1.1 200 OK");
                header("Content-type: " . (($this->atom) ? 'application/atom+xml' : 'application/rss+xml'));
            }
            echo $this->output;
        }
    }

    /**
     * Render Dom object to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render(true);
    }

    /**
     * Initialize the feed.
     *
     * @throws Exception
     * @return void
     */
    protected function init()
    {
        $this->output = str_replace('[{charset}]', $this->charset, "<?xml version=\"1.0\" encoding=\"[{charset}]\"?>\n");

        if ($this->atom) {
            $this->output .= '<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="' . (isset($this->headers['language']) ? $this->headers['language'] : 'en') . '">' . PHP_EOL;
            $indent   = '    ';
            $itemNode = 'entry';
        } else {
            $this->output .= '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/">' . PHP_EOL;
            $this->output .= '    <channel>' . PHP_EOL;
            $indent   = '        ';
            $itemNode = 'item';
        }

        foreach ($this->headers as $header => $value) {
            if ((stripos($header, 'pubdate') !== false) ||
                (stripos($header, 'published') !== false) ||
                (stripos($header, 'updated') !== false)) {
                $value = date($this->dateFormat, strtotime($value));
            }
            if (($this->atom) && ($header == 'author')) {
                $this->output .= $indent . '<' . $header . '>' . PHP_EOL . $indent .
                    '    <name>' . $value . '</name>' . PHP_EOL . $indent . '</' . $header . '>' . PHP_EOL;
            } else if (($this->atom) && ($header == 'link')) {
                $this->output .= $indent . '<' . $header . ' href="' . $value . '" />' . PHP_EOL;
            } else if ((($this->atom) && ($header != 'language')) || (!$this->atom)) {
                $this->output .= $indent . '<' . $header . '>' . $value . '</' . $header . '>' . PHP_EOL;
            }
        }

        foreach ($this->items as $item) {
            $this->output .= $indent . '<' . $itemNode . '>' . PHP_EOL;
            foreach ($item as $key => $value) {
                if ((stripos($key, 'pubdate') !== false) ||
                    (stripos($key, 'published') !== false) ||
                    (stripos($key, 'updated') !== false)) {
                    $value = date($this->dateFormat, strtotime($value));
                }
                if (($this->atom) && ($key == 'link')) {
                    $this->output .= $indent . '    <' . $key . ' href="' . $value . '" />' . PHP_EOL;
                } else {
                    $this->output .= $indent . '    <' . $key . '>' . $value . '</' . $key . '>' . PHP_EOL;
                }
            }
            $this->output .= $indent . '</' . $itemNode . '>' . PHP_EOL;
        }

        if ($this->atom) {
            $this->output .= '</feed>' . PHP_EOL;
        } else {
            $this->output .= '    </channel>' . PHP_EOL;
            $this->output .= '</rss>' . PHP_EOL;
        }
    }

}
