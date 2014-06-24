<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Form\Element;

/**
 * CSRF form element class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Csrf extends \Pop\Form\Element
{

    /**
     * Current token data
     * @var array
     */
    protected $token = [];

    /**
     * Constructor
     *
     * Instantiate the CSRF form element object.
     *
     * @param  string $name
     * @param  string $value
     * @param  int    $expire
     * @param  string $indent
     * @return \Pop\Form\Element\Csrf
     */
    public function __construct($name, $value = null, $expire = 300, $indent = null)
    {
        // Start a session.
        if (session_id() == '') {
            session_start();
        }

        // If token does not exist, create one
        if (!isset($_SESSION['pop_csrf'])) {
            $this->token = [
                'value'  => sha1(rand(10000, getrandmax()) . $value),
                'expire' => (int)$expire,
                'start'  => time()
            ];
            $_SESSION['pop_csrf'] = serialize($this->token);
        // Else, retrieve existing token
        } else {
            $this->token = unserialize($_SESSION['pop_csrf']);

            // Check to see if the token has expired
            if ($this->token['expire'] > 0) {
                if (($this->token['expire'] + $this->token['start']) < time()) {
                    $this->token = [
                        'value'  => sha1(rand(10000, getrandmax()) . $value),
                        'expire' => (int)$expire,
                        'start'  => time()
                    ];
                    $_SESSION['pop_csrf'] = serialize($this->token);
                }
            }
        }

        parent::__construct('hidden', $name, $this->token['value'], null, $indent);
        $this->setRequired(true);
        $this->setValidator();
    }

    /**
     * Method to set the validator
     *
     * @throws \Pop\Form\Exception
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
                $val = (isset($queryData[$this->name])) ? $queryData[$this->name] : '';
                $this->addValidator(new \Pop\Validator\Equal($val, 'The security token does not match.'));
            }
        } else {
            throw new \Pop\Form\Exception('Error: The server request method is not set.');
        }
    }

}
