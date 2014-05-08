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
 * Youtube JSON feed reader class
 *
 * @category   Pop
 * @package    Pop_Feed
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Youtube extends \Pop\Feed\Format\Json
{

    /**
     * Feed URLs templates
     * @var array
     */
    protected $urls = array(
        'name' => 'http://gdata.youtube.com/feeds/base/users/[{name}]/uploads?v=2&alt=json',
        'id'   => 'http://gdata.youtube.com/feeds/api/playlists/[{id}]?v=2&alt=json'
    );

    /**
     * Method to create a Youtube JSON feed object
     *
     * @param  mixed  $options
     * @param  int    $limit
     * @return \Pop\Feed\Format\Json\Youtube
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
     * Method to parse Youtube JSON feed object
     *
     * @return void
     */
    public function parse()
    {
        parent::parse();

        $this->feed['title'] = $this->feed['title']['$t'];
        $this->feed['url'] = $this->feed['url'][0]['href'];
        $this->feed['description'] = $this->feed['title'];
        $this->feed['date'] = $this->feed['date']['$t'];
        $this->feed['generator'] = $this->feed['generator']['$t'];
        $this->feed['author'] = $this->feed['author'][0]['name']['$t'];

        $items = $this->feed['items'];
        foreach ($items as $key => $item) {
            if (isset($this->obj['feed']['entry'][$key]['content']['$t'])) {
                $content = html_entity_decode($this->obj['feed']['entry'][$key]['content']['$t'], ENT_QUOTES, 'UTF-8');
            } else {
                $content = $this->obj['feed']['entry'][$key]['title']['$t'];
            }
            $items[$key]['title'] = $this->obj['feed']['entry'][$key]['title']['$t'];
            $items[$key]['content'] = $content;
            $items[$key]['link'] = $items[$key]['link'][0]['href'];
            $items[$key]['published'] = $this->obj['feed']['entry'][$key]['published']['$t'];
            $items[$key]['time'] = self::calculateTime($this->obj['feed']['entry'][$key]['published']['$t']);

            $id = substr($items[$key]['link'], (strpos($items[$key]['link'], 'v=') + 2));
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
