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
use Gettext\GettextTranslator;
use Gettext\Translator;
use Handlers\MinifyCssHandler;
use Handlers\MinifyJsHandler;
use Helpers\AbsolutePathHelper;
use Managers\ModuleManager;
use Managers\ServiceManager;
use Monolog\Logger;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Services\CacheService;
use Services\DoctrineService;
use Services\LocaleService;
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
     * @var CacheService
     */
    private $cacheService;

    /**
     * @var ExtendedCacheItemPoolInterface
     */
    private $systemCacheService;

    /**
     * @var bool
     */
    private $systemCacheServiceHasFallback = false;

    /**
     * @var ExtendedCacheItemPoolInterface
     */
    private $moduleCacheService;

    /**
     * @var bool
     */
    private $moduleCacheServiceHasFallback = false;

    /**
     * @var LocaleService
     */
    private $localeService;

    /**
     * @var
     */
    private $systemLocaleService;

    /**
     * @var
     */
    private $moduleLocaleService;

    /**
     * @var Logger
     */
    private $loggerService;

    /**
     * @var DoctrineService
     */
    private $doctrineService;

    /**
     * @var DoctrineService
     */
    private $systemDbService;

    /**
     * @var DoctrineService
     */
    private $moduleDbService;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var AbsolutePathHelper;
     */
    private $absolutePathHelper;

    /**
     * @return string
     */
    public function getBaseDir(): string
    {
        return $this->baseDir;
    }

    /**
     * PROTECTED AREA
     */

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
     * @return Logger
     */
    protected function getLoggerService(): Logger
    {
        return $this->loggerService;
    }

    /**
     * @return AbsolutePathHelper
     */
    protected function getAbsolutePathHelper(): AbsolutePathHelper
    {
        return $this->absolutePathHelper;
    }

    /**
     * @return DoctrineService
     */
    protected function getModuleDbService(): DoctrineService
    {
        return $this->moduleDbService;
    }

    /**
     * For translations in Twig-Template-files use the function {% trans%},
     * which only contains the language files of the respective module.
     * @see LocaleService::getModuleTranslator()
     * @return GettextTranslator
     */
    protected function getModuleLocaleService()
    {
        return $this->moduleLocaleService;
    }

    /**
     * @return ExtendedCacheItemPoolInterface
     */
    protected function getModuleCacheService()
    {
        return $this->moduleCacheService;
    }

    /**
     * PRIVATE AREA
     */

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
     * @return ConfigValues
     */
    private function getConfig(): ConfigValues
    {
        return $this->config;
    }

    /**
     * @param string $templatePath
     */
    private function setView(string $templatePath): void
    {
        $controller = $this->getModuleManager()->getControllerShortName();
        $this->view .= $controller . '/' . $templatePath . '.tpl.twig';
    }

    /**
     * @param string|null $templatePath
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function setTemplate(?string $templatePath = null): void
    {
        if (!is_null($templatePath)) {
            $this->setView($templatePath);
        }

        $this->template = $this->templateService->getEnvironment()->load($this->getView());
    }

    /**
     * @return string
     */
    private function getView(): string
    {
        return $this->view;
    }

    /**
     * @return DoctrineService
     */
    private function getDoctrineService(): DoctrineService
    {
        return $this->doctrineService;
    }

    /**
     * @return DoctrineService
     */
    private function getSystemDbService(): DoctrineService
    {
        return $this->systemDbService;
    }

    /**
     * @return MinifyCssHandler
     */
    private function getCssHandler(): MinifyCssHandler
    {
        return $this->cssHandler;
    }

    /**
     * @return MinifyJsHandler
     */
    private function getJsHandler(): MinifyJsHandler
    {
        return $this->jsHandler;
    }

    /**
     * @return LocaleService
     */
    private function getLocaleService(): LocaleService
    {
        return $this->localeService;
    }

    /**
     * For translations in the controller, use the global functions __() and n__(),
     * each of which uses the language files of the system and the module.
     * @see LocaleService::getSystemTranslator()
     * @return Translator
     */
    private function getSystemLocaleService()
    {
        return $this->systemLocaleService;
    }

    /**
     * @return TemplateService
     */
    private function getTemplateService(): TemplateService
    {
        return $this->templateService;
    }

    /**
     * @return CacheService
     */
    private function getCacheService(): CacheService
    {
        return $this->cacheService;
    }

    /**
     * @return ExtendedCacheItemPoolInterface
     */
    private function getSystemCacheService()
    {
        return $this->systemCacheService;
    }

    /**
     * @return bool
     */
    private function systemCacheServiceHasFallback(): bool
    {
        return $this->systemCacheServiceHasFallback;
    }

    /**
     * @return bool
     */
    private function moduleCacheServiceHasFallback(): bool
    {
        return $this->moduleCacheServiceHasFallback;
    }
}