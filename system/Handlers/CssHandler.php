<?php

namespace Handlers;

use Configula\ConfigValues;
use Exceptions\CssException;
use Exceptions\MinifyJsException;
use Helpers\FileHelper;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

class CssHandler
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var array
     */
    private array $defaultNonMinifiedCss;

    /**
     * @var array
     */
    private array $nonMinifiedCss = [];

    /**
     * @var array
     */
    private array $defaultCdnCss;

    /**
     * @var array
     */
    private array $cdnCss = [];

    /**
     * @param ConfigValues $config
     */
    private final function __construct(ConfigValues $config)
    {
        $this->defaultNonMinifiedCss = $config->get("default_non_minified_css", []);
        $this->defaultCdnCss = $config->get("default_cdn_css", []);
    }

    /**
     * @param ConfigValues $config
     * @return CssHandler
     */
    public static final function init(ConfigValues $config): CssHandler
    {
        if (is_null(self::$instance) || serialize($config) !== self::$instanceKey) {
            self::$instance = new self($config);
            self::$instanceKey = serialize($config);
        }

        self::$instance->setDefaults();
        return self::$instance;
    }

    /**
     *
     */
    private function setDefaults()
    {
        $this->cdnCss = $this->defaultCdnCss;
        $this->defaultNonMinifiedCss = $this->nonMinifiedCss;
    }

    /**
     * @param string $href
     * @param string $integrity
     * @param string $crossorigin
     */
    public function addCdnCss(string $href, string $integrity = "", string $crossorigin = "")
    {
        $this->cdnCss[] = [
            "href" => $href,
            "integrity" => $integrity,
            "crossorigin" => $crossorigin
        ];
    }

    /**
     * @param string $file
     */
    public function addNonMinifiedCss(string $file)
    {
        FileHelper::init($file, CssException::class)->isReadable();
        $this->nonMinifiedCss[] = $file;
    }

    /**
     * @return array
     */
    public function getCdnCss(): array
    {
        return $this->cdnCss;
    }

    /**
     * @return array
     */
    public function getDefaultCdnCss(): array
    {
        return $this->defaultCdnCss;
    }

    /**
     * @return array
     */
    public function getNonMinifiedCss(): array
    {
        return $this->nonMinifiedCss;
    }

    /**
     * @return array
     */
    public function getDefaultNonMinifiedCss(): array
    {
        return $this->defaultNonMinifiedCss;
    }

    /**
     * @param array $cdnCss
     */
    public function setCdnCss(array $cdnCss): void
    {
        $this->cdnCss = $cdnCss;
    }

    /**
     * @param array $nonMinifiedCss
     */
    public function setNonMinifiedCss(array $nonMinifiedCss): void
    {
        $this->nonMinifiedCss = $nonMinifiedCss;
    }
}