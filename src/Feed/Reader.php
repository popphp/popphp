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
namespace Pop\Feed;

/**
 * Feed reader class
 *
 * @category   Pop
 * @package    Pop_Feed
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Reader
{

    /**
     * Feed adapter
     * @var \Pop\Feed\Format\AbstractFormat
     */
    protected $adapter = null;

    /**
     * Feed item template
     * @var string
     */
    protected $template = null;

    /**
     * Feed date format
     * @var string
     */
    protected $dateFormat = 'M j Y g:ia';

    /**
     * Constructor
     *
     * Instantiate the feed object.
     *
     * @param \Pop\Feed\Format\AbstractFormat $adapter
     * @return \Pop\Feed\Reader
     */
    public function __construct(Format\AbstractFormat $adapter)
    {
        $this->adapter = $adapter;
        $this->adapter->parse();
    }

    /**
     * Static method to instantiate the data object and return itself
     * to facilitate chaining methods together.
     *
     * @param \Pop\Feed\Format\AbstractFormat $adapter
     * @return \Pop\Feed\Reader
     */
    public static function factory(Format\AbstractFormat $adapter)
    {
        return new self($adapter);
    }

    /**
     * Static method to create a Feed Reader object from a URL
     *
     * @param  string $url
     * @param  int    $limit
     * @param  string $prefix
     * @return \Pop\Feed\Reader
     */
    public static function getByUrl($url, $limit = 0, $prefix = 'Pop\Feed\Format\\')
    {
        $options = self::getSource($url);
        $class = (class_exists($prefix . $options['format'] . '\\' . $options['service'])) ?
            $prefix . $options['format'] . '\\' . $options['service'] :
            $prefix . $options['format'];

        return new self(new $class($options, $limit));
    }

    /**
     * Static method to create a Feed Reader object from an account name
     *
     * @param  string $service
     * @param  string $name
     * @param  int    $limit
     * @param  string $prefix
     * @throws Exception
     * @return \Pop\Feed\Reader
     */
    public static function getByAccountName($service, $name, $limit = 0, $prefix = 'Pop\Feed\Format\\')
    {
        $formats = array('Atom', 'Json', 'Php', 'Rss');
        $service = ucfirst(strtolower($service));
        $class = null;

        foreach ($formats as $format) {
            if ((class_exists($prefix . $format . '\\' . $service))) {
                $class = $prefix . $format . '\\' . $service;
            }
        }

        if (null === $class) {
            throw new Exception('Error: The class for that service feed could not be found.');
        }

        return new self(new $class(array('name' => $name), $limit));
    }

    /**
     * Static method to create a Feed Reader object from an account ID
     *
     * @param  string $service
     * @param  string $id
     * @param  int    $limit
     * @param  string $prefix
     * @throws Exception
     * @return \Pop\Feed\Reader
     */
    public static function getByAccountId($service, $id, $limit = 0, $prefix = 'Pop\Feed\Format\\')
    {
        $formats = array('Atom', 'Json', 'Php', 'Rss');
        $service = ucfirst(strtolower($service));
        $class = null;

        foreach ($formats as $format) {
            if ((class_exists($prefix . $format . '\\' . $service))) {
                $class = $prefix . $format . '\\' . $service;
            }
        }

        if (null === $class) {
            throw new Exception('Error: The class for that service feed could not be found.');
        }

        return new self(new $class(array('id' => $id), $limit));
    }

    /**
     * Method to set item template
     *
     * @param  string $tmpl
     * @return \Pop\Feed\Reader
     */
    public function setTemplate($tmpl)
    {
        if (file_exists($tmpl)) {
            $this->template = file_get_contents($tmpl);
        } else {
            $this->template = $tmpl;
        }
        return $this;
    }

    /**
     * Method to set date format
     *
     * @param  string $date
     * @return \Pop\Feed\Reader
     */
    public function setDateFormat($date)
    {
        $this->dateFormat = $date;
        return $this;
    }

    /**
     * Method to get the adapter object
     *
     * @return \Pop\Feed\Format\AbstractFormat
     */
    public function adapter()
    {
        return $this->adapter;
    }

    /**
     * Method to get the adapter object feed
     *
     * @return array
     */
    public function feed()
    {
        return $this->adapter->getFeed();
    }

    /**
     * Method to get feed template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Method to get feed date format
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * Method to determine if the feed type is RSS
     *
     * @return boolean
     */
    public function isRss()
    {
        return (strpos(get_class($this->adapter), 'Rss') !== false);
    }

    /**
     * Method to determine if the feed type is Atom
     *
     * @return boolean
     */
    public function isAtom()
    {
        return (strpos(get_class($this->adapter), 'Atom') !== false);
    }

    /**
     * Method to determine if the feed type is JSON
     *
     * @return boolean
     */
    public function isJson()
    {
        return (strpos(get_class($this->adapter), 'Json') !== false);
    }

    /**
     * Method to determine if the feed type is PHP
     *
     * @return boolean
     */
    public function isPhp()
    {
        return (strpos(get_class($this->adapter), 'Php') !== false);
    }

    /**
     * Method to determine if the feed type is YouTube
     *
     * @return boolean
     */
    public function isYoutube()
    {
        return (stripos($this->adapter->url(), 'youtube') !== false);
    }

    /**
     * Method to determine if the feed type is Twitter
     *
     * @return boolean
     */
    public function isVimeo()
    {
        return (stripos($this->adapter->url(), 'vimeo') !== false);
    }

    /**
     * Method to determine if the feed type is Facebook
     *
     * @return boolean
     */
    public function isFacebook()
    {
        return (stripos($this->adapter->url(), 'facebook') !== false);
    }

    /**
     * Method to determine if the feed type is Twitter
     *
     * @return boolean
     */
    public function isTwitter()
    {
        return (stripos($this->adapter->url(), 'twitter') !== false);
    }

    /**
     * Method to determine if the feed type is a playlist
     *
     * @return boolean
     */
    public function isPlaylist()
    {
        $search = ($this->isVimeo()) ? 'album' : 'playlist';
        return (strpos($this->adapter->url(), $search) !== false);
    }

    /**
     * Method to render the feed
     *
     * @param  boolean $ret
     * @throws Exception
     * @return mixed
     */
    public function render($ret = false)
    {
        if (null === $this->template) {
            throw new Exception('Error: The feed item template is not set.');
        }
        $feed = $this->adapter()->getFeed();

        if (!isset($feed['items'])) {
            throw new Exception('Error: The feed currently has no content.');
        }

        $output = null;

        // Loop through the items, formatting them into the template as needed, using the proper date format if appropriate.
        foreach ($feed['items'] as $item) {
            $tmpl = $this->template;
            foreach ($item as $key => $value) {
                if (strpos($tmpl, '[{' . $key . '}]') !== false) {
                    if ((null !== $this->dateFormat) && ((stripos($key, 'date') !== false) || ((stripos($key, 'published') !== false)))) {
                        $value =  date($this->dateFormat, strtotime($value));
                    }
                    $tmpl = str_replace('[{' . $key . '}]', $value, $tmpl);
                }
            }
            $output .= $tmpl;
        }

        // Return the final output.
        if ($ret) {
            return $output;
        } else {
            echo $output;
        }
    }

    /**
     * Get method to return the value of feed[$name].
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        $value = null;
        $alias = null;
        $aliases = array(
            'entry', 'entries', 'images', 'posts', 'statuses', 'tweets', 'updates', 'videos'
        );

        if (in_array($name, $aliases)) {
            $alias = 'items';
        }

        $feed = $this->adapter->getFeed();

        // If the called property exists in the $feed array
        if (isset($feed[$name])) {
            $value = $feed[$name];
        // Else, if the alias to the called property exists in the $feed array
        } else if ((null !== $alias) && isset($feed[$alias])) {
            $value = $feed[$alias];
        }

        return $value;
    }

    /**
     * Render feed reader object to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render(true);
    }


    /**
     * Static method to create a Feed Reader object from a URL
     *
     * @param  string $url
     * @return array
     */
    protected static function getSource($url)
    {
        $urlInfo = parse_url($url);
        $ary = explode('.', $urlInfo['host']);
        $i = count($ary) - 2;
        $domain = $ary[$i];
        $service = ucfirst(strtolower($domain));

        $options = array(
            'http' => array(
                'method'     => 'GET',
                'user_agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:16.0) Gecko/20100101 Firefox/16.0'
            )
        );

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $options['http']['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }

        $context = stream_context_create($options);
        $source = file_get_contents($url, false, $context);

        // If Twitter or Facebook
        if (strpos($url, 'twitter.com') !== false) {
            $format = 'Rss';
        // If XML
        } else if ((strpos($source, '<?xml') !== false) ||
            (strpos($source, '<rss') !== false) ||
            (strpos($source, '<feed') !== false)) {
            // If Atom
            if (strpos($source, '<entry') !== false) {
                $format = 'Atom';
            // If RSS
            } else {
                $format = 'Rss';
            }
        // If JSON
        } else if ((substr($source, 0, 1) == '{') || (substr($source, 0, 1) == '[')) {
            $format = 'Json';
        // If PHP
        } else {
            $format = 'Php';
        }

        return array(
            'url'     => $url,
            'context' => $context,
            'source'  => $source,
            'domain'  => $domain,
            'service' => $service,
            'format'  => $format
        );
    }
}
