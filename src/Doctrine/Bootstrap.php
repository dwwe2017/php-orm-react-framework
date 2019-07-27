<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Doctrine;


use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\EntityManager;
use Exception;
use Exceptions\DoctrineException;
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
     * @var bool
     */
    protected $debugMode = false;

    /**
     * @var string
     */
    protected $defaultEntityDir = "";

    /**
     * @noinspection PhpMissingParentConstructorInspection
     * Bootstrap constructor.
     * @param \Core\Bootstrap $config
     * @throws DoctrineException
     */
    public function __construct(\Core\Bootstrap $config)
    {
        $this->baseDir = $config->getBaseDir();

        $this->debugMode = $config->isDebugMode();

        $connectionOption = $config->getConnectionOption();

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

        $configDefaultEntityDir = sprintf("%s/src/Entities", $this->baseDir);

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
            "debug_mode" => $this->debugMode,
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
     * @param \Core\Bootstrap $config
     * @return Bootstrap|null
     * @throws DoctrineException
     */
    public static function init(\Core\Bootstrap $config)
    {
        if (self::$singletonInstance == null) {
            self::$singletonInstance = new Bootstrap($config);
        }

        return self::$singletonInstance;
    }

    /**
     * @param $options
     * @throws DoctrineException
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

        $defaultTemplateCompilationPath = sprintf("%s/data/cache/compilation", $this->baseDir);

        $defaultTemplateCompilationOptions = array(
            "debug" => $options["debug_mode"],
            "charset " => "utf-8",
            "base_template_class" => "\\Twig\\Template",
            "cache" => $defaultTemplateCompilationPath,
            "auto_reload" => !$options["debug_mode"],
            "strict_variables" => !$options["debug_mode"],
            "autoescape" => "html",
            "optimizations" => $options["debug_mode"] ? -1 : 0,
        );

        if (!isset($options["template_options"]))
        {
            $options["template_options"] = $defaultTemplateCompilationOptions;
        }
        else
        {
            $options["template_options"] += $defaultTemplateCompilationOptions;
        }

        $templateCompilationPath = $options["template_options"]["cache"];

        if(!file_exists($templateCompilationPath))
        {
            if(!@mkdir($templateCompilationPath, 0777, true))
            {
                throw new DoctrineException(sprintf("The required directory '%s' for template compilation can not be found and/or be created, please check the directory permissions or create it manually.", $templateCompilationPath), E_ERROR);
            }
        }

        if(!is_writable($templateCompilationPath))
        {
            if(!@chmod($templateCompilationPath, 0777))
            {
                throw new DoctrineException(sprintf("The required directory '%s' for template compilation can not be written, please check the directory permissions.", $templateCompilationPath), E_ERROR);
            }
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
     * @return string
     */
    public function getBaseDir(): string
    {
        return $this->baseDir;
    }
}