<?php
/**
 * Utility class for SendCloud module.
 *
 * PHP version 7.4
 *
 *  @author    SendCloud Global B.V. <contact@sendcloud.eu>
 *  @copyright 2023 SendCloud Global B.V.
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 *  @category  Shipping
 *
 *  @see      https://sendcloud.eu
 */

namespace Sendcloud\PrestaShop\Classes\Bootstrap;

use InvalidArgumentException;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class ServiceRegister
 *
 * @package Sendcloud\PrestaShop\Classes\Bootstrap
 */
class ServiceRegister
{
    /**
     * Service register instance
     *
     * @var ServiceRegister
     */
    private static $instance;

    /**
     * Array of registered services
     *
     * @var array
     */
    private $services;

    /**
     * ServiceRegister constructor.
     *
     * @param array $services
     */
    public function __construct($services = array())
    {
        if (!empty($services)) {
            foreach ($services as $type => $service) {
                $this->register($type, $service);
            }
        }

        self::$instance = $this;
    }

    /**
     * Getting service register instance
     *
     * @return ServiceRegister
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new ServiceRegister();
        }

        return self::$instance;
    }

    /**
     * Gets service
     *
     * @param string $type
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function getService($type)
    {
        return self::getInstance()->get($type);
    }

    /**
     * Registers service with delegate as second parameter which represents function for creating new service instance
     *
     * @param string $type
     * @param callable $delegate
     */
    public static function registerService($type, $delegate)
    {
        self::getInstance()->register($type, $delegate);
    }

    /**
     * Register service class
     *
     * @param string $type
     * @param callable $delegate
     */
    private function register($type, $delegate)
    {
        if (!is_callable($delegate)) {
            throw new InvalidArgumentException("$type delegate is not callable.");
        }

        $this->services[$type] = $delegate;
    }

    /**
     * Getting service instance
     *
     * @param string $type
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    private function get($type)
    {
        if (empty($this->services[$type])) {
            throw new InvalidArgumentException("$type is not defined.");
        }

        return call_user_func($this->services[$type]);
    }
}
