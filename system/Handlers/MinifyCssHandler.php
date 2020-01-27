<?php

namespace Handlers;


use Configula\ConfigValues;
use CssMin;
use Exception;
use Exceptions\MinifyCssException;
use Helpers\DirHelper;
use Helpers\FileHelper;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

class MinifyCssHandler
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var string
     */
    private static string $md5checksum = "";

    /**
     * @var string
     */
    private $baseDir = "";

    /**
     * @var array
     */
    private $defaultCssPaths = [];

    /**
     * @var string
     */
    private $defaultMinifyCssDir = "";

    /**
     * @var string
     */
    private string $defaultMinifyCssFile = "";

    /**
     * @var array
     */
    private array $cssContent = [];

    /**
     * @var array
     */
    private array $filter = [
        "ImportImports" => true,
        "RemoveComments" => true,
        "RemoveEmptyRulesets" => true,
        "RemoveEmptyAtBlocks" => true,
        "ConvertLevel3Properties" => false,
        "ConvertLevel3AtKeyframes" => false,
        "Variables" => true,
        "RemoveLastDelarationSemiColon" => true
    ];

    /**
     * MinifyCssHandler constructor.
     * @param ConfigValues $config
     */
    private final function __construct(ConfigValues $config)
    {
        $this->baseDir = $config->get("base_dir");
        $this->defaultCssPaths = $config->get("default_css", []);
        $this->defaultMinifyCssDir = sprintf("%s/data/cache/css", $this->baseDir);

        FileHelper::init($this->defaultMinifyCssDir, MinifyCssException::class)
            ->isWritable(true);

        /**
         * Check and create directory restriction
         */
        DirHelper::init($this->defaultMinifyCssDir)->addDirectoryRestriction(["css"]);
    }

    /**
     *
     */
    private function setDefaults()
    {
        if(empty($this->defaultCssPaths)){
            return;
        }

        foreach ($this->defaultCssPaths as $cssPath) {
            $this->addCss($cssPath);
        }
    }

    /**
     * @param ConfigValues $config
     * @return MinifyCssHandler|null
     */
    public static final function init(ConfigValues $config)
    {
        if (is_null(self::$instance) || serialize($config) !== self::$instanceKey) {
            self::$instance = new self($config);
            self::$instanceKey = serialize($config);
        }

        self::$instance->setDefaults();
        return self::$instance;
    }

    /**
     * @param bool $clearOldFiles
     * @return bool|int
     * @throws MinifyCssException
     */
    public final function compile($clearOldFiles = true)
    {
        if(empty($this->cssContent)){
            return false;
        }

        $this->defaultMinifyCssFile = sprintf("%s/%s.css", $this->defaultMinifyCssDir, md5(self::$md5checksum));

        if ($clearOldFiles) {
            $oldDate = time() - 3600;
            $cachedFiles = scandir($this->defaultMinifyCssDir);
            foreach ($cachedFiles as $file) {
                $filepath = sprintf("%s/%s", $this->defaultMinifyCssDir, $file);
                $fileMtime = @filemtime($filepath);
                if (strlen($file) == 35 && ($fileMtime === false || $fileMtime < $oldDate)) {
                    @unlink($filepath);
                }
            }
        }

        if (!file_exists($this->getDefaultMinifyCssFile())) {
            $content = "";
            foreach ($this->cssContent as $item) {
                $content .= strlen($item) < 999 && is_file($item) ? file_get_contents($item) : trim($item);
            }

            try {
                return @file_put_contents($this->getDefaultMinifyCssFile(), CssMin::minify($content, $this->getFilter()));
            } catch (Exception $e) {
                throw new MinifyCssException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return true;
    }

    /**
     * @param bool $relative
     * @return string|null
     */
    public final function getDefaultMinifyCssFile($relative = false): ?string
    {
        return $relative ? substr(str_replace($this->baseDir, "", $this->defaultMinifyCssFile), 1) : $this->defaultMinifyCssFile;
    }

    /**
     * @param string|null $fileOrString
     * @param bool $codeAsString
     */
    public final function addCss(?string $fileOrString, $codeAsString = false): void
    {
        if (is_null($fileOrString)) {
            return;
        }

        if ($codeAsString || strcasecmp(substr($fileOrString, -4), ".css") != 0) {
            self::$md5checksum .= trim(md5($fileOrString));
        } elseif (strcasecmp(substr($fileOrString, 0, 4), "http") == 0) {
            $fileOrString = @file_get_contents($fileOrString);
            self::$md5checksum .= trim(md5($fileOrString));
        } else {
            FileHelper::init($fileOrString, MinifyCssException::class)->isReadable();
            $fileMtime = @filemtime($fileOrString);
            self::$md5checksum .= date('YmdHis', $fileMtime ? $fileMtime : NULL) . $fileOrString;
        }

        $this->cssContent[] = $fileOrString;
    }

    /**
     * @param array $cssContent
     */
    public final function setCssContent(array $cssContent): void
    {
        $this->cssContent = [];

        foreach ($cssContent as $item) {
            $this->addCss($item);
        }
    }

    /**
     * @param array $filter
     */
    public function setFilter(array $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
    }
}