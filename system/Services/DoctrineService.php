<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Services;


use Configula\ConfigValues;
use Controllers\AbstractBase;
use Doctrine\ORM\EntityManager;
use Exception;
use Exceptions\DoctrineException;
use Interfaces\ServiceInterfaces\VendorExtensionServiceInterface;
use Webmasters\Doctrine\Bootstrap as WDB;
use Webmasters\Doctrine\ORM\Util\OptionsCollection;

class DoctrineService extends WDB implements VendorExtensionServiceInterface
{
    /**
     * @var self|null
     */
    public static $instance = null;

    /**
     * @var string
     */
    private static $instanceKey = "";

    /**
     * @noinspection PhpMissingParentConstructorInspection
     * DoctrineService constructor.
     * @param ConfigValues $config
     * @param AbstractBase|null $controllerInstance
     * @throws DoctrineException
     */
    public function __construct(ConfigValues $config, AbstractBase $controllerInstance = null)
    {
        $connectionOptions = $config->get("connection_options");
        $applicationOptions = $config->get("doctrine_options");

        try {
            $this->setConnectionOptions($connectionOptions);
            $this->setApplicationOptions($applicationOptions);
            $this->errorMode();
        } catch (Exception $e) {
            throw new DoctrineException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param ConfigValues $config
     * @param AbstractBase|null $controllerInstance
     * @return DoctrineService|null
     * @throws DoctrineException
     */
    public static function init(ConfigValues $config, AbstractBase $controllerInstance = null)
    {
        if (is_null(self::$instance) || serialize(self::$instance) !== self::$instanceKey) {
            self::$instance = new self($config, $controllerInstance);
            self::$instanceKey = serialize(self::$instance);
        }

        return self::$instance;
    }

    /**
     * @return EntityManager|null
     */
    public function getEntityManager(): ?EntityManager
    {
        return parent::getEm();
    }

    /**
     * @param $options
     */
    protected function setApplicationOptions($options)
    {
        $this->applicationOptions = new OptionsCollection($options);
    }
}