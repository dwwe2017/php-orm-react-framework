<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Services;


use Exceptions\LocaleException;
use Gettext\GettextTranslator;
use Helpers\DeclarationHelper;
use Helpers\FileHelper;
use Interfaces\ServiceInterfaces\VendorExtensionServiceInterface;
use Managers\ModuleManager;
use Traits\ServiceTraits\VendorExtensionInitServiceTraits;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class LocaleService
 * @package Services
 */
class LocaleService implements VendorExtensionServiceInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitServiceTraits;

    /**
     * @var string
     */
    const DOMAIN = "messages";
    
    /**
     * @var GettextTranslator
     */
    private $translator;

    /**
     * LocaleService constructor.
     * @param ModuleManager $moduleManager
     */
    public function __construct(ModuleManager $moduleManager)
    {
        DeclarationHelper::init(null, null, "gettext",
            LocaleException::class)->functionExists();

        $config = $moduleManager->getConfig();
        $baseDir = $config->get("base_dir");
        $language = $config->get("language");

        $localeDir = sprintf("%s/locale", $moduleManager->getModuleBasePath());
        if(!FileHelper::init($localeDir)->isReadable()){
            $localeDir = sprintf("%s/locale", $baseDir);
            FileHelper::init($localeDir, LocaleException::class)->isReadable();
        }

        $this->translator = new GettextTranslator();
        $this->translator->setLanguage($language);
        $this->translator->loadDomain(self::DOMAIN, $localeDir);
        $this->translator->register();
    }

    /**
     * @return GettextTranslator
     */
    public function getTranslator(): GettextTranslator
    {
        return $this->translator;
    }
}