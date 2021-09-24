<?php
/**
 * MIT License
 *
 * Copyright (c) 2020 DW Web-Engineering
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Handlers;


use Configula\ConfigFactory;
use Configula\ConfigValues;
use Controllers\AbstractBase;
use Controllers\ApiController;
use Controllers\PublicXmlController;
use Controllers\RestrictedXmlController;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class RequestHandler
 * @package Handlers
 */
class RequestHandler
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var AbstractBase
     */
    private AbstractBase $controllerInstance;

    /**
     * @var ConfigValues
     */
    private ConfigValues $headers;

    /**
     * @var ConfigValues
     */
    private ConfigValues $request;

    /**
     * @var ConfigValues
     */
    private ConfigValues $post;

    /**
     * @var ConfigValues
     */
    private ConfigValues $query;

    /**
     * @var ConfigValues
     */
    private ConfigValues $server;

    /**
     * @var ConfigValues
     */
    private ConfigValues $axios;

    /**
     * @var string|null
     */
    private ?string $requestUrl;

    /**
     * @var bool
     */
    private bool $xmlRequest;

    /**
     * @var bool
     */
    private bool $xml;

    /**
     * @var bool
     */
    private bool $api;

    /**
     * @var string
     */
    private string $baseUrl;

    /**
     * RequestHandler constructor.
     * @param AbstractBase $controllerInstance
     */
    public function __construct(AbstractBase $controllerInstance)
    {
        $this->controllerInstance = $controllerInstance;

        $this->headers = ConfigFactory::fromArray(getallheaders() ?? []);
        $this->request = ConfigFactory::fromArray($_REQUEST ?? []);
        $this->post = ConfigFactory::fromArray($_POST ?? []);
        $this->query = ConfigFactory::fromArray($_GET ?? []);
        $this->server = ConfigFactory::fromArray($_SERVER ?? []);

        /**
         * @see https://www.quora.com/How-do-I-post-form-data-to-a-PHP-script-using-Axios
         */
        $raw_input = @file_get_contents("php://input");
        $raw_array = @json_decode($raw_input, true);
        $this->axios = ConfigFactory::fromArray(is_array($raw_array) ? $raw_array : []);

        $this->requestUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
        $this->baseUrl = ($split = explode("/index.php", $_SERVER["REQUEST_URI"])) > 1 ? $split[0] : $_SERVER["REQUEST_URI"];

        if ($this->query->get("module", null)) {
            $query = $this->query->get("module");
            $query = strpos($query, "/") ? explode("/", $query)[0] : $query;
            $this->baseUrl .= "/index.php?module=" . $query;
        }

        if ($this->query->get("controller", null)) {
            $query = $this->query->get("controller");
            $query = strpos($query, "/") ? explode("/", $query)[0] : $query;
            $this->baseUrl .= "&controller=" . $query;
        }

        if ($this->query->get("action", null)) {
            $query = $this->query->get("action");
            $query = strpos($query, "/") ? explode("/", $query)[0] : $query;
            $this->baseUrl .= "&action=" . $query;
        }

        $this->xmlRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        /**
         * @see RequestHandler::isXml()
         */
        $this->xml = $this->controllerInstance instanceof RestrictedXmlController
            || $this->controllerInstance instanceof PublicXmlController
            || $this->isXmlRequest();

        $this->api = $this->isXml() && $this->controllerInstance instanceof ApiController;
    }

    /**
     * @param AbstractBase $controllerInstance
     * @return RequestHandler|null
     */
    public static function init(AbstractBase $controllerInstance): ?RequestHandler
    {
        if (is_null(self::$instance) || serialize(get_class($controllerInstance)) !== self::$instanceKey) {
            self::$instance = new self($controllerInstance);
            self::$instanceKey = serialize(get_class($controllerInstance));
        }

        return self::$instance;
    }

    /**
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->post->count() > 0;
    }

    /**
     * @return ConfigValues
     */
    public function getRequest(): ConfigValues
    {
        return $this->request;
    }

    /**
     * @return ConfigValues
     */
    public function getPost(): ConfigValues
    {
        return $this->post;
    }

    /**
     * @return ConfigValues
     */
    public function getAxios(): ConfigValues
    {
        return $this->axios;
    }

    /**
     * @return ConfigValues
     */
    public function getQuery(): ConfigValues
    {
        return $this->query;
    }

    /**
     * @return ConfigValues
     */
    public function getHeaders(): ConfigValues
    {
        return $this->headers;
    }

    /**
     * @return ConfigValues
     */
    public function getServer(): ConfigValues
    {
        return $this->server;
    }

    /**
     * @return bool
     */
    public function isXmlRequest(): bool
    {
        return $this->xmlRequest;
    }

    /**
     * @return string|null
     */
    public function getRequestUrl(): ?string
    {
        return $this->requestUrl;
    }

    /**
     * In contrast to isXmlRequest, it also checks whether it is currently the call of an XmlController
     * @return bool
     */
    public function isXml(): bool
    {
        return $this->xml;
    }

    /**
     * @param string|null $default
     */
    public function doRedirect(?string $default = null): void
    {
        $target = $this->getRequest()->get("redirect", $default)
            ?? $this->controllerInstance->renderEntry();

        if ($target) {
            header("Location: " . trim($target));
            exit();
        }
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @return bool
     */
    public function isApi(): bool
    {
        return $this->api;
    }
}
