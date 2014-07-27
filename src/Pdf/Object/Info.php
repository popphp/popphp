<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Pdf\Object;

/**
 * Pdf info object class
 *
 * @category   Pop
 * @package    Pop_Pdf
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Info
{

    /**
     * PDF info object index
     * @var int
     */
    public $index = 3;

    /**
     * PDF info title
     * @var string
     */
    public $title = 'Pop PDF';

    /**
     * PDF info author
     * @var string
     */
    public $author = 'Pop PDF';

    /**
     * PDF info subject
     * @var string
     */
    public $subject = 'Pop PDF';

    /**
     * PDF info creation date
     * @var string
     */
    public $create_date = null;

    /**
     * PDF info mod date
     * @var string
     */
    public $mod_date = null;

    /**
     * PDF info object data
     * @var string
     */
    protected $data = null;

    /**
     * Constructor
     *
     * Instantiate a PDF info object.
     *
     * @param  string $str
     * @return Info
     */
    public function __construct($str = null)
    {
        // Use default settings for a new PDF object and its info object.
        if (null === $str) {
            $this->create_date = date('D, M j, Y h:i A');
            $this->mod_date = date('D, M j, Y h:i A');
            $this->data = "3 0 obj\n<</Creator(Pop PDF)/CreationDate([{pdf_create_date}])/ModDate([{pdf_mod_date}])/Author([{pdf_author}])/Title([{pdf_title}])/Subject([{pdf_subject}])/Producer(Pop PDF)>>\nendobj\n";
        } else {
            // Else, determine the info object index.
            $this->index = substr($str, 0, strpos($str, ' '));

            // Determine the Creator.
            if (strpos($str, '/Creator') !== false) {
                $crt = substr($str, strpos($str, '/Creator'));
                $crt = substr($crt, strpos($crt, '('));
                $crt = substr($crt, 0, strpos($crt, ')'));
                $crt =  str_replace('(', '', $crt);
                $str =  str_replace($crt, 'Pop PDF', $str);
            } else {
                $str =  str_replace('>>', '/Creator(Pop PDF)>>', $str);
            }

            // Determine the CreationDate.
            if (strpos($str, '/CreationDate') !== false) {
                $dt = substr($str, strpos($str, '/CreationDate'));
                $dt = substr($dt, strpos($dt, '('));
                $dt = substr($dt, 0, strpos($dt, ')'));
                $dt =  str_replace('(', '', $dt);
                $str =  str_replace($dt, '[{pdf_create_date}]', $str);
                $this->create_date = $dt;
            } else {
                $str =  str_replace('>>', '/CreationDate([{pdf_create_date}])>>', $str);
            }

            // Determine the ModDate.
            if (strpos($str, '/ModDate') !== false) {
                $dt = substr($str, strpos($str, '/ModDate'));
                $dt = substr($dt, strpos($dt, '('));
                $dt = substr($dt, 0, strpos($dt, ')'));
                $dt =  str_replace('(', '', $dt);
                $str =  str_replace($dt, '[{pdf_mod_date}]', $str);
                $this->mod_date = $dt;
            } else {
                $str =  str_replace('>>', '/ModDate([{pdf_mod_date}])>>', $str);
            }

            // Determine the Author.
            if (strpos($str, '/Author') !== false) {
                $auth = substr($str, strpos($str, '/Author'));
                $auth = substr($auth, strpos($auth, '('));
                $auth = substr($auth, 0, strpos($auth, ')'));
                $auth =  str_replace('(', '', $auth);
                $str =  str_replace($auth, '[{pdf_author}]', $str);
                $this->author = $auth;
            } else {
                $str =  str_replace('>>', '/Author([{pdf_author}])>>', $str);
            }

            // Determine the Title.
            if (strpos($str, '/Title') !== false) {
                $tle = substr($str, strpos($str, '/Title'));
                $tle = substr($tle, strpos($tle, '('));
                $tle = substr($tle, 0, strpos($tle, ')'));
                $tle =  str_replace('(', '', $tle);
                $str =  str_replace($tle, '[{pdf_title}]', $str);
                $this->title = $tle;
            } else {
                $str =  str_replace('>>', '/Title([{pdf_title}])>>', $str);
            }

            // Determine the Subject.
            if (strpos($str, '/Subject') !== false) {
                $subj = substr($str, strpos($str, '/Subject'));
                $subj = substr($subj, strpos($subj, '('));
                $subj = substr($subj, 0, strpos($subj, ')'));
                $subj =  str_replace('(', '', $subj);
                $str =  str_replace($subj, '[{pdf_subject}]', $str);
                $this->subject = $subj;
            } else {
                $str =  str_replace('>>', '/Subject([{pdf_subject}])>>', $str);
            }

            // Determine the Producer.
            if (strpos($str, '/Producer') !== false) {
                $prod = substr($str, strpos($str, '/Producer'));
                $prod = substr($prod, strpos($prod, '('));
                $prod = substr($prod, 0, strpos($prod, ')'));
                $prod =  str_replace('(', '', $prod);
                $str =  str_replace($prod, 'Pop PDF', $str);
            } else {
                $str =  str_replace('>>', '/Producer(Pop PDF)>>', $str);
            }

            $this->data = $str;
        }
    }

    /**
     * Method to print the PDF object.
     *
     * @return string
     */
    public function __toString()
    {
        // Set the CreationDate and the ModDate if they are null.
        if (null === $this->create_date) {
            $this->create_date = date('D, M j, Y h:i A');
        }
        if (null === $this->mod_date) {
            $this->mod_date = date('D, M j, Y h:i A');
        }

        // Swap out the placeholders.
        $data = str_replace('[{pdf_mod_date}]', $this->mod_date, $this->data);
        $data = str_replace('[{pdf_create_date}]', $this->create_date, $data);
        $data = str_replace('[{pdf_author}]', $this->author, $data);
        $data = str_replace('[{pdf_title}]', $this->title, $data);
        $data = str_replace('[{pdf_subject}]', $this->subject, $data);

        return $data;
    }

}
