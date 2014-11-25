<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Form\Element\Input;

/**
 * Form CAPTCHA element class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */

class Captcha extends Text
{
    /**
     * Constructor
     *
     * Instantiate the captcha input form element
     *
     * @param  string $name
     * @param  string $value
     * @param  string $indent
     * @param  int    $expire
     * @param  string $captcha
     * @return Captcha
     */
    public function __construct($name, $value = null, $indent = null, $expire = 300, $captcha = null)
    {
        // Start a session.
        if (session_id() == '') {
            session_start();
        }

        // If token does not exist, create one
        if (!isset($_SESSION['pop_captcha'])) {
            if (null === $captcha) {
                $captcha = $this->generateEquation();
            } else if (stripos($captcha, '<img') === false) {
                $captcha = strtoupper($captcha);
            }

            $this->token = [
                'captcha' => $captcha,
                'value'   => null,
                'expire'  => (int)$expire,
                'start'   => time()
            ];
            $_SESSION['pop_captcha'] = serialize($this->token);
            // Else, retrieve existing token
        } else {
            $this->token = unserialize($_SESSION['pop_captcha']);

            // Check to see if the token has expired
            if ($this->token['expire'] > 0) {
                if (($this->token['expire'] + $this->token['start']) < time()) {
                    if (null === $captcha) {
                        $captcha = $this->generateEquation();
                    } else if (stripos($captcha, '<img') === false) {
                        $captcha = strtoupper($captcha);
                    }

                    $this->token = [
                        'captcha' => $captcha,
                        'value'   => null,
                        'expire'  => (int)$expire,
                        'start'   => time()
                    ];
                    $_SESSION['pop_captcha'] = serialize($this->token);
                }
            }
        }

        parent::__construct($name, strtoupper($value), $indent);
        $this->setRequired(true);
        $this->setValidator();
    }

    /**
     * Set the label of the captcha form element
     *
     * @param  string $label
     * @return Captcha
     */
    public function setLabel($label)
    {
        parent::setLabel($label);

        if (isset($this->token['captcha'])) {
            if ((strpos($this->token['captcha'], '<img') === false) && ((strpos($this->token['captcha'], ' + ') !== false) || (strpos($this->token['captcha'], ' - ') !== false) || (strpos($this->token['captcha'], ' * ') !== false) || (strpos($this->token['captcha'], ' / ') !== false))) {
                $this->label = $this->label . '(' . str_replace([' * ', ' / '], [' &#215; ', ' &#247; '], $this->token['captcha'] .')');
            } else {
                $this->label = $this->label . $this->token['captcha'];
            }
        }

        return $this;
    }

    /**
     * Set the validator
     *
     * @throws Exception
     * @return void
     */
    protected function setValidator()
    {
        // Get query data
        if ($_SERVER['REQUEST_METHOD']) {
            $queryData = [];
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $queryData = $_GET;
                    break;

                case 'POST':
                    $queryData = $_POST;
                    break;

                default:
                    $input = fopen('php://input', 'r');
                    $qData = null;
                    while ($data = fread($input, 1024)) {
                        $qData .= $data;
                    }

                    parse_str($qData, $queryData);
            }

            // If there is query data, set validator to check against the token value
            if (count($queryData) > 0) {
                if (isset($queryData[$this->name])) {
                    $captcha = $this->token['captcha'];
                    if (stripos($captcha, '<img') !== false) {
                        $answer =  $this->token['value'];
                    } else if ((strpos($captcha, '<img') === false) && ((strpos($captcha, ' + ') !== false) || (strpos($captcha, ' - ') !== false) || (strpos($captcha, ' * ') !== false) || (strpos($captcha, ' / ') !== false))) {
                        $answer = eval("return ($captcha);");
                    } else {
                        $answer = $captcha;
                    }
                    $this->addValidator(new \Pop\Validator\Equal($answer, 'The answer is incorrect.'));
                }
            }
        } else {
            throw new Exception('Error: The server request method is not set.');
        }
    }

    /**
     * Randomly generate a simple, basic equation
     *
     * @return string
     */
    protected function generateEquation()
    {
        $ops = [' + ', ' - ', ' * ', ' / '];
        $equation = null;

        $rand1 = rand(1, 10);
        $rand2 = rand(1, 10);
        $op    = $ops[rand(0, 3)];

        // If the operator is division, keep the equation very simple, with no remainder
        if ($op == ' / ') {
            $mod = ($rand2 > $rand1) ? $rand2 % $rand1 : $rand1 % $rand2;
            while ($mod != 0) {
                $rand1 = rand(1, 10);
                $rand2 = rand(1, 10);
                $mod   = ($rand2 > $rand1) ? $rand2 % $rand1 : $rand1 % $rand2;
            }
        }

        $equation = ($rand2 > $rand1) ? $rand2 . $op . $rand1 : $rand1 . $op . $rand2;

        return $equation;
    }

}