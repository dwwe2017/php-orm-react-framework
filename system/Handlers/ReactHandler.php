<?php

namespace Handlers;


use Configula\ConfigValues;
use Controllers\AbstractBase;
use Controllers\PublicController;
use Controllers\RestrictedController;
use Helpers\FileHelper;
use Managers\ModuleManager;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class ReactHandler
 * @package Handlers
 */
class ReactHandler
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var string
     */
    private string $baseDir = "";

    /**
     * @var string
     */
    private string $moduleBaseDir = "";

    /**
     * @var string
     */
    private string $moduleBaseUrl = "";

    /**
     * @var string
     */
    private ?string $moduleControllerShortName = "";

    /**
     * @var FileHelper|null
     */
    private ?FileHelper $systemControllerEntryPointFile;

    /**
     * @var ConfigValues
     */
    private ConfigValues $systemControllerEntryPointConfig;

    /**
     * @var array
     */
    private $systemControllerEntryPoints = [];

    /**
     * @var FileHelper|null
     */
    private ?FileHelper $moduleControllerEntryPointFile;

    /**
     * @var FileHelper|null
     */
    private ?FileHelper $moduleControllerManifestFile;

    /**
     * @var ConfigValues
     */
    private ConfigValues $moduleControllerEntryPointConfig;

    /**
     * @var ConfigValues
     */
    private ConfigValues $moduleControllerManifestConfig;

    /**
     * @var array
     */
    private array $moduleControllerJsEntryPoints = [];

    /**
     * @var array
     */
    private array $moduleControllerCssEntryPoints = [];

    /**
     * @var string
     */
    private string $moduleControllerAction = "";

    /**
     * ReactHandler constructor.
     * @param AbstractBase $controllerInstance
     * @param ModuleManager $moduleManager
     */
    private final function __construct(AbstractBase $controllerInstance, ModuleManager $moduleManager)
    {
        $this->baseDir = $controllerInstance->getBaseDir();
        $this->systemControllerEntryPointFile = FileHelper::init(sprintf("%s/assets/js/react/entrypoints.json", $this->baseDir));
        $this->systemControllerEntryPointConfig = $this->systemControllerEntryPointFile->isReadable()
            ? new ConfigValues(json_decode($this->systemControllerEntryPointFile->getContents("[]"), true)) : new ConfigValues([]);

        if ($controllerInstance instanceof RestrictedController) {
            $this->systemControllerEntryPoints = $this->systemControllerEntryPointConfig->get("entrypoints.RestrictedController.js", []);
        } elseif ($controllerInstance instanceof PublicController) {
            $this->systemControllerEntryPoints = $this->systemControllerEntryPointConfig->get("entrypoints.PublicController.js", []);
        }

        if (!empty($this->systemControllerEntryPoints)) {
            $this->systemControllerEntryPoints = array_map([$this, "addRelativeBaseAssetJsReactPath"], $this->systemControllerEntryPoints);
        }

        $this->moduleBaseDir = $controllerInstance->getModuleBaseDir();
        $this->moduleBaseUrl = $moduleManager->getBaseUrl();
        $this->moduleControllerShortName = $moduleManager->getControllerShortName();
        $this->moduleControllerEntryPointFile = FileHelper::init(sprintf("%s/views/entrypoints.json", $this->moduleBaseDir));
        $this->moduleControllerEntryPointConfig = $this->moduleControllerEntryPointFile->isReadable()
            ? new ConfigValues(json_decode($this->moduleControllerEntryPointFile->getContents("{}"), true)) : new ConfigValues([]);

        /**
         * Manifest to be for the current controller/action is a related view
         */
        $this->moduleControllerManifestFile = FileHelper::init(sprintf("%s/views/manifest.json", $this->moduleBaseDir));
        $this->moduleControllerManifestConfig = $this->moduleControllerManifestFile->isReadable()
            ? new ConfigValues(json_decode($this->moduleControllerManifestFile->getContents("{}"), true)) : new ConfigValues([]);
    }

    /**
     * @return bool
     */
    public function hasModuleEntryPoint()
    {
        $manifestTag = ucfirst(sprintf("%s/%s.js", $this->moduleControllerShortName, $this->getModuleControllerAction()));
        return $this->moduleControllerManifestConfig->has($manifestTag);
    }

    /**
     * @param $file
     * @return string
     */
    public function addRelativeBaseAssetJsReactPath($file)
    {
        return sprintf("assets/js/react%s", $file);
    }

    /**
     * @param $file
     * @return string
     */
    public function addRelativeModuleViewsPath($file)
    {
        $path = sprintf("%s/views%s", substr($this->moduleBaseUrl, 1), $file);
        return empty($this->moduleBaseUrl) ? substr($path, 1) : $path;
    }

    /**
     * @param AbstractBase $controllerInstance
     * @param ModuleManager $moduleManager
     * @return ReactHandler|null
     */
    public static final function init(AbstractBase $controllerInstance, ModuleManager $moduleManager)
    {
        if (is_null(self::$instance) || serialize(get_class($controllerInstance) . get_class($moduleManager)) !== self::$instanceKey) {
            self::$instance = new self($controllerInstance, $moduleManager);
            self::$instanceKey = serialize(get_class($controllerInstance) . get_class($moduleManager));
        }

        return self::$instance;
    }

    /**
     * @param MinifyCssHandler $minifyCssHandler
     * @internal Works perfect
     * @see AbstractBase::preRun()
     */
    public function addReactCss(MinifyCssHandler $minifyCssHandler): void
    {
        if (empty($this->getModuleControllerCssEntryPoints())) {
            return;
        }

        foreach ($this->getModuleControllerCssEntryPoints() as $css) {
            $minifyCssHandler->addCss($css);
        }
    }

    /**
     * @return array
     */
    public function getSystemControllerEntryPoints(): array
    {
        return $this->systemControllerEntryPoints;
    }

    /**
     * @return array
     */
    public function getModuleControllerCssEntryPoints(): array
    {
        if (empty($this->getModuleControllerAction())) {
            return [];
        }

        $moduleEntryPointTag = ucfirst(sprintf("%s/%s", $this->moduleControllerShortName, $this->getModuleControllerAction()));
        $this->moduleControllerCssEntryPoints = $this->moduleControllerEntryPointConfig->get(sprintf("entrypoints.%s.css", $moduleEntryPointTag), []);
        if (!empty($this->moduleControllerCssEntryPoints)) {
            $this->moduleControllerCssEntryPoints = array_map([$this, "addRelativeModuleViewsPath"], $this->moduleControllerCssEntryPoints);
        }

        return $this->moduleControllerCssEntryPoints;
    }

    /**
     * @return array
     */
    public function getModuleControllerJsEntryPoints(): array
    {
        if (empty($this->getModuleControllerAction())) {
            return [];
        }

        $moduleEntryPointTag = ucfirst(sprintf("%s/%s", $this->moduleControllerShortName, $this->getModuleControllerAction()));
        $this->moduleControllerJsEntryPoints = $this->moduleControllerEntryPointConfig->get(sprintf("entrypoints.%s.js", $moduleEntryPointTag), []);
        if (!empty($this->moduleControllerJsEntryPoints)) {
            $this->moduleControllerJsEntryPoints = array_map([$this, "addRelativeModuleViewsPath"], $this->moduleControllerJsEntryPoints);
        }

        return $this->moduleControllerJsEntryPoints;
    }

    /**
     * @return string
     */
    public function getModuleControllerAction(): string
    {
        return $this->moduleControllerAction;
    }

    /**
     * @param string $moduleControllerAction
     */
    public function setModuleControllerAction(string $moduleControllerAction): void
    {
        $this->moduleControllerAction = $moduleControllerAction;
    }
}
