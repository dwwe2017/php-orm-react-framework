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
use Exception;
use Exceptions\DoctrineException;
use Interfaces\ServiceInterfaces\VendorExtensionServiceInterface;
use Managers\ModuleManager;
use Traits\ServiceTraits\VendorExtensionInitServiceTraits;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;
use Webmasters\Doctrine\Bootstrap as WDB;
use Webmasters\Doctrine\ORM\Util\OptionsCollection;

class DoctrineService extends WDB implements VendorExtensionServiceInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitServiceTraits;

    /**
     * @noinspection PhpMissingParentConstructorInspection
     * DoctrineService constructor.
     * @param ModuleManager|null $moduleManager
     * @throws DoctrineException
     */
    public function __construct(ModuleManager $moduleManager)
    {
        $config = $moduleManager->getConfig();
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