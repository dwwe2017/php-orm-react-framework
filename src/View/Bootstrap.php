<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace View;


use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Class Bootstrap
 * @package Bootstrap
 */
class Bootstrap
{
    /**
     * @var Bootstrap|null
     */
    public static $instance;

    /**
     * @var string
     */
    protected $baseDir = "";

    /**
     * @var bool
     */
    protected $debugMode = false;

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
     * Bootstrap constructor.
     * @param \Core\Bootstrap $config
     * @param string $templates_root
     */
    public function __construct(\Core\Bootstrap $config, string $templates_root = "templates")
    {
        $this->baseDir = $config->getBaseDir();

        $this->debugMode = $config->isDebugMode();

        $this->options = $config->getProperties("template_options");

        $defaultTemplateCompilationPath = sprintf("%s/data/cache/compilation", $this->baseDir);

        $defaultTemplateCompilationOptions = array(
            "debug" => $this->debugMode,
            "charset " => "utf-8",
            "base_template_class" => "\\Twig\\Template",
            "cache" => $defaultTemplateCompilationPath,
            "auto_reload" => !$this->debugMode,
            "strict_variables" => !$this->debugMode,
            "autoescape" => "html",
            "optimizations" => $this->debugMode ? -1 : 0,
        );

        $this->options += $defaultTemplateCompilationOptions;

        $this->loader = new FilesystemLoader(sprintf("%s/%s", $this->baseDir, $templates_root));

        $this->twig = new Environment($this->loader, $this->options);
    }

    /**
     * @param \Core\Bootstrap $config
     * @param string $templates_root
     * @return Bootstrap|null
     */
    public static function init(\Core\Bootstrap $config, string $templates_root = "templates")
    {
        if (self::$instance == null) {
            self::$instance = new Bootstrap($config, $templates_root);
        }

        return self::$instance;
    }
}