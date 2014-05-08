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
 * Youtube Atom feed reader class
 *
 * @category   Pop
 * @package    Pop_Feed
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Youtube extends \Pop\Feed\Format\Atom
{

    /**
     * Feed URLs templates
     * @var array
     */
    protected $urls = array(
        'name' => 'http://gdata.youtube.com/feeds/base/users/[{name}]/uploads?v=2&alt=atom',
        'id'   => 'http://gdata.youtube.com/feeds/api/playlists/[{id}]?v=2&alt=atom'
    );

    /**
     * Method to create a Youtube Atom feed object
     *
     * @param  mixed  $options
     * @param  int    $limit
     * @return \Pop\Feed\Format\Atom\Youtube
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
     * Method to parse Youtube Atom feed object
     *
     * @return void
     */
    public function parse()
    {
        parent::parse();

        $items = $this->feed['items'];
        foreach ($items as $key => $item) {
            if ($items[$key]['content'] == '') {
                $items[$key]['content'] = $item['title'];
            }

            $id = substr($item['link'], (strpos($item['link'], 'v=') + 2));
            if (strpos($id, '&') !== false) {
                $id = substr($id, 0, strpos($id, '&'));
            }
            $items[$key]['id'] = $id;
            $youtube = \Pop\Http\Response::parse('http://gdata.youtube.com/feeds/api/videos/' . $id . '?v=2&alt=json');
            if (!$youtube->isError()) {
                $info = json_decode($youtube->getBody(), true);
                $items[$key]['views'] = $info['entry']['yt$statistics']['viewCount'];
                $items[$key]['likes'] = $info['entry']['yt$rating']['numLikes'];
                $items[$key]['duration'] = $info['entry']['media$group']['yt$duration']['seconds'];
                $items[$key]['image_thumb']  = 'http://i.ytimg.com/vi/' . $id . '/default.jpg';
                $items[$key]['image_medium'] = 'http://i.ytimg.com/vi/' . $id . '/mqdefault.jpg';
                $items[$key]['image_large']  = 'http://i.ytimg.com/vi/' . $id . '/hqdefault.jpg';
                foreach ($info as $k => $v) {
                    if ($v != '') {
                        $items[$key][$k] = $v;
                    }
                }
            }
        }

        $this->feed['items'] = $items;
    }

}
