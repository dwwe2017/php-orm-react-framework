<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Bootstrap;


use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\EntityManager;
use Exception;
use Exceptions\BootstrapException;
use Webmasters\Doctrine\Bootstrap as WDB;
use Webmasters\Doctrine\ORM\Util\OptionsCollection;

/**
 * Class Bootstrap
 * @package Bootstrap
 */
class Bootstrap extends WDB
{
    /**
     * @var string
     */
    protected $baseDir = "";

    /**
     * @var array
     */
    protected $configDefault = [];

    /**
     * @var array
     */
    protected $defaultOptions = [];

    /**
     * @var string
     */
    protected $connectionOption = "";

    /**
     * @noinspection PhpMissingParentConstructorInspection
     * Bootstrap constructor.
     * @param string $baseDir
     * @param string $connection_option
     * @throws BootstrapException
     */
    public function __construct(string $baseDir, $connection_option = "default")
    {
        $this->baseDir = $baseDir;

        $this->connectionOption = $connection_option;

        $configDefaultPath = sprintf("%s/config/default-config.php", $this->baseDir);

        if(!file_exists($configDefaultPath))
        {
            throw new BootstrapException("The global configuration file default-config.php in the config directory was not found", E_ERROR);
        }

        /** @noinspection PhpIncludeInspection */
        $this->configDefault = include_once $configDefaultPath;

        if(empty($this->configDefault) || !is_array($this->configDefault) || !key_exists("connection_options", $this->configDefault))
        {
            throw new BootstrapException("The global configuration file config-default.php in the config directory did not specify a valid database connection", E_ERROR);
        }
        elseif(!key_exists($this->connectionOption, $this->configDefault["connection_options"]) || count($this->configDefault["connection_options"][$this->connectionOption]) < 6)
        {
            throw new BootstrapException(sprintf("In the config global configuration file config-default.php in the config directory, no valid database connection was set under the field '%s'", $this->connectionOption), E_ERROR);
        }
        else
        {
           $this->connectionOptions = $this->configDefault["connection_options"][$this->connectionOption];
        }

        $this->defaultOptions = [
            'autogenerate_proxy_classes' => true,
            "base_dir" => $this->baseDir,
            'debug_mode' => true,
            'em_class' => '\\Webmasters\\Doctrine\\ORM\\EntityManager',
            "entity_dir" => $this->baseDir . "/src/Entities",
            'gedmo_ext' => ['Timestampable'],
            "proxy_dir" => sprintf("%s/data/proxy", $this->baseDir),
            "vendor_dir" => sprintf("%s/vendor", $this->baseDir)
        ];

        if(key_exists("application_options", $this->configDefault))
        {
            $this->applicationOptions = $this->configDefault["application_options"];
        }

        $this->applicationOptions += $this->defaultOptions;

        try
        {
            $this->setConnectionOptions($this->connectionOptions);
            $this->setApplicationOptions($this->applicationOptions);
            $this->errorMode();
        }
        catch (Exception $e)
        {
            throw new BootstrapException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param string $baseDir
     * @param string $connection_option
     * @return Bootstrap|null
     * @throws BootstrapException
     */
    public static function init(string $baseDir, $connection_option = "default")
    {
        if (self::$singletonInstance == null) {
            self::$singletonInstance = new Bootstrap($baseDir, $connection_option);
        }

        return self::$singletonInstance;
    }

    /**
     * @param $options
     */
    protected function setApplicationOptions($options): void
    {
        if (!isset($options["entity_namespace"]))
        {
            $options["entity_namespace"] = basename($options["entity_dir"]);
        }

        if (!isset($options["cache"]))
        {
            $cacheDriver = new ArrayCache();

            if (!$options["debug_mode"])
            {
                if(function_exists("apc_store"))
                {
                    $cacheDriver = new ApcuCache();
                }
                else
                {
                    $filesystemCacheDirExists = true;
                    $filesystemCacheDirIsWritable = true;

                    $filesystemCacheDir = sprintf("%s/data/cache", $this->baseDir);

                    if(!file_exists($filesystemCacheDir))
                    {
                        $filesystemCacheDirExists = @mkdir($filesystemCacheDir, 0777, true);
                    }

                    if(!is_writable($filesystemCacheDir))
                    {
                        $filesystemCacheDirIsWritable = @chmod($filesystemCacheDir, 0777);
                    }

                    if($filesystemCacheDirExists && $filesystemCacheDirIsWritable)
                    {
                        $cacheDriver = new FilesystemCache(sprintf("%s/data/cache", $this->baseDir));
                    }
                }
            }

            $options["cache"] = $cacheDriver;
        }

        $this->applicationOptions = new OptionsCollection($options);
    }

    /**
     * @return EntityManager|null
     */
    public function getEntityManager(): ?EntityManager
    {
        return parent::getEm();
    }

    /**
     * @return array|mixed
     */
    public function getConnectionOptions()
    {
        return $this->connectionOptions;
    }

    /**
     * @return array
     */
    public function getConfigDefault(): array
    {
        return $this->configDefault;
    }

    /**
     * @return string
     */
    public function getBaseDir(): string
    {
        return $this->baseDir;
    }

    /**
     * @return string
     */
    public function getConnectionOption(): string
    {
        return $this->connectionOption;
    }
}