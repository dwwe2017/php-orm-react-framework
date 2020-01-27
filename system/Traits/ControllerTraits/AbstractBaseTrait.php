<?php

namespace Traits\ControllerTraits;


use Configula\ConfigValues;
use Controllers\PublicController;
use Controllers\RestrictedController;
use Controllers\SettingsController;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Gettext\GettextTranslator;
use Gettext\Translations;
use Gettext\Translator;
use Handlers\CacheHandler;
use Handlers\BufferHandler;
use Handlers\MinifyCssHandler;
use Handlers\MinifyJsHandler;
use Handlers\NavigationHandler;
use Handlers\ReactHandler;
use Handlers\RequestHandler;
use Handlers\SessionHandler;
use Helpers\AbsolutePathHelper;
use Helpers\EntityViewHelper;
use Managers\ModuleManager;
use Managers\ServiceManager;
use Mike4ip\HttpAuth;
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
    private string $baseDir = "";

    /**
     * @var ConfigValues
     */
    private ConfigValues $config;

    /**
     * @var bool
     */
    private bool $debugMode = false;

    /**
     * @var ModuleManager
     */
    private ModuleManager $moduleManager;

    /**
     * @var string
     */
    private string $moduleBaseDir = "";

    /**
     * @var ServiceManager
     */
    private ServiceManager $serviceManager;

    /**
     * @var array
     */
    private array $context = [];

    /**
     * @var TemplateService
     */
    private TemplateService $templateService;

    /**
     * @var BufferHandler
     */
    private BufferHandler $bufferHandler;

    /**
     * @var TemplateWrapper
     */
    private TemplateWrapper $template;

    /**
     * @var string
     */
    private string $view = "";

    /**
     * @var MinifyCssHandler
     */
    private MinifyCssHandler $cssHandler;

    /**
     * @var MinifyJsHandler
     */
    private MinifyJsHandler $jsHandler;

    /**
     * @var ReactHandler
     */
    private ReactHandler $reactHandler;

    /**
     * @var array
     */
    private array $pureJs = [];

    /**
     * @var SessionHandler
     */
    private SessionHandler $sessionHandler;

    /**
     * @var CacheHandler
     */
    private CacheHandler $systemCacheHandler;

    /**
     * @var CacheHandler
     */
    private CacheHandler $moduleCacheHandler;

    /**
     * @var RequestHandler
     */
    private RequestHandler $requestHandler;

    /**
     * @var NavigationHandler
     */
    private NavigationHandler $navigationHandler;

    /**
     * @var CacheService
     */
    private CacheService $cacheService;

    /**
     * @var ExtendedCacheItemPoolInterface
     */
    private ExtendedCacheItemPoolInterface $systemCacheService;

    /**
     * @var bool
     */
    private bool $systemCacheServiceHasFallback = false;

    /**
     * @var ExtendedCacheItemPoolInterface
     */
    private ExtendedCacheItemPoolInterface $moduleCacheService;

    /**
     * @var bool
     */
    private bool $moduleCacheServiceHasFallback = false;

    /**
     * @var LocaleService
     */
    private LocaleService $localeService;

    /**
     * @var Translator
     */
    private Translator $systemLocaleService;

    /**
     * @var GettextTranslator
     */
    private GettextTranslator $moduleLocaleService;

    /**
     * @var Logger
     */
    private Logger $loggerService;

    /**
     * @var DoctrineService
     */
    private DoctrineService $doctrineService;

    /**
     * @var DoctrineService
     */
    private DoctrineService $systemDbService;

    /**
     * @var DoctrineService
     */
    private DoctrineService $moduleDbService;

    /**
     * @var EntityManager
     */
    private EntityManager $entityManager;

    /**
     * @var AbsolutePathHelper;
     */
    private AbsolutePathHelper $absolutePathHelper;

    /**
     * @var ReflectionClass
     */
    private ReflectionClass $reflectionHelper;

    /**
     * @var AnnotationReader
     */
    private AnnotationReader $annotationReader;

    /**
     * @var EntityViewHelper
     */
    private EntityViewHelper $viewHelper;

    /**
     * @var string
     */
    private string $navigationRoute = NavigationHandler::PUBLIC_NAV;

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
     * @param array $context
     */
    protected final function contextPush(array $context): void
    {
        foreach ($context as $key => $value){
            $this->context[$key] = $value;
        }
    }

    /**
     *
     */
    protected final function contextClear(): void
    {
        $this->context = [];
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
     * @param bool $fromSystem
     * @example $this->addCss("assets/css/custom.css")
     */
    protected final function addCss(?string $fileOrString, bool $codeAsString = false, bool $fromSystem = false): void
    {
        if (is_null($fileOrString)) {
            return;
        }

        $fileOrString = $codeAsString ? $fileOrString
            : sprintf("%s/%s", $fromSystem ? $this->getBaseDir() : $this->getModuleBaseDir(), $fileOrString);

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
     * @param bool $fromSystem
     * @example $this->addJs("assets/js/custom.js")
     */
    protected final function addJs(?string $fileOrString, bool $codeAsString = false, bool $fromSystem = false): void
    {
        if (is_null($fileOrString)) {
            return;
        }

        $fileOrString = $codeAsString ? $fileOrString
            : sprintf("%s/%s", $fromSystem ? $this->getBaseDir() : $this->getModuleBaseDir(), $fileOrString);

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
     * @return array
     */
    protected function getBreadcrumbRoutes(): array
    {
        return $this->getNavigationHandler()->getBreadcrumbRoutes();
    }

    /**
     * @return BufferHandler
     * @internal Whole methods and their results can be buffered
     * @example
     * $this->getBufferHandler()->setMaxLifetime(60)
     * $this->getBufferHandler()->setObject($this->getNavigationHandler())->getRoutes(NavigationHandler::RESTRICTED_NAV)
     * $this->getBufferHandler()->getBufferItem()->getExpirationDate()->format("d.m.Y H:i:s")
     */
    protected function getBufferHandler(): BufferHandler
    {
        return $this->bufferHandler;
    }

    /**
     * @param string|null $localeCode
     * @return Translations
     */
    protected function getTranslations(?string $localeCode = null)
    {
        return $this->getLocaleService()->getTranslations($localeCode);
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

        if ($this->getReactHandler()->hasModuleEntryPoint()) {
            $this->view = "layout.react.body.page.content.tpl.twig";
        } else {
            $this->view = $controller . '/' . $templatePath . '.tpl.twig';
        }
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
         * Breadcrumbs are set here by default, but can be adjusted individually
         * from each controller or method via the "breadcrumb_routes" context field.
         */
        $this->addContext("breadcrumb_routes", $this->getBreadcrumbRoutes());

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
     * @return ReactHandler
     */
    private function getReactHandler(): ReactHandler
    {
        return $this->reactHandler;
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
    private function isDebugMode(): bool
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
     * @return HttpAuth
     */
    public function getHttpAuthWrapper()
    {
        if (isset($_SERVER["HTTP_AUTHORIZATION"]) && !empty($_SERVER["HTTP_AUTHORIZATION"])) {
            list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
        }

        return new HttpAuth();
    }
}
