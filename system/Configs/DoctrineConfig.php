<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Configs;


use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\EntityManager;
use Exception;
use Exceptions\DoctrineException;
use Interfaces\ConfigInterfaces\DoctrineConfigInterface;
use Webmasters\Doctrine\Bootstrap as WDB;
use Webmasters\Doctrine\ORM\Util\OptionsCollection;

/**
 * Class DoctrineConfig
 * @package Configs
 */
class DoctrineConfig extends WDB implements DoctrineConfigInterface
{
    /**
     * @var string
     */
    protected $baseDir = "";

    /**
     * @noinspection PhpMissingParentConstructorInspection
     * DoctrineConfig constructor.
     * @param DefaultConfig $config
     * @param string $connectionOption
     * @throws DoctrineException
     */
    public function __construct(DefaultConfig $config, $connectionOption = "default")
    {
        $this->baseDir = $config->getBaseDir();

        $debugMode = $config->isDebugMode();

        $connectionOptions = $config->getProperties("connection_options");

        $configDefaultPath = $config->getConfigDefaultPath();

        if(empty($connectionOptions))
        {
            throw new DoctrineException(sprintf("The global configuration file '%s' did not specify a valid database connection", $configDefaultPath), E_ERROR);
        }
        elseif(!key_exists($connectionOption, $connectionOptions) || count($connectionOptions[$connectionOption]) < 6)
        {
            throw new DoctrineException(sprintf("The '%s' field of the global configuration file '%s' does not contain a valid database connection", $connectionOption, $configDefaultPath), E_ERROR);
        }
        else
        {
            $connectionOptions = $connectionOptions[$connectionOption];
        }

        $configDefaultEntityDir = sprintf("%s/system/Entities", $this->baseDir);

        if(!file_exists($configDefaultEntityDir))
        {
            throw new DoctrineException(sprintf("The directory '%s' does not exist, please check the installation manually", $configDefaultEntityDir), E_ERROR);
        }
        if(!is_readable($configDefaultEntityDir))
        {
            throw new DoctrineException(sprintf("The directory '%s' can not be loaded, please check the directory permissions", $configDefaultEntityDir), E_ERROR);
        }

        $applicationOptions = $config->getProperties("doctrine_options");

        // Merge with custom configuration parameters
        $applicationOptions += [
            "autogenerate_proxy_classes" => true,
            "debug_mode" => $debugMode,
            "proxy_dir" => sprintf("%s/data/proxy/%s", $this->baseDir, $connectionOption)
        ];

        // Non-customizable parameters
        $applicationOptions["base_dir"] = $this->baseDir;
        $applicationOptions["em_class"] = \Webmasters\Doctrine\ORM\EntityManager::class;
        $applicationOptions["entity_dir"] = $configDefaultEntityDir;
        $applicationOptions["gedmo_ext"] = ["Timestampable"];
        $applicationOptions["vendor_dir"] = $config->getDefaultVendorDir();

        $configProxyDir = $applicationOptions["proxy_dir"];

        if(!file_exists($configProxyDir))
        {
            if(!@mkdir($configProxyDir, 0777, true))
            {
                throw new DoctrineException(sprintf("The required proxy directory '%s' can not be created, please check the directory permissions or create it manually.", $configProxyDir), E_ERROR);
            }
        }

        if(!is_writable($configProxyDir))
        {
            if(!@chmod($configProxyDir, 0777))
            {
                throw new DoctrineException(sprintf("The required proxy directory '%s' can not be written, please check the directory permissions.", $configProxyDir), E_ERROR);
            }
        }

        try
        {
            $this->setConnectionOptions($connectionOptions);
            $this->setApplicationOptions($applicationOptions);
            $this->errorMode();
        }
        catch (Exception $e)
        {
            throw new DoctrineException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param DefaultConfig $config
     * @param string $connectionOption
     * @return DoctrineConfig|null
     * @throws DoctrineException
     */
    public static function init(DefaultConfig $config, $connectionOption = "default")
    {
        if (self::$singletonInstance == null) {
            self::$singletonInstance = new DoctrineConfig($config, $connectionOption);
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

                    $filesystemCacheDir = sprintf("%s/data/cache/doctrine", $this->baseDir);

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
}