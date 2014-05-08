<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Paginator
 * @author     Nick Sagona, III <info@popphp.org>
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
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Paginator
{

    /**
     * Constant for using the single arrows bookends
     * @var int
     */
    const SINGLE_ARROWS = 0;

    /**
     * Constant for using the double arrows bookends
     * @var int
     */
    const DOUBLE_ARROWS = 1;

    /**
     * Constant for using the prev|next bookends
     * @var int
     */
    const PREV_NEXT = 2;

    /**
     * Constant for using the ellipsis bookends
     * @var int
     */
    const ELLIPSIS = 3;

    /**
     * Header template
     * @var string
     */
    protected $header = null;

    /**
     * Row template
     * @var string
     */
    protected $rowTemplate = null;

    /**
     * Footer template
     * @var string
     */
    protected $footer = null;

    /**
     * Page links property
     * @var array
     */
    protected $links = array();

    /**
     * Content items
     * @var array
     */
    protected $items = array();

    /**
     * Items per page property
     * @var int
     */
    protected $perPage = 10;

    /**
     * Page range property
     * @var int
     */
    protected $range = 10;

    /**
     * Total item count property
     * @var int
     */
    protected $total = null;

    /**
     * Page bookends
     * @var array
     */
    protected $bookends = array(
        array('prev' => '&lt;',     'next' => '&gt;'),
        array('prev' => '&lt;&lt;', 'next' => '&gt;&gt;'),
        array('prev' => 'Prev',     'next' => 'Next'),
        array('prev' => '...',      'next' => '...')
    );

    /**
     * Page bookend key
     * @var int
     */
    protected $bookendKey = 0;

    /**
     * Bookend separator
     * @var string
     */
    protected $separator = ' | ';

    /**
     * Date format for handle date strings
     * @var string
     */
    protected $dateFormat = null;

    /**
     * Class 'on' name for page link <a> tags
     * @var string
     */
    protected $classOn = null;

    /**
     * Class 'off' name for page link <a> tags
     * @var string
     */
    protected $classOff = null;

    /**
     * Number of pages property
     * @var int
     */
    protected $numPages = null;

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
     * Remainder property
     * @var int
     */
    protected $rem = 0;

    /**
     * Page ouput
     * @var string
     */
    protected $output = null;

    /**
     * Constructor
     *
     * Instantiate the paginator object.
     *
     * @param  array $items
     * @param  int $perPage
     * @param  int $range
     * @param  int $total
     * @return \Pop\Paginator\Paginator
     */
    public function __construct(array $items, $perPage = 10, $range = 10, $total = null)
    {
        $this->items = $items;
        $this->perPage = (int)$perPage;
        $this->range = ($range > 0) ? (int)$range : 10;
        $this->total = (null !== $total) ? (int)$total : null;
    }

    /**
     * Static method to instantiate the paginator object and return itself
     * to facilitate chaining methods together.
     *
     * @param  array $items
     * @param  int $perPage
     * @param  int $range
     * @param  int $total
     * @return \Pop\Paginator\Paginator
     */
    public static function factory(array $items, $perPage = 10, $range = 10, $total = null)
    {
        return new self($items, $perPage, $range, $total);
    }

    /**
     * Method to set the content items.
     *
     * @param  array $items
     * @return \Pop\Paginator\Paginator
     */
    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * Method to set the page range.
     *
     * @param  int $perPage
     * @return \Pop\Paginator\Paginator
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
     * @return \Pop\Paginator\Paginator
     */
    public function setRange($range = 10)
    {
        $this->range = ($range > 0) ? (int)$range : 10;
        return $this;
    }

    /**
     * Method to set the content items total
     *
     * @param  int $total
     * @return \Pop\Paginator\Paginator
     */
    public function setTotal($total = null)
    {
        $this->total = (null !== $total) ? (int)$total : null;
        return $this;
    }

    /**
     * Method to set the bookend key.
     *
     * @param  int $key
     * @return \Pop\Paginator\Paginator
     */
    public function setBookend($key = Paginator::SINGLE_ARROWS)
    {
        $this->bookendKey = (int)$key;
        return $this;
    }

    /**
     * Method to set the bookend separator.
     *
     * @param  string $sep
     * @return \Pop\Paginator\Paginator
     */
    public function setSeparator($sep = ' | ')
    {
        $this->separator = $sep;
        return $this;
    }

    /**
     * Method to set the date format.
     *
     * @param  string $date
     * @return \Pop\Paginator\Paginator
     */
    public function setDateFormat($date = null)
    {
        $this->dateFormat = $date;
        return $this;
    }

    /**
     * Method to set the class 'on' name.
     *
     * @param  string $cls
     * @return \Pop\Paginator\Paginator
     */
    public function setClassOn($cls)
    {
        $this->classOn = $cls;
        return $this;
    }

    /**
     * Method to set the class 'off' name.
     *
     * @param  string $cls
     * @return \Pop\Paginator\Paginator
     */
    public function setClassOff($cls)
    {
        $this->classOff = $cls;
        return $this;
    }

    /**
     * Method to set the header template.
     *
     * @param  string $hdr
     * @return \Pop\Paginator\Paginator
     */
    public function setHeader($hdr)
    {
        $this->header = $hdr;
        return $this;
    }

    /**
     * Method to set the row template.
     *
     * @param  string $tmpl
     * @return \Pop\Paginator\Paginator
     */
    public function setRowTemplate($tmpl)
    {
        $this->rowTemplate = $tmpl;
        return $this;
    }

    /**
     * Method to set the footer template.
     *
     * @param  string $ftr
     * @return \Pop\Paginator\Paginator
     */
    public function setFooter($ftr)
    {
        $this->footer = $ftr;
        return $this;
    }

    /**
     * Method to get the content items.
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Method to get the number of content items.
     *
     * @return int
     */
    public function getItemCount()
    {
        return count($this->items);
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
     * Method to get the content items total
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
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
     * Method to get the date format.
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
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
     * Method to get the page links.
     *
     * @param  int  $pg
     * @return string
     */
    public function getLinks($pg = null)
    {
        $this->calcItems($pg);
        $this->createLinks($pg);

        return $this->links;
    }

    /**
     * Method to get the header template.
     *
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Method to get the row template.
     *
     * @return string
     */
    public function getRowTemplate()
    {
        return $this->rowTemplate;
    }

    /**
     * Method to get the footer template.
     *
     * @return string
     */
    public function getFooter()
    {
        return $this->footer;
    }

    /**
     * Method to render the current page.
     *
     * @param  int|string $pg
     * @param  boolean $ret
     * @return mixed
     */
    public function render($pg, $ret = false)
    {
        // Initialize the output.
        $this->output = null;

        // Calculate the necessary properties.
        $this->calcItems($pg);
        $this->createLinks($pg);

        // Format and output the header.
        if (null === $this->header) {
            if (count($this->links) > 1) {
                $this->output .= implode($this->separator, $this->links) . PHP_EOL;
            }
            $this->output .= '<table class="paged-table" cellpadding="0" cellspacing="0">' . PHP_EOL;
        } else {
            if (count($this->links) > 1) {
                $hdr = str_replace('[{page_links}]', implode($this->separator, $this->links), $this->header);
            } else {
                $hdr = str_replace('[{page_links}]', '', $this->header);
            }
            $this->output .= $hdr;
        }

        // Format and output the rows.
        for ($i = $this->start; $i < $this->end; $i++) {
            if (null === $this->rowTemplate) {
                $this->output .= "    <tr>";
                if (isset($this->items[$i])) {
                    foreach ($this->items[$i] as $value) {
                        if (null !== $this->dateFormat) {
                            $val = (strtotime($value) !== false) ? date($this->dateFormat, strtotime($value)) : $value;
                        } else {
                            $val = $value;
                        }
                        $this->output .= "<td>{$val}</td>";
                    }
                    $this->output .= "</tr>" . PHP_EOL;
                }
            } else {
                $tmpl = $this->rowTemplate;
                if (isset($this->items[$i])) {
                    foreach ($this->items[$i] as $key => $value) {
                        if (null !== $this->dateFormat) {
                            $val = ((strtotime($value) !== false) || (stripos($key, 'date') !== false)) ? date($this->dateFormat, strtotime($value)) : $value;
                        } else {
                            $val = $value;
                        }
                        $tmpl = str_replace('[{' . $key . '}]', $val, $tmpl);
                    }
                    $this->output .= $tmpl;
                }
            }
        }

        // Format and output the footer.
        if (null === $this->footer) {
            $this->output .= "</table>" . PHP_EOL;
            if (count($this->links) > 1) {
                $this->output .= implode($this->separator, $this->links) . PHP_EOL;
            }
        } else {
            if (count($this->links) > 1) {
                $ftr = str_replace('[{page_links}]', implode($this->separator, $this->links), $this->footer);
            } else {
                $ftr = str_replace('[{page_links}]', '', $this->footer);
            }
            $this->output .= $ftr;
        }

        if ($ret) {
            return $this->output;
        } else {
            echo $this->output;
        }
    }

    /**
     * Method to create links.
     *
     * @param  int  $pg
     * @return void
     */
    protected function createLinks($pg = null)
    {
        // Generate the page links.
        $this->links = array();

        // Preserve any passed GET parameters.
        $query = null;
        $uri = null;
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));

            if (count($_GET) > 0) {
                foreach ($_GET as $key => $value) {
                    if ($key != 'page') {
                        $query .= '&' . $key . '=' . $value;
                    }
                }
            }
        }

        // Calculate page range links.
        $pageRange = $this->calcRange($pg);

        for ($i = $pageRange['start']; $i <= $pageRange['end']; $i++) {
            $newLink = null;
            $prevLink = null;
            $nextLink = null;
            $classOff = (null !== $this->classOff) ? " class=\"{$this->classOff}\"" : null;
            $classOn = (null !== $this->classOn) ? " class=\"{$this->classOn}\"" : null;

            $newLink = ($i == $pg) ? "<span{$classOff}>{$i}</span>" : "<a{$classOn} href=\"" . $uri . "?page={$i}{$query}\">{$i}</a>";

            if (($i == $pageRange['start']) && ($pageRange['prev'])) {
                $prevLink = "<a{$classOn} href=\"" . $uri . "?page=" . ($i - 1) . "{$query}\">" . $this->bookends[$this->bookendKey]['prev'] . "</a>";
                $this->links[] = $prevLink;
            }
            $this->links[] = $newLink;
            if (($i == $pageRange['end']) && ($pageRange['next'])) {
                $nextLink = "<a{$classOn} href=\"" . $uri . "?page=" . ($i + 1) . "{$query}\">" . $this->bookends[$this->bookendKey]['next'] . "</a>";
                $this->links[] = $nextLink;
            }
        }
    }

    /**
     * Method to calculate the page items.
     *
     * @param  int|string $p
     * @return void
     */
    protected function calcItems($p)
    {
        // Calculate the number of pages based on the remainder.
        if ((null !== $this->total) && ((int)$this->total > 0)) {
            $this->rem = $this->total % $this->perPage;
            $this->numPages = ($this->rem != 0) ? (floor(($this->total / $this->perPage)) + 1) : floor(($this->total / $this->perPage));
        } else {
            $this->rem = (count($this->items)) % $this->perPage;
            $this->numPages = ($this->rem != 0) ? (floor((count($this->items) / $this->perPage)) + 1) : floor((count($this->items) / $this->perPage));
        }

        // Calculate the start index.
        $this->start = ($p * $this->perPage) - $this->perPage;

        // Calculate the end index.
        if (($p == $this->numPages) && ($this->rem == 0)) {
            $this->end = $this->start + $this->perPage;
        } else if ($p == $this->numPages) {
            $this->end = (($p * $this->perPage) - ($this->perPage - $this->rem));
        } else {
            $this->end = ($p * $this->perPage);
        }

        // Calculate if out of range.
        if ($this->start >= count($this->items)) {
            $this->start = 0;
            $this->end = $this->perPage;
        }
    }

    /**
     * Method to calculate the page range.
     *
     * @param  int|string $pg
     * @return array
     */
    protected function calcRange($pg)
    {
        $range = array();

        // Check and calculate for any page ranges.
        if (((null === $this->range) || ($this->range > $this->numPages)) && (null === $this->total)) {
            $range = array(
                'start' => 1,
                'end'   => $this->numPages,
                'prev'  => false,
                'next'  => false
            );
        } else {
            // If page is within the first range block.
            if (($pg <= $this->range) && ($this->numPages <= $this->range)) {
                $range = array(
                    'start' => 1,
                    'end'   => $this->numPages,
                    'prev'  => false,
                    'next'  => false
                );
            // If page is within the first range block, with a next range.
            } else if (($pg <= $this->range) && ($this->numPages > $this->range)) {
                $range = array(
                    'start' => 1,
                    'end'   => $this->range,
                    'prev'  => false,
                    'next'  => true
                );
            // Else, if page is within the last range block, with an uneven remainder.
            } else if ($pg > ($this->range * floor($this->numPages / $this->range))) {
                $range = array(
                    'start' => ($this->range * floor($this->numPages / $this->range)) + 1,
                    'end'   => $this->numPages,
                    'prev'  => true,
                    'next'  => false
                );
            // Else, if page is within the last range block, with no remainder.
            } else if ((($this->numPages % $this->range) == 0) && ($pg > ($this->range * (($this->numPages / $this->range) - 1)))) {
                $range = array(
                    'start' => ($this->range * (($this->numPages / $this->range) - 1)) + 1,
                    'end'   => $this->numPages,
                    'prev'  => true,
                    'next'  => false
                );
            // Else, if page is within a middle range block.
            } else {
                $posInRange = (($pg % $this->range) == 0) ? ($this->range - 1) : (($pg % $this->range) - 1);
                $linkStart = $pg - $posInRange;
                $range = array(
                    'start' => $linkStart,
                    'end'   => $linkStart + ($this->range - 1),
                    'prev'  => true,
                    'next'  => true
                );
            }
        }

        return $range;
    }

    /**
     * Output the rendered page
     *
     * @return string
     */

    public function __toString()
    {
        if (isset($_GET['page']) && ((int)$_GET['page'] > 0)) {
            $pg = (int)$_GET['page'];
        } else {
            $pg = 1;
        }
        return $this->render($pg, true);
    }

}
