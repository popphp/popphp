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
 * RSS feed reader class
 *
 * @category   Pop
 * @package    Pop_Feed
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Rss extends AbstractFormat
{

    /**
     * Method to create an RSS feed object
     *
     * @param  mixed  $options
     * @param  int    $limit
     * @throws Exception
     * @return \Pop\Feed\Format\Rss
     */
    public function __construct($options, $limit = 0)
    {
        parent::__construct($options, $limit);

        // Create the SimpleXMLElement
        if (null === $this->obj) {
            if (!($this->obj = simplexml_load_string($this->source, 'SimpleXMLElement', LIBXML_NOWARNING))) {
                throw new Exception('That feed URL cannot be read at this time. Please try again later.');
            }
        }

        // Check for the date
        if (isset($this->obj->channel->lastBuildDate)) {
            $date = (string)$this->obj->channel->lastBuildDate;
        } else if (isset($this->obj->channel->pubDate)) {
            $date = (string)$this->obj->channel->pubDate;
        } else {
            $date = null;
        }

        // Get the main header info of the feed
        $feed = array();

        $feed['title']       = (isset($this->obj->channel->title)) ? (string)$this->obj->channel->title : null;
        $feed['url']         = (isset($this->obj->channel->link)) ? (string)(string)$this->obj->channel->link : null;
        $feed['description'] = (isset($this->obj->channel->description)) ? (string)$this->obj->channel->description : null;
        $feed['date']        = $date;
        $feed['generator']   = (isset($this->obj->channel->generator)) ? (string)$this->obj->channel->generator : null;
        $feed['author']      = (isset($this->obj->channel->managingEditor)) ? (string)$this->obj->channel->managingEditor : null;
        $feed['items']       = array();

        $this->feed = new \ArrayObject($feed, \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Method to parse an RSS feed object
     *
     * @return void
     */
    public function parse()
    {
        $items = array();
        $itemObjs = (isset($this->obj->channel->item)) ? $this->obj->channel->item : $this->obj->item;
        $count = count($itemObjs);
        $limit = (($this->limit > 0) && ($this->limit <= $count)) ? $this->limit : $count;

        for ($i = 0; $i < $limit; $i++) {
            $title = trim((string)$itemObjs[$i]->title);
            if ($title == '') {
                $title = (string)$itemObjs[$i]->link;
            }
            $items[] = new \ArrayObject(array(
                'title'     => html_entity_decode($title, ENT_QUOTES, 'UTF-8'),
                'content'   => html_entity_decode((string)$itemObjs[$i]->description, ENT_QUOTES, 'UTF-8'),
                'link'      => (string)$itemObjs[$i]->link,
                'published' => (string)$itemObjs[$i]->pubDate,
                'time'      => self::calculateTime((string)$itemObjs[$i]->pubDate)
            ), \ArrayObject::ARRAY_AS_PROPS);
        }

        $this->feed->items = $items;
    }

}
