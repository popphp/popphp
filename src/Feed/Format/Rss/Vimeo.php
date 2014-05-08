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
namespace Pop\Feed\Format\Rss;

/**
 * Vimeo RSS feed reader class
 *
 * @category   Pop
 * @package    Pop_Feed
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Vimeo extends \Pop\Feed\Format\Rss
{

    /**
     * Feed URLs templates
     * @var array
     */
    protected $urls = array(
        'name' => 'http://vimeo.com/channels/[{name}]/videos/rss',
        'id'   => 'http://vimeo.com/album/[{id}]/rss'
    );

    /**
     * Method to create a Vimeo RSS feed object
     *
     * @param  mixed  $options
     * @param  int    $limit
     * @return \Pop\Feed\Format\Rss\Vimeo
     */
    public function __construct($options, $limit = 0)
    {
        // Attempt to get the correct URL to parse
        if (is_array($options)) {
            if (isset($options['name'])) {
                $this->url = str_replace('[{name}]', $options['name'], $this->urls['name']);
            } else if (isset($options['id'])) {
                $this->url = str_replace('[{id}]', $options['id'], $this->urls['id']);
            }
        }

        parent::__construct($options, $limit);
    }

    /**
     * Method to parse a Vimeo RSS feed object
     *
     * @return void
     */
    public function parse()
    {
        parent::parse();

        if (null === $this->feed['author']) {
            $this->feed['author'] = str_replace('Vimeo / ', null, $this->feed['title']);
        }

        $items = $this->feed['items'];
        foreach ($items as $key => $item) {
            $id = substr($item['link'], (strrpos($item['link'], '/') + 1));
            $items[$key]['id'] = $id;
            $vimeo = \Pop\Http\Response::parse('http://vimeo.com/api/v2/video/' . $id . '.php');
            if (!$vimeo->isError()) {
                $info = unserialize($vimeo->getBody());
                if (isset($info[0]) && is_array($info[0])) {
                    $items[$key]['views'] = (isset($info[0]['stats_number_of_plays']) ? $info[0]['stats_number_of_plays'] : null);
                    $items[$key]['likes'] = (isset($info[0]['stats_number_of_likes']) ? $info[0]['stats_number_of_likes'] : null);
                    $items[$key]['duration'] = $info[0]['duration'];
                    $items[$key]['image_thumb']  = $info[0]['thumbnail_small'];
                    $items[$key]['image_medium'] = $info[0]['thumbnail_medium'];
                    $items[$key]['image_large']  = $info[0]['thumbnail_large'];
                    foreach ($info[0] as $k => $v) {
                        if ($v != '') {
                            $items[$key][$k] = $v;
                        }
                    }
                }
            }
        }

        $this->feed['items'] = $items;

    }

}
