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
use Doctrine\ORM\Tools\SchemaTool;
use Exception;
use Exceptions\DoctrineException;
use Helpers\ArrayHelper;
use Helpers\DirHelper;
use Helpers\FileHelper;
use Helpers\StringHelper;
use Interfaces\ServiceInterfaces\VendorExtensionServiceInterface;
use Managers\ModuleManager;
use PDO;
use Traits\ControllerTraits\AbstractBaseTrait;
use Traits\ServiceTraits\VendorExtensionInitServiceTraits;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;
use Webmasters\Doctrine\Bootstrap as WDB;
use Webmasters\Doctrine\ORM\Util\OptionsCollection;

/**
 * Class DoctrineService
 * @package Services
 */
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
     * @param ModuleManager $moduleManager
     * @return DoctrineService|null
     * @throws DoctrineException
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
     * @throws DoctrineException
     */
    public final function setModuleDoctrineService(): void
    {
        $config = $this->moduleManager->getConfig();
        $connectionOption = $config->get("connection_option", "default");
        $this->currentConnectionOption = $connectionOption;
        $connectionOptions = $config->get(sprintf("connection_options.%s", $connectionOption));
        $applicationOptions = $config->get("doctrine_options.module");
        $this->moduleDoctrineService = new static($this->moduleManager);
        $this->moduleDoctrineService->setApplicationOptions($applicationOptions);
        $this->moduleDoctrineService->setConnectionOptions($connectionOptions);
        $this->moduleDoctrineService->errorMode();
    }

    /**
     * ORM for Entities of the module
     * @return DoctrineService
     * @see DoctrineService::setModuleDoctrineService()
     * @see AbstractBaseTrait::getModuleDbService()
     */
    public final function getModuleDoctrineService(): DoctrineService
    {
        return $this->moduleDoctrineService;
    }

    /**
     * @throws DoctrineException
     */
    public final function setSystemDoctrineService(): void
    {
        $config = $this->moduleManager->getConfig();
        $connectionOption = $config->get("connection_option", "default");
        $this->currentConnectionOption = $connectionOption;
        $connectionOptions = $config->get(sprintf("connection_options.%s", $connectionOption));
        $applicationOptions = $config->get("doctrine_options.system");
        $this->systemDoctrineService = new static($this->moduleManager);
        $this->systemDoctrineService->setApplicationOptions($applicationOptions);
        $this->systemDoctrineService->setConnectionOptions($connectionOptions);
        $this->systemDoctrineService->errorMode();
    }

    /**
     * @param null $connectionOption
     * @return EntityManager
     * @throws DoctrineException
     * @example $this->getModuleDbService()->getEntityManager("module");
     */
    public final function getEntityManager($connectionOption = null)
    {
        if (!is_null($connectionOption) && $this->currentConnectionOption !== $connectionOption) {
            $config = $this->moduleManager->getConfig();
            $connectionOptions = $config->get(sprintf("connection_options.%s", $connectionOption));
            $this->currentConnectionOption = $connectionOption;
            $this->setConnectionOptions($connectionOptions);
        }

        return parent::getEm();
    }

    /**
     * ORM for Entities of the system
     * @return DoctrineService
     * @see DoctrineService::setSystemDoctrineService()
     * @see AbstractBaseTrait::getSystemDbService()
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

    /**
     * @param $options
     * @throws DoctrineException
     */
    protected final function setConnectionOptions($options): void
    {
        parent::setConnectionOptions($options);
        $options = ArrayHelper::init($options);

        /**
         * @internal Both for the system and for modules, if the default driver "pdo_sqlite"
         * is selected, the database is automatically created if it does not exist.
         */
        if (strcasecmp($options->get("driver", false), "pdo_sqlite") == 0) {
            $sqLitePath = $options->get("path", false);
            if ($sqLitePath && !FileHelper::init($sqLitePath)->isWritable()) {
                try {
                    $em = $this->getEntityManager($this->currentConnectionOption);
                    $tool = new SchemaTool($em);
                    $schemas = $this->getEntitySchemas($em);
                    if (empty($schemas)) {
                        return;
                    }

                    /**
                     * @internal SQLite connection is always UTF-8
                     * @see http://www.alberton.info/dbms_charset_settings_explained.html
                     */
                    $pdo = new PDO(sprintf("sqlite:%s", $sqLitePath));
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    foreach ($tool->getCreateSchemaSql($schemas) as $query) {
                        $pdo->exec($query);
                    }
                } catch (Exception $e) {
                    throw new DoctrineException($e->getMessage(), $e->getCode(), $e);
                }
            }
        }
    }

    /**
     * Prepare array with all necessary metadata of the entities to be able to automatically create the database if necessary.
     * @param EntityManager $em
     * @return array
     * @see DoctrineService::setConnectionOptions()
     */
    private function getEntitySchemas(EntityManager $em)
    {
        try {
            $result = [];
            $entityDir = $this->getOption("entity_dir");
            $entityNamespace = $this->getOption("entity_namespace");
            $entities = DirHelper::init($entityDir)->getScan([".php"]);
            if (!empty($entities)) {
                foreach ($entities as $entity) {
                    $entityName = StringHelper::init($entity)->replace(".php", "")->getString();
                    $result[] = $em->getClassMetadata(sprintf("\\%s\\%s", $entityNamespace, $entityName));
                }

                return $result;
            }
        } catch (Exception $e) {
        }
        return $result;
    }
}