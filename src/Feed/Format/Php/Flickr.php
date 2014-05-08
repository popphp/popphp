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
namespace Pop\Feed\Format\Php;

/**
 * Flickr Php feed reader class
 *
 * @category   Pop
 * @package    Pop_Feed
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Flickr extends \Pop\Feed\Format\Php
{

    /**
     * Feed URLs templates
     * @var array
     */
    protected $urls = array(
        'id'   => 'http://api.flickr.com/services/feeds/photos_public.gne?id=[{id}]&format=php_serial'
    );

    /**
     * Method to create a Flickr Php feed object
     *
     * @param  mixed  $options
     * @param  int    $limit
     * @return \Pop\Feed\Format\Php\Flickr
     */
    public function __construct($options, $limit = 0)
    {
        // Attempt to get the correct URL to parse
        if (is_array($options)) {
            if (isset($options['id'])) {
                $this->url = str_replace('[{id}]', $options['id'], $this->urls['id']);
            }
        }

        parent::__construct($options, $limit);
    }

    /**
     * Method to parse Flickr Php feed object
     *
     * @return void
     */
    public function parse()
    {
        parent::parse();

        if (!isset($this->feed['author']) || (null === $this->feed['author'])) {
            $this->feed['author'] = str_replace('Uploads from ', '', $this->feed['title']);
        }

        if (!isset($this->feed['date']) || (null === $this->feed['date'])) {
            $this->feed['date'] = date('D, d M Y H:i:s O', $this->feed['pub_date']);
        }

        if (!isset($this->feed['generator']) || (null === $this->feed['generator'])) {
            $this->feed['generator'] = 'Flickr';
        }

        $items = $this->feed['items'];
        foreach ($items as $key => $item) {
            $items[$key]['image_thumb']  = $item['t_url'];
            $items[$key]['image_medium'] = $item['m_url'];
            $items[$key]['image_large']  = $item['l_url'];
            $items[$key]['image_orig']  = $item['photo_url'];

        }

        $this->feed['items'] = $items;

    }

}
