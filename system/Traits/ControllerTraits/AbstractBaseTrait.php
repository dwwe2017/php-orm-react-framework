<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Traits\ControllerTraits;


use Configs\DefaultConfig;
use Configs\DoctrineConfig;
use Configs\LoggerConfig;
use Configs\TemplateConfig;
use Doctrine\ORM\EntityManager;
use Handlers\MinifyCssHandler;
use Handlers\MinifyJsHandler;
use Monolog\Logger;
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
     * @var DefaultConfig|null
     */
    private $coreConfig = null;

    /**
     * @var array
     */
    private $context = [];

    /**
     * @var string
     */
    private $templatePath = "";

    /**
     * @var TemplateConfig
     */
    private $twig;

    /**
     * @var TemplateWrapper
     */
    private $template;

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
    private $logger;

    /**
     * @var int
     */
    private $log_level = LoggerConfig::ERROR;

    /**
     * @var string
     */
    private $connectionOption = "default";

    /**
     * @var DoctrineConfig|null
     */
    private $doctrine = null;

    /**
     * @var EntityManager|null
     */
    private $entityManager = null;

    /**
     * @return DoctrineConfig|null
     */
    protected function getDoctrine(): ?DoctrineConfig
    {
        return $this->doctrine;
    }

    /**
     * @return string
     */
    protected function getBaseDir(): string
    {
        return $this->baseDir;
    }

    /**
     * @return DefaultConfig
     */
    protected function getCoreConfig(): DefaultConfig
    {
        return $this->coreConfig;
    }

    /**
     * @return string
     */
    protected function getConnectionOption(): string
    {
        return $this->connectionOption;
    }

    /**
     * @param string $connectionOption
     */
    protected function setConnectionOption(string $connectionOption): void
    {
        $this->connectionOption = $connectionOption;
    }

    /**
     * @return EntityManager|null
     */
    protected function getEntityManager(): ?EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @return string|null
     */
    protected function getModuleName(): ?string
    {
        $className = get_class($this); // i.e. Controllers\MainController or Controllers\Backend\MainController
        $isModule =  (strcasecmp(substr($className, 0, 7), "Modules") === 0);
        $nameParts = $isModule ? explode("\\", $className) : null;
        return $isModule ? $nameParts[1] : null;
    }

    /**
     * @return string|null
     */
    protected function getModuleTemplatesPath(): ?string
    {
        $moduleName = $this->getModuleName();
        return $moduleName ? sprintf("modules/%s/templates", $moduleName) : null;
    }

    /**
     * @return string|null
     */
    protected function getControllerShortName(): ?string
    {
        $className = get_class($this); // i.e. Controllers\MainController or Controllers\Backend\MainController
        return preg_replace('/^([A-Za-z]+\\\)+/', '', $className); // i.e. MainController
    }

    /**
     * @param string $templatePath
     */
    protected function setTemplatePath(string $templatePath): void
    {
        $controller = $this->getControllerShortName();
        $this->templatePath .= $controller . '/' . $templatePath . '.tpl.twig';
    }

    /**
     * @param string|null $templatePath
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function setTemplate(?string $templatePath = null): void
    {
        if(!is_null($templatePath))
        {
            $this->setTemplatePath($templatePath);
        }

        $this->template = $this->twig->getTemplateWrapper($this->getTemplatePath());
    }

    /**
     * @return string|null
     */
    protected function getTemplatePath(): ?string
    {
        return $this->templatePath;
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

        if (isset($_SESSION['message']))
        {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        return $message;
    }

    /**
     * @return Logger
     */
    protected function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @return int
     */
    public function getLogLevel(): int
    {
        return $this->log_level;
    }
}