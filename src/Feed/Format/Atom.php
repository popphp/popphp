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
namespace Pop\Feed\Format;

/**
 * Atom feed reader class
 *
 * @category   Pop
 * @package    Pop_Feed
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Atom extends AbstractFormat
{

    /**
     * Method to create an Atom feed object
     *
     * @param  mixed  $options
     * @param  int    $limit
     * @throws Exception
     * @return Atom
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

        // Get the main header info of the feed
        $feed = [];

        $feed['title']       = (isset($this->obj->title)) ? (string)$this->obj->title : null;
        $feed['url']         = (isset($this->obj->link->attributes()->href)) ? (string)$this->obj->link->attributes()->href : null;
        $feed['description'] = (isset($this->obj->subtitle)) ? (string)$this->obj->subtitle : null;
        $feed['date']        = (isset($this->obj->updated)) ? (string)$this->obj->updated : null;
        $feed['generator']   = (isset($this->obj->generator)) ? (string)$this->obj->generator : null;
        $feed['author']      = (isset($this->obj->author->name)) ? (string)$this->obj->author->name : null;
        $feed['items']       = [];

        $this->feed = new \ArrayObject($feed, \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Method to parse an Atom feed object
     *
     * @return void
     */
    public function parse()
    {
        $items = [];
        $count = count($this->obj->entry);
        $limit = (($this->limit > 0) && ($this->limit <= $count)) ? $this->limit : $count;

        for ($i = 0; $i < $limit; $i++) {
            $content = (isset($this->obj->entry[$i]->content) ?
                (string)$this->obj->entry[$i]->content :
                (string)$this->obj->entry[$i]->summary);

            $date = (isset($this->obj->entry[$i]->published)) ?
                (string)$this->obj->entry[$i]->published :
                (string)$this->obj->entry[$i]->updated;

            $title = trim((string)$this->obj->entry[$i]->title);
            if ($title == '') {
                $title = (string)$this->obj->entry[$i]->link->attributes()->href;
            }

            $items[] = new \ArrayObject([
                'title'     => html_entity_decode($title, ENT_QUOTES, 'UTF-8'),
                'content'   => html_entity_decode($content, ENT_QUOTES, 'UTF-8'),
                'link'      => (string)$this->obj->entry[$i]->link->attributes()->href,
                'published' => $date,
                'time'      => self::calculateTime($date)
            ], \ArrayObject::ARRAY_AS_PROPS);
        }

        $this->feed->items = $items;
    }

}
