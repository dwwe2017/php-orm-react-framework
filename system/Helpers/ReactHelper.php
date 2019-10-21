<?php


namespace Helpers;


use Configula\ConfigFactory;
use Configula\ConfigValues;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class ReactHelper
 * @package Helpers
 */
class ReactHelper
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var string
     */
    private $moduleBaseDir = "";

    /**
     * @var string
     */
    private $moduleBaseUrl = "";

    /**
     * @var string
     */
    private $entryPointsFile = "";

    /**
     * @var string
     */
    private $manifestFile = "";

    /**
     * @var ConfigValues
     */
    private $entryPoints;

    /**
     * @var ConfigValues
     */
    private $manifestData;

    /**
     * @var string
     */
    private $runtimeJsFile = "";

    /**
     * @var string
     */
    private $mainJsFile = "";

    /**
     * ReactHelper constructor.
     * @param string $moduleBaseDir
     * @param string $moduleBaseUrl
     */
    private function __construct(string $moduleBaseDir, string $moduleBaseUrl)
    {
        $this->moduleBaseDir = $moduleBaseDir;
        $this->moduleBaseUrl = $moduleBaseUrl;

        $this->entryPointsFile = sprintf("%s/views/entrypoints.json", $this->moduleBaseDir);
        $this->manifestFile = sprintf("%s/views/manifest.json", $this->moduleBaseDir);

        $this->manifestData = FileHelper::init($this->manifestFile)->isReadable()
            ? ConfigFactory::loadPath($this->manifestFile) : new ConfigValues([]);

        $this->mainJsFile = sprintf("%s/views%s", $this->moduleBaseDir,
            $this->getManifestData()->get("main.js", null)
        );

        $this->runtimeJsFile = sprintf("%s/views%s", $this->moduleBaseDir,
            $this->getManifestData()->get("runtime.js", null)
        );

        $this->entryPoints = FileHelper::init($this->entryPointsFile)->isReadable()
            ? ConfigFactory::loadPath($this->entryPointsFile) : new ConfigValues([]);
    }

    /**
     * @param string $moduleBaseDir
     * @param string $moduleBaseUrl
     * @return ReactHelper|null
     */
    public static final function init(string $moduleBaseDir, string $moduleBaseUrl)
    {
        if (is_null(self::$instance) || serialize($moduleBaseDir.$moduleBaseUrl) !== self::$instanceKey) {
            self::$instance = new self($moduleBaseDir, $moduleBaseUrl);
            self::$instanceKey = serialize($moduleBaseDir.$moduleBaseUrl);
        }

        return self::$instance;
    }

    /**
     * @return bool
     */
    public function usesReactJs()
    {
        if (!FileHelper::init($this->getRuntimeJsFile())->isReadable()) {
            return false;
        } elseif (!FileHelper::init($this->getMainJsFile())->isReadable()) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getEntryScriptTags()
    {
        $result = "";
        if($this->usesReactJs())
        {
            foreach ($this->getManifestData()->getArrayCopy() as $value){
                $result .= sprintf("<script src=\"%s/views%s\"></script>", substr($this->getModuleBaseUrl(), 1), $value);
            }
        }

        return $result;
    }

    /**
     * @return ConfigValues
     */
    protected function getEntryPoints(): ConfigValues
    {
        return $this->entryPoints;
    }

    /**
     * @return ConfigValues
     */
    protected function getManifestData(): ConfigValues
    {
        return $this->manifestData;
    }

    /**
     * @return string
     */
    public function getMainJsFile(): string
    {
        return $this->mainJsFile;
    }

    /**
     * @return string
     */
    public function getRuntimeJsFile(): string
    {
        return $this->runtimeJsFile;
    }

    /**
     * @return string
     */
    private function getModuleBaseUrl(): string
    {
        return $this->moduleBaseUrl;
    }
}
