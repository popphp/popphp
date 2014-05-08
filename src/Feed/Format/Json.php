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
 * JSON feed reader class
 *
 * @category   Pop
 * @package    Pop_Feed
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Json extends AbstractFormat
{

    /**
     * Method to create a JSON feed object
     *
     * @param  mixed $options
     * @param  int   $limit
     * @throws Exception
     * @return \Pop\Feed\Format\Json
     */
    public function __construct($options, $limit = 0)
    {
        parent::__construct($options, $limit);

        // Create the PHP data array from JSON
        if (null === $this->obj) {
            if (!($this->obj = json_decode($this->source, true))) {
                throw new Exception('That feed URL cannot be read at this time. Please try again later.');
            }
        }

        // Get the main header info of the feed
        $objs = (isset($this->obj['feed'])) ? $this->obj['feed'] : $this->obj;
        $feed = array();

        $feed['title']       = (isset($objs['title'])) ? $objs['title'] : null;
        $feed['url']         = (isset($objs['link'])) ? $objs['link'] : null;
        $feed['description'] = (isset($objs['description'])) ? $objs['description'] : null;
        $feed['date']        = (isset($objs['updated'])) ? $objs['updated'] : null;
        $feed['generator']   = (isset($objs['generator'])) ? $objs['generator'] : null;
        $feed['author']      = (isset($objs['author'])) ? $objs['author'] : null;

        $this->feed = new \ArrayObject($feed, \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Method to parse a JSON feed object
     *
     * @return void
     */
    public function parse()
    {
        // Attempt to find the main feed and its entries
        if (isset($this->obj['feed']) && isset($this->obj['feed']['entry']) && is_array($this->obj['feed']['entry'])) {
            $objItems = $this->obj['feed']['entry'];
        } else if (isset($this->obj['feed']) && isset($this->obj['feed']['item']) && is_array($this->obj['feed']['item'])) {
            $objItems = $this->obj['feed']['item'];
        } else if (isset($this->obj['item']) && is_array($this->obj['item'])) {
            $objItems = $this->obj['item'];
        } else if (isset($this->obj['entry']) && is_array($this->obj['entry'])) {
            $objItems = $this->obj['entry'];
        } else if (isset($this->obj['items']) && is_array($this->obj['items'])) {
            $objItems = $this->obj['items'];
        } else if (isset($this->obj['entries']) && is_array($this->obj['entries'])) {
            $objItems = $this->obj['entries'];
        } else if (is_array($this->obj)) {
            $objItems = $this->obj;
        } else {
            $objItems = null;
        }

        $items = array();
        $count = count($objItems);
        $limit = (($this->limit > 0) && ($this->limit <= $count)) ? $this->limit : $count;

        // Attempt to parse standard feed data from the data
        for ($i = 0; $i < $limit; $i++) {
            if (isset($objItems[$i]['content'])) {
                $content = $objItems[$i]['content'];
            } else if (isset($objItems[$i]['summary'])) {
                $content = $objItems[$i]['summary'];
            } else if (isset($objItems[$i]['text'])) {
                $content = $objItems[$i]['text'];
            } else {
                $content = null;
            }

            if (isset($objItems[$i]['published'])) {
                $date = $objItems[$i]['published'];
            } else if (isset($objItems[$i]['updated'])) {
                $date = $objItems[$i]['updated'];
            } else if (isset($objItems[$i]['created'])) {
                $date = $objItems[$i]['created'];
            } else if (isset($objItems[$i]['created_at'])) {
                $date = $objItems[$i]['created_at'];
            } else {
                $date = null;
            }

            if (isset($objItems[$i]['link'])) {
                $link = $objItems[$i]['link'];
            } else if (isset($objItems[$i]['alternate'])) {
                $link = $objItems[$i]['alternate'];
            } else {
                $link = null;
            }

            if (isset($objItems[$i]['title'])) {
                $title = $objItems[$i]['title'];
            } else {
                $title = $content;
            }

            $title = (is_string($title)) ? html_entity_decode($title, ENT_QUOTES, 'UTF-8') : null;
            $content = (is_string($content)) ? html_entity_decode($content, ENT_QUOTES, 'UTF-8') : null;

            if (is_string($date)) {
                $time = (null !== $date) ? self::calculateTime($date) : null;
            } else {
                $date = null;
                $time = null;
            }

            $title = trim($title);
            if ($title == '') {
                $title = $link;
            }

            $items[] = new \ArrayObject(array(
                'title'     => $title,
                'content'   => $content,
                'link'      => $link,
                'published' => $date,
                'time'      => $time
            ), \ArrayObject::ARRAY_AS_PROPS);
        }

        $this->feed->items = $items;
    }

}
