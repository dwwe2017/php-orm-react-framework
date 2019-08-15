<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Traits\ControllerTraits;


use Configula\ConfigValues;
use Doctrine\ORM\EntityManager;
use Handlers\MinifyCssHandler;
use Handlers\MinifyJsHandler;
use Helpers\AbsolutePathHelper;
use Managers\ModuleManager;
use Managers\ServiceManager;
use Monolog\Logger;
use Services\DoctrineService;
use Services\TemplateService;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\TemplateWrapper;

/**
 * Trait AbstractBaseTrait
 * @package Traits
 */
trait AbstractBaseTrait
{
    /**
     * @var string
     */
    private $baseDir = "";

    /**
     * @var ConfigValues
     */
    private $config;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * @var array
     */
    private $context = [];

    /**
     * @var TemplateService
     */
    private $templateService;

    /**
     * @var TemplateWrapper
     */
    private $template;

    /**
     * @var string
     */
    private $view = "";

    /**
     * @var MinifyCssHandler
     */
    private $cssHandler;

    /**
     * @var MinifyJsHandler
     */
    private $jsHandler;

    /**
     * @var Logger
     */
    private $loggerService;

    /**
     * @var DoctrineService
     */
    private $doctrineService;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var AbsolutePathHelper;
     */
    private $absolutePathHelper;

    /**
     * @return ServiceManager
     */
    private function getServiceManager(): ServiceManager
    {
        return $this->serviceManager;
    }

    /**
     * @return ModuleManager
     */
    private function getModuleManager(): ModuleManager
    {
        return $this->moduleManager;
    }

    /**
     * @return string
     */
    public function getBaseDir(): string
    {
        return $this->baseDir;
    }

    /**
     * @return ConfigValues
     */
    protected function getConfig(): ConfigValues
    {
        return $this->config;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @return string|null
     */
    protected function getModuleShortName(): ?string
    {
        return $this->getModuleManager()->getModuleShortName();
    }

    /**
     * @return string|null
     */
    protected function getControllerShortName(): ?string
    {
        return $this->getModuleManager()->getControllerShortName();
    }

    /**
     * @return string|null
     */
    protected function getModuleViewsDir(): ?string
    {
        $moduleName = $this->getModuleShortName();
        return $moduleName ? sprintf("modules/%s/views", $moduleName) : null;
    }

    /**
     * @param string $templatePath
     */
    protected function setView(string $templatePath): void
    {
        $controller = $this->getControllerShortName();
        $this->view .= $controller . '/' . $templatePath . '.tpl.twig';
    }

    /**
     * @param string|null $templatePath
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function setTemplate(?string $templatePath = null): void
    {
        if (!is_null($templatePath)) {
            $this->setView($templatePath);
        }

        $this->template = $this->templateService->getEnvironment()->load($this->getView());
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return $this->view;
    }

    /**
     * @param $key
     * @param $value
     */
    protected function addContext($key, $value): void
    {
        $this->context[$key] = $value;
    }

    /**
     * @param $message
     */
    protected function setMessage(string $message): void
    {
        $_SESSION['message'] = $message; // Set flash message
    }

    /**
     * @return string|null
     */
    protected function getMessage(): ?string
    {
        $message = null;

        if (isset($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        return $message;
    }

    /**
     * @return array
     */
    protected function getContext(): array
    {
        return $this->context;
    }

    /**
     * @return DoctrineService
     */
    protected function getDoctrineService(): DoctrineService
    {
        return $this->doctrineService;
    }

    /**
     * @return Logger
     */
    protected function getLoggerService(): Logger
    {
        return $this->loggerService;
    }

    /**
     * @return TemplateService
     */
    protected function getTemplateService(): TemplateService
    {
        return $this->templateService;
    }

    /**
     * @return AbsolutePathHelper
     */
    protected function getAbsolutePathHelper(): AbsolutePathHelper
    {
        return $this->absolutePathHelper;
    }
}