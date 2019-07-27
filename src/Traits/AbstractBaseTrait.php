<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Traits;


use Core\Bootstrap;
use Doctrine\ORM\EntityManager;
use View\Environment;
use View\Loader\FilesystemLoader;

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
     * @var Bootstrap
     */
    private $config;

    /**
     * @var array
     */
    private $context = [];

    /**
     * @var string
     */
    private $template = "";

    /**
     * @var
     */
    private $twig;

    /**
     * @var string
     */
    private $connectionOption = "default";

    /**
     * @var \Doctrine\Bootstrap|null
     */
    private $doctrine = null;

    /**
     * @var EntityManager|null
     */
    private $entityManager = null;

    /**
     * @return \Doctrine\Bootstrap|null
     */
    protected function getDoctrine(): ?\Doctrine\Bootstrap
    {
        return $this->doctrine;
    }

    /**
     * @return string
     */
    protected function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @return Bootstrap
     */
    protected function getConfig(): Bootstrap
    {
        return $this->config;
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
     */
    protected function setTemplate(string $template): void
    {
        $controller = $this->getControllerShortName();

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