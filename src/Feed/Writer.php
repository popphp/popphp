<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
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

use Pop\Dom\Dom;
use Pop\Dom\Child;

/**
 * Feed writer class
 *
 * @category   Pop
 * @package    Pop_Feed
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Writer extends Dom
{

    /**
     * Constant to use JSON
     * @var int
     */
    const JSON = 11;

    /**
     * Constant to use PHP
     * @var int
     */
    const PHP = 12;

    /**
     * Feed headers
     * @var array
     */
    protected $headers = array();

    /**
     * Feed items
     * @var array
     */
    protected $items = array();

    /**
     * Feed type
     * @var string
     */
    protected $feedType = null;

    /**
     * Feed date format
     * @var string
     */
    protected $dateFormat = null;

    /**
     * Feed data for JSON and PHP formats
     * @var array
     */
    protected $data = array();

    /**
     * Constructor
     *
     * Instantiate the feed object.
     *
     * @param  array  $headers
     * @param  array  $items
     * @param  mixed  $type
     * @param  string $date
     * @return \Pop\Feed\Writer
     */
    public function __construct($headers, $items, $type = Writer::RSS, $date = 'D, j M Y H:i:s O')
    {
        $this->headers = $headers;
        $this->items = $items;
        $this->feedType = $type;
        $this->dateFormat = $date;

        parent::__construct($this->feedType, 'utf-8');
        $this->init();
    }

    /**
     * Static method to instantiate the feed writer object and return itself
     * to facilitate chaining methods together.
     *
     * @param  array  $headers
     * @param  array  $items
     * @param  mixed  $type
     * @param  string $date
     * @return \Pop\Feed\Writer
     */
    public static function factory($headers, $items, $type = Writer::RSS, $date = 'D, j M Y H:i:s O')
    {
        return new self($headers, $items, $type, $date);
    }

    /**
     * Initialize the feed.
     *
     * @throws Exception
     * @return void
     */
    protected function init()
    {
        if ($this->feedType == Writer::RSS) {
            // Set up the RSS child node.
            $rss = new Child('rss');
            $rss->setAttributes('version', '2.0');
            $rss->setAttributes('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
            $rss->setAttributes('xmlns:wfw', 'http://wellformedweb.org/CommentAPI/');

            // Set up the Channel child node and the header children.
            $channel = new Child('channel');
            foreach ($this->headers as $key => $value) {
                $channel->addChild(new Child($key, $value));
            }

            // Set up the Item child nodes and add them to the Channel child node.
            foreach ($this->items as $itm) {
                $item = new Child('item');
                foreach ($itm as $key => $value) {
                    $item->addChild(new Child($key, $value));
                }
                $channel->addChild($item);
            }

            // Add the Channel child node to the RSS child node, add the RSS child node to the DOM.
            $rss->addChild($channel);
            $this->addChild($rss);
        } else if ($this->feedType == Writer::ATOM) {
            // Set up the Feed child node.
            $feed = new Child('feed');
            $feed->setAttributes('xmlns', 'http://www.w3.org/2005/Atom');

            if (isset($this->headers['language'])) {
                $feed->setAttributes('xml:lang', $this->headers['language']);
            }

            // Set up the header children.
            foreach ($this->headers as $key => $value) {
                if ($key == 'author') {
                    $auth = new Child($key);
                    $auth->addChild(new Child('name', $value));
                    $feed->addChild($auth);
                } else if ($key == 'link') {
                    $link = new Child($key);
                    $link->setAttributes('href', $value);
                    $feed->addChild($link);
                } else if ($key != 'language') {
                    $val = ((stripos($key, 'date') !== false) || (stripos($key, 'published') !== false)) ?
                        date($this->dateFormat, strtotime($value)) : $value;
                    $feed->addChild(new Child($key, $val));
                }
            }

            // Set up the Entry child nodes and add them to the Feed child node.
            foreach ($this->items as $itm) {
                $item = new Child('entry');
                foreach ($itm as $key => $value) {
                    if ($key == 'link') {
                        $link = new Child($key);
                        $link->setAttributes('href', $value);
                        $item->addChild($link);
                    } else {
                        $val = ((stripos($key, 'date') !== false) || (stripos($key, 'published') !== false)) ? date($this->dateFormat, strtotime($value)) : $value;
                        $item->addChild(new Child($key, $val));
                    }
                }
                $feed->addChild($item);
            }

            // Add the Feed child node to the DOM.
            $this->addChild($feed);
        } else if (($this->feedType == Writer::JSON) || ($this->feedType == Writer::PHP)) {
            // Set up the header data.
            foreach ($this->headers as $key => $value) {
                $val = ((stripos($key, 'date') !== false) || (stripos($key, 'published') !== false)) ?
                    date($this->dateFormat, strtotime($value)) : $value;
                $this->data[$key] = $val;
            }

            // Set up the items data
            $this->data['items'] = array();
            foreach ($this->items as $itm) {
                foreach ($itm as $key => $value) {
                    $val = ((stripos($key, 'date') !== false) || (stripos($key, 'published') !== false)) ? date($this->dateFormat, strtotime($value)) : $value;
                    $itm[$key] = $val;
                }
                $this->data['items'][] = $itm;
            }

            // Set the content type
            $this->contentType = ($this->feedType == Writer::JSON) ? 'application/json' : 'text/plain';
        } else {
            throw new Exception('Error: The feed type must be only RSS, ATOM, JSON or PHP.');
        }
    }

    /**
     * Method to render the feed and its items
     *
     * @param  boolean $ret
     * @return mixed
     */
    public function render($ret = false)
    {
        if (($this->feedType == Writer::JSON) || ($this->feedType == Writer::PHP)) {
            $this->output = null;
            if ($this->feedType == Writer::JSON) {
                $this->output = json_encode($this->data);
            } else if ($this->feedType == Writer::PHP) {
                $this->output = serialize($this->data);
            }
            if ($ret) {
                return $this->output;
            } else {
                if (!headers_sent()) {
                    header("HTTP/1.1 200 OK");
                    header("Content-type: " . $this->contentType);
                }
                echo $this->output;
            }
        } else {
            return parent::render($ret);
        }
    }

}
