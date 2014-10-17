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
     * Constant for using the single arrows bookends
     * @var string
     */
    const SINGLE_ARROW = 'SINGLE_ARROW';

    /**
     * Constant for using the double arrows bookends
     * @var string
     */
    const DOUBLE_ARROW = 'DOUBLE_ARROW';

    /**
     * Constant for using the ellipsis bookends
     * @var string
     */
    const ELLIPSIS = 'ELLIPSIS';

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
     * Page bookends
     * @var array
     */
    protected $bookends = [
        'SINGLE_ARROW' => ['&lt;', '&gt;'],
        'DOUBLE_ARROW' => ['&lt;&lt;', '&gt;&gt;'],
        'ELLIPSIS'     => ['...', '...']
    ];

    /**
     * Page bookend key
     * @var string
     */
    protected $bookend = 'SINGLE_ARROW';

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
     * Instantiate the paginator object.
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
     * Method to set the content items total
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
     * Method to set the page range.
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
     * Method to set the page range.
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
     * Method to set the bookend separator.
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
     * Method to set the class 'on' name.
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
     * Method to set the class 'off' name.
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
     * Method to set the bookend key.
     *
     * @param  int $key
     * @return Paginator
     */
    public function setBookend($key)
    {
        switch ($key) {
            case self::ELLIPSIS:
                $this->bookend = self::ELLIPSIS;
                break;
            case self::DOUBLE_ARROW:
                $this->bookend = self::DOUBLE_ARROW;
                break;
            default:
                $this->bookend = self::SINGLE_ARROW;
        }

        return $this;
    }

    /**
     * Method to get the content items total
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Method to get the page range.
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * Method to get the page range.
     *
     * @return int
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * Method to get the bookend separator.
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * Method to get the class 'on' name.
     *
     * @return string
     */
    public function getClassOn()
    {
        return $this->classOn;
    }

    /**
     * Method to get the class 'off' name.
     *
     * @return string
     */
    public function getClassOff()
    {
        return $this->classOff;
    }

    /**
     * Method to get the bookend key
     *
     * @return string
     */
    public function getBookend()
    {
        return $this->bookend;
    }

    /**
     * Method to get the number of pages.
     *
     * @return int
     */
    public function getNumberOfPages()
    {
        return $this->numberOfPages;
    }

    /**
     * Method to get the page links.
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
     * Method to calculate the page range
     *
     * @param  int $page
     * @return array
     */
    protected function calculateRange($page = 1)
    {
        // Calculate the number of pages based on the remainder.
        $remainder = $this->total % $this->perPage;
        $this->numberOfPages = ($remainder != 0) ? (floor(($this->total / $this->perPage)) + 1) : floor(($this->total / $this->perPage));

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
     * Method to create links.
     *
     * @param  int  $page
     * @return void
     */
    protected function createLinks($page = 1)
    {
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
                    if ($key != 'page') {
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

            $newLink = ($i == $page) ? "<span{$classOff}>{$i}</span>" : "<a{$classOn} href=\"" . $uri . "?page={$i}{$query}\">{$i}</a>";

            if (($i == $pageRange['start']) && ($pageRange['prev'])) {
                $prevLink = "<a{$classOn} href=\"" . $uri . "?page=" . ($i - 1) . "{$query}\">" . $this->bookends[$this->bookend][0] . "</a>";
                $this->links[] = $prevLink;
            }
            $this->links[] = $newLink;
            if (($i == $pageRange['end']) && ($pageRange['next'])) {
                $nextLink = "<a{$classOn} href=\"" . $uri . "?page=" . ($i + 1) . "{$query}\">" . $this->bookends[$this->bookend][1] . "</a>";
                $this->links[] = $nextLink;
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
        $page = (isset($_GET['page']) && ((int)$_GET['page'] > 0)) ? (int)$_GET['page'] : 1;
        return implode($this->separator, $this->getLinks($page));
    }

}
