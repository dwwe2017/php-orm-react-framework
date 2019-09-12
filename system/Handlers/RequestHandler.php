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
use Dflydev\DotAccessData\Util;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;
use Whoops\Util\Misc;

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
     * @var bool
     */
    private $xmlRequest = false;

    /**
     * RequestHandler constructor.
     */
    public function __construct()
    {
        $this->headers = ConfigFactory::fromArray(getallheaders() ?? []);
        $this->request = ConfigFactory::fromArray($_REQUEST ?? []);
        $this->post = ConfigFactory::fromArray($_POST ?? []);
        $this->query = ConfigFactory::fromArray($_GET ?? []);
        $this->server = ConfigFactory::fromArray($_SERVER ?? []);
        $this->xmlRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     *
     */
    public static function init()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
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
}