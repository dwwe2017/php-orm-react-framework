<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Services;


use Interfaces\ServiceInterfaces\VendorExtensionServiceInterface;
use Managers\ModuleManager;
use Traits\ServiceTraits\VendorExtensionInitServiceTraits;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use Twig_Extensions_Extension_I18n;

/**
 * Class TemplateService
 * @package Services
 */
class TemplateService implements VendorExtensionServiceInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitServiceTraits;

    /**
     * @var FilesystemLoader
     */
    private $loader;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var string
     */
    private $assetBaseDir = "";

    /**
     * TemplateService constructor.
     * @see ServiceManager::__construct()
     * @param ModuleManager $moduleManager
     */
    public final function __construct(ModuleManager $moduleManager)
    {
        $config = $moduleManager->getConfig();
        $baseDir = $config->get("base_dir");

        $this->assetBaseDir = !$moduleManager->isModule() ? "assets/"
            : str_replace(
                sprintf("%s/", $baseDir), "",
                sprintf("%s/assets/", $moduleManager->getModuleBaseDir())
            );

        $moduleTplDir = !$moduleManager->isModule() ? null
            : sprintf("modules/%s/views", $moduleManager->getModuleShortName());

        $mainTplDir = sprintf("templates/Controllers/%s", $config->get("template_options.template"));

        $filesystemLoaderPaths = is_null($moduleTplDir) ? ["views", $mainTplDir]
            : [$moduleTplDir, "views", $mainTplDir];

        $this->loader = new FilesystemLoader($filesystemLoaderPaths, $baseDir);

        $envOptions = $config->get("template_options", []);
        $this->environment = new Environment($this->loader, $envOptions);
        $this->environment->addExtension(new Twig_Extensions_Extension_I18n());
    }

    /**
     * @return Environment
     */
    public final function getEnvironment(): Environment
    {
        $this->environment->addFunction(new TwigFunction("sha1", function (string $string){
            return sha1($string);
        }));

        $this->environment->addFunction(new TwigFunction("md5", function (string $string){
            return md5($string);
        }));

        $this->environment->addFunction(new TwigFunction("__", function (string $original){
            return __($original);
        }));

        $this->environment->addFunction(new TwigFunction("n__", function (string $original, string $plural, string $value){
            return n__($original, $plural, $value);
        }));

        $this->environment->addFunction(new TwigFunction("asset", function (string $file){
            return sprintf("%s%s", $this->assetBaseDir, $file);
        }));

        return $this->environment;
    }
}