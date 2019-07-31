<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Configs;


use Exceptions\TemplateException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\TemplateWrapper;

/**
 * Class TemplateConfig
 * @package Configs
 */
class TemplateConfig
{
    /**
     * @var TemplateConfig|null
     */
    public static $instance;

    /**
     * @var FilesystemLoader|null
     */
    protected $loader;

    /**
     * @var Environment|null
     */
    protected $twig;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var TemplateWrapper
     */
    protected $templateWrapper;

    /**
     * TemplateConfig constructor.
     * @param CoreConfig $config
     * @param string $templates_root
     * @param string $base_template
     * @throws TemplateException
     */
    public function __construct(CoreConfig $config, string $templates_root = "templates")
    {
        $baseDir = $config->getBaseDir();

        $debugMode = $config->isDebugMode();

        $this->options = $config->getProperties("template_options");

        $defaultTemplateCompilationPath = sprintf("%s/data/cache/compilation", $baseDir);

        $defaultTemplateCompilationOptions = array(
            "debug" => $debugMode,
            "charset " => "utf-8",
            "base_template_class" => "\\Twig\\Template",
            "cache" => $debugMode ? false : $defaultTemplateCompilationPath,
            "auto_reload" => $debugMode,
            "strict_variables" => !$debugMode,
            "autoescape" => "html",
            "optimizations" => $debugMode ? -1 : 0,
        );

        $this->options += $defaultTemplateCompilationOptions;

        $templateCompilationPath = $this->options["cache"];

        if($debugMode !== true && !file_exists($templateCompilationPath))
        {
            if(!@mkdir($templateCompilationPath, 0777, true))
            {
                throw new TemplateException(sprintf("The required directory '%s' for template compilation can not be found and/or be created, please check the directory permissions or create it manually.", $templateCompilationPath), E_ERROR);
            }
        }

        if($debugMode !== true && !is_writable($templateCompilationPath))
        {
            if(!@chmod($templateCompilationPath, 0777))
            {
                throw new TemplateException(sprintf("The required directory '%s' for template compilation can not be written, please check the directory permissions.", $templateCompilationPath), E_ERROR);
            }
        }

        $this->loader = new FilesystemLoader($templates_root, $baseDir);

        $this->twig = new Environment($this->loader, $this->options);
    }

    /**
     * @param CoreConfig $config
     * @param string $templates_root
     * @param string $base_template
     * @return TemplateConfig|null
     * @throws TemplateException
     */
    public static function init(CoreConfig $config, string $templates_root = "templates")
    {
        if (self::$instance == null) {
            self::$instance = new TemplateConfig($config, $templates_root);
        }

        return self::$instance;
    }

    /**
     * @return Environment|null
     */
    public function getTwig(): ?Environment
    {
        return $this->twig;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return FilesystemLoader|null
     */
    public function getLoader(): ?FilesystemLoader
    {
        return $this->loader;
    }

    /**
     * @param string $baseTemplate
     */
    public function setBaseTemplate(string $baseTemplate): void
    {
        $this->baseTemplate = $baseTemplate;
    }

    /**
     * @param string $base_template
     * @return TemplateWrapper
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getTemplateWrapper($base_template = "layout.default.tpl.twig"): TemplateWrapper
    {
        $this->templateWrapper = $this->twig->load($base_template);

        return $this->templateWrapper;
    }
}