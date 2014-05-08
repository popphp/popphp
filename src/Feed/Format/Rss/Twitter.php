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
 * Twitter RSS feed reader class
 *
 * @category   Pop
 * @package    Pop_Feed
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Twitter extends \Pop\Feed\Format\Rss
{

    /**
     * Feed URLs templates
     * @var array
     */
    protected $urls = array(
        'name' => 'https://twitter.com/[{name}]'
    );

    /**
     * Method to create a Twitter RSS feed object
     *
     * @param  mixed  $options
     * @param  int    $limit
     * @return \Pop\Feed\Format\Rss\Twitter
     */
    public function __construct($options, $limit = 0)
    {
        // Attempt to get the correct URL to parse
        if (is_array($options)) {
            if (isset($options['name'])) {
                $this->url = str_replace('[{name}]', $options['name'], $this->urls['name']);
            } else if (isset($options['url'])) {
                $this->url = $options['url'];
            }
        }

        // Get the main header info of the feed
        $feed = array();

        $feed['title']       = null;
        $feed['url']         = null;
        $feed['description'] = null;
        $feed['date']        = null;
        $feed['generator']   = null;
        $feed['author']      = null;
        $feed['items']       = array();

        $this->feed = new \ArrayObject($feed, \ArrayObject::ARRAY_AS_PROPS);
        $this->limit = $limit;
    }

    /**
     * Method to parse a Twitter feed object
     *
     * @throws \Pop\Feed\Exception
     * @return void
     */
    public function parse()
    {
        $twitter = array(
            'user'      => null,
            'username'  => substr($this->url, (strrpos($this->url, '/') + 1)),
            'profile'   => $this->url,
            'tweets'    => null,
            'followers' => null,
            'following' => null,
            'images'    => array(
                'small'  => null,
                'medium' => null,
                'large'  => null
            ),
            'statuses' => array()
        );

        if ((null === $this->url) || !($this->source = file_get_contents($this->url, false))) {
            throw new \Pop\Feed\Exception('That feed URL cannot be read at this time. Please try again later.');
        }

        $user = substr($this->source, (strpos($this->source, '<span class="profile-field">') + 28));
        $user = substr($user, 0, strpos($user, '<'));
        $twitter['user'] = $user;

        $tweetsRegex = "/\<strong\>(.*)\<\/strong\>\sTweets/";
        $followersRegex = "/\<strong\>(.*)\<\/strong\>\sFollowers/";
        $followingRegex = "/\<strong\>(.*)\<\/strong\>\sFollowing/";
        $imagesRegex = "/\"http(.*)\.(jpg|jpeg)\"/";
        $statusesRegex = "/\<div\sclass\=\"content\"\>/m";

        $matches = array();
        preg_match($tweetsRegex, $this->source, $matches, PREG_OFFSET_CAPTURE);
        $twitter['tweets'] = (isset($matches[1]) && isset($matches[1][0])) ? $matches[1][0] : '0';

        $matches = array();
        preg_match($followersRegex, $this->source, $matches, PREG_OFFSET_CAPTURE);
        $twitter['followers'] = (isset($matches[1]) && isset($matches[1][0])) ? $matches[1][0] : '0';

        $matches = array();
        preg_match($followingRegex, $this->source, $matches, PREG_OFFSET_CAPTURE);
        $twitter['following'] = (isset($matches[1]) && isset($matches[1][0])) ? $matches[1][0] : '0';

        $matches = array();
        preg_match($imagesRegex, $this->source, $matches, PREG_OFFSET_CAPTURE);
        if (isset($matches[0]) && isset($matches[0][0])) {
            $img = substr($matches[0][0], 1);
            $img = substr($img, 0, strpos($img, '"'));
            $twitter['images']['small']  = str_replace('.jp', '_normal.jp', $img);
            $twitter['images']['medium'] = str_replace('.jp', '_bigger.jp', $img);
            $twitter['images']['large']  = $img;
        }

        $matches = array();
        preg_match_all($statusesRegex, $this->source, $matches, PREG_OFFSET_CAPTURE);

        if (isset($matches[0]) && isset($matches[0][0])) {
            $i = 0;
            foreach ($matches[0] as $match) {
                $status = array(
                    'user'      => null,
                    'username'  => null,
                    'profile'   => null,
                    'image'     => null,
                    'link'      => null,
                    'time'      => null,
                    'published' => null,
                    'retweet'   => false,
                    'html'      => null,
                    'title'     => null
                );
                $html = substr($this->source, $match[1]);

                $context = substr($html, strpos($html, '<div class="context">'));
                $context = substr($context, 0, strpos($context, '</div>'));
                if (strpos($context, 'Retweeted') !== false) {
                    $status['retweet'] = true;
                }

                $html = substr($html, 0, strpos($html, '<div class="context">'));

                $img = substr($html, (strpos($html, 'src="') + 5));
                $img = substr($img, 0, strpos($img, '"'));
                $status['image'] = $img;

                $user = substr($html, (strpos($html, '<strong') + 7));
                $user = substr($user, (strpos($user, '>') + 1));
                $user = substr($user, 0, strpos($user, '<'));
                $status['user'] = $user;

                $username = substr($html, (strpos($html, '<s>@</s>') + 8));
                $username = substr($username, (strpos($username, '>') + 1));
                $username = substr($username, 0, strpos($username, '<'));
                $status['username'] = $username;
                $status['profile'] = 'https://twitter.com/' . $username;

                $link = substr($html, (strpos($html, '<small class="time">') + 20));
                $link = substr($link, (strpos($link, 'href="') + 6));
                $link = substr($link, 0, strpos($link, '"'));
                $status['link'] = 'https://twitter.com' . $link;

                $time = substr($html, (strpos($html, 'data-time="') + 11));
                $time = substr($time, 0, strpos($time, '"'));
                $status['published'] = date('M j Y g:i:s', $time);
                $status['time'] = self::calculateTime($status['published']);

                $title = substr($html, (strpos($html, 'tweet-text">') + 12));
                $title = substr($title, 0, strpos($title, '</p>'));
                $status['html'] = str_replace(array('href="/', '<s>', '</s>'), array('href="https://twitter.com/', '<b>', '</b>'), $title);
                $status['title'] = strip_tags($status['html']);

                if ((($this->limit > 0) && ($i < $this->limit)) || ($this->limit == 0)) {
                    $twitter['statuses'][] = $status;
                }
                $i++;
            }
        }

        $this->feed['title']       = $twitter['user'] . "'s Twitter Feed";
        $this->feed['url']         = $this->url;
        $this->feed['description'] = $twitter['user'] . "'s Twitter Feed";
        $this->feed['username']    = $twitter['username'];
        $this->feed['date']        = date('D, d M Y H:i:s O');
        $this->feed['generator']   = 'Twitter';
        $this->feed['author']      = $twitter['user'];
        $this->feed['profile']     = $twitter['profile'];
        $this->feed['tweets']      = $twitter['tweets'];
        $this->feed['followers']   = $twitter['followers'];
        $this->feed['following']   = $twitter['following'];
        $this->feed['images']      = $twitter['images'];
        $this->feed['items']       = $twitter['statuses'];
    }

}
