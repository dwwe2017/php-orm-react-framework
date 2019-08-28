<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Services;


use Doctrine\ORM\EntityManager;
use Interfaces\ServiceInterfaces\VendorExtensionServiceInterface;
use Managers\ModuleManager;
use Managers\ServiceManager;
use Traits\ServiceTraits\VendorExtensionInitServiceTraits;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;
use Webmasters\Doctrine\Bootstrap as WDB;
use Webmasters\Doctrine\ORM\Util\OptionsCollection;

class DoctrineService extends WDB implements VendorExtensionServiceInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitServiceTraits;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var string|null
     */
    private $currentConnectionOption;

    /**
     * @var self
     */
    private $moduleDoctrineService;

    /**
     * @var self
     */
    private $systemDoctrineService;

    /**
     * @noinspection PhpMissingParentConstructorInspection
     * DoctrineService constructor.
     * @param ModuleManager $moduleManager
     */
    public final function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @see ServiceManager::__construct()
     * @param ModuleManager $moduleManager
     * @return DoctrineService|null
     */
    public static final function init(ModuleManager $moduleManager)
    {
        if (is_null(self::$instance) || serialize($moduleManager) !== self::$instanceKey) {
            self::$instance = new self($moduleManager);
            self::$instanceKey = serialize($moduleManager);
        }

        self::$instance->setSystemDoctrineService();
        self::$instance->setModuleDoctrineService();
        return self::$instance;
    }

    /**
     * ORM for Entites of the module
     * @see DoctrineService::init()
     */
    public final function setModuleDoctrineService(): void
    {
        $config = $this->moduleManager->getConfig();
        $connectionOption = $config->get("connection_option", "default");
        $this->currentConnectionOption = $connectionOption;
        $connectionOptions = $config->get(sprintf("connection_options.%s", $connectionOption));
        $applicationOptions = $config->get("doctrine_options.module");
        $this->moduleDoctrineService = new static($this->moduleManager);
        $this->moduleDoctrineService->setConnectionOptions($connectionOptions);
        $this->moduleDoctrineService->setApplicationOptions($applicationOptions);
        $this->moduleDoctrineService->errorMode();
    }

    /**
     * ORM for Entites of the module
     * @see DoctrineService::setModuleDoctrineService()
     * @return DoctrineService
     */
    public final function getModuleDoctrineService(): DoctrineService
    {
        return $this->moduleDoctrineService;
    }

    /**
     * ORM for Entites of the system
     * @see DoctrineService::init()
     */
    public final function setSystemDoctrineService(): void
    {
        $config = $this->moduleManager->getConfig();
        $connectionOption = $config->get("connection_option", "default");
        $this->currentConnectionOption = $connectionOption;
        $connectionOptions = $config->get(sprintf("connection_options.%s", $connectionOption));
        $applicationOptions = $config->get("doctrine_options.system");
        $this->systemDoctrineService = new static($this->moduleManager);
        $this->systemDoctrineService->setConnectionOptions($connectionOptions);
        $this->systemDoctrineService->setApplicationOptions($applicationOptions);
        $this->systemDoctrineService->errorMode();
    }

    /**
     * @param null $connectionOption
     * @return EntityManager
     */
    public final function getEntityManager($connectionOption = null)
    {
        if (!is_null($connectionOption) && $this->currentConnectionOption !== $connectionOption) {
            $config = $this->moduleManager->getConfig();
            $connectionOptions = $config->get(sprintf("connection_options.%s", $connectionOption));
            $this->currentConnectionOption = $connectionOptions;
            $this->setConnectionOptions($connectionOptions);
        }

        return parent::getEm();
    }

    /**
     * ORM for Entites of the system
     * @see DoctrineService::setSystemDoctrineService()
     * @return DoctrineService
     */
    public final function getSystemDoctrineService(): DoctrineService
    {
        return $this->systemDoctrineService;
    }

    /**
     * @param $options
     */
    protected final function setApplicationOptions($options)
    {
        $this->applicationOptions = new OptionsCollection($options);
    }
}