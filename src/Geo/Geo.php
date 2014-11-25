<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Geo
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Geo;

/**
 * Geo class
 *
 * @category   Pop
 * @package    Pop_Geo
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Geo
{

    /**
     * Host name to look up
     * @var string
     */
    protected $host = null;

    /**
     * Latitude value
     * @var string
     */
    protected $latitude = null;

    /**
     * Longitude value
     * @var string
     */
    protected $longitude = null;

    /**
     * Host info
     * @var array
     */
    protected $hostInfo = [
        'areaCode'      => null,
        'city'          => null,
        'continentCode' => null,
        'country'       => null,
        'countryCode'   => null,
        'countryCode3'  => null,
        'dmaCode'       => null,
        'isp'           => null,
        'latitude'      => null,
        'longitude'     => null,
        'org'           => null,
        'postalCode'    => null,
        'region'        => null,
        'netspeed'      => null,
    ];

    /**
     * Array of available databases
     * @var string
     */
    protected $databases = [
        'asnum'      => false,
        'city'       => false,
        'country'    => false,
        'countryv6'  => false,
        'domainname' => false,
        'isp'        => false,
        'netspeed'   => false,
        'org'        => false,
        'proxy'      => false,
        'region'     => false
    ];

    /**
     * Constructor
     *
     * Instantiate the Geo object
     *
     * @param  array $options
     * @return Geo
     */
    public function __construct(array $options = [])
    {
        if (isset($options['host'])) {
            $this->host = $options['host'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $this->host = $_SERVER['REMOTE_ADDR'];
        }

        if (isset($options['latitude'])) {
            $this->latitude  = $options['latitude'];
        }

        if (isset($options['longitude'])) {
            $this->longitude = $options['longitude'];
        }

        $this->getAvailableDatabases();
        $this->getGeoIpHostInfo();

        // If lat and long weren't passed, try and get it from the host location
        if ((null === $this->latitude) && (null === $this->longitude)) {
            $this->latitude  = $this->hostInfo['latitude'];
            $this->longitude = $this->hostInfo['longitude'];
        }
    }

    /**
     * Get an available database
     *
     * @param  string $name
     * @return boolean
     */
    public function isDbAvailable($name)
    {
        $key = strtolower($name);
        if (array_key_exists($key, $this->databases)) {
            return $this->databases[$key];
        } else {
            return false;
        }
    }

    /**
     * Get all available databases
     *
     * @return array
     */
    public function getDatabases()
    {
        return $this->databases;
    }

    /**
     * Get host info
     *
     * @return array
     */
    public function getHostInfo()
    {
        return $this->hostInfo;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Get distance from current Geo object coordinates to another
     *
     * @param  Geo     $dest
     * @param  int     $round
     * @param  boolean $km
     * @throws Exception
     * @return mixed
     */
    public function distanceTo(Geo $dest, $round = 2, $km = false)
    {
        $distance = null;

        if ((null === $this->latitude) || (null === $this->longitude)) {
            throw new Exception('The origin coordinates are not set.');
        }

        if ((null === $dest->getLatitude()) || (null === $dest->getLongitude())) {
            throw new Exception('The destination coordinates are not set.');
        }

        $origin = [
            'latitude'  => $this->latitude,
            'longitude' => $this->longitude
        ];

        $destination = [
            'latitude'  => $dest->getLatitude(),
            'longitude' => $dest->getLongitude()
        ];

        return self::calculateDistance($origin, $destination, $round, $km);
    }

    /**
     * Calculate the distance between 2 sets of coordinate
     *
     * @param  array   $origin
     * @param  array   $destination
     * @param  int     $round
     * @param  boolean $km
     * @throws Exception
     * @return mixed
     */
    public static function calculateDistance(array $origin, array $destination, $round = 2, $km = false)
    {
        if (!isset($origin['latitude']) || !isset($origin['longitude'])) {
            throw new Exception('The origin coordinates are not set.');
        }
        if (!isset($destination['latitude']) || !isset($destination['longitude'])) {
            throw new Exception('The destination coordinates are not set.');
        }

        $distance = (acos(
                sin($origin['latitude'] * pi() / 180)
                * sin($destination['latitude'] * pi() / 180)
                + cos($origin['latitude'] * pi() / 180)
                * cos($destination['latitude'] * pi() / 180)
                * cos(($origin['longitude'] - $destination['longitude']) * pi() / 180)
            ) * 180 / pi()
        ) * 60 * 1.1515;

        $distance = abs(round($distance, $round));

        if ($km) {
            $distance = round($distance * 1.60934, $round);
        }

        return $distance;
    }

    /**
     * Get method to return the value of hostInfo[$name].
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return (array_key_exists($name, $this->hostInfo)) ? $this->hostInfo[$name] : null;
    }

    /**
     * Get available databases
     *
     * @return void
     */
    protected function getAvailableDatabases()
    {
        if (function_exists('geoip_db_get_all_info')) {
            $databases = geoip_db_get_all_info();

            foreach ($databases as $db) {
                if ((stripos($db['description'], 'ASNum') !== false) && ($db['available'])) {
                    $this->databases['asnum'] = true;
                }
                if ((stripos($db['description'], 'City') !== false) && ($db['available'])) {
                    $this->databases['city'] = true;
                }
                if ((stripos($db['description'], 'Country') !== false) && ($db['available'])) {
                    $this->databases['country'] = true;
                }
                if ((stripos($db['description'], 'Country V6') !== false) && ($db['available'])) {
                    $this->databases['countryv6'] = true;
                }
                if ((stripos($db['description'], 'Domain Name') !== false) && ($db['available'])) {
                    $this->databases['domainname'] = true;
                }
                if ((stripos($db['description'], 'ISP') !== false) && ($db['available'])) {
                    $this->databases['isp'] = true;
                }
                if ((stripos($db['description'], 'Netspeed') !== false) && ($db['available'])) {
                    $this->databases['netspeed'] = true;
                }
                if ((stripos($db['description'], 'Organization') !== false) && ($db['available'])) {
                    $this->databases['org'] = true;
                }
                if ((stripos($db['description'], 'Proxy') !== false) && ($db['available'])) {
                    $this->databases['proxy'] = true;
                }
                if ((stripos($db['description'], 'Region') !== false) && ($db['available'])) {
                    $this->databases['region'] = true;
                }
            }
        }
    }

    /**
     * Get GeoIp host information
     *
     * @return void
     */
    protected function getGeoIpHostInfo()
    {
        if (function_exists('geoip_db_get_all_info') && (null !== $this->host) &&
            ($this->host != '127.0.0.1') && ($this->host != 'localhost')) {
            // Get base info by city
            if ($this->databases['city']) {
                $data = geoip_record_by_name($this->host);
                $this->hostInfo['areaCode'] = $data['area_code'];
                $this->hostInfo['city'] = $data['city'];
                $this->hostInfo['continentCode'] = $data['continent_code'];
                $this->hostInfo['country'] = $data['country_name'];
                $this->hostInfo['countryCode'] = $data['country_code'];
                $this->hostInfo['countryCode3'] = $data['country_code3'];
                $this->hostInfo['dmaCode'] = $data['dma_code'];
                $this->hostInfo['latitude'] = $data['latitude'];
                $this->hostInfo['longitude'] = $data['longitude'];
                $this->hostInfo['postalCode'] = $data['postal_code'];
                $this->hostInfo['region'] = $data['region'];
            // Else, get base info by country
            } else if ($this->databases['country']) {
                $this->hostInfo['continentCode'] = geoip_continent_code_by_name($this->host);
                $this->hostInfo['country'] = geoip_country_name_by_name($this->host);
                $this->hostInfo['countryCode'] = geoip_country_code_by_name($this->host);
                $this->hostInfo['countryCode3'] = geoip_country_code3_by_name($this->host);
            }

            // If available, get ISP name
            if ($this->databases['isp']) {
                $this->hostInfo['isp'] = geoip_isp_by_name($this->host);
            }

            // If available, get internet connection speed
            if ($this->databases['netspeed']) {
                $netspeed = geoip_id_by_name($this->host);
                switch ($netspeed) {
                    case GEOIP_DIALUP_SPEED:
                        $this->hostInfo['netspeed'] = 'Dial-Up';
                        break;
                    case GEOIP_CABLEDSL_SPEED:
                        $this->hostInfo['netspeed'] = 'Cable/DSL';
                        break;
                    case GEOIP_CORPORATE_SPEED:
                        $this->hostInfo['netspeed'] = 'Corporate';
                        break;
                    default:
                        $this->hostInfo['netspeed'] = 'Unknown';
                }
            }

            // If available, get Organization name
            if ($this->databases['org']) {
                $this->hostInfo['org'] = geoip_org_by_name($this->host);
            }
        }
    }
}
