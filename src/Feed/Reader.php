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
namespace Pop\Feed;

/**
 * Feed reader class
 *
 * @category   Pop
 * @package    Pop_Feed
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Reader
{

    /**
     * Feed adapter
     * @var Format\AbstractFormat
     */
    protected $adapter = null;

    /**
     * Constructor
     *
     * Instantiate the feed object.
     *
     * @param Format\AbstractFormat $adapter
     * @return Reader
     */
    public function __construct(Format\AbstractFormat $adapter)
    {
        $this->adapter = $adapter;
        $this->adapter->parse();
    }

    /**
     * Method to get the adapter object
     *
     * @return Format\AbstractFormat
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
     * Get method to return the value of feed[$name].
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        $value = null;
        $alias = null;
        $aliases = [
            'entry', 'entries', 'images', 'posts',
            'statuses', 'tweets', 'updates', 'videos'
        ];

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

}
