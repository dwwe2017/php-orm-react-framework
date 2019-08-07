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
use Interfaces\ConfigInterfaces\TemplateConfigInterface;
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
class TemplateConfig implements TemplateConfigInterface
{
    /**
     * @var TemplateConfig|null
     */
    public static $instance;

    /**
     * @var FilesystemLoader|null
     */
    private $loader;

    /**
     * @var Environment|null
     */
    private $twig;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var string
     */
    private $template = "default";

    /**
     * @var TemplateWrapper
     */
    private $templateWrapper;

    /**
     * TemplateConfig constructor.
     * @param DefaultConfig $config
     * @param string|null $moduleViewsDir
     * @param string $defaultTemplatesDir
     * @param string $defaultViewsDir
     * @throws TemplateException
     */
    public function __construct(DefaultConfig $config, ?string $moduleViewsDir = null, string $defaultTemplatesDir = "templates/Controllers", string $defaultViewsDir = "views")
    {
        $baseDir = $config->getBaseDir();

        $debugMode = $config->isDebugMode();

        $this->options = $config->getProperties("template_options");

        $this->template = $config->getTsiOptionsProperty("template");

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

        $defaultViewsDir = !is_null($defaultViewsDir) ? $defaultViewsDir : "views";
        $defaultTemplatesDir = sprintf("%s/%s", !is_null($defaultTemplatesDir)
            ? $defaultTemplatesDir : "templates", $this->template);

        $filesystemLoaderPaths = empty($moduleViewsDir) ? [$defaultViewsDir, $defaultTemplatesDir]
            : [$moduleViewsDir, $defaultViewsDir, $defaultTemplatesDir];

        $this->loader = new FilesystemLoader($filesystemLoaderPaths, $baseDir);

        $this->twig = new Environment($this->loader, $this->options);
    }

    /**
     * @param DefaultConfig $config
     * @param string|null $moduleViewsDir
     * @param string $defaultTemplatesDir
     * @param string $defaultViewsDir
     * @return TemplateConfig|null
     * @throws TemplateException
     */
    public static function init(DefaultConfig $config, ?string $moduleViewsDir = "modules", string $defaultTemplatesDir = "templates/Controllers", string $defaultViewsDir = "views")
    {
        if (self::$instance == null) {
            self::$instance = new TemplateConfig($config, $moduleViewsDir, $defaultTemplatesDir, $defaultViewsDir);
        }

        return self::$instance;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
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