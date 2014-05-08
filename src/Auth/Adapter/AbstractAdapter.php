<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth\Adapter;

use Pop\Auth\Auth;
use Pop\Crypt;

/**
 * Abstract auth adapter class
 *
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
abstract class AbstractAdapter implements AdapterInterface
{

    /**
     * User data array
     * @var array
     */
    protected $user = array();

    /**
     * Method to verify password
     *
     * @param  string $hash
     * @param  string $attemptedPassword
     * @param  int    $encryption
     * @param  array  $options
     * @return boolean
     */
    public function verifyPassword($hash, $attemptedPassword, $encryption, $options)
    {
        $pw   = false;
        $salt = (!empty($options['salt'])) ? $options['salt'] : null;

        if (!empty($options['secret'])) {
            $attemptedPassword .= $options['secret'];
        }

        switch ($encryption) {
            case Auth::ENCRYPT_NONE:
                $pw = ($hash == $attemptedPassword);
                break;

            case Auth::ENCRYPT_MD5:
                $pw = ($hash == md5($attemptedPassword));
                break;

            case Auth::ENCRYPT_SHA1:
                $pw = ($hash == sha1($attemptedPassword));
                break;

            case Auth::ENCRYPT_CRYPT:
                $crypt = new Crypt\Crypt();
                $crypt->setSalt($salt);
                $pw = $crypt->verify($attemptedPassword, $hash);
                break;

            case Auth::ENCRYPT_BCRYPT:
                $crypt = new Crypt\Bcrypt();
                $crypt->setSalt($salt);

                // Set cost and prefix, if applicable
                if (!empty($options['cost'])) {
                    $crypt->setCost($options['cost']);
                }
                if (!empty($options['prefix'])) {
                    $crypt->setPrefix($options['prefix']);
                }

                $pw = $crypt->verify($attemptedPassword, $hash);
                break;

            case Auth::ENCRYPT_MCRYPT:
                $crypt = new Crypt\Mcrypt();
                $crypt->setSalt($salt);

                // Set cipher, mode and source, if applicable
                if (!empty($options['cipher'])) {
                    $crypt->setCipher($options['cipher']);
                }
                if (!empty($options['mode'])) {
                    $crypt->setMode($options['mode']);
                }
                if (!empty($options['source'])) {
                    $crypt->setSource($options['source']);
                }

                $pw = $crypt->verify($attemptedPassword, $hash);
                break;

            case Auth::ENCRYPT_CRYPT_MD5:
                $crypt = new Crypt\Md5();
                $crypt->setSalt($salt);
                $pw = $crypt->verify($attemptedPassword, $hash);
                break;

            case Auth::ENCRYPT_CRYPT_SHA_256:
                $crypt = new Crypt\Sha(256);
                $crypt->setSalt($salt);

                // Set rounds, if applicable
                if (!empty($options['rounds'])) {
                    $crypt->setRounds($options['rounds']);
                }

                $pw = $crypt->verify($attemptedPassword, $hash);
                break;

            case Auth::ENCRYPT_CRYPT_SHA_512:
                $crypt = new Crypt\Sha(512);
                $crypt->setSalt($salt);

                // Set rounds, if applicable
                if (!empty($options['rounds'])) {
                    $crypt->setRounds($options['rounds']);
                }

                $pw = $crypt->verify($attemptedPassword, $hash);
                break;
        }

        return $pw;
    }

    /**
     * Method to the user data array
     *
     * @return array
     */
    public function getUser()
    {
        return $this->user;
    }

}
