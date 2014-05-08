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
namespace Pop\Feed\Format;

/**
 * PHP feed reader class
 *
 * @category   Pop
 * @package    Pop_Feed
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Php extends AbstractFormat
{

    /**
     * Method to create a PHP feed object
     *
     * @param  mixed  $options
     * @param  int    $limit
     * @throws Exception
     * @return \Pop\Feed\Format\Php
     */
    public function __construct($options, $limit = 0)
    {
        parent::__construct($options, $limit);

        // Create the PHP data object from the serialized PHP string source
        if (null === $this->obj) {
            if (!($this->obj = unserialize($this->source))) {
                throw new Exception('That feed URL cannot be read at this time. Please try again later.');
            }
        }
    }

    /**
     * Method to parse a PHP feed object
     *
     * @return void
     */
    public function parse()
    {
        if (is_array($this->obj)) {
            $this->feed = new \ArrayObject($this->obj, \ArrayObject::ARRAY_AS_PROPS);
        } else {
            $this->feed = $this->obj;
        }

        $key = null;
        $items = array();

        // Attempt to find the main feed and its entries
        if (isset($this->feed['item']) && is_array($this->feed['item'])) {
            $key = 'item';
        } else if (isset($this->feed['items']) && is_array($this->feed['items'])) {
            $key = 'items';
        } else if (isset($this->feed['entry']) && is_array($this->feed['entry'])) {
            $key = 'entry';
        } else if (isset($this->feed['entries']) && is_array($this->feed['entries'])) {
            $key = 'entries';
        }

        // Attempt to parse standard feed data from the data
        if (null !== $key) {
            $count = count($this->feed[$key]);
            $limit = (($this->limit > 0) && ($this->limit <= $count)) ? $this->limit : $count;
            for ($i = 0; $i < $limit; $i++) {

                if (is_array($this->feed[$key][$i])) {
                    $itm = (array)$this->feed[$key][$i];

                    $title = null;
                    $content = null;
                    $link = null;
                    $date = null;
                    $time = null;

                    if (isset($itm['title'])) {
                        $title = $itm['title'];
                        unset($itm['title']);
                    }
                    if (isset($itm['description'])) {
                        $content = $itm['description'];
                        unset($itm['description']);
                    }
                    if (isset($itm['link'])) {
                        $link = $itm['link'];
                        unset($itm['link']);
                    } else if (isset($itm['url'])) {
                        $link = $itm['url'];
                        unset($itm['url']);
                    }

                    foreach ($itm as $k => $v) {
                        if (stripos($k, 'date') !== false) {
                            $date = $v;
                            unset($itm[$k]);
                        }
                    }

                    if (null !== $date) {
                        $time = self::calculateTime($date);
                    }

                    $newItem = array(
                        'title'     => $title,
                        'content'   => $content,
                        'link'      => $link,
                        'published' => $date,
                        'time'      => $time
                    );

                    $item =  new \ArrayObject(array_merge($newItem, $itm), \ArrayObject::ARRAY_AS_PROPS);
                } else {
                    $item = $this->feed[$key][$i];
                }
                $items[] = $item;
            }
            $this->feed[$key] = $items;
        }

    }

}
