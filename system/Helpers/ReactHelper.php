<?php


namespace Helpers;


use Configula\ConfigValues;
use Controllers\AbstractBase;
use Handlers\MinifyCssHandler;
use Handlers\MinifyJsHandler;
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
     * @var ConfigValues
     */
    private $entryPoints;

    /**
     * ReactHelper constructor.
     * @param string $moduleBaseDir
     * @param string $moduleBaseUrl
     */
    private function __construct(string $moduleBaseDir, string $moduleBaseUrl)
    {
        $this->moduleBaseDir = $moduleBaseDir;
        $this->moduleBaseUrl = $moduleBaseUrl;

        $this->entryPointsFile = FileHelper::init(sprintf("%s/views/entrypoints.json", $this->moduleBaseDir));
        $this->entryPoints = $this->entryPointsFile->isReadable()
            ? new ConfigValues(json_decode($this->entryPointsFile->getContents("{}"), true)) : new ConfigValues([]);
    }

    /**
     * @param string $moduleBaseDir
     * @param string $moduleBaseUrl
     * @return ReactHelper|null
     */
    public static final function init(string $moduleBaseDir, string $moduleBaseUrl)
    {
        if (is_null(self::$instance) || serialize($moduleBaseDir . $moduleBaseUrl) !== self::$instanceKey) {
            self::$instance = new self($moduleBaseDir, $moduleBaseUrl);
            self::$instanceKey = serialize($moduleBaseDir . $moduleBaseUrl);
        }

        return self::$instance;
    }

    /**
     * @return bool
     */
    public function usesReactJs()
    {
        if (!$this->entryPointsFile->isReadable()) {
            return false;
        }

        return true;
    }

    /**
     * @param string $tag
     * @param bool $asArray
     * @return array|string
     */
    public function getEntryScriptTags($tag = "js", $asArray = false)
    {
        $result = $asArray ? [] : "";
        if ($this->usesReactJs()) {
            $entrypoints = $this->getEntryPoints()->get(sprintf("entrypoints.main.%s", strtolower($tag)), []);
            if (empty($entrypoints)) {
                return $result;
            }

            foreach ($entrypoints as $value) {
                if ($asArray) {
                    $result[] = sprintf("%s/views%s", substr($this->getModuleBaseUrl(), 1), $value);
                    continue;
                }

                switch ($tag) {
                    case "css":
                        $result .= sprintf("<link href=\"%s/views%s\" rel=\"stylesheet\" type=\"text/css\" />", substr($this->getModuleBaseUrl(), 1), $value);
                        break;
                    default:
                        $result .= sprintf("<script src=\"%s/views%s\"></script>", substr($this->getModuleBaseUrl(), 1), $value);
                        break;
                }
            }
        }

        return $result;
    }

    /**
     * @param MinifyCssHandler $minifyCssHandler
     * @internal Works perfect
     * @see AbstractBase::preRun()
     */
    public function addReactCss(MinifyCssHandler $minifyCssHandler): void
    {
        $reactCss = $this->getEntryScriptTags("css", true);
        if (empty($reactCss)) {
            return;
        }

        foreach ($reactCss as $css) {
            $minifyCssHandler->addCss($css);
        }
    }

    /**
     * @param MinifyJsHandler $minifyJsHandler
     * @internal Unfortunately it does not work, because the code compiled by the webpack and probably too complex for minimizing :)
     */
    public function addReactJs(MinifyJsHandler $minifyJsHandler): void
    {
        $reactJs = $this->getEntryScriptTags("js", true);
        if (empty($reactJs)) {
            return;
        }

        foreach ($reactJs as $js) {
            $minifyJsHandler->addJsContent($js);
        }
    }

    /**
     * @return ConfigValues
     */
    protected function getEntryPoints(): ConfigValues
    {
        return $this->entryPoints;
    }

    /**
     * @return string
     */
    private function getModuleBaseUrl(): string
    {
        return $this->moduleBaseUrl;
    }
}
