<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Services;


use Configula\ConfigValues;
use Controllers\AbstractBase;
use Interfaces\ServiceInterfaces\VendorExtensionServiceInterface;
use Traits\ServiceTraits\VendorExtensionInitServiceTraits;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

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
     * TemplateService constructor.
     * @param ConfigValues $config
     * @param AbstractBase|null $controllerInstance
     */
    public final function __construct(ConfigValues $config, AbstractBase $controllerInstance = null)
    {
        $baseDir = $config->get("base_dir");
        $moduleTplDir = $this->getModuleViewsDir($controllerInstance);
        $mainTplDir = sprintf("templates/Controllers/%s", $config->get("template_options.template"));

        $filesystemLoaderPaths = is_null($moduleTplDir) ? ["views", $mainTplDir]
            : [$moduleTplDir, "views", $mainTplDir];

        $this->loader = new FilesystemLoader($filesystemLoaderPaths, $baseDir);

        $envOptions = $config->get("template_options", []);
        $this->environment = new Environment($this->loader, $envOptions);
    }

    /**
     * @param AbstractBase $controllerInstance
     * @return string|null
     */
    private final function getModuleViewsDir(AbstractBase $controllerInstance)
    {
        $className = get_class($controllerInstance);
        $isModule = (strcasecmp(substr($className, 0, 7), "Modules") === 0);
        return $isModule ? sprintf("modules/%s/views", explode("\\", $className)[1]) : null;
    }

    /**
     * @return Environment
     */
    public final function getEnvironment(): Environment
    {
        return $this->environment;
    }

    /**
     * @return FilesystemLoader
     */
    public final function getLoader(): FilesystemLoader
    {
        return $this->loader;
    }
}