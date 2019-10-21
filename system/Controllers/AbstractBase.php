<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Controllers;


use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Exceptions\CacheException;
use Exceptions\DoctrineException;
use Exceptions\FileFactoryException;
use Exceptions\InvalidArgumentException;
use Exceptions\MinifyCssException;
use Exceptions\MinifyJsException;
use Exceptions\SessionException;
use Handlers\CacheHandler;
use Handlers\ErrorHandler;
use Handlers\MinifyCssHandler;
use Handlers\MinifyJsHandler;
use Handlers\NavigationHandler;
use Handlers\RequestHandler;
use Handlers\SessionHandler;
use Helpers\AbsolutePathHelper;
use Helpers\FileHelper;
use Helpers\ReactHelper;
use Interfaces\ControllerInterfaces\XmlControllerInterface;
use Managers\ModuleManager;
use Managers\ServiceManager;
use ReflectionClass;
use ReflectionException;
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
     * @throws AnnotationException
     * @throws CacheException
     * @throws DoctrineException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws SessionException
     */
    public function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;
        $this->moduleBaseDir = $this->getBaseDir();

        $this->initModule();
        $this->initServices();
        $this->initHelpers();
        $this->initHandlers();
        $this->initSettings();
    }

    /**
     *
     */
    private function initModule()
    {
        $this->moduleManager = ModuleManager::init($this);
        $this->moduleBaseDir = $this->getModuleManager()->getModuleBaseDir();
        $this->config = $this->getModuleManager()->getConfig();
        $this->debugMode = $this->getConfig()->get("debug_mode", false) === true;
    }

    /**
     * @throws CacheException
     * @throws DoctrineException
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
        //todo! add to system monitoring messages
        $this->addContext("system_cache_service_has_fallback", $this->systemCacheServiceHasFallback());

        $this->moduleCacheService = $this->getCacheService()->getCacheInstance(CacheService::CACHE_MODULE); // !Only available for system
        $this->moduleCacheServiceHasFallback = $this->cacheService->hasFallback(); // !Only available for system
        //todo! add to system monitoring messages
        $this->addContext("module_cache_service_has_fallback", $this->moduleCacheServiceHasFallback());

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
     * @throws AnnotationException
     * @throws ReflectionException
     * @throws SessionException
     * @throws InvalidArgumentException
     */
    private function initHandlers(): void
    {
        /**
         * Error handler
         * Reinitialize error handler with logger instance for better persistence
         * !Not dynamically modifiable
         */
        ErrorHandler::init($this->getConfig(), $this->getLoggerService());

        /**
         * Request handler
         * @see AbstractBaseTrait::getRequestHandler() // Available in modules
         */
        $this->requestHandler = RequestHandler::init($this);

        /**
         * Session handler
         */
        $this->sessionHandler = SessionHandler::init($this->getSystemDbService());

        /**
         * @see RequestHandler::isXml()
         * @see RequestHandler::isXmlRequest()
         */
        if ($this->getRequestHandler()->isXml()) {
            return;
        }

        /**
         * Cache handlers
         * @see AbstractBaseTrait::getSystemCacheHandler() // !Only available for system
         * @see AbstractBaseTrait::getModuleCacheHandler() // Available in modules
         */
        $this->systemCacheHandler = CacheHandler::init($this->getSystemCacheService());
        $this->moduleCacheHandler = CacheHandler::init($this->getModuleCacheService());

        /**
         * Asset handlers
         * @see AbstractBaseTrait::getCssHandler() // !Only available for system
         * @see AbstractBaseTrait::getJsHandler() // !Only available for system
         */
        $this->cssHandler = MinifyCssHandler::init($this->getConfig());
        $this->jsHandler = MinifyJsHandler::init($this->getConfig());

        /**
         * Navigation handler
         * @see AbstractBaseTrait::getNavigationHandler() // !Only available for system
         */
        $this->navigationHandler = NavigationHandler::init($this, $this->getSessionHandler());
    }

    /**
     * Make final settings after the system has been initialized
     */
    private function initSettings()
    {
        $userSession = $this->getSessionHandler()->getUser();

        if ($userSession) {
            $this->getLocaleService()->setLanguage($userSession->getLocale());
            $this->getModuleLocaleService()->setLanguage($userSession->getLocale());
        }
    }

    /**
     * @throws AnnotationException
     * @throws ReflectionException
     */
    private function initHelpers(): void
    {
        /**
         * Path helper
         * @see AbstractBaseTrait::getAbsolutePathHelper() // Available in modules
         */
        $this->absolutePathHelper = AbsolutePathHelper::init($this->getBaseDir()); // Available in modules

        /**
         * ReactHelper
         * @see AbstractBase::run()
         * @see https://github.com/dwwe2017/tsi2-module-skeletton
         */
        $this->reactHelper = ReactHelper::init($this->getModuleBaseDir(), $this->getModuleManager()->getBaseUrl());

        /**
         * Reflection Helper
         * @see AbstractBaseTrait::getReflectionHelper() // !Only available for system
         */
        $this->reflectionHelper = new ReflectionClass($this);

        /**
         * Annotation Helper
         * @see AbstractBaseTrait::getAnnotationReader() // !Only available for system
         */
        $this->annotationReader = new AnnotationReader();
    }

    /**
     * @param string $action
     * @return void
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Throwable
     * @throws LoaderError
     * @internal Divided for better inheritance possibilities
     */
    public function run(string $action)
    {
        $this->preRun($action);
    }

    /**
     * @param string $action
     * @throws LoaderError
     * @throws MinifyCssException
     * @throws MinifyJsException
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function preRun(string $action): void
    {
        $methodName = sprintf("%sAction", $action);

        if ($this->getReactHelper()->usesReactJs()) {
            $this->reactJs = $this->getReactHelper()->getEntryScriptTags();
            $action = "index";
        } elseif (!method_exists($this, $methodName)) {
            $this->render404();
        }

        $this->addContext('action', $action);

        $this->betRun($action);
    }

    /**
     * @param string $action
     * @throws LoaderError
     * @throws MinifyCssException
     * @throws MinifyJsException
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function betRun(string $action)
    {
        /**
         * For any functions that can be performed between the process
         * @see RestrictedController::betRun()
         */
        $this->postRun($action);
    }

    /**
     * @param string $action
     * @throws LoaderError
     * @throws MinifyCssException
     * @throws MinifyJsException
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function postRun(string $action): void
    {
        $methodName = sprintf("%sAction", $action);

        /**
         * @internal Here also the correct view is automatically set
         */
        $this->setTemplate($methodName);

        /**
         * @internal Auto-inclusion for Javascript
         * @see ModuleManager::getJsAssetsPath()
         * @see ModuleManager::getMethodJsAction()
         */
        $this->addJs($this->getModuleManager()->getMethodJsAction($methodName, true));

        /**
         * @internal Auto-inclusion for CSS
         * @see ModuleManager::getCssAssetsPath()
         * @see ModuleManager::getMethodCssAction()
         */
        $this->addCss($this->getModuleManager()->getMethodCssAction($methodName, true));

        /**
         * Run method
         */
        $this->$methodName();

        /**
         * Render template and views
         */
        $this->render();
    }

    /**
     *
     */
    public function render404(): void
    {
        if ($this->getRequestHandler()->isXml()) {
            header(XmlControllerInterface::HEADER_ERROR_404);
            header(XmlControllerInterface::HEADER_CONTENT_TYPE_JSON);
            $this->addContext("error", "Not Found");
            die(json_encode($this->getContext()));
        } else {
            header('HTTP/1.0 404 Not Found');
            /** @noinspection PhpIncludeInspection */
            $html = include_once $this->getAbsolutePathHelper()->get("templates/Handlers/errors/error404.php");
            exit($html ?? "Not Found");
        }
    }

    /**
     * @param bool $loginRedirect
     */
    public function render403($loginRedirect = false): void
    {
        if ($this->getRequestHandler()->isXml()) {
            header(XmlControllerInterface::HEADER_ERROR_403);
            header(XmlControllerInterface::HEADER_CONTENT_TYPE_JSON);
            $this->addContext("error", "Forbidden");
            die(json_encode($this->getContext()));
        } elseif (!$loginRedirect) {
            header('HTTP/1.0 403 Forbidden');
            /** @noinspection PhpIncludeInspection */
            $html = include_once $this->getAbsolutePathHelper()->get("templates/Handlers/errors/error403.php");
            exit($html ?? "Forbidden");
        } else {
            /**
             * @see PublicController::loginAction()
             */
            $this->redirect(null, "public", "login", array(
                "redirect" => urlencode($this->getRequestHandler()->getRequestUrl())
            ));
        }
    }

    /**
     *
     */
    protected final function renderEntry()
    {
        $this->redirect($this->getModuleManager()->getEntryModule(), "index", "index");
    }

    /**
     * @param string|null $module
     * @param string|null $controller
     * @param string|null $action
     * @param array $querys
     * @param string $tab
     */
    protected function redirect(?string $module = null, ?string $controller = null, ?string $action = null, array $querys = [], $tab = ""): void
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

        if (!empty($querys)) {
            foreach ($querys as $key => $query)
                $params[] = $key . '=' . $query;
        }

        $to = '';
        if (!empty($params)) {
            $to = '?' . implode('&', $params);
        }

        header('Location: index.php' . $to . $tab);
        exit;
    }

    /**
     * @throws MinifyCssException
     * @throws MinifyJsException
     */
    protected function render(): void
    {
        /**
         * Necessary Environment vars
         */
        $this->addContext("base_url", $this->getModuleManager()->getBaseUrl(true));
        $this->addContext("module_id", $this->getModuleManager()->getModuleShortName());
        $this->addContext("lang_code", $this->getLocaleService()->getLanguageCode());

        /**
         * Flash messages
         */
        $this->addContext("message", $this->getMessage());

        /**
         * CSS vars
         * @see AbstractBaseTrait::getCssHandler()
         */
        $this->getCssHandler()->compileAndGet();
        $this->addContext("minified_css", $this->getCssHandler()
            ->getDefaultMinifyCssFile(true)
        );

        /**
         * JS vars
         * @see AbstractBaseTrait::getJsHandler()
         */
        $this->getJsHandler()->compileAndGet();
        $this->addContext("minified_js", $this->getJsHandler()
            ->getDefaultMinifyJsFile(true)
        );

        /**
         * Navigation vars
         * @see AbstractBaseTrait::getNavigationHandler()
         */
        $this->addContext("navigation_routes",
            $this->getNavigationRoutes()
        );

        /**
         * Render Twig
         * @see AbstractBaseTrait::getTemplateService()
         */
        echo $this->template->render($this->context);
    }
}
