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
namespace Pop\Feed\Format\Json;

/**
 * Facebook JSON feed reader class
 *
 * @category   Pop
 * @package    Pop_Feed
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Facebook extends \Pop\Feed\Format\Json
{

    /**
     * Feed URLs templates
     * @var array
     */
    protected $urls = array(
        'name' => 'http://graph.facebook.com/[{name}]',
        'id'   => 'http://www.facebook.com/feeds/page.php?id=[{id}]&format=json'
    );


    /**
     * Feed ID
     * @var string
     */
    protected $id = null;

    /**
     * Method to create a Facebook JSON feed object
     *
     * @param  mixed  $options
     * @param  int    $limit
     * @return \Pop\Feed\Format\Json\Facebook
     */
    public function __construct($options, $limit = 0)
    {
        // Attempt to get the correct URL to parse
        if (is_array($options)) {
            if (isset($options['name'])) {
                $jsonUrl = str_replace('[{name}]', $options['name'], $this->urls['name']);
                $json = json_decode(file_get_contents($jsonUrl), true);

                $this->url = str_replace('[{id}]', $json['id'], $this->urls['id']);
                foreach ($json as $key => $value) {
                    $this->feed[$key] = $value;
                }
                $this->id = $json['id'];
            } else if (isset($options['id'])) {
                $this->url = str_replace('[{id}]', $options['id'], $this->urls['id']);
                $this->id = $options['id'];
            } else if (isset($options['source'])) {
                $json = json_decode($options['source'], true);
                $this->url = str_replace('[{id}]', $json['id'], $this->urls['id']);
                foreach ($json as $key => $value) {
                    $this->feed[$key] = $value;
                }
                $this->id = $json['id'];
            }
        }

        parent::__construct($options, $limit);
    }

    /**
     * Method to parse Facebook JSON feed object
     *
     * @return void
     */
    public function parse()
    {
        parent::parse();

        $rss = new \Pop\Feed\Format\Rss\Facebook(array('url' => 'http://www.facebook.com/feeds/page.php?id=' . $this->id . '&format=rss20'), $this->limit);
        $this->feed->username = substr($this->feed->url, (strpos($this->feed->url, '.com/') + 5));
        $this->feed->title = (string)$rss->obj()->channel->title;
        $this->feed->date = (string)$rss->obj()->channel->lastBuildDate;
        $this->feed->generator = (string)$rss->obj()->channel->generator;
        $this->feed->author = substr($this->feed->title, 0, strpos($this->feed->title, "'s"));

        $i = 0;
        foreach ($rss->obj()->channel->item as $item) {
            if (isset($this->feed->items[$i])) {
                $title = trim((string)$item->title);
                if ($title == '') {
                    $title = (string)$item->link;
                }
                $this->feed->items[$i]->title     = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
                $this->feed->items[$i]->content   = html_entity_decode((string)$item->description, ENT_QUOTES, 'UTF-8');
                $this->feed->items[$i]->link      = (string)$item->link;
                $this->feed->items[$i]->published = (string)$item->pubDate;
                $this->feed->items[$i]->time      = self::calculateTime((string)$item->pubDate);
            }
            $i++;
        }

    }

}
