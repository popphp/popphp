<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Paginator
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Paginator;

/**
 * Paginator class
 *
 * @category   Pop
 * @package    Pop_Paginator
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Paginator
{

    /**
     * Total number of items
     * @var int
     */
    protected $total = 0;

    /**
     * Number of items per page
     * @var int
     */
    protected $perPage = 10;

    /**
     * Range of pages per page
     * @var int
     */
    protected $range = 10;

    /**
     * Query key
     * @var string
     */
    protected $queryKey = 'page';

    /**
     * Page bookends
     * @var array
     */
    protected $bookends = [
        'start'    => '&laquo;',
        'previous' => '&lsaquo;',
        'next'     => '&rsaquo;',
        'end'      => '&raquo;'
    ];

    /**
     * Flag to use an input form field
     * @var boolean
     */
    protected $useInput = false;

    /**
     * Input separator
     * @var string
     */
    protected $inputSeparator = 'of';

    /**
     * Link separator
     * @var string
     */
    protected $separator = null;

    /**
     * Class 'on' name for page link tags
     * @var string
     */
    protected $classOn = null;

    /**
     * Class 'off' name for page link tags
     * @var string
     */
    protected $classOff = null;

    /**
     * Current page property
     * @var int
     */
    protected $currentPage = 1;

    /**
     * Number of pages property
     * @var int
     */
    protected $numberOfPages = null;

    /**
     * Current page start index property
     * @var int
     */
    protected $start = null;

    /**
     * Current page end index property
     * @var int
     */
    protected $end = null;

    /**
     * Page links property
     * @var array
     */
    protected $links = [];

    /**
     * Constructor
     *
     * Instantiate the paginator object
     *
     * @param  int $total
     * @param  int $perPage
     * @param  int $range
     * @return Paginator
     */
    public function __construct($total, $perPage = 10, $range = 10)
    {
        $this->setTotal($total);
        $this->setPerPage($perPage);
        $this->setRange($range);
    }

    /**
     * Set the content items total
     *
     * @param  int $total
     * @return Paginator
     */
    public function setTotal($total)
    {
        $this->total = (int)$total;
        return $this;
    }

    /**
     * Set the per page
     *
     * @param  int $perPage
     * @return Paginator
     */
    public function setPerPage($perPage = 10)
    {
        $this->perPage = (int)$perPage;
        return $this;
    }

    /**
     * Set the page range
     *
     * @param  int $range
     * @return Paginator
     */
    public function setRange($range = 10)
    {
        $this->range = (int)$range;
        return $this;
    }

    /**
     * Set the query key
     *
     * @param  string $key
     * @return Paginator
     */
    public function setQueryKey($key)
    {
        $this->queryKey = $key;
        return $this;
    }

    /**
     * Set the bookend separator
     *
     * @param  string $sep
     * @return Paginator
     */
    public function setSeparator($sep)
    {
        $this->separator = $sep;
        return $this;
    }

    /**
     * Set the class 'on' name
     *
     * @param  string $class
     * @return Paginator
     */
    public function setClassOn($class)
    {
        $this->classOn = $class;
        return $this;
    }

    /**
     * Set the class 'off' name.
     *
     * @param  string $class
     * @return Paginator
     */
    public function setClassOff($class)
    {
        $this->classOff = $class;
        return $this;
    }

    /**
     * Set the bookends
     *
     * @param  array $bookends
     * @return Paginator
     */
    public function setBookends(array $bookends)
    {
        if (array_key_exists('start', $bookends)) {
            $this->bookends['start'] = $bookends['start'];
        }
        if (array_key_exists('previous', $bookends)) {
            $this->bookends['previous'] = $bookends['previous'];
        }
        if (array_key_exists('next', $bookends)) {
            $this->bookends['next'] = $bookends['next'];
        }
        if (array_key_exists('end', $bookends)) {
            $this->bookends['end'] = $bookends['end'];
        }

        return $this;
    }

    /**
     * Set whether to use an input form field
     *
     * @param  boolean $flag
     * @param  string  $separator
     * @return Paginator
     */
    public function useInput($flag, $separator = 'of')
    {
        $this->useInput       = (bool)$flag;
        $this->inputSeparator = $separator;
        if ($this->useInput) {
            $this->range = 1;
        }
        return $this;
    }

    /**
     * Get the content items total
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Get the per page
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * Get the page range
     *
     * @return int
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * Get the query key
     *
     * @return int
     */
    public function getQueryKey()
    {
        return $this->queryKey;
    }

    /**
     * Get the bookend separator
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * Get the class 'on' name
     *
     * @return string
     */
    public function getClassOn()
    {
        return $this->classOn;
    }

    /**
     * Get the class 'off' name.
     *
     * @return string
     */
    public function getClassOff()
    {
        return $this->classOff;
    }

    /**
     * Get a bookend
     *
     * @param  string $key
     * @return string
     */
    public function getBookend($key)
    {
        return (isset($this->bookends[$key])) ? $this->bookends[$key] : null;
    }

    /**
     * Get the bookends
     *
     * @return array
     */
    public function getBookends()
    {
        return $this->bookends;
    }

    /**
     * Get the current page
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Get the number of pages
     *
     * @return int
     */
    public function getNumberOfPages()
    {
        return $this->numberOfPages;
    }

    /**
     * Get the page links
     *
     * @param  int $page
     * @return array
     */
    public function getLinks($page = 1)
    {
        $this->calculateRange($page);
        $this->createLinks($page);

        return $this->links;
    }

    /**
     * Calculate the page range
     *
     * @param  int $page
     * @return array
     */
    protected function calculateRange($page = 1)
    {
        $this->currentPage = $page;

        // Calculate the number of pages based on the remainder.
        $remainder = $this->total % $this->perPage;
        $this->numberOfPages = ($remainder != 0) ? (floor(($this->total / $this->perPage)) + 1) :
            floor(($this->total / $this->perPage));

        // Calculate the start index.
        $this->start = ($page * $this->perPage) - $this->perPage;

        // Calculate the end index.
        if (($page == $this->numberOfPages) && ($remainder == 0)) {
            $this->end = $this->start + $this->perPage;
        } else if ($page == $this->numberOfPages) {
            $this->end = (($page * $this->perPage) - ($this->perPage - $remainder));
        } else {
            $this->end = ($page * $this->perPage);
        }

        // Calculate if out of range.
        if ($this->start >= $this->total) {
            $this->start = 0;
            $this->end   = $this->perPage;
        }

        // Check and calculate for any page ranges.
        if (((null === $this->range) || ($this->range > $this->numberOfPages)) && (null === $this->total)) {
            $range = [
                'start' => 1,
                'end'   => $this->numberOfPages,
                'prev'  => false,
                'next'  => false
            ];
        } else {
            // If page is within the first range block.
            if (($page <= $this->range) && ($this->numberOfPages <= $this->range)) {
                $range = [
                    'start' => 1,
                    'end'   => $this->numberOfPages,
                    'prev'  => false,
                    'next'  => false
                ];
                // If page is within the first range block, with a next range.
            } else if (($page <= $this->range) && ($this->numberOfPages > $this->range)) {
                $range = [
                    'start' => 1,
                    'end'   => $this->range,
                    'prev'  => false,
                    'next'  => true
                ];
                // Else, if page is within the last range block, with an uneven remainder.
            } else if ($page > ($this->range * floor($this->numberOfPages / $this->range))) {
                $range = [
                    'start' => ($this->range * floor($this->numberOfPages / $this->range)) + 1,
                    'end'   => $this->numberOfPages,
                    'prev'  => true,
                    'next'  => false
                ];
                // Else, if page is within the last range block, with no remainder.
            } else if ((($this->numberOfPages % $this->range) == 0) && ($page > ($this->range * (($this->numberOfPages / $this->range) - 1)))) {
                $range = [
                    'start' => ($this->range * (($this->numberOfPages / $this->range) - 1)) + 1,
                    'end'   => $this->numberOfPages,
                    'prev'  => true,
                    'next'  => false
                ];
                // Else, if page is within a middle range block.
            } else {
                $posInRange = (($page % $this->range) == 0) ? ($this->range - 1) : (($page % $this->range) - 1);
                $linkStart = $page - $posInRange;
                $range = [
                    'start' => $linkStart,
                    'end'   => $linkStart + ($this->range - 1),
                    'prev'  => true,
                    'next'  => true
                ];
            }
        }

        return $range;
    }

    /**
     * Create links
     *
     * @param  int  $page
     * @return void
     */
    protected function createLinks($page = 1)
    {
        $this->currentPage = $page;

        // Generate the page links.
        $this->links = [];

        // Preserve any passed GET parameters.
        $query = null;
        $uri   = null;

        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = (!empty($_SERVER['QUERY_STRING'])) ?
                str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']) :
                $_SERVER['REQUEST_URI'];

            if (count($_GET) > 0) {
                foreach ($_GET as $key => $value) {
                    if ($key != $this->queryKey) {
                        $query .= '&' . $key . '=' . $value;
                    }
                }
            }
        }

        // Calculate page range links.
        $pageRange = $this->calculateRange($page);

        for ($i = $pageRange['start']; $i <= $pageRange['end']; $i++) {
            $newLink  = null;
            $prevLink = null;
            $nextLink = null;
            $classOff = (null !== $this->classOff) ? " class=\"{$this->classOff}\"" : null;
            $classOn  = (null !== $this->classOn) ? " class=\"{$this->classOn}\"" : null;

            if ($this->useInput) {
                $newLink = '<form action="' . $uri . ((null !== $query) ? '?' . substr($query, 1)  : null) .
                    '" method="get"><div><input type="text" name="' . $this->queryKey . '" size="2" value="' .
                    $this->currentPage . '" /> ' . $this->inputSeparator . ' ' . $this->numberOfPages . '</div></form>';
            } else {
                $newLink = ($i == $page) ? "<span{$classOff}>{$i}</span>" : "<a{$classOn} href=\"" . $uri . "?" .
                    $this->queryKey . "={$i}{$query}\">{$i}</a>";
            }

            if (($i == $pageRange['start']) && ($pageRange['prev'])) {
                if (null !== $this->bookends['start']) {
                    $startLink = "<a{$classOn} href=\"" . $uri . "?" . $this->queryKey . "=1" . "{$query}\">" .
                        $this->bookends['start'] . "</a>";
                    $this->links[] = $startLink;
                }
                if (null !== $this->bookends['previous']) {
                    $prevLink  = "<a{$classOn} href=\"" . $uri . "?" . $this->queryKey . "=" . ($i - 1) . "{$query}\">" .
                        $this->bookends['previous'] . "</a>";
                    $this->links[] = $prevLink;
                }
            }

            $this->links[] = $newLink;

            if (($i == $pageRange['end']) && ($pageRange['next'])) {
                if (null !== $this->bookends['next']) {
                    $nextLink = "<a{$classOn} href=\"" . $uri . "?" . $this->queryKey . "=" . ($i + 1) . "{$query}\">" .
                        $this->bookends['next'] . "</a>";
                    $this->links[] = $nextLink;
                }
                if (null !== $this->bookends['end']) {
                    $endLink  = "<a{$classOn} href=\"" . $uri . "?" . $this->queryKey . "=" . $this->numberOfPages .
                        "{$query}\">" . $this->bookends['end'] . "</a>";
                    $this->links[] = $endLink;
                }
            }
        }
    }

    /**
     * Output the rendered page links
     *
     * @return string
     */
    public function __toString()
    {
        $page = (isset($_GET[$this->queryKey]) && ((int)$_GET[$this->queryKey] > 0)) ? (int)$_GET[$this->queryKey] : 1;
        return implode($this->separator, $this->getLinks($page));
    }

}
