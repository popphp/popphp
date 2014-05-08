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
namespace Pop\Feed\Format\Atom;

/**
 * Facebook Atom feed reader class
 *
 * @category   Pop
 * @package    Pop_Feed
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Facebook extends \Pop\Feed\Format\Atom
{

    /**
     * Feed URLs templates
     * @var array
     */
    protected $urls = array(
        'name' => 'http://graph.facebook.com/[{name}]',
        'id'   => 'http://www.facebook.com/feeds/page.php?id=[{id}]&format=atom10'
    );

    /**
     * Method to create a Facebook Atom feed object
     *
     * @param  mixed  $options
     * @param  int    $limit
     * @return \Pop\Feed\Format\Atom\Facebook
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
            } else if (isset($options['id'])) {
                $this->url = str_replace('[{id}]', $options['id'], $this->urls['id']);
            }
        }

        parent::__construct($options, $limit);
    }

    /**
     * Method to parse Facebook Atom feed object
     *
     * @return void
     */
    public function parse()
    {
        parent::parse();

        // If graph.facebook.com hasn't been parsed yet.
        if (!isset($this->feed['username'])) {
            $objItems = $this->obj->entry;
            $username = null;
            foreach ($objItems as $itm) {
                if (strpos($itm->link->attributes()->href, '/posts/') !== false) {
                    $username = substr($itm->link->attributes()->href, (strpos($itm->link->attributes()->href, 'http://www.facebook.com/') + 24));
                    $username = substr($username, 0, strpos($username, '/'));
                }
            }
            if (null !== $username) {
                $json = json_decode(file_get_contents('http://graph.facebook.com/' . $username), true);
                foreach ($json as $key => $value) {
                    $this->feed[$key] = $value;
                }
            } else if (isset($this->options['name'])) {
                $this->feed['username'] = $this->options['name'];
            }
        }

        if (strpos($this->feed['url'], $this->feed['username']) === false) {
            $this->feed['url'] .= $this->feed['username'];
        }
        if (null === $this->feed['author']) {
            $this->feed['author'] = (isset($this->obj->entry[0]->author->name)) ?
                (string)$this->obj->entry[0]->author->name : $this->feed['username'];
        }
        if (null === $this->feed['date']) {
            $this->feed['date'] = (string)$this->obj->updated;
        }

        $items = $this->feed['items'];
        foreach ($items as $key => $item) {
            $items[$key]['title'] = str_replace(array('<![CDATA[', ']]>'), array(null, null), $item['title']);
            $items[$key]['content'] = str_replace(array('<![CDATA[', ']]>'), array(null, null), $item['content']);
        }

        $this->feed['items'] = $items;
    }

}
