<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Configs;


use Configula\ConfigFactory;
use Interfaces\ConfigInterfaces\VendorExtensionConfigInterface;
use Services\TemplateService;
use Traits\ConfigTraits\VendorExtensionInitConfigTrait;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class TemplateConfig
 * @package Configs Revised and added options of the configuration file
 * @see ModuleManager::$portalConfig
 */
class PortalConfig implements VendorExtensionConfigInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitConfigTrait;

    /**
     * PortalConfig constructor.
     * @param DefaultConfig $defaultConfig
     * @see TemplateService::__construct()
     */
    public final function __construct(DefaultConfig $defaultConfig)
    {
        $this->config = $defaultConfig->getConfigValues();

        /**
         * Build portal options
         */
        $portalConfig = ["portal_options" => $this->config->get("portal_options", [])];
        $portalConfig = ConfigFactory::fromArray($this->getOptionsDefault())->mergeValues($portalConfig);

        /**
         * Finished
         */
        $this->configValues = $portalConfig;
    }

    /**
     * @return array
     */
    public final function getOptionsDefault(): array
    {
        $isDebug = $this->config->get("debug_mode");
        $htmlLang = strtolower(substr($this->config->get("language"), 0, 2));

        return [
            "portal_options" => [
                //DOM HTML attribute
                "s_html_lang" => $htmlLang,
                //Default or dark layout
                "b_dark_layout" => false,
                //Any visitor can register themselves
                "b_allow_registration" => false,
                //When logging in, users can select the option to remain permanently logged on
                "b_allow_stay_logged_in" => true,
            ]
        ];
    }
}