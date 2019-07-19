<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Traits;


use Doctrine\ORM\EntityManager;
use Webmasters\Doctrine\Bootstrap;

/**
 * Trait AbstractBaseTrait
 * @package Traits
 */
trait AbstractBaseTrait
{
    /**
     * @var string
     */
    private $basePath = "";

    /**
     * @var array
     */
    private $context = [];

    /**
     * @var string
     */
    private $template = "";

    /**
     * @var string
     */
    private $connectionOption = "default";

    /**
     * @var Bootstrap|null
     */
    private $bootstrap = null;

    /**
     * @var EntityManager|null
     */
    private $entityManager = null;

    /**
     * @return Bootstrap|null
     */
    protected function getBootstrap(): ?Bootstrap
    {
        return $this->bootstrap;
    }

    /**
     * @return string
     */
    protected function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @return string
     */
    protected function getConnectionOption(): string
    {
        return $this->connectionOption;
    }

    /**
     * @param string $connectionOption
     */
    protected function setConnectionOption(string $connectionOption): void
    {
        $this->connectionOption = $connectionOption;
    }

    /**
     * @return EntityManager|null
     */
    protected function getEntityManager(): ?EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @return string|null
     */
    protected function getControllerShortName(): ?string
    {
        $className = get_class($this); // i.e. Controllers\IndexController or Controllers\Backend\IndexController

        return preg_replace('/^([A-Za-z]+\\\)+/', '', $className); // i.e. IndexController
    }

    /**
     * @param string $template
     * @param string|null $controller
     */
    protected function setTemplate(string $template, ?string $controller = null): void
    {
        if (empty($controller)) {
            $controller = $this->getControllerShortName();
        }

        $this->template = $controller . '/' . $template . '.tpl.php';
    }

    /**
     * @return string|null
     */
    protected function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @param $key
     * @param $value
     */
    protected function addContext($key, $value): void
    {
        $this->context[$key] = $value;
    }

    /**
     * @param $message
     */
    protected function setMessage(string $message): void
    {
        $_SESSION['message'] = $message; // Set flash message
    }

    /**
     * @return string|null
     */
    protected function getMessage(): ?string
    {
        $message = null;

        if (isset($_SESSION['message']))
        {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        return $message;
    }
}