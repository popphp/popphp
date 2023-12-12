<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop;

use Pop\Cookie\Cookie;

/**
 * Application helper class
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class App
{

    /**
     * Application object
     * @var ?Application
     */
    private static ?Application $application = null;

    /**
     * Set application object
     *
     * @param  Application $application
     * @return void
     */
    public static function set(Application $application): void
    {
        self::$application = $application;
    }

    /**
     * Get application object
     *
     * @return ?Application
     */
    public static function get(): ?Application
    {
        return self::$application;
    }

    /**
     * Has application object
     *
     * @return bool
     */
    public static function has(): bool
    {
        return (self::$application !== null);
    }

    /**
     * Get configuration
     *
     * @param  ?string $key
     * @return mixed
     */
    public static function config(?string $key = null): mixed
    {
        if (self::$application !== null) {
            return ($key !== null) ? self::$application->config[$key] : self::$application->config();
        } else {
            return null;
        }
    }

    /**
     * Get application name
     *
     * @return ?string
     */
    public static function name(): ?string
    {
        return self::env('APP_NAME');
    }

    /**
     * Get application URL
     *
     * @return ?string
     */
    public static function url(): ?string
    {
        return self::env('APP_URL');
    }

    /**
     * Get environment value
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function env(string $key, mixed $default = null): mixed
    {
        if (!isset($_ENV[$key])) {
            return $default;
        }

        $value = $_ENV[$key];

        switch ($value) {
            case 'true':
            case '(true)':
                $value = true;
                break;
            case 'false':
            case '(false)':
                $value = false;
                break;
            case 'null':
            case '(null)':
                $value = null;
                break;
            case 'empty':
            case '(empty)':
                $value = '';
                break;
        }

        return $value;
    }

    /**
     * Get application environment
     *
     * @param  mixed $env
     * @return string|null|bool
     */
    public static function environment(mixed $env = null): string|null|bool
    {
        if ($env === null) {
            return self::env('APP_ENV');
        }

        if (!is_array($env)) {
            $env = [$env];
        }

        return in_array(self::env('APP_ENV'), $env);
    }

    /**
     * Check if application environment is local
     *
     * @return bool
     */
    public static function isLocal(): bool
    {
        return (self::env('APP_ENV') == 'local');
    }

    /**
     * Check if application environment is dev
     *
     * @return bool
     */
    public static function isDev(): bool
    {
        return (self::env('APP_ENV') == 'dev');
    }

    /**
     * Check if application environment is testing
     *
     * @return bool
     */
    public static function isTesting(): bool
    {
        return (self::env('APP_ENV') == 'testing');
    }

    /**
     * Check if application environment is staging
     *
     * @return bool
     */
    public static function isStaging(): bool
    {
        return (self::env('APP_ENV') == 'staging');
    }

    /**
     * Check if application environment is production
     *
     * @return bool
     */
    public static function isProduction(): bool
    {
        return !empty(self::env('APP_ENV')) && str_starts_with(self::env('APP_ENV'), 'prod');
    }

    /**
     * Check if application is in maintenance mode
     *
     * @return bool
     */
    public static function isDown(): bool
    {
        return (self::env('MAINTENANCE_MODE') === true);
    }

    /**
     * Check if application is in not maintenance mode
     *
     * @return bool
     */
    public static function isUp(): bool
    {
        return (self::env('MAINTENANCE_MODE') === false);
    }

    /**
     * Check if application is in not maintenance mode
     *
     * @return bool
     */
    public static function isSecretRequest(): bool
    {
        if (isset($_GET['secret'])) {
            $secret = $_GET['secret'];
            $cookie = Cookie::getInstance();
            $cookie->set('pop_mm_secret', $_GET['secret']);
        } else {
            $cookie = Cookie::getInstance();
            $secret = $cookie['pop_mm_secret'];
        }

        return (!empty($secret) && ($secret == App::env('MAINTENANCE_MODE_SECRET')));
    }

}
