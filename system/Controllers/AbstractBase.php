<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Controllers;


use Exceptions\CacheException;
use Handlers\ErrorHandler;
use Handlers\MinifyCssHandler;
use Handlers\MinifyJsHandler;
use Helpers\AbsolutePathHelper;
use Managers\ModuleManager;
use Managers\ServiceManager;
use Services\CacheService;
use Throwable;
use Traits\ControllerTraits\AbstractBaseTrait;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class Config
 * @package Controllers
 */
abstract class AbstractBase
{
    use AbstractBaseTrait;

    /**
     * AbstractBase constructor.
     * @param string $baseDir
     * @throws CacheException
     */
    public function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;
        $this->moduleBaseDir = $this->getBaseDir();

        $this->initModule();
        $this->initServices();
        $this->initHelpers();
        $this->initHandlers();
    }

    /**
     *
     */
    private function initModule()
    {
        $this->moduleManager = ModuleManager::init($this);
        $this->moduleBaseDir = $this->getModuleManager()->getModuleBaseDir();
        $this->config = $this->getModuleManager()->getConfig();
    }

    /**
     * @throws CacheException
     */
    private function initServices()
    {
        $this->serviceManager = ServiceManager::init($this->getModuleManager()); // !Only available for system

        /**
         * PhpFastCache services and fallback check
         * @author https://www.phpfastcache.com/
         * @see AbstractBaseTrait::getCacheService()
         * @see AbstractBaseTrait::getSystemCacheService()
         * @see AbstractBaseTrait::getModuleCacheService()
         * @internal AbstractBaseTrait::getModuleCacheService() => If no module controller is currently active, the system values are used
         * @see AbstractBaseTrait::systemCacheServiceHasFallback()
         * @see AbstractBaseTrait::moduleCacheServiceHasFallback()
         * @internal AbstractBaseTrait::moduleCacheServiceHasFallback() => If no module controller is currently active, the system values are used
         */
        $this->cacheService = $this->getServiceManager()->getCacheService(); // !Only available for system
        $this->systemCacheService = $this->getCacheService()->getCacheInstance(CacheService::CACHE_SYSTEM); // !Only available for system
        $this->systemCacheServiceHasFallback = $this->cacheService->hasFallback(); // !Only available for system
        $this->moduleCacheService = $this->getCacheService()->getCacheInstance(CacheService::CACHE_MODULE); // Available in modules
        $this->moduleCacheServiceHasFallback = $this->cacheService->hasFallback(); // !Only available for system

        /**
         * Gettext locale services
         * @author https://github.com/oscarotero/Gettext
         * @see AbstractBaseTrait::getLocaleService()
         * @see AbstractBaseTrait::getSystemLocaleService()
         * @see AbstractBaseTrait::getModuleLocaleService()
         */
        $this->localeService = $this->getServiceManager()->getLocaleService(); // !Only available for system
        $this->systemLocaleService = $this->getLocaleService()->getSystemTranslator(); // !Only available for system
        $this->moduleLocaleService = $this->getLocaleService()->getModuleTranslator(); // Available in modules

        /**
         * Logger service
         * @author https://github.com/Seldaek/monolog
         * @see AbstractBaseTrait::getLoggerService()
         */
        $this->loggerService = $this->getServiceManager()->getLoggerService();

        /**
         * Doctrine ORM services
         * @author https://www.doctrine-project.org/index.html
         * @see AbstractBaseTrait::getDoctrineService()
         * @see AbstractBaseTrait::getSystemDbService()
         * @see AbstractBaseTrait::getModuleDbService()
         * @internal AbstractBaseTrait::getModuleDbService() => If no module controller is currently active, the system values are used
         */
        $this->doctrineService = $this->getServiceManager()->getDoctrineService(); // !Only available for system
        $this->systemDbService = $this->getDoctrineService()->getSystemDoctrineService(); // !Only available for system
        $this->moduleDbService = $this->getDoctrineService()->getModuleDoctrineService(); // Available in modules

        /**
         * Twig template service
         * @author https://twig.symfony.com/
         * @see AbstractBaseTrait::getTemplateService()
         */
        $this->templateService = $this->getServiceManager()->getTemplateService(); // !Only available for system
    }

    /**
     *
     */
    private function initHandlers(): void
    {
        //Reinitialize error handler with logger instance for better persistence
        ErrorHandler::init($this->getConfig(), $this->getLoggerService());

        //Asset handlers
        $this->cssHandler = MinifyCssHandler::init($this->getConfig());
        $this->jsHandler = MinifyJsHandler::init($this->getConfig());
    }

    /**
     *
     */
    private function initHelpers(): void
    {
        $this->absolutePathHelper = AbsolutePathHelper::init($this->getBaseDir());
    }

    /**
     * @param string $action
     * @return void
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Throwable
     * @throws LoaderError
     */
    public function run(string $action)
    {
        $this->addContext('action', $action);

        $methodName = $action . 'Action';

        if (method_exists($this, $methodName)) {
            $this->setTemplate($methodName);
            $this->$methodName();
        } else {
            $this->render404();
        }

        $this->render();
    }

    /**
     *
     */
    public function render404(): void
    {
        header('HTTP/1.0 404 Not Found');
        /** @noinspection PhpIncludeInspection */
        $error = require_once $this->getAbsolutePathHelper()->{"templates/Handlers/errors/error404.php"};
        exit($error);
    }

    /**
     * @param string|null $module
     * @param string|null $controller
     * @param string|null $action
     */
    protected function redirect(?string $module = null, ?string $controller = null, ?string $action = null): void
    {
        $params = [];

        if (!empty($module)) {
            $params[] = 'module=' . $module;
        }

        if (!empty($controller)) {
            $params[] = 'controller=' . $controller;
        }

        if (!empty($action)) {
            $params[] = 'action=' . $action;
        }

        $to = '';
        if (!empty($params)) {
            $to = '?' . implode('&', $params);
        }

        header('Location: index.php' . $to);
        exit;
    }

    /**
     * @throws Throwable
     */
    protected function render(): void
    {
        $this->getCssHandler()->compileAndGet();
        $this->getJsHandler()->compileAndGet();

        $this->addContext("message", $this->getMessage());
        $this->addContext("minified_css", $this->getCssHandler()->getDefaultMinifyCssFile(true));
        $this->addContext("minified_js", $this->getJsHandler()->getDefaultMinifyJsFile(true));

        echo $this->template->render($this->context);
    }
}
