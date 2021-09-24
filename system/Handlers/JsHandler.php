<?php

namespace Handlers;

use Configula\ConfigValues;
use Exceptions\CssException;
use Exceptions\JsException;
use Helpers\FileHelper;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

class JsHandler
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var array
     */
    private array $defaultNonMinifiedJs;

    /**
     * @var array
     */
    private array $nonMinifiedJs = [];

    /**
     * @var array
     */
    private array $defaultCdnJs;

    /**
     * @var array
     */
    private array $cdnJs = [];

    /**
     * @param ConfigValues $config
     */
    private final function __construct(ConfigValues $config)
    {
        $this->defaultNonMinifiedJs = $config->get("default_non_minified_js", []);
        $this->defaultCdnJs = $config->get("default_cdn_js", []);
    }

    /**
     * @param ConfigValues $config
     * @return JsHandler
     */
    public static final function init(ConfigValues $config): JsHandler
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
        $this->cdnJs = $this->defaultCdnJs;
        $this->defaultNonMinifiedJs = $this->nonMinifiedJs;
    }

    /**
     * @param string $href
     * @param string $integrity
     * @param string $crossorigin
     */
    public function addCdnJs(string $href, string $integrity = "", string $crossorigin = "")
    {
        $this->cdnJs[] = [
            "href" => $href,
            "integrity" => $integrity,
            "crossorigin" => $crossorigin
        ];
    }

    /**
     * @param string $file
     */
    public function addNonMinifiedJs(string $file)
    {
        FileHelper::init($file, JsException::class)->isReadable();
        $this->nonMinifiedJs[] = $file;
    }

    /**
     * @return array
     */
    public function getCdnJs(): array
    {
        return $this->cdnJs;
    }

    /**
     * @return array
     */
    public function getDefaultCdnJs(): array
    {
        return $this->defaultCdnJs;
    }

    /**
     * @return array
     */
    public function getNonMinifiedJs(): array
    {
        return $this->nonMinifiedJs;
    }

    /**
     * @return array
     */
    public function getDefaultNonMinifiedJs(): array
    {
        return $this->defaultNonMinifiedJs;
    }

    /**
     * @param array $cdnJs
     */
    public function setCdnJs(array $cdnJs): void
    {
        $this->cdnJs = $cdnJs;
    }

    /**
     * @param array $nonMinifiedJs
     */
    public function setNonMinifiedJs(array $nonMinifiedJs): void
    {
        $this->nonMinifiedJs = $nonMinifiedJs;
    }
}