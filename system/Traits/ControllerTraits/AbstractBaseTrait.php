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
use Controllers\PublicController;
use Controllers\RestrictedController;
use Controllers\SettingsController;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Gettext\GettextTranslator;
use Gettext\Translator;
use Handlers\CacheHandler;
use Handlers\MinifyCssHandler;
use Handlers\MinifyJsHandler;
use Handlers\NavigationHandler;
use Handlers\RequestHandler;
use Handlers\SessionHandler;
use Helpers\AbsolutePathHelper;
use Helpers\EntityViewHelper;
use Managers\ModuleManager;
use Managers\ServiceManager;
use Monolog\Logger;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException;
use ReflectionClass;
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
     * @var bool
     */
    private $debugMode = false;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var string
     */
    private $moduleBaseDir = "";

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
     * @var SessionHandler
     */
    private $sessionHandler;

    /**
     * @var CacheHandler
     */
    private $systemCacheHandler;

    /**
     * @var CacheHandler
     */
    private $moduleCacheHandler;

    /**
     * @var RequestHandler
     */
    private $requestHandler;

    /**
     * @var NavigationHandler
     */
    private $navigationHandler;

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
     * @var Translator
     */
    private $systemLocaleService;

    /**
     * @var GettextTranslator
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
     * @var ReflectionClass
     */
    private $reflectionHelper;

    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @var EntityViewHelper
     */
    private $viewHelper;

    /**
     * @var string
     */
    private $navigationRoute = NavigationHandler::PUBLIC_NAV;

    /**
     * @return string
     */
    public final function getBaseDir(): string
    {
        return $this->baseDir;
    }

    /**
     * @return string
     * @internal Fallback if no module controller is active or empty string is AbstractBaseTrait::getBaseDir
     * @see ModuleManager::getModuleBaseDir()
     */
    public final function getModuleBaseDir(): string
    {
        return $this->moduleBaseDir;
    }

    /**
     * PROTECTED AREA
     */

    /**
     * @param $key
     * @param $value
     */
    protected final function addContext($key, $value): void
    {
        $this->context[$key] = $value;
    }

    /**
     * @param $message
     */
    protected final function setMessage(string $message): void
    {
        $_SESSION['message'] = $message; // Set flash message
    }

    /**
     * @return string|null
     */
    protected final function getMessage(): ?string
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
    protected final function getContext(): array
    {
        return $this->context;
    }

    /**
     * @return Logger
     * @example $this->getLoggerService()->error("Error message")
     */
    protected final function getLoggerService(): Logger
    {
        return $this->loggerService;
    }

    /**
     * @return AbsolutePathHelper
     * @example $this->getAbsolutePathHelper()->get("relative/path/to/example.png") => "/var/www/.../htdocs/tsi/.."
     */
    protected final function getAbsolutePathHelper(): AbsolutePathHelper
    {
        return $this->absolutePathHelper;
    }

    /**
     * Returns the Doctrine 2 instance of each module currently active
     * @return DoctrineService
     * @example $this->getModuleDbService()->getEntityManager()
     */
    protected final function getModuleDbService(): DoctrineService
    {
        return $this->moduleDbService;
    }

    /**
     * For translations in Twig-Template-files use the function {% trans%},
     * which only contains the language files of the respective module.
     * @return GettextTranslator
     * @see LocaleService::getModuleTranslator()
     * @example $this->getModuleLocaleService()->setLanguage("de_DE")
     */
    protected final function getModuleLocaleService()
    {
        return $this->moduleLocaleService;
    }

    /**
     * @param string|null $fileOrString
     * @param bool $codeAsString
     * @example $this->addCss("assets/css/custom.css")
     */
    protected final function addCss(?string $fileOrString, bool $codeAsString = false)
    {
        if (is_null($fileOrString)) {
            return;
        }

        $fileOrString = $codeAsString ? $fileOrString
            : sprintf("%s/%s", $this->getModuleBaseDir(), $fileOrString);

        $this->getCssHandler()->addCss($fileOrString, $codeAsString);
    }

    /**
     * @param array $cssFiles
     * @example $this->setCss([
     *  "assets/css/custom1.css",
     *  "assets/css/custom2.css",
     *  "assets/css/custom3.css",
     *  ".."
     * ])
     */
    protected final function setCss(array $cssFiles)
    {
        $files = [];
        foreach ($cssFiles as $file) {
            $files[] = sprintf("%s/%s", $this->getModuleBaseDir(), $file);
        }

        $this->getCssHandler()->setCssContent($files);
    }

    /**
     * @param string|null $fileOrString
     * @param bool $codeAsString
     * @example $this->addJs("assets/js/custom.js")
     */
    protected final function addJs(?string $fileOrString, bool $codeAsString = false): void
    {
        if (is_null($fileOrString)) {
            return;
        }

        $fileOrString = $codeAsString ? $fileOrString
            : sprintf("%s/%s", $this->getModuleBaseDir(), $fileOrString);

        $this->getJsHandler()->addJsContent($fileOrString, $codeAsString);
    }

    /**
     * @param array $jsFiles
     * @example $this->setJs([
     *  "assets/js/custom1.js",
     *  "assets/js/custom2.js",
     *  "assets/js/custom3.js",
     *  ".."
     * ])
     */
    protected final function setJs(array $jsFiles)
    {
        $files = [];
        foreach ($jsFiles as $file) {
            $files[] = sprintf("%s/%s", $this->getModuleBaseDir(), $file);
        }

        $this->getJsHandler()->setJsContent($files);
    }

    /**
     * @return RequestHandler
     * @example $this->getRequestHandler()->isPost()
     * @example $this->getRequestHandler()->getPost()->getArrayCopy()
     * @example $this->getRequestHandler()->getQuery()->controller
     * @example $this->getRequestHandler()->getQuery()->get("action", "default")
     */
    protected final function getRequestHandler(): RequestHandler
    {
        return $this->requestHandler;
    }

    /**
     * @return string
     */
    protected final function getControllerAccessLevel()
    {
        if ($this instanceof RestrictedController) {
            return "restricted";
        } elseif ($this instanceof PublicController) {
            return "public";
        } elseif ($this instanceof SettingsController) {
            return "settings";
        } else {
            return "misc";
        }
    }

    /**
     * @return SessionHandler
     */
    protected final function getSessionHandler(): SessionHandler
    {
        return $this->sessionHandler;
    }

    /**
     * @return ReflectionClass
     */
    protected function getReflectionHelper(): ReflectionClass
    {
        return $this->reflectionHelper;
    }

    /**
     * @return CacheHandler
     */
    protected function getModuleCacheHandler(): CacheHandler
    {
        return $this->moduleCacheHandler;
    }

    /**
     * @param string $navigationRoute
     */
    protected function setNavigationRoute(string $navigationRoute): void
    {
        $this->navigationRoute = $navigationRoute;
    }

    /**
     * @return string
     */
    protected function getNavigationRoute(): string
    {
        return $this->navigationRoute;
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
     * @example $this->setView($templatePath)
     */
    private function setView(string $templatePath): void
    {
        $controller = $this->getModuleManager()->getControllerShortName();
        $this->view = $controller . '/' . $templatePath . '.tpl.twig';
    }

    /**
     * @param string|null $templatePath
     * @throws LoaderError !Silent if debug mode is inactive
     * @throws RuntimeError !Silent if debug mode is inactive
     * @throws SyntaxError !Silent if debug mode is inactive
     * @example $this->setTemplate($methodName)
     */
    private function setTemplate(?string $templatePath = null): void
    {
        if (!is_null($templatePath)) {
            $this->setView($templatePath);
        }

        /**
         * @internal Silent exceptions if debug mode is inactive
         */
        if (!$this->isDebugMode()) {
            try {
                $this->template = $this->templateService->getEnvironment()->load($this->getView());
            } catch (LoaderError|RuntimeError|SyntaxError $e) {
                $this->getLoggerService()->error($e->getMessage(), $e->getTrace());
                $this->render404();
            }

            return;
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
     * @return Translator
     * @see LocaleService::getSystemTranslator()
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
     * @return ExtendedCacheItemPoolInterface
     */
    private final function getModuleCacheService()
    {
        return $this->moduleCacheService;
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

    /**
     * @return NavigationHandler
     */
    private function getNavigationHandler(): NavigationHandler
    {
        return $this->navigationHandler;
    }

    /**
     * @return AnnotationReader
     */
    private function getAnnotationReader(): AnnotationReader
    {
        return $this->annotationReader;
    }

    /**
     * @return CacheHandler
     */
    private function getSystemCacheHandler(): CacheHandler
    {
        return $this->systemCacheHandler;
    }

    /**
     * @return array
     */
    private function getNavigationRoutes(): array
    {
        return $this->getNavigationHandler()->getRoutes($this->getNavigationRoute());
    }

    /**
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->debugMode;
    }

    /**
     * @param $object
     * @param string $method
     * @param array $args
     * @param int $expiration
     * @return mixed
     * @example $this->fromSystemCache($this->getNavigationHandler(), "getRoutes", [], 60)
     */
    private function fromSystemCache($object, string $method, array $args = array(), $expiration = 3600)
    {
        try {
            $itemKey = session_id();
            $itemKey .= get_class($object);
            $itemKey .= $method;
            $itemKey .= serialize($args);

            $systemCache = $this->getSystemCacheHandler();
            $item = $systemCache->getItem($itemKey);

            if (!$item->isHit()) {
                $result = call_user_func_array([$object, $method], $args);
                $item->set($result)->expiresAfter($expiration);
                $systemCache->save($item);

                return $result;
            }

            return $item->get();
        } catch (PhpfastcacheInvalidArgumentException $e) {
            $this->getLoggerService()->error($e->getMessage(), $e->getTrace());
        }

        $result = call_user_func_array([$object, $method], $args);
        return $result;
    }

    /**
     * CACHED AREA - PUBLIC
     */

    //***

    /**
     * CACHED AREA - PROTECTED
     */

    //***

    /**
     * CACHED AREA - PRIVATE
     */

    //***
}