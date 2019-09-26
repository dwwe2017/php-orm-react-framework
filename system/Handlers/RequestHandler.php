<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Handlers;


use Configula\ConfigFactory;
use Configula\ConfigValues;
use Controllers\AbstractBase;
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
     * @var ConfigValues
     */
    private $headers;

    /**
     * @var ConfigValues
     */
    private $request;

    /**
     * @var ConfigValues
     */
    private $post;

    /**
     * @var ConfigValues
     */
    private $query;

    /**
     * @var ConfigValues
     */
    private $server;

    /**
     * @var string|null
     */
    private $requestUrl;

    /**
     * @var bool
     */
    private $xmlRequest = false;

    /**
     * @var bool
     */
    private $xml = false;

    /**
     * RequestHandler constructor.
     * @param AbstractBase $controllerInstance
     */
    public function __construct(AbstractBase $controllerInstance)
    {
        $this->headers = ConfigFactory::fromArray(getallheaders() ?? []);
        $this->request = ConfigFactory::fromArray($_REQUEST ?? []);
        $this->post = ConfigFactory::fromArray($_POST ?? []);
        $this->query = ConfigFactory::fromArray($_GET ?? []);
        $this->server = ConfigFactory::fromArray($_SERVER ?? []);

        $this->requestUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI]";

        $this->xmlRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        /**
         * @see RequestHandler::isXml()
         */
        $this->xml = $controllerInstance instanceof RestrictedXmlController
            || $controllerInstance instanceof PublicXmlController
            || $this->isXmlRequest();
    }

    /**
     * @param AbstractBase $controllerInstance
     * @return RequestHandler|null
     */
    public static function init(AbstractBase $controllerInstance)
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
    public function isPost()
    {
        return $this->post->count() > 0;
    }

    /**
     * @return ConfigValues
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return ConfigValues
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @return ConfigValues
     */
    public function getQuery()
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
     * @param null $default
     */
    public function doRedirect($default = null): void
    {
        if(($target = $this->getRequest()->get("redirect", $default))){
            header("Location: " . $target);
            exit();
        }
    }
}