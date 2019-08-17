<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Configs;


use Configula\ConfigValues;
use Interfaces\ConfigInterfaces\VendorExtensionConfigInterface;
use Traits\ConfigTraits\VendorExtensionInitConfigTrait;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class CacheConfig
 * @package Configs
 */
class CacheConfig implements VendorExtensionConfigInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitConfigTrait;

    /**
     *
     */
    const CACHE_MANAGER_CONFIGS = [
        "memcache" => ["class" => "\Phpfastcache\Drivers\Memcache\Config",
            "host" => true, "port" => true, "db" => false, "user" => false, "pwd" => false, "timeout" => false,
            "ssl" => false, "persistent" => true, "compression" => true,
            "defaults" => ["127.0.0.1", 11211, false, false, 0, "", "", "", false]],
        "cassandra" => ["class" => "\Phpfastcache\Drivers\Cassandra\Config",
            "host" => true, "port" => true, "db" => false, "user" => true, "pwd" => true, "timeout" => true,
            "ssl" => true, "persistent" => false, "compression" => true,
            "defaults" => ["127.0.0.1", 9142, false, false, 2, "", "", "", false]],
        "couchbase" => ["class" => "\Phpfastcache\Drivers\Couchbase\Config",
            "host" => true, "port" => true, "db" => true, "user" => true, "pwd" => true, "timeout" => false,
            "ssl" => true, "persistent" => false, "compression" => true,
            "defaults" => ["127.0.0.1", 8091, false, false, 0, "", "", "default", false]],
        "couchdb" => ["class" => "\Phpfastcache\Drivers\Couchdb\Config",
            "host" => true, "port" => true, "db" => true, "user" => true, "pwd" => true, "timeout" => true,
            "ssl" => true, "persistent" => false, "compression" => true,
            "defaults" => ["127.0.0.1", 5984, false, false, 10, "", "", "default", false]],
        "memcached" => ["class" => "\Phpfastcache\Drivers\Memcached\Config",
            "host" => true, "port" => true, "db" => false, "user" => true, "pwd" => true, "timeout" => false,
            "ssl" => false, "persistent" => false, "compression" => true,
            "defaults" => ["127.0.0.1", 11211, false, false, 0, "", "", "", false]],
        "mongodb" => ["class" => "\Phpfastcache\Drivers\Mongodb\Config",
            "host" => true, "port" => true, "db" => true, "user" => true, "pwd" => true, "timeout" => true,
            "ssl" => false, "persistent" => false, "compression" => true,
            "defaults" => ["127.0.0.1", 27017, false, false, 3, "", "", "default", false]],
        "predis" => ["class" => "\Phpfastcache\Drivers\Predis\Config",
            "host" => true, "port" => true, "db" => true, "user" => false, "pwd" => true, "timeout" => true,
            "ssl" => false, "persistent" => false, "compression" => true,
            "defaults" => ["127.0.0.1", 6379, false, false, 5, "", "", "0", false]],
        "redis" => ["class" => "\Phpfastcache\Drivers\Redis\Config",
            "host" => true, "port" => true, "db" => true, "user" => false, "pwd" => true, "timeout" => true,
            "ssl" => false, "persistent" => false, "compression" => true,
            "defaults" => ["127.0.0.1", 6379, false, false, 5, "", "", "0", false]],
        "riak" => ["class" => "\Phpfastcache\Drivers\Riak\Config",
            "host" => true, "port" => true, "db" => true, "user" => false, "pwd" => false, "timeout" => false,
            "ssl" => false, "persistent" => false, "compression" => true,
            "defaults" => ["127.0.0.1", 8098, false, false, 0, "", "", "default", false]],
        "ssdb" => ["class" => "\Phpfastcache\Drivers\Ssdb\Config",
            "host" => true, "port" => true, "db" => false, "user" => false, "pwd" => true, "timeout" => true,
            "ssl" => false, "persistent" => false, "compression" => true,
            "defaults" => ["127.0.0.1", 8888, false, false, 2000, "", "", "", false]],
        "default" => ["class" => "",
            "host" => false, "port" => false, "db" => false, "user" => false, "pwd" => false, "timeout" => false,
            "ssl" => false, "persistent" => false, "compression" => true]
    ];

    /**
     * CacheConfig constructor.
     * @param ConfigValues $config
     */
    public function __construct(ConfigValues $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getOptionsDefault(): array
    {
        // TODO: Implement getOptionsDefault() method.
    }
}